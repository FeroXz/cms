<?php
function ensure_default_settings(PDO $pdo): void
{
    $defaults = [
        'site_title' => 'FeroxZ Reptile Center',
        'site_tagline' => 'Spezialisierte Pflege für Bartagamen und Hakennasennattern',
        'hero_intro' => 'Entdecke unsere Leidenschaft für verantwortungsvolle Haltung und Zucht.',
        'adoption_intro' => 'Diese Tiere suchen ein liebevolles Zuhause. Kontaktiere uns für mehr Informationen.',
        'footer_text' => '© ' . date('Y') . ' FeroxZ CMS — Version 3.0',
        'contact_email' => 'info@example.com'
    ];

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
