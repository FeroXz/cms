<?php

function global_search(PDO $pdo, string $term, int $limit = 12): array
{
    $query = trim($term);
    if ($query === '') {
        return [];
    }

    $like = '%' . $query . '%';
    $results = [];

    $animalStmt = $pdo->prepare('SELECT id, name, species FROM animals WHERE is_private = 0 AND (name LIKE :term OR species LIKE :term OR genetics LIKE :term) ORDER BY created_at DESC LIMIT 4');
    $animalStmt->bindValue(':term', $like, PDO::PARAM_STR);
    $animalStmt->execute();
    foreach ($animalStmt->fetchAll() as $row) {
        $results[] = [
            'type' => 'Tier',
            'title' => $row['name'],
            'subtitle' => $row['species'],
            'url' => BASE_URL . '/index.php?route=animal&id=' . (int)$row['id'],
        ];
    }

    $morphStmt = $pdo->prepare('SELECT id, display_name, species_name FROM genetic_morphs WHERE display_name LIKE :term OR normalized_name LIKE :term OR species_name LIKE :term ORDER BY display_name COLLATE NOCASE ASC LIMIT 4');
    $morphStmt->bindValue(':term', $like, PDO::PARAM_STR);
    $morphStmt->execute();
    foreach ($morphStmt->fetchAll() as $row) {
        $results[] = [
            'type' => 'Morph',
            'title' => $row['display_name'],
            'subtitle' => $row['species_name'],
            'url' => BASE_URL . '/index.php?route=genetics&highlightMorph=' . (int)$row['id'],
        ];
    }

    $newsStmt = $pdo->prepare('SELECT slug, title, excerpt FROM news_posts WHERE is_published = 1 AND (title LIKE :term OR excerpt LIKE :term) ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3');
    $newsStmt->bindValue(':term', $like, PDO::PARAM_STR);
    $newsStmt->execute();
    foreach ($newsStmt->fetchAll() as $row) {
        $results[] = [
            'type' => 'News',
            'title' => $row['title'],
            'subtitle' => $row['excerpt'] ?? '',
            'url' => BASE_URL . '/index.php?route=news&slug=' . urlencode($row['slug']),
        ];
    }

    $wikiStmt = $pdo->prepare('SELECT slug, title, summary FROM care_articles WHERE is_published = 1 AND (title LIKE :term OR summary LIKE :term) ORDER BY title COLLATE NOCASE ASC LIMIT 3');
    $wikiStmt->bindValue(':term', $like, PDO::PARAM_STR);
    $wikiStmt->execute();
    foreach ($wikiStmt->fetchAll() as $row) {
        $results[] = [
            'type' => 'Wiki',
            'title' => $row['title'],
            'subtitle' => $row['summary'] ?? '',
            'url' => BASE_URL . '/index.php?route=wiki&slug=' . urlencode($row['slug']),
        ];
    }

    return array_slice($results, 0, $limit);
}
