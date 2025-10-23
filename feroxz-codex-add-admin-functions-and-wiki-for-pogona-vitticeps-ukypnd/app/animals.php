<?php
function create_animal(PDO $pdo, array $data): void
{
    $speciesSlug = $data['species_slug'] ?? normalize_species_slug($data['species'] ?? null);
    $stmt = $pdo->prepare('INSERT INTO animals(name, species, species_slug, age, genetics, genetics_profile, origin, special_notes, description, image_path, owner_id, is_private, is_showcased, is_piebald, sex, status, price, sire_id, dam_id, admin_notes) VALUES (:name, :species, :species_slug, :age, :genetics, :genetics_profile, :origin, :special_notes, :description, :image_path, :owner_id, :is_private, :is_showcased, :is_piebald, :sex, :status, :price, :sire_id, :dam_id, :admin_notes)');
    $stmt->execute([
        'name' => trim($data['name'] ?? ''),
        'species' => trim($data['species'] ?? ''),
        'species_slug' => $speciesSlug,
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
        'sex' => normalize_sex($data['sex'] ?? null),
        'status' => normalize_animal_status($data['status'] ?? null),
        'price' => $data['price'] ?? null,
        'sire_id' => normalize_nullable_id($data['sire_id'] ?? null),
        'dam_id' => normalize_nullable_id($data['dam_id'] ?? null),
        'admin_notes' => $data['admin_notes'] ?? null,
    ]);
}

