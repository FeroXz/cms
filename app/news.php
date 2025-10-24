<?php
function create_news(PDO $pdo, array $data): int
{
    $title = trim($data['title'] ?? '');
    $slugInput = trim($data['slug'] ?? '');
    $slug = $slugInput !== '' ? $slugInput : slugify($title);
    $slug = ensure_unique_slug($pdo, 'news_posts', $slug);
    $publishedAtInput = trim($data['published_at'] ?? '');
    $publishedAt = !empty($data['is_published']) ? ($publishedAtInput !== '' ? $publishedAtInput : date('c')) : null;
    $stmt = $pdo->prepare('INSERT INTO news_posts(title, slug, excerpt, content, is_published, published_at) VALUES (:title, :slug, :excerpt, :content, :is_published, :published_at)');
    $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $data['excerpt'] ?? null,
        'content' => $data['content'],
        'is_published' => !empty($data['is_published']) ? 1 : 0,
        'published_at' => $publishedAt,
    ]);
    return (int)$pdo->lastInsertId();
}

function update_news(PDO $pdo, int $id, array $data): void
{
    $title = trim($data['title'] ?? '');
    $slugInput = trim($data['slug'] ?? '');
    $slug = $slugInput !== '' ? $slugInput : slugify($title);
    $slug = ensure_unique_slug($pdo, 'news_posts', $slug, $id);
    $publishedAtInput = trim($data['published_at'] ?? '');
    $publishedAt = !empty($data['is_published']) ? ($publishedAtInput !== '' ? $publishedAtInput : date('c')) : null;
    $stmt = $pdo->prepare('UPDATE news_posts SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, is_published = :is_published, published_at = :published_at, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $data['excerpt'] ?? null,
        'content' => $data['content'],
        'is_published' => !empty($data['is_published']) ? 1 : 0,
        'published_at' => $publishedAt,
        'id' => $id,
    ]);
}

function delete_news(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM news_posts WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function get_news(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM news_posts ORDER BY COALESCE(published_at, created_at) DESC')->fetchAll();
}

function get_news_post(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch();
    return $post ?: null;
}

function get_news_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    $post = $stmt->fetch();
    return $post ?: null;
}

function get_published_news(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM news_posts WHERE is_published = 1 ORDER BY COALESCE(published_at, created_at) DESC')->fetchAll();
}

function get_latest_published_news(PDO $pdo, int $limit = 3): array
{
    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE is_published = 1 ORDER BY COALESCE(published_at, created_at) DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}


