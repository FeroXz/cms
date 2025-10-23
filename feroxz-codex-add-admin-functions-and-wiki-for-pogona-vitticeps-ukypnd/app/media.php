<?php

const MEDIA_OWNER_TYPES = ['animal', 'news', 'wiki', 'gallery'];

function normalize_media_owner_type($value): ?string
{
    if ($value === null) {
        return null;
    }
    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        return in_array($normalized, MEDIA_OWNER_TYPES, true) ? $normalized : null;
    }
    return null;
}

function get_next_media_sort_order(PDO $pdo, ?string $ownerType, ?int $ownerId): int
{
    if ($ownerType === null) {
        $stmt = $pdo->query('SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM media WHERE owner_type IS NULL');
        $row = $stmt->fetch();
        return (int)($row['max_order'] ?? 0) + 1;
    }

    if ($ownerId === null) {
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM media WHERE owner_type = :owner_type AND owner_id IS NULL');
        $stmt->execute(['owner_type' => $ownerType]);
        $row = $stmt->fetch();
        return (int)($row['max_order'] ?? 0) + 1;
    }

    $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) AS max_order FROM media WHERE owner_type = :owner_type AND owner_id = :owner_id');
    $stmt->execute([
        'owner_type' => $ownerType,
        'owner_id' => $ownerId,
    ]);
    $row = $stmt->fetch();
    return (int)($row['max_order'] ?? 0) + 1;
}

function create_media_entry(PDO $pdo, array $data): array
{
    $ownerType = normalize_media_owner_type($data['owner_type'] ?? null);
    $ownerId = normalize_nullable_id($data['owner_id'] ?? null);
    $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : get_next_media_sort_order($pdo, $ownerType, $ownerId);

    $stmt = $pdo->prepare('INSERT INTO media (file_name, alt, width, height, size, type, owner_type, owner_id, path_original, path_thumb, path_medium, path_webp, sort_order) VALUES (:file_name, :alt, :width, :height, :size, :type, :owner_type, :owner_id, :path_original, :path_thumb, :path_medium, :path_webp, :sort_order)');
    $stmt->execute([
        'file_name' => $data['file_name'],
        'alt' => $data['alt'] ?? null,
        'width' => $data['width'] ?? null,
        'height' => $data['height'] ?? null,
        'size' => $data['size'] ?? null,
        'type' => $data['type'],
        'owner_type' => $ownerType,
        'owner_id' => $ownerId,
        'path_original' => $data['path_original'],
        'path_thumb' => $data['path_thumb'] ?? null,
        'path_medium' => $data['path_medium'] ?? null,
        'path_webp' => $data['path_webp'] ?? null,
        'sort_order' => $sortOrder,
    ]);

    $id = (int)$pdo->lastInsertId();
    return get_media_item($pdo, $id) ?? [];
}

function get_media_item(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM media WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ? format_media_row($row) : null;
}

function get_media_for_owner(PDO $pdo, string $ownerType, int $ownerId): array
{
    $stmt = $pdo->prepare('SELECT * FROM media WHERE owner_type = :owner_type AND owner_id = :owner_id ORDER BY sort_order ASC, id ASC');
    $stmt->execute([
        'owner_type' => $ownerType,
        'owner_id' => $ownerId,
    ]);
    $rows = $stmt->fetchAll();
    return array_map('format_media_row', $rows ?: []);
}

function format_media_row(array $row): array
{
    return [
        'id' => (int)$row['id'],
        'fileName' => $row['file_name'],
        'alt' => $row['alt'] ?? '',
        'width' => $row['width'] !== null ? (int)$row['width'] : null,
        'height' => $row['height'] !== null ? (int)$row['height'] : null,
        'size' => $row['size'] !== null ? (int)$row['size'] : null,
        'type' => $row['type'],
        'ownerType' => $row['owner_type'] ?? null,
        'ownerId' => $row['owner_id'] !== null ? (int)$row['owner_id'] : null,
        'order' => (int)($row['sort_order'] ?? 0),
        'paths' => [
            'original' => $row['path_original'],
            'medium' => $row['path_medium'] ?? null,
            'thumb' => $row['path_thumb'] ?? null,
            'webp' => $row['path_webp'] ?? null,
        ],
        'urls' => [
            'original' => media_public_url($row['path_original'] ?? ''),
            'medium' => media_public_url($row['path_medium'] ?? ''),
            'thumb' => media_public_url($row['path_thumb'] ?? ''),
            'webp' => media_public_url($row['path_webp'] ?? ''),
        ],
    ];
}

function media_public_url(?string $path): ?string
{
    if (!$path) {
        return null;
    }
    $base = rtrim(BASE_URL, '/');
    return ($base !== '' ? $base . '/' : '/') . ltrim($path, '/');
}

function update_media_alt(PDO $pdo, int $id, string $alt): ?array
{
    $stmt = $pdo->prepare('UPDATE media SET alt = :alt, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'alt' => trim($alt) !== '' ? trim($alt) : null,
        'id' => $id,
    ]);
    return get_media_item($pdo, $id);
}

function update_media_order(PDO $pdo, string $ownerType, int $ownerId, array $orderPairs): void
{
    $pdo->beginTransaction();
    try {
        foreach ($orderPairs as $entry) {
            $id = isset($entry['id']) ? (int)$entry['id'] : 0;
            $order = isset($entry['order']) ? (int)$entry['order'] : 0;
            if ($id <= 0) {
                continue;
            }
            $stmt = $pdo->prepare('UPDATE media SET sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND owner_type = :owner_type AND owner_id = :owner_id');
            $stmt->execute([
                'sort_order' => $order,
                'id' => $id,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function reassign_media_owner(PDO $pdo, int $id, ?string $ownerType, ?int $ownerId): ?array
{
    $normalizedType = normalize_media_owner_type($ownerType);
    $stmt = $pdo->prepare('UPDATE media SET owner_type = :owner_type, owner_id = :owner_id, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'owner_type' => $normalizedType,
        'owner_id' => $ownerId,
        'id' => $id,
    ]);
    return get_media_item($pdo, $id);
}

function ensure_media_owner_allowed(?string $ownerType): bool
{
    return $ownerType === null || in_array($ownerType, MEDIA_OWNER_TYPES, true);
}
