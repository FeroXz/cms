<?php

function get_gallery_images(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM gallery_images ORDER BY display_order ASC, created_at DESC');
    return $stmt->fetchAll();
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

    $stmt = $pdo->prepare('INSERT INTO gallery_images(title, caption, image_path, display_order, is_published) VALUES (:title, :caption, :image_path, :display_order, :is_published)');
    $stmt->execute([
        'title' => $title,
        'caption' => trim($data['caption'] ?? '') ?: null,
        'image_path' => $imagePath,
        'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 0,
        'is_published' => !empty($data['is_published']) ? 1 : 0,
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

    $stmt = $pdo->prepare('UPDATE gallery_images SET title = :title, caption = :caption, image_path = :image_path, display_order = :display_order, is_published = :is_published, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'title' => $title,
        'caption' => trim($data['caption'] ?? '') ?: null,
        'image_path' => trim($data['image_path'] ?? $image['image_path']) ?: $image['image_path'],
        'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : (int)$image['display_order'],
        'is_published' => !empty($data['is_published']) ? 1 : 0,
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
        ],
        [
            'title' => 'Neon Terrarium',
            'caption' => 'Abendliche Stimmung mit speziellem LED-Lichtsetup.',
            'image_path' => 'https://images.unsplash.com/photo-1546182990-dffeafbe841d?auto=format&fit=crop&w=1200&q=80',
            'display_order' => 2,
        ],
        [
            'title' => 'Hakennasenporträt',
            'caption' => 'Detailaufnahme einer Heterodon nasicus im Macro-Fokus.',
            'image_path' => 'https://images.unsplash.com/photo-1610970878458-1c0d565c4ce6?auto=format&fit=crop&w=1200&q=80',
            'display_order' => 3,
        ],
    ];

    foreach ($samples as $sample) {
        create_gallery_image($pdo, array_merge($sample, ['is_published' => 1]));
    }
}

