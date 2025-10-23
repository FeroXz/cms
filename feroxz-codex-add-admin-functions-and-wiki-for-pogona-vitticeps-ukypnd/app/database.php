<?php
function get_database_connection(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $needsDirectory = !is_dir(dirname(DATA_PATH));
    if ($needsDirectory) {
        mkdir(dirname(DATA_PATH), 0775, true);
    }

    $pdo = new PDO('sqlite:' . DATA_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

function initialize_database(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "admin",
        can_manage_animals INTEGER NOT NULL DEFAULT 1,
        can_manage_settings INTEGER NOT NULL DEFAULT 1,
        can_manage_adoptions INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS animals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        species TEXT NOT NULL,
        species_slug TEXT,
        age TEXT,
        genetics TEXT,
        genetics_profile TEXT,
        origin TEXT,
        special_notes TEXT,
        description TEXT,
        image_path TEXT,
        owner_id INTEGER,
        is_private INTEGER NOT NULL DEFAULT 0,
        is_showcased INTEGER NOT NULL DEFAULT 0,
        is_piebald INTEGER NOT NULL DEFAULT 0,
        sex TEXT,
        status TEXT,
        price TEXT,
        sire_id INTEGER,
        dam_id INTEGER,
        admin_notes TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(owner_id) REFERENCES users(id)
    )');

    $animalColumns = $pdo->query('PRAGMA table_info(animals)')->fetchAll();
    $animalColumnNames = array_column($animalColumns, 'name');
    if (!in_array('is_piebald', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN is_piebald INTEGER NOT NULL DEFAULT 0');
    }
    if (!in_array('species_slug', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN species_slug TEXT');
    }
    if (!in_array('genetics_profile', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN genetics_profile TEXT');
    }
    if (!in_array('sex', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN sex TEXT');
    }
    if (!in_array('status', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN status TEXT');
    }
    if (!in_array('price', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN price TEXT');
    }
    if (!in_array('sire_id', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN sire_id INTEGER');
    }
    if (!in_array('dam_id', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN dam_id INTEGER');
    }
    if (!in_array('admin_notes', $animalColumnNames, true)) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN admin_notes TEXT');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS adoption_listings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        animal_id INTEGER,
        title TEXT NOT NULL,
        species TEXT,
        species_slug TEXT,
        genetics TEXT,
        genetics_profile TEXT,
        price TEXT,
        description TEXT,
        image_path TEXT,
        status TEXT NOT NULL DEFAULT "available",
        contact_email TEXT,
        gender TEXT,
        price_amount INTEGER,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(animal_id) REFERENCES animals(id)
    )');

    $adoptionColumns = $pdo->query('PRAGMA table_info(adoption_listings)')->fetchAll();
    $adoptionColumnNames = array_column($adoptionColumns, 'name');
    if (!in_array('species_slug', $adoptionColumnNames, true)) {
        $pdo->exec('ALTER TABLE adoption_listings ADD COLUMN species_slug TEXT');
    }
    if (!in_array('genetics_profile', $adoptionColumnNames, true)) {
        $pdo->exec('ALTER TABLE adoption_listings ADD COLUMN genetics_profile TEXT');
    }
    if (!in_array('gender', $adoptionColumnNames, true)) {
        $pdo->exec('ALTER TABLE adoption_listings ADD COLUMN gender TEXT');
    }
    if (!in_array('price_amount', $adoptionColumnNames, true)) {
        $pdo->exec('ALTER TABLE adoption_listings ADD COLUMN price_amount INTEGER');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS gallery_collections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        description TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS adoption_inquiries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        listing_id INTEGER NOT NULL,
        interested_in TEXT,
        sender_name TEXT NOT NULL,
        sender_email TEXT NOT NULL,
        message TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(listing_id) REFERENCES adoption_listings(id)
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        content TEXT NOT NULL,
        is_published INTEGER NOT NULL DEFAULT 0,
        show_in_menu INTEGER NOT NULL DEFAULT 0,
        parent_id INTEGER,
        menu_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pageColumns = $pdo->query('PRAGMA table_info(pages)')->fetchAll();
    $pageColumnNames = array_column($pageColumns, 'name');
    if (!in_array('show_in_menu', $pageColumnNames, true)) {
        $pdo->exec('ALTER TABLE pages ADD COLUMN show_in_menu INTEGER NOT NULL DEFAULT 0');
    }
    if (!in_array('parent_id', $pageColumnNames, true)) {
        $pdo->exec('ALTER TABLE pages ADD COLUMN parent_id INTEGER');
    }
    if (!in_array('menu_order', $pageColumnNames, true)) {
        $pdo->exec('ALTER TABLE pages ADD COLUMN menu_order INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS news_posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        excerpt TEXT,
        content TEXT NOT NULL,
        is_published INTEGER NOT NULL DEFAULT 0,
        published_at TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS breeding_plans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        season TEXT,
        notes TEXT,
        expected_genetics TEXT,
        incubation_notes TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS breeding_plan_parents (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        plan_id INTEGER NOT NULL,
        parent_type TEXT NOT NULL,
        animal_id INTEGER,
        name TEXT,
        sex TEXT,
        species TEXT,
        genetics TEXT,
        notes TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(plan_id) REFERENCES breeding_plans(id) ON DELETE CASCADE,
        FOREIGN KEY(animal_id) REFERENCES animals(id)
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS care_articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        summary TEXT,
        content TEXT NOT NULL,
        is_published INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS genetic_species (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        scientific_name TEXT,
        description TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS genetic_genes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        species_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        slug TEXT NOT NULL,
        shorthand TEXT,
        inheritance_mode TEXT NOT NULL,
        description TEXT,
        normal_label TEXT,
        heterozygous_label TEXT,
        homozygous_label TEXT,
        is_reference INTEGER NOT NULL DEFAULT 0,
        display_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(species_id) REFERENCES genetic_species(id) ON DELETE CASCADE,
        UNIQUE(species_id, slug)
    )');

    $geneColumns = $pdo->query('PRAGMA table_info(genetic_genes)')->fetchAll();
    $geneColumnNames = array_column($geneColumns, 'name');
    if (!in_array('is_reference', $geneColumnNames, true)) {
        $pdo->exec('ALTER TABLE genetic_genes ADD COLUMN is_reference INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS genetic_morphs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        species_slug TEXT NOT NULL,
        species_name TEXT NOT NULL,
        display_name TEXT NOT NULL,
        normalized_name TEXT NOT NULL,
        morph_type TEXT NOT NULL,
        aliases TEXT,
        normalized_aliases TEXT,
        description TEXT,
        source_url TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(species_slug, normalized_name)
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS media (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        file_name TEXT NOT NULL,
        alt TEXT,
        width INTEGER,
        height INTEGER,
        size INTEGER,
        type TEXT NOT NULL,
        owner_type TEXT,
        owner_id INTEGER,
        path_original TEXT NOT NULL,
        path_thumb TEXT,
        path_medium TEXT,
        path_webp TEXT,
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $mediaColumns = $pdo->query('PRAGMA table_info(media)')->fetchAll();
    $mediaColumnNames = array_column($mediaColumns, 'name');
    $mediaSchemaUpdates = [
        'alt' => 'ALTER TABLE media ADD COLUMN alt TEXT',
        'width' => 'ALTER TABLE media ADD COLUMN width INTEGER',
        'height' => 'ALTER TABLE media ADD COLUMN height INTEGER',
        'size' => 'ALTER TABLE media ADD COLUMN size INTEGER',
        'type' => 'ALTER TABLE media ADD COLUMN type TEXT NOT NULL DEFAULT "image/jpeg"',
        'owner_type' => 'ALTER TABLE media ADD COLUMN owner_type TEXT',
        'owner_id' => 'ALTER TABLE media ADD COLUMN owner_id INTEGER',
        'path_thumb' => 'ALTER TABLE media ADD COLUMN path_thumb TEXT',
        'path_medium' => 'ALTER TABLE media ADD COLUMN path_medium TEXT',
        'path_webp' => 'ALTER TABLE media ADD COLUMN path_webp TEXT',
        'sort_order' => 'ALTER TABLE media ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0',
        'updated_at' => 'ALTER TABLE media ADD COLUMN updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ];
    foreach ($mediaSchemaUpdates as $column => $sql) {
        if (!in_array($column, $mediaColumnNames, true)) {
            $pdo->exec($sql);
        }
    }
}
