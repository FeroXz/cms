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
        age TEXT,
        genetics TEXT,
        origin TEXT,
        special_notes TEXT,
        description TEXT,
        image_path TEXT,
        owner_id INTEGER,
        is_private INTEGER NOT NULL DEFAULT 0,
        is_showcased INTEGER NOT NULL DEFAULT 0,
        is_piebald INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(owner_id) REFERENCES users(id)
    )');

    $animalColumns = $pdo->query('PRAGMA table_info(animals)')->fetchAll();
    $hasPiebald = false;
    foreach ($animalColumns as $column) {
        if (($column['name'] ?? '') === 'is_piebald') {
            $hasPiebald = true;
            break;
        }
    }
    if (!$hasPiebald) {
        $pdo->exec('ALTER TABLE animals ADD COLUMN is_piebald INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS adoption_listings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        animal_id INTEGER,
        title TEXT NOT NULL,
        species TEXT,
        genetics TEXT,
        price TEXT,
        description TEXT,
        image_path TEXT,
        status TEXT NOT NULL DEFAULT "available",
        contact_email TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(animal_id) REFERENCES animals(id)
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
        display_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(species_id) REFERENCES genetic_species(id) ON DELETE CASCADE,
        UNIQUE(species_id, slug)
    )');
}
