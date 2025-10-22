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
        originator TEXT,
        origin_url TEXT,
        image_path TEXT,
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
    if (!in_array('originator', $geneColumnNames, true)) {
        $pdo->exec('ALTER TABLE genetic_genes ADD COLUMN originator TEXT');
    }
    if (!in_array('origin_url', $geneColumnNames, true)) {
        $pdo->exec('ALTER TABLE genetic_genes ADD COLUMN origin_url TEXT');
    }
    if (!in_array('image_path', $geneColumnNames, true)) {
        $pdo->exec('ALTER TABLE genetic_genes ADD COLUMN image_path TEXT');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS gallery_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        caption TEXT,
        image_path TEXT NOT NULL,
        display_order INTEGER NOT NULL DEFAULT 0,
        is_published INTEGER NOT NULL DEFAULT 1,
        fit_mode TEXT NOT NULL DEFAULT "cover",
        focus_x INTEGER NOT NULL DEFAULT 50,
        focus_y INTEGER NOT NULL DEFAULT 50,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $galleryColumns = $pdo->query('PRAGMA table_info(gallery_images)')->fetchAll();
    $galleryColumnNames = array_column($galleryColumns, 'name');
    if (!in_array('fit_mode', $galleryColumnNames, true)) {
        $pdo->exec('ALTER TABLE gallery_images ADD COLUMN fit_mode TEXT NOT NULL DEFAULT "cover"');
    }
    if (!in_array('focus_x', $galleryColumnNames, true)) {
        $pdo->exec('ALTER TABLE gallery_images ADD COLUMN focus_x INTEGER NOT NULL DEFAULT 50');
    }
    if (!in_array('focus_y', $galleryColumnNames, true)) {
        $pdo->exec('ALTER TABLE gallery_images ADD COLUMN focus_y INTEGER NOT NULL DEFAULT 50');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS media_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        file_path TEXT NOT NULL,
        alt_text TEXT,
        description TEXT,
        uploaded_by TEXT,
        uploaded_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $mediaColumns = $pdo->query('PRAGMA table_info(media_items)')->fetchAll();
    $mediaColumnNames = array_column($mediaColumns, 'name');
    if (!in_array('uploaded_by', $mediaColumnNames, true)) {
        $pdo->exec('ALTER TABLE media_items ADD COLUMN uploaded_by TEXT');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS menus (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        description TEXT,
        is_primary INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS menu_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        menu_id INTEGER NOT NULL,
        parent_id INTEGER,
        label TEXT NOT NULL,
        url TEXT,
        page_slug TEXT,
        open_in_new_tab INTEGER NOT NULL DEFAULT 0,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(menu_id) REFERENCES menus(id) ON DELETE CASCADE,
        FOREIGN KEY(parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
    )');

    $menuItemColumns = $pdo->query('PRAGMA table_info(menu_items)')->fetchAll();
    $menuItemColumnNames = array_column($menuItemColumns, 'name');
    if (!in_array('position', $menuItemColumnNames, true)) {
        $pdo->exec('ALTER TABLE menu_items ADD COLUMN position INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS layout_blueprints (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        description TEXT,
        definition TEXT NOT NULL,
        is_default INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS cms_activity_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        actor TEXT,
        action TEXT NOT NULL,
        context TEXT,
        details TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
}
