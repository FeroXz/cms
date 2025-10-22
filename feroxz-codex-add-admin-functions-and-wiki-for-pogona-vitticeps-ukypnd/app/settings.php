<?php
function ensure_default_settings(PDO $pdo): void
{
    $defaults = [
        'site_title' => 'FeroxZ Reptile Center',
        'site_tagline' => 'Spezialisierte Pflege für Bartagamen und Hakennasennattern',
        'hero_intro' => 'Entdecke unsere Leidenschaft für verantwortungsvolle Haltung und Zucht.',
        'adoption_intro' => 'Diese Tiere suchen ein liebevolles Zuhause. Kontaktiere uns für mehr Informationen.',
        'footer_text' => '© ' . date('Y') . ' FeroxZ CMS — Version 5.0',
        'contact_email' => 'info@example.com',
        'active_theme' => 'aurora',
        'home_show_hero' => '1',
        'home_show_animals' => '1',
        'home_show_adoption' => '1',
        'home_show_news' => '1',
        'home_show_care' => '1',
        'site_logo_path' => '',
        'nova_features_enabled' => '1',
        'active_layout_blueprint' => 'stellar-ribbon',
        'global_meta_description' => 'Modernes Reptilien-CMS mit modularen Inhalten, Mediathek und Erlebnis-Oberfläche.',
        'global_meta_image' => '',
        'cms_version' => '5.0',
    ];

    foreach (get_content_definitions() as $key => $definition) {
        $defaults[$key] = $definition['default'] ?? '';
    }

    foreach ($defaults as $key => $value) {
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO settings(key, value) VALUES (:key, :value)');
        $stmt->execute(['key' => $key, 'value' => $value]);
    }

    $currentFooter = get_setting($pdo, 'footer_text', '');
    if ($currentFooter !== '') {
        $updatedFooter = preg_replace('/Version\s+\d+\.\d+/', 'Version 5.0', $currentFooter);
        if ($updatedFooter !== null && $updatedFooter !== $currentFooter) {
            set_setting($pdo, 'footer_text', $updatedFooter);
        }
    }

    $storedVersion = get_setting($pdo, 'cms_version', '');
    if ($storedVersion !== '5.0') {
        set_setting($pdo, 'cms_version', '5.0');
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

function setting_enabled(array $settings, string $key, bool $default = true): bool
{
    if (!array_key_exists($key, $settings)) {
        return $default;
    }

    $value = $settings[$key];
    if (is_bool($value)) {
        return $value;
    }

    $normalized = strtolower(trim((string)$value));
    if ($normalized === '') {
        return $default;
    }

    return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
}
