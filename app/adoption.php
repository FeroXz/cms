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

function find_listing_for_import(PDO $pdo, ?int $id, string $title, ?string $speciesSlug): ?array
{
    if ($id !== null) {
        $stmt = $pdo->prepare('SELECT * FROM adoption_listings WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
    }

    $title = trim($title);
    if ($title === '') {
        return null;
    }

    $params = ['title' => strtolower($title)];
    $sql = 'SELECT * FROM adoption_listings WHERE LOWER(title) = :title';
    if ($speciesSlug) {
        $sql .= ' AND species_slug = :slug';
        $params['slug'] = $speciesSlug;
    }
    $sql .= ' ORDER BY created_at DESC LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }

    if ($speciesSlug === null) {
        $stmt = $pdo->prepare('SELECT * FROM adoption_listings WHERE LOWER(title) = :title COLLATE NOCASE ORDER BY created_at DESC LIMIT 1');
        $stmt->execute(['title' => strtolower($title)]);
        $fallback = $stmt->fetch();
        return $fallback ?: null;
    }

    return null;
}

function import_adoption_listings_from_csv(PDO $pdo, string $filePath, array $options = []): array
{
    $dryRun = !empty($options['dry_run']);
    $previewLimit = isset($options['preview_limit']) ? (int)$options['preview_limit'] : 10;
    $columnMap = array_change_key_case($options['column_map'] ?? [], CASE_LOWER);

    if (!is_file($filePath) || !is_readable($filePath)) {
        throw new InvalidArgumentException('CSV-Datei konnte nicht gelesen werden.');
    }

    if (($handle = fopen($filePath, 'rb')) === false) {
        throw new RuntimeException('CSV-Datei konnte nicht geöffnet werden.');
    }

    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        throw new InvalidArgumentException('CSV-Datei enthält keine Kopfzeile.');
    }

    $headerMap = [];
    foreach ($header as $index => $label) {
        $headerMap[strtolower(trim((string)$label))] = $index;
    }

    $resolveColumn = static function (string $field) use ($columnMap, $headerMap) {
        $field = strtolower($field);
        $mapped = $columnMap[$field] ?? $field;
        $mapped = strtolower(trim((string)$mapped));
        if ($mapped !== '' && array_key_exists($mapped, $headerMap)) {
            return $headerMap[$mapped];
        }
        return $headerMap[$field] ?? null;
    };

    $required = ['title'];
    $fields = [
        'id', 'animal_id', 'animal_name', 'title', 'species', 'species_slug', 'genetics', 'genetics_profile',
        'price', 'price_amount', 'description', 'image_path', 'status', 'contact_email', 'gender',
    ];

    $summary = [
        'total' => 0,
        'valid' => 0,
        'created' => 0,
        'updated' => 0,
        'unchanged' => 0,
        'duplicates' => 0,
        'skipped' => 0,
        'errors' => [],
        'warnings' => [],
    ];
    $preview = [];
    $seenKeys = [];
    $lineNumber = 1;

    if (!$dryRun) {
        $pdo->beginTransaction();
    }

    try {
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $summary['total']++;

            $values = [];
            foreach ($fields as $field) {
                $columnIndex = $resolveColumn($field);
                $values[$field] = $columnIndex !== null ? trim((string)($row[$columnIndex] ?? '')) : '';
            }

            $missing = [];
            foreach ($required as $field) {
                if ($values[$field] === '') {
                    $missing[] = $field;
                }
            }

            if ($missing) {
                $summary['skipped']++;
                $summary['errors'][] = sprintf('Zeile %d: fehlende Pflichtfelder (%s)', $lineNumber, implode(', ', $missing));
                if (count($preview) < $previewLimit) {
                    $preview[] = [
                        'line' => $lineNumber,
                        'name' => $values['title'] ?? '',
                        'species' => $values['species'] ?? '',
                        'action' => 'error',
                        'note' => 'Pflichtfelder fehlen',
                    ];
                }
                continue;
            }

            $id = $values['id'] !== '' ? (int)$values['id'] : null;
            $speciesSlug = $values['species_slug'] !== '' ? $values['species_slug'] : normalize_species_slug($values['species']);
            $titleSlug = slugify($values['title']);
            $dedupeKey = $id !== null ? 'id:' . $id : $speciesSlug . '|' . $titleSlug;
            if (isset($seenKeys[$dedupeKey])) {
                $summary['duplicates']++;
                if (count($preview) < $previewLimit) {
                    $preview[] = [
                        'line' => $lineNumber,
                        'name' => $values['title'],
                        'species' => $values['species'],
                        'action' => 'duplicate',
                        'note' => 'Bereits in dieser Datei verarbeitet',
                    ];
                }
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $existing = find_listing_for_import($pdo, $id, $values['title'], $speciesSlug);

            $animalId = normalize_nullable_id($values['animal_id']);
            if ($animalId === null && $values['animal_name'] !== '') {
                $animal = find_animal_for_import($pdo, null, $values['animal_name'], $speciesSlug);
                if ($animal) {
                    $animalId = (int)$animal['id'];
                }
            }

            $priceText = interpret_string($values['price'], $existing['price'] ?? null);
            $priceAmount = null;
            if ($values['price_amount'] !== '') {
                $priceAmount = normalize_price_to_cents($values['price_amount']);
            }
            if ($priceAmount === null && $priceText !== null) {
                $priceAmount = normalize_price_to_cents($priceText);
            }
            if ($priceAmount === null && isset($existing['price_amount'])) {
                $priceAmount = $existing['price_amount'];
            }

            $payload = [
                'animal_id' => $animalId ?? ($existing['animal_id'] ?? null),
                'title' => $values['title'],
                'species' => interpret_string($values['species'], $existing['species'] ?? null),
                'species_slug' => $speciesSlug,
                'genetics' => interpret_string($values['genetics'], $existing['genetics'] ?? null),
                'genetics_profile' => interpret_string($values['genetics_profile'], $existing['genetics_profile'] ?? null),
                'price' => $priceText,
                'description' => interpret_string($values['description'], $existing['description'] ?? null),
                'image_path' => interpret_string($values['image_path'], $existing['image_path'] ?? null),
                'status' => $values['status'] !== '' ? normalize_listing_status($values['status']) : ($existing['status'] ?? 'available'),
                'contact_email' => interpret_string($values['contact_email'], $existing['contact_email'] ?? null),
                'gender' => $values['gender'] !== '' ? (normalize_sex($values['gender']) ?? ($existing['gender'] ?? null)) : ($existing['gender'] ?? null),
                'price_amount' => $priceAmount,
            ];

            $action = 'create';
            if ($existing) {
                $hasChanges = false;
                foreach ($payload as $key => $value) {
                    $existingValue = $existing[$key] ?? null;
                    if ($key === 'price_amount') {
                        $existingValue = $existingValue !== null ? (int)$existingValue : null;
                        $value = $value !== null ? (int)$value : null;
                    }
                    if ($value !== $existingValue) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    $action = 'update';
                    $summary['updated']++;
                    $summary['valid']++;
                    if (!$dryRun) {
                        update_listing($pdo, (int)$existing['id'], $payload);
                    }
                } else {
                    $action = 'unchanged';
                    $summary['unchanged']++;
                }
            } else {
                $summary['created']++;
                $summary['valid']++;
                if (!$dryRun) {
                    create_listing($pdo, $payload);
                }
            }

            if (count($preview) < $previewLimit) {
                $preview[] = [
                    'line' => $lineNumber,
                    'name' => $values['title'],
                    'species' => $values['species'],
                    'action' => $action,
                    'note' => $existing ? 'Bestehender Eintrag' : 'Neu',
                ];
            }
        }

        if (!$dryRun) {
            $pdo->commit();
        }
    } catch (Throwable $exception) {
        if (!$dryRun && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fclose($handle);
        throw $exception;
    }

    fclose($handle);

    return [
        'summary' => $summary,
        'preview' => $preview,
    ];
}
