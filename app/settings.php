<?php
function ensure_default_settings(PDO $pdo): void
{
    $defaults = [
        'site_title' => 'FeroxZ Reptile Center',
        'site_tagline' => 'Spezialisierte Pflege für Bartagamen und Hakennasennattern',
        'hero_intro' => 'Entdecke unsere Leidenschaft für verantwortungsvolle Haltung und Zucht.',
        'adoption_intro' => 'Diese Tiere suchen ein liebevolles Zuhause. Kontaktiere uns für mehr Informationen.',
        'footer_text' => '© ' . date('Y') . ' FeroxZ CMS — Version 3.6.0',
        'contact_email' => 'info@example.com',
        'active_theme' => 'aurora',
        'app_version' => '3.6.0',
    ];

    foreach (get_content_definitions() as $key => $definition) {
        $defaults[$key] = $definition['default'] ?? '';
    }

    foreach ($defaults as $key => $value) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES (:key, :value)');
        $stmt->execute(['key' => $key, 'value' => $value]);
    }
}

function update_settings(PDO $pdo, array $values): void
{
    foreach ($values as $key => $value) {
        set_setting($pdo, $key, $value);
    }
}

function get_all_settings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT key, value FROM settings');
    $settings = [];
    foreach ($stmt as $row) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

function record_changelog_entry(PDO $pdo, string $version, string $notes = '', array $logs = [], string $status = 'success'): array
{
    $version = trim($version) !== '' ? trim($version) : 'unversioned';
    $payload = [
        'version' => $version,
        'status' => $status,
        'notes' => $notes,
        'logs' => $logs ? json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
    ];

    $stmt = $pdo->prepare('INSERT INTO changelog_entries(version, status, notes, logs) VALUES (:version, :status, :notes, :logs)');
    $stmt->execute([
        'version' => $payload['version'],
        'status' => $payload['status'],
        'notes' => $payload['notes'],
        'logs' => $payload['logs'],
    ]);

    return get_changelog_entry($pdo, (int)$pdo->lastInsertId());
}

function get_changelog_entry(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM changelog_entries WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $entry = $stmt->fetch();
    if (!$entry) {
        return null;
    }

    if (!empty($entry['logs'])) {
        $decoded = json_decode($entry['logs'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $entry['logs'] = $decoded;
        }
    } else {
        $entry['logs'] = [];
    }

    return $entry;
}

function get_recent_changelog_entries(PDO $pdo, int $limit = 10): array
{
    $limit = max(1, $limit);
    $stmt = $pdo->prepare('SELECT * FROM changelog_entries ORDER BY datetime(created_at) DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $entries = $stmt->fetchAll();

    foreach ($entries as &$entry) {
        if (!empty($entry['logs'])) {
            $decoded = json_decode($entry['logs'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $entry['logs'] = $decoded;
            } else {
                $entry['logs'] = [];
            }
        } else {
            $entry['logs'] = [];
        }
    }

    return $entries;
}

function get_latest_changelog_entry(PDO $pdo): ?array
{
    $stmt = $pdo->query('SELECT * FROM changelog_entries ORDER BY datetime(created_at) DESC LIMIT 1');
    $entry = $stmt->fetch();
    if (!$entry) {
        return null;
    }

    if (!empty($entry['logs'])) {
        $decoded = json_decode($entry['logs'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $entry['logs'] = $decoded;
        } else {
            $entry['logs'] = [];
        }
    } else {
        $entry['logs'] = [];
    }

    return $entry;
}
