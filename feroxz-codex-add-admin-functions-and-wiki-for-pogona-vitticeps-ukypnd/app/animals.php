<?php
function create_animal(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('INSERT INTO animals(name, species, species_slug, age, genetics, genetics_profile, origin, special_notes, description, image_path, owner_id, is_private, is_showcased, is_piebald) VALUES (:name, :species, :species_slug, :age, :genetics, :genetics_profile, :origin, :special_notes, :description, :image_path, :owner_id, :is_private, :is_showcased, :is_piebald)');
    $stmt->execute([
        'name' => trim($data['name'] ?? ''),
        'species' => trim($data['species'] ?? ''),
        'species_slug' => $data['species_slug'] ?? null,
        'age' => $data['age'] ?? null,
        'genetics' => $data['genetics'] ?? null,
        'genetics_profile' => $data['genetics_profile'] ?? null,
        'origin' => $data['origin'] ?? null,
        'special_notes' => $data['special_notes'] ?? null,
        'description' => $data['description'] ?? null,
        'image_path' => $data['image_path'] ?? null,
        'owner_id' => normalize_nullable_id($data['owner_id'] ?? null),
        'is_private' => normalize_flag($data['is_private'] ?? false),
        'is_showcased' => normalize_flag($data['is_showcased'] ?? false),
        'is_piebald' => normalize_flag($data['is_piebald'] ?? false),
    ]);
}

function update_animal(PDO $pdo, int $id, array $data): void
{
    $stmt = $pdo->prepare('UPDATE animals SET name = :name, species = :species, species_slug = :species_slug, age = :age, genetics = :genetics, genetics_profile = :genetics_profile, origin = :origin, special_notes = :special_notes, description = :description, image_path = :image_path, owner_id = :owner_id, is_private = :is_private, is_showcased = :is_showcased, is_piebald = :is_piebald WHERE id = :id');
    $stmt->execute([
        'name' => trim($data['name'] ?? ''),
        'species' => trim($data['species'] ?? ''),
        'species_slug' => $data['species_slug'] ?? null,
        'age' => $data['age'] ?? null,
        'genetics' => $data['genetics'] ?? null,
        'genetics_profile' => $data['genetics_profile'] ?? null,
        'origin' => $data['origin'] ?? null,
        'special_notes' => $data['special_notes'] ?? null,
        'description' => $data['description'] ?? null,
        'image_path' => $data['image_path'] ?? null,
        'owner_id' => normalize_nullable_id($data['owner_id'] ?? null),
        'is_private' => normalize_flag($data['is_private'] ?? false),
        'is_showcased' => normalize_flag($data['is_showcased'] ?? false),
        'is_piebald' => normalize_flag($data['is_piebald'] ?? false),
        'id' => $id
    ]);
}

function delete_animal(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM animals WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function get_animal(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM animals WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $animal = $stmt->fetch();
    return $animal ?: null;
}

function get_animals(PDO $pdo): array
{
    return $pdo->query('SELECT animals.*, users.username as owner_name FROM animals LEFT JOIN users ON users.id = animals.owner_id ORDER BY created_at DESC')->fetchAll();
}

function get_showcased_animals(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM animals WHERE is_showcased = 1 AND (is_private = 0) ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function get_user_animals(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT * FROM animals WHERE owner_id = :owner ORDER BY created_at DESC');
    $stmt->execute(['owner' => $userId]);
    return $stmt->fetchAll();
}

function get_public_animals(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM animals WHERE is_private = 0 ORDER BY created_at DESC')->fetchAll();
}

function get_animals_with_genetics(PDO $pdo, ?string $speciesSlug = null): array
{
    $sql = 'SELECT id, name, species, species_slug, genetics, genetics_profile FROM animals WHERE is_private = 0 AND genetics_profile IS NOT NULL AND genetics_profile != ""';
    $params = [];
    if ($speciesSlug !== null) {
        $sql .= ' AND species_slug = :slug';
        $params['slug'] = $speciesSlug;
    }
    $sql .= ' ORDER BY name COLLATE NOCASE ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $profile = [];
        if (!empty($row['genetics_profile'])) {
            $decoded = json_decode($row['genetics_profile'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $geneSlug => $state) {
                    $profile[(string)$geneSlug] = sanitize_gene_state((string)$state);
                }
            }
        }

        $result[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'species' => $row['species'],
            'species_slug' => $row['species_slug'],
            'genetics' => $row['genetics'],
            'genetics_profile' => $profile,
        ];
    }

    return $result;
}
