<?php

function normalize_listing_status(?string $status): string
{
    $allowed = ['available', 'reserved', 'adopted'];
    $normalized = strtolower(trim((string)$status));
    return in_array($normalized, $allowed, true) ? $normalized : 'available';
}

function create_listing(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('INSERT INTO adoption_listings(animal_id, title, species, species_slug, genetics, genetics_profile, price, description, image_path, status, contact_email, gender, price_amount) VALUES (:animal_id, :title, :species, :species_slug, :genetics, :genetics_profile, :price, :description, :image_path, :status, :contact_email, :gender, :price_amount)');
    $stmt->execute([
        'animal_id' => normalize_nullable_id($data['animal_id'] ?? null),
        'title' => trim($data['title'] ?? ''),
        'species' => trim($data['species'] ?? '') ?: null,
        'species_slug' => $data['species_slug'] ?? null,
        'genetics' => trim($data['genetics'] ?? '') ?: null,
        'genetics_profile' => $data['genetics_profile'] ?? null,
        'price' => ($price = trim((string)($data['price'] ?? ''))) === '' ? null : $price,
        'description' => $data['description'] ?? null,
        'image_path' => $data['image_path'] ?? null,
        'status' => normalize_listing_status($data['status'] ?? 'available'),
        'contact_email' => ($email = trim($data['contact_email'] ?? '')) === '' ? null : $email,
        'gender' => normalize_sex($data['gender'] ?? null),
        'price_amount' => normalize_price_to_cents($data['price_amount'] ?? ($data['price'] ?? null)),
    ]);
}

function update_listing(PDO $pdo, int $id, array $data): void
{
    $stmt = $pdo->prepare('UPDATE adoption_listings SET animal_id = :animal_id, title = :title, species = :species, species_slug = :species_slug, genetics = :genetics, genetics_profile = :genetics_profile, price = :price, description = :description, image_path = :image_path, status = :status, contact_email = :contact_email, gender = :gender, price_amount = :price_amount WHERE id = :id');
    $stmt->execute([
        'animal_id' => normalize_nullable_id($data['animal_id'] ?? null),
        'title' => trim($data['title'] ?? ''),
        'species' => trim($data['species'] ?? '') ?: null,
        'species_slug' => $data['species_slug'] ?? null,
        'genetics' => trim($data['genetics'] ?? '') ?: null,
        'genetics_profile' => $data['genetics_profile'] ?? null,
        'price' => ($price = trim((string)($data['price'] ?? ''))) === '' ? null : $price,
        'description' => $data['description'] ?? null,
        'image_path' => $data['image_path'] ?? null,
        'status' => normalize_listing_status($data['status'] ?? 'available'),
        'contact_email' => ($email = trim($data['contact_email'] ?? '')) === '' ? null : $email,
        'gender' => normalize_sex($data['gender'] ?? null),
        'price_amount' => normalize_price_to_cents($data['price_amount'] ?? ($data['price'] ?? null)),
        'id' => $id,
    ]);
}

function delete_listing(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM adoption_listings WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function get_listing(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM adoption_listings WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $listing = $stmt->fetch();
    return $listing ?: null;
}

function get_listings(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM adoption_listings ORDER BY created_at DESC')->fetchAll();
}

function get_public_listings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM adoption_listings WHERE status != "adopted" ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function get_public_listing_filters(PDO $pdo): array
{
    $speciesRows = $pdo->query('SELECT DISTINCT species, species_slug FROM adoption_listings WHERE TRIM(COALESCE(species, "")) != "" ORDER BY species COLLATE NOCASE ASC')->fetchAll();
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
        ['value' => 'adopted', 'label' => 'vermittelt'],
    ];

    $genders = [
        ['value' => 'male', 'label' => 'Männlich'],
        ['value' => 'female', 'label' => 'Weiblich'],
        ['value' => 'unknown', 'label' => 'Unbekannt'],
    ];

    $genes = $pdo->query('SELECT DISTINCT genetics FROM adoption_listings WHERE genetics IS NOT NULL AND genetics != "" LIMIT 10')->fetchAll();

    return [
        'species' => $species,
        'statuses' => $statuses,
        'genders' => $genders,
        'geneExamples' => array_map(static fn($row) => $row['genetics'], $genes ?: []),
    ];
}