function find_news_for_import(PDO $pdo, ?int $id, string $slug): ?array
{
    if ($id !== null) {
        $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
    }

    $slug = trim($slug);
    if ($slug === '') {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function import_news_from_csv(PDO $pdo, string $filePath, array $options = []): array
{
    $dryRun = !empty($options['dry_run']);
    $previewLimit = isset($options['preview_limit']) ? (int)$options['preview_limit'] : 10;
    $columnMap = array_change_key_case($options['column_map'] ?? [], CASE_LOWER);

    if (!is_file($filePath) || !is_readable($filePath)) {
        throw new InvalidArgumentException('CSV-Datei konnte nicht gelesen werden.');
    }

    if (($handle = fopen($filePath, 'rb')) === false) {
        throw new RuntimeException('CSV-Datei konnte nicht geöffnet werden.');
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        throw new InvalidArgumentException('CSV-Datei enthält keine Kopfzeile.');
    }

    $headerMap = [];
    foreach ($header as $index => $label) {
        $headerMap[strtolower(trim((string)$label))] = $index;
    }

    $resolveColumn = static function (string $field) use ($columnMap, $headerMap) {
        $field = strtolower($field);
        $mapped = $columnMap[$field] ?? $field;
        $mapped = strtolower(trim((string)$mapped));
        if ($mapped !== '' && array_key_exists($mapped, $headerMap)) {
            return $headerMap[$mapped];
        }
        return $headerMap[$field] ?? null;
    };

    $required = ['title', 'content'];
    $fields = ['id', 'title', 'slug', 'excerpt', 'content', 'is_published', 'published_at'];

    $summary = [
        'total' => 0,
        'valid' => 0,
        'created' => 0,
        'updated' => 0,
        'unchanged' => 0,
        'duplicates' => 0,
        'skipped' => 0,
        'errors' => [],
        'warnings' => [],
    ];
    $preview = [];
    $seenKeys = [];
    $lineNumber = 1;

    if (!$dryRun) {
        $pdo->beginTransaction();
    }

    try {
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $summary['total']++;

            $values = [];
            foreach ($fields as $field) {
                $columnIndex = $resolveColumn($field);
                $values[$field] = $columnIndex !== null ? trim((string)($row[$columnIndex] ?? '')) : '';
            }

            $missing = [];
            foreach ($required as $field) {
                if ($values[$field] === '') {
                    $missing[] = $field;
                }
            }

            if ($missing) {
                $summary['skipped']++;
                $summary['errors'][] = sprintf('Zeile %d: fehlende Pflichtfelder (%s)', $lineNumber, implode(', ', $missing));
                if (count($preview) < $previewLimit) {
                    $preview[] = [
                        'line' => $lineNumber,
                        'name' => $values['title'] ?? '',
                        'species' => '',
                        'action' => 'error',
                        'note' => 'Pflichtfelder fehlen',
                    ];
                }
                continue;
            }

            $id = $values['id'] !== '' ? (int)$values['id'] : null;
            $slug = $values['slug'] !== '' ? $values['slug'] : slugify($values['title']);
            $dedupeKey = $id !== null ? 'id:' . $id : $slug;
            if (isset($seenKeys[$dedupeKey])) {
                $summary['duplicates']++;
                if (count($preview) < $previewLimit) {
                    $preview[] = [
                        'line' => $lineNumber,
                        'name' => $values['title'],
                        'species' => '',
                        'action' => 'duplicate',
                        'note' => 'Bereits in dieser Datei verarbeitet',
                    ];
                }
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $existing = find_news_for_import($pdo, $id, $slug);

            $isPublished = interpret_boolean_field($values['is_published'], $existing ? (int)$existing['is_published'] : 0) ?? 0;
            $publishedAt = interpret_string($values['published_at'], $existing['published_at'] ?? null);
            if ($isPublished && !$publishedAt) {
                $publishedAt = $existing['published_at'] ?? date('c');
            }

            $payload = [
                'title' => $values['title'],
                'slug' => $slug,
                'excerpt' => interpret_string($values['excerpt'], $existing['excerpt'] ?? null),
                'content' => $values['content'],
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
            ];

            $action = 'create';
            if ($existing) {
                $hasChanges = false;
                foreach ($payload as $key => $value) {
                    $existingValue = $existing[$key] ?? null;
                    if ($key === 'is_published') {
                        $existingValue = (int)$existingValue;
                        $value = (int)$value;
                    }
                    if ($value !== $existingValue) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    $action = 'update';
                    $summary['updated']++;
                    $summary['valid']++;
                    if (!$dryRun) {
                        update_news($pdo, (int)$existing['id'], $payload);
                    }
                } else {
                    $action = 'unchanged';
                    $summary['unchanged']++;
                }
            } else {
                $summary['created']++;
                $summary['valid']++;
                if (!$dryRun) {
                    create_news($pdo, $payload);
                }
            }

            if (count($preview) < $previewLimit) {
                $preview[] = [
                    'line' => $lineNumber,
                    'name' => $values['title'],
                    'species' => '',
                    'action' => $action,
                    'note' => $existing ? 'Bestehender Beitrag' : 'Neu',
                ];
            }
        }

        if (!$dryRun) {
            $pdo->commit();
        }
    } catch (Throwable $exception) {
        if (!$dryRun && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fclose($handle);
        throw $exception;
    }

    fclose($handle);

    return [
        'summary' => $summary,
        'preview' => $preview,
    ];
}
