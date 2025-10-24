<?php

declare(strict_types=1);

function get_import_queue_root(): string
{
    $envPath = getenv('IMPORT_QUEUE_PATH');
    if ($envPath !== false && trim($envPath) !== '') {
        return rtrim($envPath, '/');
    }

    return defined('IMPORT_QUEUE_PATH') ? IMPORT_QUEUE_PATH : __DIR__ . '/../storage/import_queue';
}

function get_import_queue_directories(): array
{
    $root = get_import_queue_root();

    return [
        'root' => $root,
        'processed' => $root . '/processed',
        'failed' => $root . '/failed',
    ];
}

function get_import_queue_types(): array
{
    $root = get_import_queue_root();

    return [
        'morphs' => [
            'label' => 'Morph-Definitionen',
            'directory' => $root . '/morphs',
            'handler' => 'import_genetic_morphs_from_csv',
        ],
        'animals' => [
            'label' => 'Tiere',
            'directory' => $root . '/animals',
            'handler' => 'import_animals_from_csv',
        ],
        'news' => [
            'label' => 'News',
            'directory' => $root . '/news',
            'handler' => 'import_news_from_csv',
        ],
        'adoptions' => [
            'label' => 'Abgabelisten',
            'directory' => $root . '/adoptions',
            'handler' => 'import_adoption_listings_from_csv',
        ],
    ];
}

function ensure_import_directories(): void
{
    $directories = get_import_queue_directories();
    $types = get_import_queue_types();

    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    foreach ($types as $type => $config) {
        $paths = [
            $config['directory'],
            $directories['processed'] . '/' . $type,
            $directories['failed'] . '/' . $type,
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }
    }
}

function record_auto_import_results(array $results): void
{
    if (!isset($_SESSION)) {
        return;
    }

    if (!empty($results)) {
        $_SESSION['auto_import_results'] = [
            'timestamp' => time(),
            'items' => $results,
        ];
    }
}

function get_last_auto_import_results(): array
{
    if (!isset($_SESSION['auto_import_results']) || !is_array($_SESSION['auto_import_results'])) {
        return [];
    }

    return $_SESSION['auto_import_results'];
}

function list_import_files(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new DirectoryIterator($directory);
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $extension = strtolower($fileInfo->getExtension());
            if ($extension === 'csv') {
                $files[] = $fileInfo->getPathname();
            }
        }
    }

    sort($files);
    return $files;
}

function move_import_file(string $source, string $targetDirectory): void
{
    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0775, true);
    }

    $filename = basename($source);
    $timestamp = date('Ymd_His');
    $destination = rtrim($targetDirectory, '/') . '/' . $timestamp . '_' . $filename;

    if (!@rename($source, $destination)) {
        @copy($source, $destination);
        @unlink($source);
    }
}

function process_queued_imports(PDO $pdo): array
{
    ensure_import_directories();
    $directories = get_import_queue_directories();
    $types = get_import_queue_types();

    $results = [];

    foreach ($types as $type => $config) {
        $files = list_import_files($config['directory']);
        if (empty($files)) {
            continue;
        }

        foreach ($files as $file) {
            $entry = [
                'type' => $type,
                'file' => basename($file),
                'status' => 'success',
                'summary' => null,
                'message' => null,
            ];

            try {
                $handler = $config['handler'];
                if (!function_exists($handler)) {
                    throw new RuntimeException(sprintf('Import-Handler "%s" ist nicht definiert.', $handler));
                }

                $result = $handler($pdo, $file, ['dry_run' => false, 'preview_limit' => 5]);
                $entry['summary'] = $result['summary'] ?? null;
                move_import_file($file, $directories['processed'] . '/' . $type);
            } catch (Throwable $exception) {
                $entry['status'] = 'failed';
                $entry['message'] = $exception->getMessage();
                move_import_file($file, $directories['failed'] . '/' . $type);
            }

            $results[] = $entry;
        }
    }

    record_auto_import_results($results);

    return $results;
}

function store_manual_import_result(string $entity, array $result): void
{
    if (!isset($_SESSION)) {
        return;
    }

    $_SESSION['manual_import_result'] = [
        'entity' => $entity,
        'dry_run' => !empty($result['dry_run']),
        'summary' => $result['summary'] ?? [],
        'preview' => $result['preview'] ?? [],
        'timestamp' => time(),
    ];
}

function consume_manual_import_result(): ?array
{
    if (!isset($_SESSION['manual_import_result'])) {
        return null;
    }

    $payload = $_SESSION['manual_import_result'];
    unset($_SESSION['manual_import_result']);
    return $payload;
}

function get_import_queue_overview(): array
{
    ensure_import_directories();
    $directories = get_import_queue_directories();
    $types = get_import_queue_types();

    $overview = [];

    foreach ($types as $type => $config) {
        $waiting = count(list_import_files($config['directory']));
        $processed = count(list_import_files($directories['processed'] . '/' . $type));
        $failed = count(list_import_files($directories['failed'] . '/' . $type));

        $overview[] = [
            'type' => $type,
            'label' => $config['label'],
            'waiting' => $waiting,
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    return [
        'root' => $directories['root'],
        'types' => $overview,
    ];
}