function update_animal(PDO $pdo, int $id, array $data): void
{
    $speciesSlug = $data['species_slug'] ?? normalize_species_slug($data['species'] ?? null);
    $stmt = $pdo->prepare('UPDATE animals SET name = :name, species = :species, species_slug = :species_slug, age = :age, genetics = :genetics, genetics_profile = :genetics_profile, origin = :origin, special_notes = :special_notes, description = :description, image_path = :image_path, owner_id = :owner_id, is_private = :is_private, is_showcased = :is_showcased, is_piebald = :is_piebald, sex = :sex, status = :status, price = :price, sire_id = :sire_id, dam_id = :dam_id, admin_notes = :admin_notes WHERE id = :id');
    $stmt->execute([
        'name' => trim($data['name'] ?? ''),
        'species' => trim($data['species'] ?? ''),
        'species_slug' => $speciesSlug,
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
        'sex' => normalize_sex($data['sex'] ?? null),
        'status' => normalize_animal_status($data['status'] ?? null),
        'price' => $data['price'] ?? null,
        'sire_id' => normalize_nullable_id($data['sire_id'] ?? null),
        'dam_id' => normalize_nullable_id($data['dam_id'] ?? null),
        'admin_notes' => $data['admin_notes'] ?? null,
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

function get_public_animal_filters(PDO $pdo): array
{
    $speciesRows = $pdo->query('SELECT DISTINCT species, species_slug FROM animals WHERE is_private = 0 AND TRIM(species) != "" ORDER BY species COLLATE NOCASE ASC')->fetchAll();
    $species = [];
    foreach ($speciesRows ?: [] as $row) {
        $slug = $row['species_slug'] ?: slugify($row['species']);
        $species[] = [
            'name' => $row['species'],
            'slug' => $slug,
        ];
    }

    $statuses = [
        ['value' => 'available', 'label' => 'verfügbar'],
        ['value' => 'reserved', 'label' => 'reserviert'],
        ['value' => 'holdback', 'label' => 'Holdback'],
        ['value' => 'sold', 'label' => 'verkauft'],
        ['value' => 'not-for-sale', 'label' => 'nicht verfügbar'],
    ];

    $sexes = [
        ['value' => 'male', 'label' => 'Männlich'],
        ['value' => 'female', 'label' => 'Weiblich'],
        ['value' => 'unknown', 'label' => 'Unbekannt'],
    ];

    $geneExamples = $pdo->query('SELECT DISTINCT genetics FROM animals WHERE genetics IS NOT NULL AND genetics != "" LIMIT 10')->fetchAll();

    return [
        'species' => $species,
        'statuses' => $statuses,
        'sexes' => $sexes,
        'geneExamples' => array_map(static fn($row) => $row['genetics'], $geneExamples ?: []),
    ];
}

function resolve_species_name_by_slug(PDO $pdo, string $slug): ?string
{
    $stmt = $pdo->prepare('SELECT species FROM animals WHERE species_slug = :slug ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $row = $stmt->fetch();
    if ($row && !empty($row['species'])) {
        return $row['species'];
    }

    $stmt = $pdo->prepare('SELECT species FROM animals WHERE LOWER(REPLACE(species, " ", "-")) = :slug ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['slug' => strtolower($slug)]);
    $fallback = $stmt->fetch();
    return $fallback['species'] ?? null;
}

function parse_public_animal_filters(PDO $pdo, array $input): array
{
    $filters = [
        'species' => null,
        'species_name' => null,
        'sex' => null,
        'status' => null,
        'gene' => null,
        'price_min' => null,
        'price_max' => null,
    ];

    if (!empty($input['species']) && is_string($input['species'])) {
        $filters['species'] = trim($input['species']);
        $filters['species_name'] = resolve_species_name_by_slug($pdo, $filters['species']);
    }

    if (!empty($input['sex'])) {
        $sex = normalize_sex($input['sex']);
        if ($sex !== null) {
            $filters['sex'] = $sex;
        }
    }

    if (!empty($input['status'])) {
        $status = normalize_animal_status($input['status']);
        if ($status !== null) {
            $filters['status'] = $status;
        }
    }

    if (!empty($input['gene']) && is_string($input['gene'])) {
        $filters['gene'] = trim($input['gene']);
    }

    if (isset($input['price_min'])) {
        $filters['price_min'] = normalize_price_to_cents($input['price_min']);
    }

    if (isset($input['price_max'])) {
        $filters['price_max'] = normalize_price_to_cents($input['price_max']);
    }

    return $filters;
}

function parse_public_animal_sort(array $input): array
{
    $sortKey = $input['sort'] ?? 'created_desc';
    $allowed = ['created_desc', 'created_asc', 'name_asc', 'name_desc', 'price_asc', 'price_desc'];
    if (!in_array($sortKey, $allowed, true)) {
        $sortKey = 'created_desc';
    }

    return [
        'key' => $sortKey,
    ];
}

function get_public_animals_filtered(PDO $pdo, array $filters, array $sort): array
{
    $sql = 'SELECT a.*, sire.name AS sire_name, dam.name AS dam_name FROM animals a '
        . 'LEFT JOIN animals sire ON a.sire_id = sire.id '
        . 'LEFT JOIN animals dam ON a.dam_id = dam.id '
        . 'WHERE a.is_private = 0';
    $params = [];

    if ($filters['species']) {
        $sql .= ' AND (a.species_slug = :species';
        $params['species'] = $filters['species'];
        if ($filters['species_name']) {
            $sql .= ' OR LOWER(a.species) = :species_name';
            $params['species_name'] = strtolower($filters['species_name']);
        }
        $sql .= ')';
    }

    if ($filters['sex']) {
        if ($filters['sex'] === 'unknown') {
            $sql .= ' AND (a.sex IS NULL OR a.sex = "" OR a.sex = :sex_unknown)';
            $params['sex_unknown'] = 'unknown';
        } else {
            $sql .= ' AND a.sex = :sex';
            $params['sex'] = $filters['sex'];
        }
    }

    if ($filters['status']) {
        $sql .= ' AND a.status = :status';
        $params['status'] = $filters['status'];
    }

    if ($filters['gene']) {
        $sql .= ' AND (a.genetics LIKE :gene OR a.genetics_profile LIKE :gene)';
        $params['gene'] = '%' . $filters['gene'] . '%';
    }

    if ($filters['price_min'] !== null) {
        $sql .= ' AND (a.price != "" AND a.price IS NOT NULL)';
    }
    if ($filters['price_max'] !== null) {
        $sql .= ' AND (a.price != "" AND a.price IS NOT NULL)';
    }

    switch ($sort['key']) {
        case 'created_asc':
            $sql .= ' ORDER BY a.created_at ASC';
            break;
        case 'name_asc':
            $sql .= ' ORDER BY a.name COLLATE NOCASE ASC';
            break;
        case 'name_desc':
            $sql .= ' ORDER BY a.name COLLATE NOCASE DESC';
            break;
        case 'price_asc':
            $sql .= ' ORDER BY a.created_at DESC';
            break;
        case 'price_desc':
            $sql .= ' ORDER BY a.created_at DESC';
            break;
        case 'created_desc':
        default:
            $sql .= ' ORDER BY a.created_at DESC';
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $priceCents = normalize_price_to_cents($row['price'] ?? null);
        if ($filters['price_min'] !== null && $priceCents !== null && $priceCents < $filters['price_min']) {
            continue;
        }
        if ($filters['price_max'] !== null && $priceCents !== null && $priceCents > $filters['price_max']) {
            continue;
        }

        $row['price_amount'] = $priceCents;
        $result[] = $row;
    }

    if (in_array($sort['key'], ['price_asc', 'price_desc'], true)) {
        usort($result, static function ($a, $b) use ($sort) {
            $aPrice = $a['price_amount'] ?? PHP_INT_MAX;
            $bPrice = $b['price_amount'] ?? PHP_INT_MAX;
            if ($sort['key'] === 'price_desc') {
                return $bPrice <=> $aPrice;
            }
            return $aPrice <=> $bPrice;
        });
    }

    return $result;
}

function get_public_animal_detail(PDO $pdo, int $id, bool $allowPrivate = false): ?array
{
    $stmt = $pdo->prepare('SELECT a.*, sire.name AS sire_name, sire.id AS sire_id, dam.name AS dam_name, dam.id AS dam_id '
        . 'FROM animals a '
        . 'LEFT JOIN animals sire ON a.sire_id = sire.id '
        . 'LEFT JOIN animals dam ON a.dam_id = dam.id '
        . 'WHERE a.id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    if (!$allowPrivate && !empty($row['is_private']) && !current_user()) {
        return null;
    }

    $row['price_amount'] = normalize_price_to_cents($row['price'] ?? null);
    $row['media'] = get_media_for_owner($pdo, 'animal', (int)$row['id']);

    if (!empty($row['genetics_profile'])) {
        $decoded = json_decode($row['genetics_profile'], true);
        $row['genetics_profile'] = is_array($decoded) ? $decoded : [];
    } else {
        $row['genetics_profile'] = [];
    }

    return $row;
}
