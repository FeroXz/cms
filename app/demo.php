<?php

function ensure_demo_content(PDO $pdo): void
{
    ensure_demo_animals($pdo);
    ensure_demo_adoptions($pdo);
    ensure_demo_news($pdo);
}

function ensure_demo_animals(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM animals')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $samples = [
        [
            'name' => 'Aurora',
            'species' => 'Pogona vitticeps',
            'species_slug' => 'pogona-vitticeps',
            'age' => '3 Jahre',
            'genetics' => 'Translucent Het Hypo',
            'special_notes' => '<p>Unsere zuverlässig zahme Botschafterin für Schulklassenführungen.</p>',
            'description' => '<p>Aurora stammt aus einer verantwortungsvollen Hobbyzucht und begeistert mit sattem Orange und ruhigem Gemüt.</p>',
            'image_path' => 'https://images.unsplash.com/photo-1581888227599-779811939961?auto=format&fit=crop&w=1200&q=80',
            'is_showcased' => 1,
        ],
        [
            'name' => 'Nebula',
            'species' => 'Heterodon nasicus',
            'species_slug' => 'heterodon-nasicus',
            'age' => '2 Jahre',
            'genetics' => 'Arctic Anaconda het Toffee',
            'special_notes' => '<p>Aktiver Futtersucher mit neugierigem Wesen. Verpaarung für 2025 geplant.</p>',
            'description' => '<p>Nebula zeigt einen außergewöhnlich hellen Rückenverlauf und klare Seitenlinien.</p>',
            'image_path' => 'https://images.unsplash.com/photo-1571832261077-7bf11ef1a978?auto=format&fit=crop&w=1200&q=80',
            'is_showcased' => 1,
        ],
        [
            'name' => 'Solstice',
            'species' => 'Pogona vitticeps',
            'age' => '8 Monate',
            'genetics' => 'Leatherback Citrus',
            'special_notes' => '<p>Aufzuchtsprojekt mit Schwerpunkt UV-Intensität &amp; abwechslungsreicher Ernährung.</p>',
            'description' => '<p>Solstice wächst in unserer Jugendstation auf und gewöhnt sich aktuell an das Handling.</p>',
            'image_path' => 'https://images.unsplash.com/photo-1501706362039-c6e08e3f663e?auto=format&fit=crop&w=1200&q=80',
            'is_showcased' => 1,
        ],
    ];

    foreach ($samples as $sample) {
        create_animal($pdo, $sample);
    }
}

function ensure_demo_adoptions(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM adoption_listings')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $listings = [
        [
            'title' => 'Heterodon nasicus "Pixel"',
            'species' => 'Heterodon nasicus',
            'genetics' => 'Super Arctic Toffeebelly',
            'price' => '350 € VB',
            'description' => '<p>Pixel frisst zuverlässig auf Frostfutter und wurde regelmäßig gewogen. Abgabe nur an erfahrene Halter*innen.</p>',
            'image_path' => 'https://images.unsplash.com/photo-1587731549560-5f72f66445b1?auto=format&fit=crop&w=1200&q=80',
        ],
        [
            'title' => 'Pogona vitticeps Nachwuchs (2.0)',
            'species' => 'Pogona vitticeps',
            'genetics' => 'Red Hypo Leatherback',
            'price' => '180 €',
            'description' => '<p>Nachzucht von 2024 aus getesteten Elterntieren. Bei Interesse stellen wir ein Starter-Setup zusammen.</p>',
            'image_path' => 'https://images.unsplash.com/photo-1526318472351-c75fcf070305?auto=format&fit=crop&w=1200&q=80',
        ],
    ];

    foreach ($listings as $listing) {
        create_listing($pdo, array_merge($listing, [
            'status' => 'available',
            'contact_email' => 'info@example.com',
        ]));
    }
}

function ensure_demo_news(PDO $pdo): void
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM news_posts')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $articles = [
        [
            'title' => 'Neue UV-Station für Jungtiere',
            'excerpt' => 'Unsere Aufzuchtstation erhielt eine moderne UVB-Lichtstrecke mit automatisierter Messung.',
            'content' => '<p>Dank der Unterstützung unserer Community konnten wir die UV-Station komplett modernisieren. Sensoren messen nun tagesaktuell die Bestrahlungsstärke und passen den Tagesverlauf automatisch an.</p>',
        ],
        [
            'title' => 'Morph-Workshop im Vereinshaus',
            'excerpt' => 'Wir luden Expert*innen ein, die neuesten Erkenntnisse zu Heterodon-Genetik zu teilen.',
            'content' => '<p>Der Workshop beleuchtete unter anderem Swiss Chocolate, Arctic-Kombinationen sowie sichere Punnett-Methoden. Alle Folien stehen Mitgliedern im Downloadbereich zur Verfügung.</p>',
        ],
        [
            'title' => 'Tiervermittlung: Drei Erfolgsgeschichten',
            'excerpt' => 'Unsere jüngsten Vermittlungen zeigen, wie wichtig Vor- und Nachbetreuung sind.',
            'content' => '<p>In enger Zusammenarbeit mit den neuen Halter*innen konnten wir individuelle Ernährungs- und Lichtpläne erstellen. Die Tiere haben sich bereits nach kurzer Zeit hervorragend eingewöhnt.</p>',
        ],
    ];

    foreach ($articles as $article) {
        create_news($pdo, array_merge($article, [
            'is_published' => 1,
            'published_at' => date('c'),
        ]));
    }
}

