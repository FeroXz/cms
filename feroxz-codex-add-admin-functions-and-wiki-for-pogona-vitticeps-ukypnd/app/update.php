<?php

declare(strict_types=1);

function updates_are_enabled(): bool
{
    $value = getenv('ENABLE_UPDATE');
    if ($value === false) {
        $value = getenv('APP_ENABLE_UPDATE');
    }

    if ($value === false) {
        return false;
    }

    $normalized = strtolower(trim((string)$value));
    if ($normalized === '') {
        return false;
    }

    if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }

    if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
        return false;
    }

    return filter_var($normalized, FILTER_VALIDATE_BOOLEAN) === true;
}

function app_environment(): string
{
    $env = getenv('APP_ENV');
    if ($env === false || trim($env) === '') {
        return 'production';
    }

    return strtolower(trim($env));
}

function run_update_command(string $command, string $workingDirectory): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $pipes = [];
    $process = @proc_open($command, $descriptorSpec, $pipes, $workingDirectory);
    if (!is_resource($process)) {
        return [
            'command' => $command,
            'exitCode' => -1,
            'output' => 'Befehl konnte nicht gestartet werden. Prüfe Server-Konfiguration.',
            'simulated' => false,
        ];
    }

    foreach ($pipes as $index => $pipe) {
        if ($index === 0) {
            fclose($pipe);
        }
    }

    $stdout = stream_get_contents($pipes[1] ?? null);
    $stderr = stream_get_contents($pipes[2] ?? null);

    if (isset($pipes[1])) {
        fclose($pipes[1]);
    }
    if (isset($pipes[2])) {
        fclose($pipes[2]);
    }

    $exitCode = proc_close($process);

    $output = trim((string)$stdout . (empty($stderr) ? '' : PHP_EOL . (string)$stderr));

    return [
        'command' => $command,
        'exitCode' => (int)$exitCode,
        'output' => $output === '' ? 'Kein Konsolenausgabentext verfügbar.' : $output,
        'simulated' => false,
    ];
}

function perform_system_update(PDO $pdo, string $version, string $notes = ''): array
{
    $enabled = updates_are_enabled();
    if (!$enabled) {
        return [
            'status' => 'disabled',
            'message' => 'Die Update-Funktion ist deaktiviert. Bitte ENABLE_UPDATE in der Umgebung setzen.',
        ];
    }

    $version = trim($version) !== '' ? trim($version) : 'unversioned';
    $notes = trim($notes);
    $environment = app_environment();
    $projectRoot = realpath(__DIR__ . '/..');
    $commands = [
        'git pull --rebase',
        'npx prisma migrate deploy',
        'npm run build',
    ];
    $logs = [];

    if ($environment !== 'production') {
        foreach ($commands as $command) {
            $logs[] = [
                'command' => $command,
                'exitCode' => 0,
                'output' => sprintf('Simulationsmodus (%s): Befehl nicht ausgeführt.', $environment),
                'simulated' => true,
            ];
        }

        $entry = record_changelog_entry(
            $pdo,
            $version,
            $notes !== '' ? $notes : sprintf('Simulierter Update-Lauf (%s)', date('Y-m-d H:i')), 
            $logs,
            'simulated'
        );
        set_setting($pdo, 'app_version', $version);

        return [
            'status' => 'simulated',
            'message' => 'Update im Simulationsmodus abgeschlossen. Keine Befehle ausgeführt.',
            'mode' => $environment,
            'version' => $version,
            'notes' => $notes,
            'logs' => $logs,
            'changelog' => $entry,
        ];
    }

    foreach ($commands as $command) {
        $logs[] = run_update_command($command, $projectRoot ?: getcwd());
    }

    $hasFailure = false;
    foreach ($logs as $log) {
        if ($log['exitCode'] !== 0) {
            $hasFailure = true;
            break;
        }
    }

    $status = $hasFailure ? 'failed' : 'success';
    $message = $hasFailure ? 'Update abgeschlossen – einige Schritte meldeten Fehler. Bitte Logs prüfen.' : 'Update erfolgreich abgeschlossen.';

    $entry = record_changelog_entry($pdo, $version, $notes, $logs, $status);

    if (!$hasFailure) {
        set_setting($pdo, 'app_version', $version);
    }

    return [
        'status' => $status,
        'message' => $message,
        'mode' => $environment,
        'version' => $version,
        'notes' => $notes,
        'logs' => $logs,
        'changelog' => $entry,
    ];
}