function resolve_listing_species_name(PDO $pdo, string $slug): ?string
{
    $stmt = $pdo->prepare('SELECT species FROM adoption_listings WHERE species_slug = :slug ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $row = $stmt->fetch();
    if ($row && !empty($row['species'])) {
        return $row['species'];
    }

    $stmt = $pdo->prepare('SELECT species FROM adoption_listings WHERE LOWER(REPLACE(COALESCE(species, ""), " ", "-")) = :slug ORDER BY created_at DESC LIMIT 1');
    $stmt->execute(['slug' => strtolower($slug)]);
    $fallback = $stmt->fetch();
    return $fallback['species'] ?? null;
}

function parse_public_listing_filters(PDO $pdo, array $input): array
{
    $filters = [
        'species' => null,
        'species_name' => null,
        'status' => null,
        'gender' => null,
        'gene' => null,
        'price_min' => null,
        'price_max' => null,
    ];

    if (!empty($input['species']) && is_string($input['species'])) {
        $filters['species'] = trim($input['species']);
        $filters['species_name'] = resolve_listing_species_name($pdo, $filters['species']);
    }

    if (!empty($input['status'])) {
        $status = strtolower(trim((string)$input['status']));
        if (in_array($status, ['available', 'reserved', 'adopted'], true)) {
            $filters['status'] = $status;
        }
    }

    if (!empty($input['gender'])) {
        $gender = normalize_sex($input['gender']);
        if ($gender !== null) {
            $filters['gender'] = $gender;
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

function get_filtered_public_listings(PDO $pdo, array $filters): array
{
    $sql = 'SELECT * FROM adoption_listings WHERE 1=1';
    $params = [];

    if ($filters['status']) {
        $sql .= ' AND status = :status';
        $params['status'] = $filters['status'];
    } else {
        $sql .= ' AND status != "adopted"';
    }

    if ($filters['species']) {
        $sql .= ' AND (species_slug = :species';
        $params['species'] = $filters['species'];
        if ($filters['species_name']) {
            $sql .= ' OR LOWER(species) = :species_name';
            $params['species_name'] = strtolower($filters['species_name']);
        }
        $sql .= ')';
    }

    if ($filters['gender']) {
        if ($filters['gender'] === 'unknown') {
            $sql .= ' AND (gender IS NULL OR gender = "" OR gender = :gender_unknown)';
            $params['gender_unknown'] = 'unknown';
        } else {
            $sql .= ' AND gender = :gender';
            $params['gender'] = $filters['gender'];
        }
    }

    if ($filters['gene']) {
        $sql .= ' AND (genetics LIKE :gene OR genetics_profile LIKE :gene)';
        $params['gene'] = '%' . $filters['gene'] . '%';
    }

    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $price = $row['price_amount'] !== null ? (int)$row['price_amount'] : normalize_price_to_cents($row['price'] ?? null);
        if ($filters['price_min'] !== null && $price !== null && $price < $filters['price_min']) {
            continue;
        }
        if ($filters['price_max'] !== null && $price !== null && $price > $filters['price_max']) {
            continue;
        }

        $row['price_amount'] = $price;
        $result[] = $row;
    }

    return $result;
}

function get_public_listing_detail(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM adoption_listings WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $listing = $stmt->fetch();
    if (!$listing) {
        return null;
    }

    $listing['price_amount'] = $listing['price_amount'] !== null ? (int)$listing['price_amount'] : normalize_price_to_cents($listing['price'] ?? null);
    $listing['media'] = [];

    if (!empty($listing['animal_id'])) {
        $listing['media'] = get_media_for_owner($pdo, 'animal', (int)$listing['animal_id']);
        $listing['animal'] = get_public_animal_detail($pdo, (int)$listing['animal_id'], true);
    }

    return $listing;
}

function create_inquiry(PDO $pdo, array $data): void
{
    $listingId = normalize_nullable_id($data['listing_id'] ?? null);
    if (!$listingId) {
        throw new InvalidArgumentException('Bitte wählen Sie einen gültigen Abgabeeintrag aus.');
    }

    if (!get_listing($pdo, $listingId)) {
        throw new InvalidArgumentException('Der ausgewählte Abgabeeintrag ist nicht mehr verfügbar.');
    }

    $interestedIn = isset($data['interested_in']) ? trim((string)$data['interested_in']) : null;
    if ($interestedIn === '') {
        $interestedIn = null;
    }

    $name = trim($data['sender_name'] ?? '');
    $email = trim($data['sender_email'] ?? '');
    $message = trim($data['message'] ?? '');

    $stmt = $pdo->prepare('INSERT INTO adoption_inquiries(listing_id, interested_in, sender_name, sender_email, message) VALUES (:listing_id, :interested_in, :sender_name, :sender_email, :message)');
    $stmt->execute([
        'listing_id' => $listingId,
        'interested_in' => $interestedIn,
        'sender_name' => $name,
        'sender_email' => $email,
        'message' => $message,
    ]);
}

function get_inquiries(PDO $pdo): array
{
    $sql = 'SELECT adoption_inquiries.*, adoption_listings.title as listing_title FROM adoption_inquiries JOIN adoption_listings ON adoption_listings.id = adoption_inquiries.listing_id ORDER BY adoption_inquiries.created_at DESC';
    return $pdo->query($sql)->fetchAll();
}
