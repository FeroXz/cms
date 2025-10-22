<?php

declare(strict_types=1);

function ensure_default_cms_features(PDO $pdo): void
{
    ensure_primary_menu_exists($pdo);
    ensure_default_layout_blueprints($pdo);
}

function ensure_primary_menu_exists(PDO $pdo): void
{
    $stmt = $pdo->prepare('SELECT id FROM menus WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => 'primary']);
    $menu = $stmt->fetch();

    if (!$menu) {
        $pdo->prepare('INSERT INTO menus(name, slug, description, is_primary) VALUES (:name, :slug, :description, 1)')->execute([
            'name' => 'Hauptnavigation',
            'slug' => 'primary',
            'description' => 'Automatisch erzeugte Navigation über alle Kernbereiche.',
        ]);
        $menuId = (int)$pdo->lastInsertId();
    } else {
        $menuId = (int)$menu['id'];
    }

    $count = (int)$pdo->query('SELECT COUNT(*) AS count FROM menu_items WHERE menu_id = ' . $menuId)->fetchColumn();
    if ($count > 0) {
        return;
    }

    $position = 1;
    $coreLinks = [
        ['label' => 'Startseite', 'url' => BASE_URL . '/index.php', 'page_slug' => null],
        ['label' => 'Tierübersicht', 'url' => BASE_URL . '/index.php?route=animals', 'page_slug' => null],
        ['label' => 'Neuigkeiten', 'url' => BASE_URL . '/index.php?route=news', 'page_slug' => null],
        ['label' => 'Pflegeleitfaden', 'url' => BASE_URL . '/index.php?route=care-guide', 'page_slug' => null],
        ['label' => 'Galerie', 'url' => BASE_URL . '/index.php?route=gallery', 'page_slug' => null],
        ['label' => 'Tierabgabe', 'url' => BASE_URL . '/index.php?route=adoption', 'page_slug' => null],
    ];

    foreach ($coreLinks as $link) {
        $pdo->prepare('INSERT INTO menu_items(menu_id, parent_id, label, url, page_slug, position) VALUES (:menu_id, NULL, :label, :url, :page_slug, :position)')
            ->execute([
                'menu_id' => $menuId,
                'label' => $link['label'],
                'url' => $link['url'],
                'page_slug' => $link['page_slug'],
                'position' => $position++,
            ]);
    }

    if (function_exists('get_published_pages')) {
        $pages = get_published_pages($pdo);
        foreach ($pages as $page) {
            if (empty($page['show_in_menu'])) {
                continue;
            }
            $pdo->prepare('INSERT INTO menu_items(menu_id, parent_id, label, url, page_slug, position) VALUES (:menu_id, NULL, :label, NULL, :page_slug, :position)')
                ->execute([
                    'menu_id' => $menuId,
                    'label' => $page['title'],
                    'page_slug' => $page['slug'],
                    'position' => $position++,
                ]);
        }
    }
}

function ensure_default_layout_blueprints(PDO $pdo): void
{
    $defaults = [
        [
            'slug' => 'stellar-ribbon',
            'name' => 'Stellar Ribbon',
            'description' => 'Prägnante Bühne mit Bandlayout und flankierenden Highlights.',
            'definition' => json_encode([
                'home_sections' => ['hero', 'animals', 'adoption', 'news', 'care'],
                'highlight_layout' => 'split-panels',
                'hero_style' => 'ribbon',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_default' => 1,
        ],
        [
            'slug' => 'lunar-grid',
            'name' => 'Lunar Grid',
            'description' => 'Kartenbasierte Struktur mit modularer Rasteraufteilung.',
            'definition' => json_encode([
                'home_sections' => ['hero', 'news', 'animals', 'care', 'adoption'],
                'highlight_layout' => 'card-grid',
                'hero_style' => 'split-columns',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'is_default' => 0,
        ],
    ];

    foreach ($defaults as $blueprint) {
        $stmt = $pdo->prepare('SELECT id FROM layout_blueprints WHERE slug = :slug');
        $stmt->execute(['slug' => $blueprint['slug']]);
        if ($stmt->fetch()) {
            continue;
        }

        $pdo->prepare('INSERT INTO layout_blueprints(name, slug, description, definition, is_default) VALUES (:name, :slug, :description, :definition, :is_default)')
            ->execute([
                'name' => $blueprint['name'],
                'slug' => $blueprint['slug'],
                'description' => $blueprint['description'],
                'definition' => $blueprint['definition'],
                'is_default' => $blueprint['is_default'],
            ]);
    }
}

function get_menu_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $menu = $stmt->fetch();
    return $menu ?: null;
}

function get_menu_items(PDO $pdo, int $menuId): array
{
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE menu_id = :menu_id ORDER BY position ASC, label COLLATE NOCASE ASC');
    $stmt->execute(['menu_id' => $menuId]);
    return $stmt->fetchAll();
}

function get_menu_tree(PDO $pdo, string $slug): array
{
    $menu = get_menu_by_slug($pdo, $slug);
    if (!$menu) {
        return [];
    }

    $items = get_menu_items($pdo, (int)$menu['id']);
    $children = [];
    foreach ($items as $item) {
        $parentId = (int)($item['parent_id'] ?? 0);
        $children[$parentId][] = $item;
    }

    return build_menu_branch($children, 0);
}

function build_menu_branch(array $children, int $parentId): array
{
    $branch = [];
    foreach ($children[$parentId] ?? [] as $item) {
        $item['children'] = build_menu_branch($children, (int)$item['id']);
        $branch[] = $item;
    }

    return $branch;
}

function save_menu_item(PDO $pdo, int $menuId, array $data, ?int $id = null): void
{
    $parentId = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null;
    $parentId = normalize_menu_parent($pdo, $menuId, $parentId, $id);
    $label = trim($data['label'] ?? '');
    $url = trim($data['url'] ?? '');
    $pageSlug = trim($data['page_slug'] ?? '');
    $openInNewTab = !empty($data['open_in_new_tab']) ? 1 : 0;
    $position = isset($data['position']) ? (int)$data['position'] : 0;

    if ($id) {
        $pdo->prepare('UPDATE menu_items SET parent_id = :parent_id, label = :label, url = :url, page_slug = :page_slug, open_in_new_tab = :open_in_new_tab, position = :position, updated_at = CURRENT_TIMESTAMP WHERE id = :id')
            ->execute([
                'parent_id' => $parentId,
                'label' => $label,
                'url' => $url !== '' ? $url : null,
                'page_slug' => $pageSlug !== '' ? $pageSlug : null,
                'open_in_new_tab' => $openInNewTab,
                'position' => $position,
                'id' => $id,
            ]);
    } else {
        $pdo->prepare('INSERT INTO menu_items(menu_id, parent_id, label, url, page_slug, open_in_new_tab, position) VALUES (:menu_id, :parent_id, :label, :url, :page_slug, :open_in_new_tab, :position)')
            ->execute([
                'menu_id' => $menuId,
                'parent_id' => $parentId,
                'label' => $label,
                'url' => $url !== '' ? $url : null,
                'page_slug' => $pageSlug !== '' ? $pageSlug : null,
                'open_in_new_tab' => $openInNewTab,
                'position' => $position,
            ]);
    }
}

function normalize_menu_parent(PDO $pdo, int $menuId, ?int $parentId, ?int $currentId = null): ?int
{
    if ($parentId === null) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id FROM menu_items WHERE id = :id AND menu_id = :menu_id');
    $stmt->execute(['id' => $parentId, 'menu_id' => $menuId]);
    $parent = $stmt->fetch();
    if (!$parent) {
        return null;
    }

    if ($currentId !== null && $parentId === $currentId) {
        return null;
    }

    return $parentId;
}

function delete_menu_item(PDO $pdo, int $id): void
{
    $pdo->prepare('DELETE FROM menu_items WHERE id = :id')->execute(['id' => $id]);
}

function get_media_items(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM media_items ORDER BY uploaded_at DESC, id DESC')->fetchAll();
}

function create_media_item(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('INSERT INTO media_items(title, file_path, alt_text, description, uploaded_by) VALUES (:title, :file_path, :alt_text, :description, :uploaded_by)');
    $stmt->execute([
        'title' => $data['title'],
        'file_path' => $data['file_path'],
        'alt_text' => $data['alt_text'] ?? null,
        'description' => $data['description'] ?? null,
        'uploaded_by' => $data['uploaded_by'] ?? null,
    ]);

    return (int)$pdo->lastInsertId();
}

function update_media_item(PDO $pdo, int $id, array $data): void
{
    $pdo->prepare('UPDATE media_items SET title = :title, alt_text = :alt_text, description = :description, updated_at = CURRENT_TIMESTAMP WHERE id = :id')
        ->execute([
            'title' => $data['title'],
            'alt_text' => $data['alt_text'] ?? null,
            'description' => $data['description'] ?? null,
            'id' => $id,
        ]);
}

function delete_media_item(PDO $pdo, int $id): void
{
    $pdo->prepare('DELETE FROM media_items WHERE id = :id')->execute(['id' => $id]);
}

function get_layout_blueprints(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM layout_blueprints ORDER BY is_default DESC, name COLLATE NOCASE ASC')->fetchAll();
}

function get_layout_blueprint_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM layout_blueprints WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    $blueprint = $stmt->fetch();
    return $blueprint ?: null;
}

function get_active_layout_blueprint(PDO $pdo, array $settings): ?array
{
    $activeSlug = $settings['active_layout_blueprint'] ?? null;
    if ($activeSlug) {
        $blueprint = get_layout_blueprint_by_slug($pdo, $activeSlug);
        if ($blueprint) {
            return $blueprint;
        }
    }

    $stmt = $pdo->query('SELECT * FROM layout_blueprints WHERE is_default = 1 ORDER BY id ASC LIMIT 1');
    $fallback = $stmt->fetch();
    if ($fallback) {
        return $fallback;
    }

    $stmt = $pdo->query('SELECT * FROM layout_blueprints ORDER BY id ASC LIMIT 1');
    $blueprint = $stmt->fetch();
    return $blueprint ?: null;
}

function set_active_layout_blueprint(PDO $pdo, string $slug): void
{
    $blueprint = get_layout_blueprint_by_slug($pdo, $slug);
    if (!$blueprint) {
        return;
    }

    set_setting($pdo, 'active_layout_blueprint', $slug);
}

function log_cms_activity(PDO $pdo, string $action, array $context = []): void
{
    $user = current_user();
    $actor = $user['username'] ?? 'system';
    $details = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

    $stmt = $pdo->prepare('INSERT INTO cms_activity_log(actor, action, context, details) VALUES (:actor, :action, :context, :details)');
    $stmt->execute([
        'actor' => $actor,
        'action' => $action,
        'context' => $context['context'] ?? null,
        'details' => $details,
    ]);
}

function get_cms_activity(PDO $pdo, int $limit = 50): array
{
    $stmt = $pdo->prepare('SELECT * FROM cms_activity_log ORDER BY created_at DESC, id DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $entries = $stmt->fetchAll();

    foreach ($entries as &$entry) {
        if (!empty($entry['details'])) {
            $decoded = json_decode($entry['details'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $entry['details'] = $decoded;
            }
        }
    }

    return $entries;
}

function clear_cms_activity(PDO $pdo): void
{
    $pdo->exec('DELETE FROM cms_activity_log');
}

