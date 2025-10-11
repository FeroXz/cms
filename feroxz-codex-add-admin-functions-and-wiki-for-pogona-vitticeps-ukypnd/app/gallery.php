<?php

function get_gallery_images(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM gallery_images ORDER BY display_order ASC, created_at DESC');
    return $stmt->fetchAll();
}

function normalize_gallery_fit_mode(?string $value): string
{
    $allowed = ['cover', 'contain'];
    $value = strtolower(trim((string)$value));
    return in_array($value, $allowed, true) ? $value : 'cover';
}

function normalize_gallery_focus($value, int $fallback = 50): int
{
    if ($value === null || $value === '') {
        return $fallback;
    }

    if (is_string($value) && !is_numeric($value)) {
        return $fallback;
    }

    $normalized = (int)round((float)$value);
    return max(0, min(100, $normalized));
}

function get_gallery_image(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM gallery_images WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_published_gallery_images(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM gallery_images WHERE is_published = 1 ORDER BY display_order ASC, created_at DESC');
    return $stmt->fetchAll();
}

function create_gallery_image(PDO $pdo, array $data): int
{
    $title = trim($data['title'] ?? '');
    $imagePath = trim($data['image_path'] ?? '');
    if ($title === '' || $imagePath === '') {
        throw new InvalidArgumentException('Title and image path are required.');
    }

    $fitMode = normalize_gallery_fit_mode($data['fit_mode'] ?? 'cover');
    $focusX = normalize_gallery_focus($data['focus_x'] ?? 50);
    $focusY = normalize_gallery_focus($data['focus_y'] ?? 50);

    $stmt = $pdo->prepare('INSERT INTO gallery_images(title, caption, image_path, display_order, is_published, fit_mode, focus_x, focus_y) VALUES (:title, :caption, :image_path, :display_order, :is_published, :fit_mode, :focus_x, :focus_y)');
    $stmt->execute([
        'title' => $title,
        'caption' => trim($data['caption'] ?? '') ?: null,
        'image_path' => $imagePath,
        'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 0,
        'is_published' => !empty($data['is_published']) ? 1 : 0,
        'fit_mode' => $fitMode,
        'focus_x' => $focusX,
        'focus_y' => $focusY,
    ]);

    return (int)$pdo->lastInsertId();
}

function update_gallery_image(PDO $pdo, int $id, array $data): void
{
    $image = get_gallery_image($pdo, $id);
    if (!$image) {
        throw new InvalidArgumentException('Gallery image not found.');
    }

    $title = trim($data['title'] ?? $image['title'] ?? '');
    if ($title === '') {
        throw new InvalidArgumentException('Title is required.');
    }

    $fitMode = array_key_exists('fit_mode', $data) ? normalize_gallery_fit_mode($data['fit_mode']) : normalize_gallery_fit_mode($image['fit_mode'] ?? 'cover');
    $focusX = array_key_exists('focus_x', $data) ? normalize_gallery_focus($data['focus_x']) : normalize_gallery_focus($image['focus_x'] ?? 50);
    $focusY = array_key_exists('focus_y', $data) ? normalize_gallery_focus($data['focus_y']) : normalize_gallery_focus($image['focus_y'] ?? 50);

    $stmt = $pdo->prepare('UPDATE gallery_images SET title = :title, caption = :caption, image_path = :image_path, display_order = :display_order, is_published = :is_published, fit_mode = :fit_mode, focus_x = :focus_x, focus_y = :focus_y, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'title' => $title,
        'caption' => trim($data['caption'] ?? '') ?: null,
        'image_path' => trim($data['image_path'] ?? $image['image_path']) ?: $image['image_path'],
        'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : (int)$image['display_order'],
        'is_published' => !empty($data['is_published']) ? 1 : 0,
        'fit_mode' => $fitMode,
        'focus_x' => $focusX,
        'focus_y' => $focusY,
        'id' => $id,
    ]);
}

function delete_gallery_image(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM gallery_images WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function ensure_default_gallery_samples(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM gallery_images')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $samples = [
        [
            'title' => 'Sonnenbaden',
            'caption' => 'Pogona vitticeps im liebevoll eingerichteten Wüstenhabitat.',
            'image_path' => 'https://images.unsplash.com/photo-1612810806695-30ba0a65f3de?auto=format&fit=crop&w=1200&q=80',
            'display_order' => 1,
            'fit_mode' => 'cover',
            'focus_x' => 50,
            'focus_y' => 45,
        ],
        [
            'title' => 'Neon Terrarium',
            'caption' => 'Abendliche Stimmung mit speziellem LED-Lichtsetup.',
            'image_path' => 'https://images.unsplash.com/photo-1546182990-dffeafbe841d?auto=format&fit=crop&w=1200&q=80',
            'display_order' => 2,
            'fit_mode' => 'contain',
            'focus_x' => 50,
            'focus_y' => 50,
        ],
        [
            'title' => 'Hakennasenporträt',
            'caption' => 'Detailaufnahme einer Heterodon nasicus im Macro-Fokus.',
            'image_path' => 'https://images.unsplash.com/photo-1610970878458-1c0d565c4ce6?auto=format&fit=crop&w=1200&q=80',
            'display_order' => 3,
            'fit_mode' => 'cover',
            'focus_x' => 42,
            'focus_y' => 32,
        ],
    ];

    foreach ($samples as $sample) {
        create_gallery_image($pdo, array_merge($sample, ['is_published' => 1]));
    }
}

