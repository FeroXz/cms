<?php

const GENETIC_INHERITANCE_MODES = ['recessive', 'dominant', 'incomplete_dominant', 'codominant'];
const GENETIC_MORPH_TYPES = ['recessive', 'dominant', 'codominant', 'polygenic'];
const GENETICS_SUPPORTED_SPECIES = [
    'pogona-vitticeps' => [
        'name' => 'Pogona vitticeps',
        'scientific_name' => 'Pogona vitticeps',
    ],
    'heterodon-nasicus' => [
        'name' => 'Heterodon nasicus',
        'scientific_name' => 'Heterodon nasicus',
    ],
    'pantherophis-guttatus' => [
        'name' => 'Pantherophis guttatus',
        'scientific_name' => 'Pantherophis guttatus',
    ],
    'python-regius' => [
        'name' => 'Python regius',
        'scientific_name' => 'Python regius',
    ],
];

function get_supported_genetic_species(PDO $pdo): array
{
    $records = get_genetic_species($pdo);
    if (!$records) {
        return [];
    }

    $supported = [];
    foreach ($records as $record) {
        if (isset(GENETICS_SUPPORTED_SPECIES[$record['slug']])) {
            $supported[] = $record;
        }
    }

    if (!$supported) {
        return $records;
    }

    usort($supported, static function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    return $supported;
}

function get_genetic_species(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM genetic_species ORDER BY name ASC')->fetchAll();
}

function get_genetic_species_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM genetic_species WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_genetic_species_by_slug(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM genetic_species WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function create_genetic_species(PDO $pdo, array $data): int
{
    $name = trim($data['name'] ?? '');
    if ($name === '') {
        throw new InvalidArgumentException('Species name is required.');
    }

    $slug = trim($data['slug'] ?? '');
    if ($slug === '') {
        $slug = slugify($name);
    }
    $slug = ensure_unique_slug($pdo, 'genetic_species', $slug);

    $stmt = $pdo->prepare('INSERT INTO genetic_species(name, slug, scientific_name, description) VALUES (:name, :slug, :scientific_name, :description)');
    $stmt->execute([
        'name' => $name,
        'slug' => $slug,
        'scientific_name' => trim($data['scientific_name'] ?? '') ?: null,
        'description' => trim($data['description'] ?? '') ?: null,
    ]);

    return (int)$pdo->lastInsertId();
}

function update_genetic_species(PDO $pdo, int $id, array $data): void
{
    $name = trim($data['name'] ?? '');
    if ($name === '') {
        throw new InvalidArgumentException('Species name is required.');
    }

    $slug = trim($data['slug'] ?? '');
    if ($slug === '') {
        $slug = slugify($name);
    }
    $slug = ensure_unique_slug($pdo, 'genetic_species', $slug, $id);

    $stmt = $pdo->prepare('UPDATE genetic_species SET name = :name, slug = :slug, scientific_name = :scientific_name, description = :description, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'slug' => $slug,
        'scientific_name' => trim($data['scientific_name'] ?? '') ?: null,
        'description' => trim($data['description'] ?? '') ?: null,
        'id' => $id,
    ]);
}

function delete_genetic_species(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM genetic_species WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function get_genetic_genes(PDO $pdo, int $speciesId): array
{
    $stmt = $pdo->prepare('SELECT * FROM genetic_genes WHERE species_id = :species ORDER BY is_reference ASC, display_order ASC, name ASC');
    $stmt->execute(['species' => $speciesId]);
    return $stmt->fetchAll();
}

function get_all_genetic_genes(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM genetic_genes')->fetchAll();
}

function get_genetic_gene(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM genetic_genes WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function normalize_inheritance_mode(string $mode): string
{
    $mode = strtolower(trim($mode));
    $mode = str_replace([' ', '-'], '_', $mode);

    if ($mode === 'co_dominant' || $mode === 'codominant') {
        $mode = 'incomplete_dominant';
    }

    if (!in_array($mode, GENETIC_INHERITANCE_MODES, true)) {
        return 'recessive';
    }

    return $mode;
}

function ensure_unique_gene_slug(PDO $pdo, int $speciesId, string $slug, ?int $ignoreId = null): string
{
    $base = $slug ?: bin2hex(random_bytes(4));
    $candidate = $base;
    $counter = 1;

    while (true) {
        $sql = 'SELECT COUNT(*) FROM genetic_genes WHERE species_id = :species AND slug = :slug';
        $params = ['species' => $speciesId, 'slug' => $candidate];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ((int)$stmt->fetchColumn() === 0) {
            return $candidate;
        }
        $candidate = $base . '-' . (++$counter);
    }
}

function create_genetic_gene(PDO $pdo, array $data): int
{
    $speciesId = (int)($data['species_id'] ?? 0);
    if ($speciesId <= 0) {
        throw new InvalidArgumentException('Species reference missing.');
    }
    $name = trim($data['name'] ?? '');
    if ($name === '') {
        throw new InvalidArgumentException('Gene name is required.');
    }

    $slug = trim($data['slug'] ?? '');
    if ($slug === '') {
        $slug = slugify($name);
    }
    $slug = ensure_unique_gene_slug($pdo, $speciesId, $slug);

    $inheritance = normalize_inheritance_mode($data['inheritance_mode'] ?? '');

    $stmt = $pdo->prepare('INSERT INTO genetic_genes(species_id, name, slug, shorthand, inheritance_mode, description, normal_label, heterozygous_label, homozygous_label, is_reference, display_order) VALUES (:species_id, :name, :slug, :shorthand, :inheritance_mode, :description, :normal_label, :heterozygous_label, :homozygous_label, :is_reference, :display_order)');
    $stmt->execute([
        'species_id' => $speciesId,
        'name' => $name,
        'slug' => $slug,
        'shorthand' => trim($data['shorthand'] ?? '') ?: null,
        'inheritance_mode' => $inheritance,
        'description' => trim($data['description'] ?? '') ?: null,
        'normal_label' => trim($data['normal_label'] ?? '') ?: null,
        'heterozygous_label' => trim($data['heterozygous_label'] ?? '') ?: null,
        'homozygous_label' => trim($data['homozygous_label'] ?? '') ?: null,
        'is_reference' => !empty($data['is_reference']) ? 1 : 0,
        'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 0,
    ]);

    return (int)$pdo->lastInsertId();
}

function update_genetic_gene(PDO $pdo, int $id, array $data): void
{
    $gene = get_genetic_gene($pdo, $id);
    if (!$gene) {
        throw new InvalidArgumentException('Gene not found.');
    }

    $name = trim($data['name'] ?? '');
    if ($name === '') {
        throw new InvalidArgumentException('Gene name is required.');
    }

    $slug = trim($data['slug'] ?? '');
    if ($slug === '') {
        $slug = slugify($name);
    }
    $slug = ensure_unique_gene_slug($pdo, (int)$gene['species_id'], $slug, $id);

    $inheritance = normalize_inheritance_mode($data['inheritance_mode'] ?? $gene['inheritance_mode']);

    $stmt = $pdo->prepare('UPDATE genetic_genes SET name = :name, slug = :slug, shorthand = :shorthand, inheritance_mode = :inheritance_mode, description = :description, normal_label = :normal_label, heterozygous_label = :heterozygous_label, homozygous_label = :homozygous_label, is_reference = :is_reference, display_order = :display_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'slug' => $slug,
        'shorthand' => trim($data['shorthand'] ?? '') ?: null,
        'inheritance_mode' => $inheritance,
        'description' => trim($data['description'] ?? '') ?: null,
        'normal_label' => trim($data['normal_label'] ?? '') ?: null,
        'heterozygous_label' => trim($data['heterozygous_label'] ?? '') ?: null,
        'homozygous_label' => trim($data['homozygous_label'] ?? '') ?: null,
        'is_reference' => !empty($data['is_reference']) ? 1 : (int)($gene['is_reference'] ?? 0),
        'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : (int)$gene['display_order'],
        'id' => $id,
    ]);
}

function delete_genetic_gene(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM genetic_genes WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function gene_state_to_alleles(string $state): array
{
    return match ($state) {
        'homozygous' => [1, 1],
        'heterozygous' => [1, 0],
        default => [0, 0],
    };
}

function allele_sum_to_state(int $sum): string
{
    return match ($sum) {
        2 => 'homozygous',
        1 => 'heterozygous',
        default => 'normal',
    };
}

function sanitize_gene_state(?string $state): string
{
    $state = strtolower((string)$state);
    return match ($state) {
        'homozygous', 'super' => 'homozygous',
        'heterozygous', 'het' => 'heterozygous',
        default => 'normal',
    };
}

function gene_state_label(array $gene, string $state): string
{
    $inheritance = $gene['inheritance_mode'];
    $name = $gene['name'];

    $normalDefault = $gene['normal_label'] ?: 'Wildtyp';
    $heteroDefault = $gene['heterozygous_label'];
    $homoDefault = $gene['homozygous_label'];

    if (!$heteroDefault) {
        if ($inheritance === 'recessive') {
            $heteroDefault = 'het ' . $name;
        } else {
            $heteroDefault = $name;
        }
    }
    if (!$homoDefault) {
        if ($inheritance === 'recessive') {
            $homoDefault = $name;
        } elseif ($inheritance === 'dominant') {
            $homoDefault = $name . ' (Super)';
        } else {
            $homoDefault = 'Super ' . $name;
        }
    }

    return match ($state) {
        'heterozygous' => $heteroDefault,
        'homozygous' => $homoDefault,
        default => $normalDefault,
    };
}

function gene_state_is_visual(array $gene, string $state): bool
{
    return match ($gene['inheritance_mode']) {
        'recessive' => $state === 'homozygous',
        'dominant', 'incomplete_dominant', 'codominant' => in_array($state, ['heterozygous', 'homozygous'], true),
        default => false,
    };
}

function gene_state_is_carrier(array $gene, string $state): bool
{
    if ($gene['inheritance_mode'] === 'recessive' && $state === 'heterozygous') {
        return true;
    }
    return false;
}

function calculate_gene_distribution(array $gene, string $parentOneState, string $parentTwoState): array
{
    $allelesOne = gene_state_to_alleles($parentOneState);
    $allelesTwo = gene_state_to_alleles($parentTwoState);

    $distribution = [];
    $total = 0;
    foreach ($allelesOne as $alleleOne) {
        foreach ($allelesTwo as $alleleTwo) {
            $state = allele_sum_to_state($alleleOne + $alleleTwo);
            $distribution[$state] = ($distribution[$state] ?? 0) + 1;
            $total++;
        }
    }

    foreach ($distribution as $state => $count) {
        $distribution[$state] = $count / $total;
    }

    ksort($distribution);

    $states = [];
    foreach ($distribution as $state => $probability) {
        $states[] = [
            'state' => $state,
            'probability' => $probability,
            'label' => gene_state_label($gene, $state),
            'is_visual' => gene_state_is_visual($gene, $state),
            'is_carrier' => gene_state_is_carrier($gene, $state),
        ];
    }

    usort($states, static function ($a, $b) {
        return $b['probability'] <=> $a['probability'];
    });

    return $states;
}

function calculate_genetic_outcomes(array $genes, array $parentOneSelections, array $parentTwoSelections): ?array
{
    $geneResults = [];
    $polygenicResults = [];

    foreach ($genes as $gene) {
        $geneId = (int)$gene['id'];
        $stateOne = sanitize_gene_state($parentOneSelections[$geneId] ?? null);
        $stateTwo = sanitize_gene_state($parentTwoSelections[$geneId] ?? null);
        $inheritanceMode = $gene['inheritance_mode'] ?? '';

        if ($inheritanceMode === 'polygenic') {
            if ($stateOne === 'normal' && $stateTwo === 'normal') {
                continue;
            }

            $note = [
                'state' => 'polygenic',
                'probability' => null,
                'label' => 'Polygenes Merkmal – keine Punnett-Berechnung verfügbar',
                'is_visual' => false,
                'is_carrier' => false,
                'is_polygenic' => true,
            ];

            $entry = [
                'gene' => $gene,
                'states' => [$note],
                'parent_states' => [
                    'parent_one' => $stateOne,
                    'parent_two' => $stateTwo,
                ],
            ];

            $geneResults[$geneId] = $entry;
            $polygenicResults[$geneId] = $entry;
            continue;
        }

        if ($stateOne === 'normal' && $stateTwo === 'normal') {
            continue;
        }

        $states = calculate_gene_distribution($gene, $stateOne, $stateTwo);
        $geneResults[$geneId] = [
            'gene' => $gene,
            'states' => $states,
            'parent_states' => [
                'parent_one' => $stateOne,
                'parent_two' => $stateTwo,
            ],
        ];
    }

    if (empty($geneResults)) {
        return null;
    }

    $combined = [
        [
            'probability' => 1.0,
            'states' => [],
            'labels' => [],
            'visual_traits' => [],
            'carrier_traits' => [],
        ],
    ];

    foreach ($geneResults as $geneId => $geneResult) {
        if (isset($polygenicResults[$geneId])) {
            continue;
        }
        $geneStates = $geneResult['states'];
        $nextCombined = [];
        foreach ($combined as $entry) {
            foreach ($geneStates as $stateInfo) {
                $newStates = $entry['states'];
                $newStates[$geneId] = $stateInfo['state'];

                $newLabels = $entry['labels'];
                $newLabels[$geneId] = $stateInfo['label'];

                $visual = $entry['visual_traits'];
                $carriers = $entry['carrier_traits'];

                if ($stateInfo['is_visual']) {
                    $visual[$geneId] = $stateInfo['label'];
                } elseif ($stateInfo['is_carrier']) {
                    $carriers[$geneId] = $stateInfo['label'];
                }

                $nextCombined[] = [
                    'probability' => $entry['probability'] * $stateInfo['probability'],
                    'states' => $newStates,
                    'labels' => $newLabels,
                    'visual_traits' => $visual,
                    'carrier_traits' => $carriers,
                ];
            }
        }
        $combined = $nextCombined;
    }

    $normalized = [];
    foreach ($combined as $entry) {
        $key = json_encode($entry['states']);
        if (!isset($normalized[$key])) {
            $normalized[$key] = $entry;
        } else {
            $normalized[$key]['probability'] += $entry['probability'];
        }
    }

    $combinedResults = [];
    foreach ($normalized as $entry) {
        $phenotypeParts = [];
        if (!empty($entry['visual_traits'])) {
            $phenotypeParts[] = implode(', ', $entry['visual_traits']);
        }
        if (!empty($entry['carrier_traits'])) {
            $phenotypeParts[] = 'Träger: ' . implode(', ', $entry['carrier_traits']);
        }
        if (empty($phenotypeParts)) {
            $phenotype = 'Wildtyp';
        } else {
            $phenotype = implode(' • ', $phenotypeParts);
        }
        $entry['phenotype'] = $phenotype;
        $combinedResults[] = $entry;
    }

    usort($combinedResults, static function ($a, $b) {
        return $b['probability'] <=> $a['probability'];
    });

    return [
        'genes' => $geneResults,
        'combined' => $combinedResults,
        'polygenic' => array_values($polygenicResults),
    ];
}

function build_gene_slug_index(array $genes): array
{
    $index = [];
    foreach ($genes as $gene) {
        $index[(int)$gene['id']] = $gene['slug'] ?? (string)$gene['id'];
    }

    return $index;
}

function map_gene_selections_to_slugs(array $genes, array $selections): array
{
    $slugIndex = build_gene_slug_index($genes);
    $mapped = [];
    foreach ($selections as $key => $state) {
        $id = (int)$key;
        if (!isset($slugIndex[$id])) {
            continue;
        }
        $mapped[$slugIndex[$id]] = sanitize_gene_state($state);
    }

    return $mapped;
}

function build_gene_lookup_by_slug(array $genes): array
{
    $lookup = [];
    foreach ($genes as $gene) {
        if (!empty($gene['slug'])) {
            $lookup[$gene['slug']] = $gene;
        }
    }

    return $lookup;
}

function summarize_gene_selection(array $genesBySlug, array $slugSelections): array
{
    $summaries = [];
    foreach ($slugSelections as $slug => $state) {
        $gene = $genesBySlug[$slug] ?? null;
        if (!$gene) {
            continue;
        }
        $label = gene_state_label($gene, $state);
        if ($label) {
            $summaries[] = $label;
        }
    }

    return $summaries;
}

function export_gene_results(array $geneResults): array
{
    $export = [];
    foreach ($geneResults as $geneId => $result) {
        $gene = $result['gene'];
        $states = [];
        foreach ($result['states'] as $state) {
            $states[] = [
                'state' => $state['state'],
                'probability' => $state['probability'],
                'label' => $state['label'],
                'is_visual' => $state['is_visual'],
                'is_carrier' => $state['is_carrier'],
                'is_polygenic' => $state['is_polygenic'] ?? false,
            ];
        }

        $export[] = [
            'gene' => [
                'id' => (int)$gene['id'],
                'slug' => $gene['slug'] ?? (string)$geneId,
                'name' => $gene['name'] ?? '',
                'inheritance_mode' => $gene['inheritance_mode'] ?? 'recessive',
            ],
            'states' => $states,
            'parents' => $result['parent_states'] ?? [],
        ];
    }

    return $export;
}

function ensure_supported_genetic_species(PDO $pdo): void
{
    foreach (GENETICS_SUPPORTED_SPECIES as $slug => $meta) {
        $existing = get_genetic_species_by_slug($pdo, $slug);
        $description = $meta['description'] ?? null;

        if ($existing) {
            $updates = [];
            if (trim((string)$existing['name']) === '' && !empty($meta['name'])) {
                $updates['name'] = $meta['name'];
            }
            if (empty($existing['scientific_name']) && !empty($meta['scientific_name'])) {
                $updates['scientific_name'] = $meta['scientific_name'];
            }
            if (!empty($description) && trim((string)$existing['description']) === '') {
                $updates['description'] = $description;
            }

            if ($updates) {
                update_genetic_species($pdo, (int)$existing['id'], array_merge($existing, $updates));
            }
            continue;
        }

        create_genetic_species($pdo, [
            'name' => $meta['name'],
            'slug' => $slug,
            'scientific_name' => $meta['scientific_name'] ?? null,
            'description' => $description,
        ]);
    }
}

function ensure_default_genetics(PDO $pdo): void
{
    ensure_supported_genetic_species($pdo);
    $existing = get_genetic_species_by_slug($pdo, 'heterodon-nasicus');
    $defaultDescription = 'Die westliche Hakennasennatter (<em>Heterodon nasicus</em>) besticht durch eine enorme Bandbreite an rezessiven und inkomplett dominanten Linien. Die hinterlegten Gene dienen als fundierte Ausgangsbasis für Punnett-Berechnungen und Zuchtplanung.';
    if ($existing) {
        $speciesId = (int)$existing['id'];
        $currentDescription = trim((string)($existing['description'] ?? ''));
        if ($currentDescription === 'Western Hognoses (Heterodon nasicus) zeigen eine Vielzahl an rezessiven und inkomplett dominanten Morphen. Die Beispielkonfiguration liefert Startwerte für einen schnellen Einstieg in die Zuchtplanung.' || $currentDescription === '') {
            update_genetic_species($pdo, $speciesId, [
                'name' => $existing['name'],
                'slug' => $existing['slug'],
                'scientific_name' => $existing['scientific_name'],
                'description' => $defaultDescription,
            ]);
        }
    } else {
        $speciesId = create_genetic_species($pdo, [
            'name' => 'Heterodon nasicus',
            'slug' => 'heterodon-nasicus',
            'scientific_name' => 'Heterodon nasicus',
            'description' => $defaultDescription,
        ]);
    }

    $baseGenes = [
        [
            'name' => 'Albino',
            'slug' => 'albino',
            'inheritance_mode' => 'recessive',
            'description' => 'Der Albino-Morph ist ein rezessiver Morph, der die dunklen Töne, also das Melanin, aus der Farbe tilgt. Die Tiere sind meist gelb, orange oder rot und haben rote Augen.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Albino',
            'homozygous_label' => 'Albino',
            'display_order' => 1,
        ],
        [
            'name' => 'Anaconda',
            'slug' => 'anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Co-dominante Zeichnungsreduktion mit Superconda als nahezu patternless Superform.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Anaconda',
            'homozygous_label' => 'Super Anaconda',
            'display_order' => 2,
        ],
        [
            'name' => 'Arctic',
            'slug' => 'arctic',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei Arctic Hakennasennattern ist der Farbkontrast höher, sie haben dunklerer Muster, die zur Mitte hin heller werden. Außerdem haben sie mehr Grautöne als klassische Tiere.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Arctic',
            'homozygous_label' => 'Super Arctic',
            'display_order' => 3,
        ],
        [
            'name' => 'Axanthic',
            'slug' => 'axanthic',
            'inheritance_mode' => 'recessive',
            'description' => 'Beim Axanthic Gen ist kaum bis keine rote Pigmentierung im Tier vorhanden. Als Resultat sieht man komplett graue Schlangen.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Axanthic',
            'homozygous_label' => 'Axanthic',
            'display_order' => 4,
        ],
        [
            'name' => 'Caramel',
            'slug' => 'caramel',
            'inheritance_mode' => 'recessive',
            'description' => 'Caramelfarbenen Hakennasennattern lassen sich an ihrer geringen Melaninkonzentration, die zu einen pinken/pfirsichfarbenen Äußeren führt, erkennen. Eine weitere Charakteristik ist außerdem die abweichende Form der Kopfzeichnung.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Caramel',
            'homozygous_label' => 'Caramel',
            'display_order' => 5,
        ],
        [
            'name' => 'Evans Hypo (Hypo)',
            'slug' => 'evans-hypo-hypo',
            'inheritance_mode' => 'recessive',
            'description' => 'Eine Evans Hypo Hakennasennatter ist eine Form des Albinismus bei dem zwar kein Melanin vorhanden ist, im Gegensatz zu anderen dunklen Farbpigmenten. Das drückt sich vor allem an der Bauchseite deutlich aus.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Evans Hypo (Hypo)',
            'homozygous_label' => 'Evans Hypo (Hypo)',
            'display_order' => 6,
        ],
        [
            'name' => 'Extreme Red/Purple line',
            'slug' => 'extreme-red-purple-line',
            'inheritance_mode' => 'dominant',
            'description' => 'Bei diesen Hakennasennattern liegt eine sehr hohe Sättigung an rotem Farbstoff vor. Das drückt sich entweder in sehr roten oder schon fast dunkellilanen Tieren aus.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'Extreme Red/Purple line',
            'homozygous_label' => 'Extreme Red/Purple line (homozygot)',
            'display_order' => 7,
        ],
        [
            'name' => 'Lavender',
            'slug' => 'lavender',
            'inheritance_mode' => 'recessive',
            'description' => 'Lavender Hakennasennattern tragen kaum Melanin in sich. Dies führt dazu, dass die Tiere einen lavendelfarbenen oder pinken Ton haben.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Lavender',
            'homozygous_label' => 'Lavender',
            'display_order' => 8,
        ],
        [
            'name' => 'Pastel',
            'slug' => 'pastel',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Der Pastel Morph gilt als „Verstärker“. Das Muster wirkt sauberer und die Farben werden verstärkt.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Pastel',
            'homozygous_label' => 'Super Pastel',
            'display_order' => 9,
        ],
        [
            'name' => 'Pistacchio',
            'slug' => 'pistacchio',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Pistacchio Hakennasennattern haben im Gegensatz zu klassischen Tieren, einen lila angehauchten Bauch (statt Schwarz). Außerdem ist ihre Melaninkonzentration niedrig und das Muster hat einen leichten Grünton.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Pistacchio',
            'homozygous_label' => 'Super Pistacchio',
            'display_order' => 10,
        ],
        [
            'name' => 'Sable',
            'slug' => 'sable',
            'inheritance_mode' => 'recessive',
            'description' => 'Bei Sable Hakennasennattern haben eine konzentriertere Pigmentierung . Sie erscheinen dunkler.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Sable',
            'homozygous_label' => 'Sable',
            'display_order' => 11,
        ],
        [
            'name' => 'Savannah',
            'slug' => 'savannah',
            'inheritance_mode' => 'recessive',
            'description' => 'Der Savannah-Morph ist noch relativ neu und eine Art Hypo. Das Tier hat hier dunklere Muster und eine beige Körperfarbe.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Savannah',
            'homozygous_label' => 'Savannah',
            'display_order' => 12,
        ],
        [
            'name' => 'Skullface',
            'slug' => 'skullface',
            'inheritance_mode' => 'dominant',
            'description' => 'Der Skullface-Morph ist erst vor einigen Jahren zum ersten Mal in der Zucht aufgetaucht. Es ist ein dominanter Morph, der die typischen Kopfzeichnungen der Hakennasennatter entfernt.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'Skullface',
            'homozygous_label' => 'Skullface (homozygot)',
            'display_order' => 13,
        ],
        [
            'name' => 'Toffee',
            'slug' => 'toffee',
            'inheritance_mode' => 'recessive',
            'description' => 'Rezessive Linie mit karamellfarbenem Grundton und klarer Zeichnung.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Toffee',
            'homozygous_label' => 'Toffee',
            'display_order' => 14,
        ],
        [
            'name' => 'Toffeebelly',
            'slug' => 'toffeebelly',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Der Toffeebelly Morph reduziert die Melaninkonzentration. Diese Tiere haben öfter Paradoxe Flecken auf Bauch und Körper.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Toffeebelly',
            'homozygous_label' => 'Super Toffeebelly',
            'display_order' => 15,
        ]
    ];

    $referenceMorphs = [
        [
            'name' => 'Albino Anaconda',
            'slug' => 'albino-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Albino Anaconda ist homozygot für Albino und trägt eine Version des Anaconda Gens in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Albino Anaconda',
            'display_order' => 16,
            'is_reference' => true,
        ],
        [
            'name' => 'Albino Frosted',
            'slug' => 'albino-frosted',
            'inheritance_mode' => 'recessive',
            'description' => 'Eine Albino Frosted Hakennasennatter ist eine dreifach Kombination aus Albino, Caramel und Evans Hypo.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Albino Frosted',
            'display_order' => 17,
            'is_reference' => true,
        ],
        [
            'name' => 'Albino Lavender (Coral)',
            'slug' => 'albino-lavender-coral',
            'inheritance_mode' => 'recessive',
            'description' => 'Im Coral Morph drücken sich sowohl das Albino und das Lavender Gen aus. Diese Tiere sind doppelt rezessiv.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Albino Lavender (Coral)',
            'display_order' => 18,
            'is_reference' => true,
        ],
        [
            'name' => 'Albino Superconda',
            'slug' => 'albino-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Albino Superconda ist homozygot für Albino und trägt zwei Versionen des Anaconda Gens in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Albino Superconda',
            'display_order' => 19,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Anaconda',
            'slug' => 'arctic-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Arctic Anaconda hat sowohl eine Version des Arctic Gens, als auch eine Version des Anaconda Gens.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Anaconda',
            'display_order' => 20,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Lavender (Moondust)',
            'slug' => 'arctic-lavender-moondust',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Arctic Lavender Hakennasennatter trägt sowohl das Arctic Gen, als auch das Lavender Gen in sich. Diese Tiere haben eine weiß bis graue Grundfarbe und grau bis lilane Muster.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Lavender (Moondust)',
            'display_order' => 21,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Pistacchio',
            'slug' => 'arctic-pistacchio',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Arctic Pistacchio Hakennasennatter trägt sowohl das Arctic Gen, als auch das Pistacchio Gen in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Pistacchio',
            'display_order' => 22,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Sable',
            'slug' => 'arctic-sable',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Hakennasennattern mit diesem Morph haben weiße und graue Akzente auf bräunlichem Hintergrund.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Sable',
            'display_order' => 23,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Skullface',
            'slug' => 'arctic-skullface',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Hakennasennattern mit sowohl dem Arctic als auch dem Skullface Morph, haben die Farbexpressionen des Arctic Morphs und die fehlende Kopfzeichnung des Skullface Morphs.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Skullface',
            'display_order' => 24,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Superconda (Platinum)',
            'slug' => 'arctic-superconda-platinum',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei einer Arctic Superconda, findet man eine Version des Arctic Gens und zwei Versionen des Anaconda Gens, daher die Superform.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Superconda (Platinum)',
            'display_order' => 25,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Toffee',
            'slug' => 'arctic-toffee',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Beim Arctic Toffee Morph sieht man einen grün, weiß oder grauen Hintergrund und grüne bis orangene Muster.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Toffee',
            'display_order' => 26,
            'is_reference' => true,
        ],
        [
            'name' => 'Arctic Toffee Anaconda',
            'slug' => 'arctic-toffee-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Diese Hakennasennattern haben sowohl die farbliche Entsprechung des Arctic Toffee Gens, als auch die Musterreduktion des Anaconda Gens.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Arctic Toffee Anaconda',
            'display_order' => 27,
            'is_reference' => true,
        ],
        [
            'name' => 'Axanthic Anaconda',
            'slug' => 'axanthic-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Hier sieht man zusätzlich zum Axanthic Morph noch ein reduziertes Muster durch das Anaconda Gen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Axanthic Anaconda',
            'display_order' => 28,
            'is_reference' => true,
        ],
        [
            'name' => 'Axanthic Lavender (Mercury)',
            'slug' => 'axanthic-lavender-mercury',
            'inheritance_mode' => 'recessive',
            'description' => 'Hakennasennattern mit dieser Morphkombination haben dunkellila Pupillen und eine rauchgraue Färbung mit lila und pink Tönen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Axanthic Lavender (Mercury)',
            'display_order' => 29,
            'is_reference' => true,
        ],
        [
            'name' => 'Axanthic Superconda',
            'slug' => 'axanthic-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Superconda Form des Axanthic Morphs ist eine komplett graue Schlange ohne Zeichnungen auf dem Körper.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Axanthic Superconda',
            'display_order' => 30,
            'is_reference' => true,
        ],
        [
            'name' => 'Caramel Anaconda',
            'slug' => 'caramel-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Anacondaform der Caramel Hakennasennatter zeigt ein reduziertes Muster und die Farbgebung des Caramel Morphs.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Caramel Anaconda',
            'display_order' => 31,
            'is_reference' => true,
        ],
        [
            'name' => 'Caramel Ghost',
            'slug' => 'caramel-ghost',
            'inheritance_mode' => 'recessive',
            'description' => 'Eine Caramel-Ghost Hakennasennatter trägt sowohl das Caramel, das Axanthic und das Hypo Gen in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Caramel Ghost',
            'display_order' => 32,
            'is_reference' => true,
        ],
        [
            'name' => 'Caramel Ghost Anaconda',
            'slug' => 'caramel-ghost-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei diesem dreifach Morph hat die Schlange zusätzlich noch eine Version des Anaconda Gens.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Caramel Ghost Anaconda',
            'display_order' => 33,
            'is_reference' => true,
        ],
        [
            'name' => 'Caramel Ghost Superconda',
            'slug' => 'caramel-ghost-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Wenn eine Caramel-Ghost Hakennasennatter zwei Versionen des Anaconda Gens in sich trägt erhält man die Superform des Morphs.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Caramel Ghost Superconda',
            'display_order' => 34,
            'is_reference' => true,
        ],
        [
            'name' => 'Caramel Superconda',
            'slug' => 'caramel-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Sobald eine Caramel Hakennasennatter zwei Versionen des Anaconda Gens in sich trägt, spricht man von einer Caramel Superconda. Dieses Tier hat kein Muster auf dem Körper.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Caramel Superconda',
            'display_order' => 35,
            'is_reference' => true,
        ],
        [
            'name' => 'Cataleja (Lavender-Caramel)',
            'slug' => 'cataleja-lavender-caramel',
            'inheritance_mode' => 'recessive',
            'description' => 'Bei diesem Morph wechseln sich Caramel und Lavender im Farbausdruck ab. Die Grundfarbe geht ins Graue und sie haben rubinfarbene Augen und Zungen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Cataleja (Lavender-Caramel)',
            'display_order' => 36,
            'is_reference' => true,
        ],
        [
            'name' => 'Classic Anaconda',
            'slug' => 'classic-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Das einfache Anaconda Gen bewirkt eine Reduktion des Musters. Hier hat die Schlange weiterhin, die klassische in der Natur vorkommende Farbe.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Classic Anaconda',
            'display_order' => 37,
            'is_reference' => true,
        ],
        [
            'name' => 'Classic Superconda',
            'slug' => 'classic-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die zweifache Expression des Anaconda Gens, wird Superconda genannt. Hier hat das Tier keine Zeichnungen auf dem Körper.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Classic Superconda',
            'display_order' => 38,
            'is_reference' => true,
        ],
        [
            'name' => 'Classic/Wildtype',
            'slug' => 'classic-wildtype',
            'inheritance_mode' => 'recessive',
            'description' => 'Die Classic oder Wildtype Farbgebung der Hakennasennatter ist die, die in der Natur standardmäßig vorkommt. Die Schlange hat hier verschiedene braun, beige und teilweise auch schwarz Töne.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Classic/Wildtype',
            'display_order' => 39,
            'is_reference' => true,
        ],
        [
            'name' => 'Frosted (Caramel-Hypo)',
            'slug' => 'frosted-caramel-hypo',
            'inheritance_mode' => 'recessive',
            'description' => 'Frosted nennt man eine Hakennasennatter, die sowohl das Caramel und das Hypo Gen in sich trägt.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Frosted (Caramel-Hypo)',
            'display_order' => 40,
            'is_reference' => true,
        ],
        [
            'name' => 'Frosted Anaconda',
            'slug' => 'frosted-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Frosted Anaconda trägt sowohl zwei Farbgene in sich, als auch eine Version des Anaconda Gens.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Frosted Anaconda',
            'display_order' => 41,
            'is_reference' => true,
        ],
        [
            'name' => 'Frosted Superconda',
            'slug' => 'frosted-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Frosted Superconda tragt das Caramel, das Hypo Gen und zwei Versionen des Anaconda Gens in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Frosted Superconda',
            'display_order' => 42,
            'is_reference' => true,
        ],
        [
            'name' => 'Ghost (Axanthic-Hypo)',
            'slug' => 'ghost-axanthic-hypo',
            'inheritance_mode' => 'recessive',
            'description' => 'Ghost Hakennasennattern tragen sowohl das Axanthic als auch das Hypo Gen in sich. Sie haben eine beige Farbe und dunklerer Augen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Ghost (Axanthic-Hypo)',
            'display_order' => 43,
            'is_reference' => true,
        ],
        [
            'name' => 'Ghost Anaconda',
            'slug' => 'ghost-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Ghost Anaconda zeigt die typische beige Farbe, jedoch mit einem durch das Anaconda Gen reduziertem Muster.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Ghost Anaconda',
            'display_order' => 44,
            'is_reference' => true,
        ],
        [
            'name' => 'Ghost Superconda',
            'slug' => 'ghost-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Ghost Superconda ist eine rein beige bis weißliche Schlange. Die einzigen Muster sind auf dem Kopf zu sehen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Ghost Superconda',
            'display_order' => 45,
            'is_reference' => true,
        ],
        [
            'name' => 'Green Goblin (Pistacchio Anaconda)',
            'slug' => 'green-goblin-pistacchio-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei dieser Form des Pistacchio Morphs liegt auch eine Version des Anaconda Gens vor, welches das Muster reduziert.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Green Goblin (Pistacchio Anaconda)',
            'display_order' => 46,
            'is_reference' => true,
        ],
        [
            'name' => 'Hypo Anaconda',
            'slug' => 'hypo-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Hypo Anaconda ist Träger des Hypo Gen und einer Version des Anaconda Gens.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Hypo Anaconda',
            'display_order' => 47,
            'is_reference' => true,
        ],
        [
            'name' => 'Hypo Superconda',
            'slug' => 'hypo-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Superconda Version des Hypo Morphs hat kein Muster und eine helle gelbliche oder orangene Färbung.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Hypo Superconda',
            'display_order' => 48,
            'is_reference' => true,
        ],
        [
            'name' => 'Lavender Anaconda',
            'slug' => 'lavender-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Lavender Anaconda hat wie alle Anaconda Morphe ein reduziertes Muster. Die Lavender Farbgebung bleibt unverändert.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Lavender Anaconda',
            'display_order' => 49,
            'is_reference' => true,
        ],
        [
            'name' => 'Lavender Frosted',
            'slug' => 'lavender-frosted',
            'inheritance_mode' => 'recessive',
            'description' => 'Eine Lavender Frosted Hakennasennatter ist Träger dreier Gene. Diese sind das Lavender, das Caramel und das Hypo Gen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Lavender Frosted',
            'display_order' => 50,
            'is_reference' => true,
        ],
        [
            'name' => 'Lavender Skullface',
            'slug' => 'lavender-skullface',
            'inheritance_mode' => 'dominant',
            'description' => 'Bei der Lavender Skullface Hakennasennatter trägt das Lavender Tier auch noch das dominante Skullface Gen in sich, das die Kopfzeichnungen eliminiert.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Lavender Skullface',
            'display_order' => 51,
            'is_reference' => true,
        ],
        [
            'name' => 'Lavender Superconda',
            'slug' => 'lavender-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Lavender Superconda ist eine roséfarbene Hakennasennatter, frei von sämtlichen Körperzeichnungen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Lavender Superconda',
            'display_order' => 52,
            'is_reference' => true,
        ],
        [
            'name' => 'Lucy / Leucistic',
            'slug' => 'lucy-leucistic',
            'inheritance_mode' => 'recessive',
            'description' => 'Leuzistische Hakennasennattern sind komplett weiße Schlangen, mit blauen, grauen oder schwarzen Augen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Lucy / Leucistic',
            'display_order' => 53,
            'is_reference' => true,
        ],
        [
            'name' => 'MaiTai (Sable-Toffee)',
            'slug' => 'maitai-sable-toffee',
            'inheritance_mode' => 'recessive',
            'description' => 'Hakennasennattern dieses Morphes haben eine orangene Färbung.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: MaiTai (Sable-Toffee)',
            'display_order' => 54,
            'is_reference' => true,
        ],
        [
            'name' => 'Mojito',
            'slug' => 'mojito',
            'inheritance_mode' => 'recessive',
            'description' => 'Coming Soon – Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Mojito',
            'display_order' => 55,
            'is_reference' => true,
        ],
        [
            'name' => 'Pastel Anaconda',
            'slug' => 'pastel-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Pastel Anaconda hat zusätzlich zur verstärkten Farbgebung noch ein reduziertes Muster durch das Anaconda Gen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Pastel Anaconda',
            'display_order' => 56,
            'is_reference' => true,
        ],
        [
            'name' => 'Pastel Superconda',
            'slug' => 'pastel-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Superform des Pastel-Morphs hat keine Körperzeichnungen, dafür aber eine kräftige Körperfarbe und klare Kopfzeichnungen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Pastel Superconda',
            'display_order' => 57,
            'is_reference' => true,
        ],
        [
            'name' => 'Pink Panther (Pink-Pastel-Albino Anaconda)',
            'slug' => 'pink-panther-pink-pastel-albino-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Pink Panther nennt man die Anaconda-Form des Pink-Pastel-Albino-Morphs.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Pink Panther (Pink-Pastel-Albino Anaconda)',
            'display_order' => 58,
            'is_reference' => true,
        ],
        [
            'name' => 'Pink Pastel Albino',
            'slug' => 'pink-pastel-albino',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Diese Form des Albinismus, drück sich in einem pinken Ton aus. Jungtiere kommen in einem kräftigen Farbton aus, welcher im Laufe der Zeit immer heller wird.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Pink Pastel Albino',
            'display_order' => 59,
            'is_reference' => true,
        ],
        [
            'name' => 'Pink Pastel Albino Superconda',
            'slug' => 'pink-pastel-albino-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei der Supercondaform hat das Tier keine Körperzeichnungen. Die Farbe ist hier ein schöner pinkfarbener Ton.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Pink Pastel Albino Superconda',
            'display_order' => 60,
            'is_reference' => true,
        ],
        [
            'name' => 'Snow (Albino-Axanthic)',
            'slug' => 'snow-albino-axanthic',
            'inheritance_mode' => 'recessive',
            'description' => 'Snow Hakennasennattern tragen sowohl das Albino als auch das Axanthic Gen in sich. Sie haben rote Augen und ein weiß und pinkfarbene Färbung.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Snow (Albino-Axanthic)',
            'display_order' => 61,
            'is_reference' => true,
        ],
        [
            'name' => 'Snow Anaconda (Yeti)',
            'slug' => 'snow-anaconda-yeti',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Anaconda-Form des Snow Morphs wird Yeti genannt. Das pink bis weißfarbene Tier hat hier ein reduziertes und helleres Muster.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Snow Anaconda (Yeti)',
            'display_order' => 62,
            'is_reference' => true,
        ],
        [
            'name' => 'Snow Ghost',
            'slug' => 'snow-ghost',
            'inheritance_mode' => 'recessive',
            'description' => 'Wenn nun eine Hakennasennatter zusätzlich zu den Albino und Axanthix Genen noch das Hypo Gen hat, dann nennt man diese Kombination Snow Ghost. Das Tier ist hier weiß, mit hellen pink und grau Tönen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Snow Ghost',
            'display_order' => 63,
            'is_reference' => true,
        ],
        [
            'name' => 'Snow Ghost Anaconda',
            'slug' => 'snow-ghost-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Der dreifach Morph Axanthic-Albino-Hypo Morph wird hierbei noch durch den Anaconda Morph komplementiert. Das Tier hat reduzierte Zeichnungen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Snow Ghost Anaconda',
            'display_order' => 64,
            'is_reference' => true,
        ],
        [
            'name' => 'Snow Superconda (Superyeti)',
            'slug' => 'snow-superconda-superyeti',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Superyeti Hakennasennatter erhält man, wenn ein Tier zwei Versionen des Anaconda Gens und sowohl das Axanthix als auch das Albino Gen in sich trägt. Als Resultat erhält man eine fast weiße Schlange mit einem leicht pinken Ton.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Snow Superconda (Superyeti)',
            'display_order' => 65,
            'is_reference' => true,
        ],
        [
            'name' => 'Super Green Goblin',
            'slug' => 'super-green-goblin',
            'inheritance_mode' => 'recessive',
            'description' => 'Coming Soon – Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Super Green Goblin',
            'display_order' => 66,
            'is_reference' => true,
        ],
        [
            'name' => 'Superarctic',
            'slug' => 'superarctic',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Wenn eine Hakennasennatter zwei Versionen des Arctic Gens in sich trägt, dann spricht man von der Superarctic-Form. Der Grundton ist hier ein helles Grau und die Muster sind dunkel grau, schwarz oder braun und werden zur Mitte hin heller.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Superarctic',
            'display_order' => 67,
            'is_reference' => true,
        ],
        [
            'name' => 'Superarctic Anaconda',
            'slug' => 'superarctic-anaconda',
            'inheritance_mode' => 'dominant',
            'description' => 'Bei diesem Morph trägt das Tier zwei Co-Dominante Gene in sich. Sowohl das Artic, als auch das Anaconda Gen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Superarctic Anaconda',
            'display_order' => 68,
            'is_reference' => true,
        ],
        [
            'name' => 'Superarctic Lavender (Moonstone)',
            'slug' => 'superarctic-lavender-moonstone',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Moonstone Hakennasennatter entsteht aus der Superform des Arctic Morps sowie dem rezessiven Lavender Gen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Superarctic Lavender (Moonstone)',
            'display_order' => 69,
            'is_reference' => true,
        ],
        [
            'name' => 'Superarctic Superconda',
            'slug' => 'superarctic-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Superarctic Superconda hat sowohl zwei Versionen des Arctic, als auch zwei Versionen des Anaconda Gens in sich. Diese Tiere haben oftmals einen dunklen Rückenstreifen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Superarctic Superconda',
            'display_order' => 70,
            'is_reference' => true,
        ],
        [
            'name' => 'Swiss Chocolat',
            'slug' => 'swiss-chocolat',
            'inheritance_mode' => 'recessive',
            'description' => 'Swiss Chocolat ist ein Hypermelanistischer Morph. Die Hakennasennattern haben einen dunklen, fast einfarbigen Kopf.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Swiss Chocolat',
            'display_order' => 71,
            'is_reference' => true,
        ],
        [
            'name' => 'Toffeebelly Anaconda',
            'slug' => 'toffeebelly-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei der Anaconda-Form des Toffeebelly Morphs sieht man eine reduziertes Muster und teilweise auch kräftigere Farben.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toffeebelly Anaconda',
            'display_order' => 72,
            'is_reference' => true,
        ],
        [
            'name' => 'Toffeebelly Superconda (Candy)',
            'slug' => 'toffeebelly-superconda-candy',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Toffeebelly Superconda zeigt sowohl die bräunliche Farbgebung des Toffeebelly Morphs als auch keine Musterbildung.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toffeebelly Superconda (Candy)',
            'display_order' => 73,
            'is_reference' => true,
        ],
        [
            'name' => 'Toffeeglow (Toffee-Albino)',
            'slug' => 'toffeeglow-toffee-albino',
            'inheritance_mode' => 'recessive',
            'description' => 'Toffeeglow Hakennasennattern tragen sowohl das Toffeebelly als auch das Albino Gen in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toffeeglow (Toffee-Albino)',
            'display_order' => 74,
            'is_reference' => true,
        ],
        [
            'name' => 'Toffeeglow Anaconda',
            'slug' => 'toffeeglow-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Zusätzlich zu den Albino und Toffeebelly Genen hat die Hakennasennatter hier noch eine Version des Anaconda Gens in sich.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toffeeglow Anaconda',
            'display_order' => 75,
            'is_reference' => true,
        ],
        [
            'name' => 'Toffeeglow Superconda',
            'slug' => 'toffeeglow-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Toffeeglow Superconda ist die Superform des Toffeeglow Morphs. Man sieht hier hellgelbe Tiere mit leicht orangenen Kopfzeichnungen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toffeeglow Superconda',
            'display_order' => 76,
            'is_reference' => true,
        ],
        [
            'name' => 'Toxic (Axanthic-Toffee)',
            'slug' => 'toxic-axanthic-toffee',
            'inheritance_mode' => 'recessive',
            'description' => 'Bei diesem Morph haben die Hakennasennattern eine helle pinke bis weiße Grundfärbung und graue Augen.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toxic (Axanthic-Toffee)',
            'display_order' => 77,
            'is_reference' => true,
        ],
        [
            'name' => 'Toxic Anaconda',
            'slug' => 'toxic-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Toxic Anaconda ist eine helle weißlich, pink oder graue Schlange mit nur etwas dunkleren Mustern, die durch das Anaconda Gen reduziert sind.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toxic Anaconda',
            'display_order' => 78,
            'is_reference' => true,
        ],
        [
            'name' => 'Toxic Superconda',
            'slug' => 'toxic-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Bei zwei Versionen des Anaconda Gens, sieht man hier eine Toxic Superconda, die weiß oder gräulich erscheint.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Toxic Superconda',
            'display_order' => 79,
            'is_reference' => true,
        ],
        [
            'name' => 'Ultra (Albino-Caramel)',
            'slug' => 'ultra-albino-caramel',
            'inheritance_mode' => 'recessive',
            'description' => 'Hakennasennattern mit dieser Morph-Kombination haben die Bauchfärbung von Albinos und die Rückenfärbung von Caramels.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Ultra (Albino-Caramel)',
            'display_order' => 80,
            'is_reference' => true,
        ],
        [
            'name' => 'Ultra Anaconda',
            'slug' => 'ultra-anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Eine Ultra Anaconda Hakennasennattern ist eine Albino-Caramel Tier mit einer durch das Anaconda Gen reduzierten Körperzeichnung.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Ultra Anaconda',
            'display_order' => 81,
            'is_reference' => true,
        ],
        [
            'name' => 'Ultra Superconda',
            'slug' => 'ultra-superconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Die Superconda-Form des Ultra Morphs zeigt Schlangen mit einer sehr hellen, etwas gelblichen Grundfarbe. Der Bauch ist wie beim Albino Morph beige.',
            'normal_label' => 'Nicht kombiniert',
            'heterozygous_label' => 'Teil-Kombination',
            'homozygous_label' => 'Komplett: Ultra Superconda',
            'display_order' => 82,
            'is_reference' => true,
        ]
    ];

    foreach (array_merge($baseGenes, $referenceMorphs) as $sample) {
        $stmt = $pdo->prepare('SELECT id FROM genetic_genes WHERE species_id = :species AND slug = :slug');
        $stmt->execute([
            'species' => $speciesId,
            'slug' => $sample['slug'],
        ]);
        if ($stmt->fetchColumn()) {
            continue;
        }
        create_genetic_gene($pdo, array_merge($sample, ['species_id' => $speciesId]));
    }
}

function normalize_morph_identifier(string $value): string
{
    return slugify(str_replace(['/', '_', '.'], '-', strtolower(trim($value))));
}

function normalize_morph_type(string $type): string
{
    $value = strtolower(trim($type));
    $value = str_replace(['_', ' '], '-', $value);

    $map = [
        'co-dominant' => 'codominant',
        'co-dominance' => 'codominant',
        'codominant' => 'codominant',
        'co-dominant-(inkomplett)' => 'codominant',
        'incomplete-dominant' => 'codominant',
        'inkomplett-dominant' => 'codominant',
        'dominant' => 'dominant',
        'recessive' => 'recessive',
        'rezessiv' => 'recessive',
        'polygenic' => 'polygenic',
        'polygenetic' => 'polygenic',
    ];

    if (isset($map[$value])) {
        return $map[$value];
    }

    $fallback = preg_replace('/[^a-z]/', '', $value);
    if ($fallback !== '' && in_array($fallback, GENETIC_MORPH_TYPES, true)) {
        return $fallback;
    }

    return 'polygenic';
}

function parse_morph_aliases(?string $raw): array
{
    if ($raw === null || trim($raw) === '') {
        return [];
    }

    $parts = preg_split('/[;,|]/', $raw);
    $normalized = [];
    foreach ($parts as $part) {
        $trimmed = trim($part);
        if ($trimmed === '') {
            continue;
        }
        $normalized[] = $trimmed;
    }

    return array_values(array_unique($normalized));
}

function decode_morph_aliases(?string $value): array
{
    if (!$value) {
        return [];
    }

    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map('strval', $decoded)));
    }

    return [];
}

function build_existing_morph_index(PDO $pdo, string $speciesSlug): array
{
    $stmt = $pdo->prepare('SELECT * FROM genetic_morphs WHERE species_slug = :slug');
    $stmt->execute(['slug' => $speciesSlug]);
    $records = $stmt->fetchAll();

    $index = [
        'records' => [],
        'by_name' => [],
        'by_alias' => [],
    ];

    foreach ($records as $record) {
        $index['records'][(int)$record['id']] = $record;
        $index['by_name'][$record['normalized_name']] = $record;
        foreach (decode_morph_aliases($record['normalized_aliases'] ?? '') as $alias) {
            $index['by_alias'][$alias] = $record;
        }
    }

    return $index;
}

function get_genetic_morphs_for_species(PDO $pdo, string $speciesSlug): array
{
    $stmt = $pdo->prepare('SELECT * FROM genetic_morphs WHERE species_slug = :slug ORDER BY display_name ASC');
    $stmt->execute(['slug' => $speciesSlug]);
    $records = $stmt->fetchAll();

    return array_map('transform_genetic_morph', $records ?: []);
}

function get_genetic_morphs_by_ids(PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM genetic_morphs WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $result[(int)$row['id']] = transform_genetic_morph($row);
    }

    return $result;
}

function transform_genetic_morph(array $record): array
{
    return [
        'id' => (int)$record['id'],
        'species_slug' => $record['species_slug'],
        'species_name' => $record['species_name'],
        'name' => $record['display_name'],
        'slug' => $record['normalized_name'],
        'type' => $record['morph_type'],
        'aliases' => decode_morph_aliases($record['aliases'] ?? null),
        'description' => $record['description'] ?? null,
        'source_url' => $record['source_url'] ?? null,
    ];
}
function update_morph_index_entry(array &$index, array $record): void
{
    $index['records'][(int)$record['id']] = $record;
    $index['by_name'][$record['normalized_name']] = $record;
    foreach (decode_morph_aliases($record['normalized_aliases'] ?? '') as $alias) {
        $index['by_alias'][$alias] = $record;
    }
}

function find_existing_morph(array $index, string $normalizedName, array $normalizedAliases): ?array
{
    if (isset($index['by_name'][$normalizedName])) {
        return $index['by_name'][$normalizedName];
    }

    foreach ($normalizedAliases as $alias) {
        if (isset($index['by_name'][$alias])) {
            return $index['by_name'][$alias];
        }
        if (isset($index['by_alias'][$alias])) {
            return $index['by_alias'][$alias];
        }
    }

    return null;
}

function prepare_morph_preview_row(array $row, string $action, ?string $note = null): array
{
    return [
        'line' => $row['line'],
        'name' => $row['display_name'],
        'species' => $row['species_name'],
        'type' => $row['morph_type'],
        'aliases' => $row['aliases'] ? implode(', ', $row['aliases']) : '',
        'action' => $action,
        'note' => $note,
    ];
}

function get_last_morph_import_preview(): array
{
    if (!empty($_SESSION['morph_import_preview']['rows']) && is_array($_SESSION['morph_import_preview']['rows'])) {
        return $_SESSION['morph_import_preview']['rows'];
    }

    return [];
}

function get_default_morph_preview_rows(): array
{
    $files = [
        __DIR__ . '/../seed/pogona_morphs_minimal.csv',
        __DIR__ . '/../seed/cornsnake_morphs_minimal.csv',
        __DIR__ . '/../seed/ballpython_morphs_minimal.csv',
    ];

    $rows = [];
    foreach ($files as $file) {
        if (!is_file($file) || !is_readable($file)) {
            continue;
        }

        if (($handle = fopen($file, 'rb')) === false) {
            continue;
        }

        $headers = fgetcsv($handle) ?: [];
        $line = 1;
        while (($data = fgetcsv($handle)) !== false && count($rows) < 10) {
            $line++;
            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[strtolower(trim((string)$header))] = $data[$index] ?? '';
            }
            $rows[] = [
                'line' => $line,
                'name' => $assoc['name'] ?? '',
                'species' => $assoc['species'] ?? ($assoc['trait_type'] ?? ''),
                'type' => $assoc['type'] ?? ($assoc['trait_type'] ?? ''),
                'aliases' => $assoc['aliases'] ?? '',
                'action' => 'sample',
                'note' => basename($file),
            ];
        }

        fclose($handle);

        if (count($rows) >= 10) {
            break;
        }
    }

    return $rows;
}

function import_genetic_morphs_from_csv(PDO $pdo, string $filePath, array $options = []): array
{
    $dryRun = !empty($options['dry_run']);
    $columnMap = $options['column_map'] ?? [];
    $previewLimit = isset($options['preview_limit']) ? (int)$options['preview_limit'] : 10;

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
        $normalized = strtolower(trim((string)$label));
        $headerMap[$normalized] = $index;
    }

    $resolveColumn = function (string $field) use ($columnMap, $headerMap) {
        $requested = strtolower(trim((string)($columnMap[$field] ?? '')));
        if ($requested !== '' && $requested !== '__skip__' && isset($headerMap[$requested])) {
            return $headerMap[$requested];
        }

        if (isset($headerMap[$field])) {
            return $headerMap[$field];
        }

        return null;
    };

    $requiredFields = ['name', 'species', 'type'];
    $preparedRows = [];
    $previewRows = [];
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

    $seen = [];
    $lineNumber = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNumber++;
        $summary['total']++;

        $values = [];
        foreach (['name', 'species', 'type', 'aliases', 'description', 'sourceurl'] as $field) {
            $columnIndex = $resolveColumn($field);
            $values[$field] = $columnIndex !== null ? trim((string)($row[$columnIndex] ?? '')) : '';
        }

        $missing = [];
        foreach ($requiredFields as $field) {
            if ($values[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $summary['skipped']++;
            $summary['errors'][] = sprintf('Zeile %d: fehlende Pflichtfelder (%s)', $lineNumber, implode(', ', $missing));
            continue;
        }

        $speciesName = $values['species'];
        $speciesSlug = normalize_morph_identifier($speciesName);
        $displayName = $values['name'];
        $normalizedName = normalize_morph_identifier($displayName);
        $aliasList = parse_morph_aliases($values['aliases']);
        $normalizedAliases = [];
        foreach ($aliasList as $alias) {
            $normalizedAlias = normalize_morph_identifier($alias);
            if ($normalizedAlias !== '' && $normalizedAlias !== $normalizedName) {
                $normalizedAliases[] = $normalizedAlias;
            }
        }
        $normalizedAliases = array_values(array_unique($normalizedAliases));

        if (!isset($seen[$speciesSlug])) {
            $seen[$speciesSlug] = [];
        }

        $duplicateFound = isset($seen[$speciesSlug][$normalizedName]);
        if (!$duplicateFound) {
            foreach ($normalizedAliases as $alias) {
                if (isset($seen[$speciesSlug][$alias])) {
                    $duplicateFound = true;
                    break;
                }
            }
        }

        if ($duplicateFound) {
            $summary['duplicates']++;
            if (count($previewRows) < $previewLimit) {
                $previewRows[] = [
                    'line' => $lineNumber,
                    'name' => $displayName,
                    'species' => $speciesName,
                    'type' => $values['type'],
                    'aliases' => $values['aliases'],
                    'action' => 'duplicate',
                    'note' => 'In Datei dupliziert',
                ];
            }
            continue;
        }

        $seen[$speciesSlug][$normalizedName] = true;
        foreach ($normalizedAliases as $alias) {
            $seen[$speciesSlug][$alias] = true;
        }

        $typeNormalized = normalize_morph_type($values['type']);
        if (!in_array($typeNormalized, GENETIC_MORPH_TYPES, true)) {
            $summary['warnings'][] = sprintf('Zeile %d: unbekannter Morph-Typ "%s" – als polygenic behandelt.', $lineNumber, $values['type']);
            $typeNormalized = 'polygenic';
        }

        $sourceUrl = $values['sourceurl'] !== '' ? $values['sourceurl'] : null;
        if ($sourceUrl && !filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            $summary['warnings'][] = sprintf('Zeile %d: ungültige URL "%s" verworfen.', $lineNumber, $sourceUrl);
            $sourceUrl = null;
        }

        $prepared = [
            'line' => $lineNumber,
            'display_name' => $displayName,
            'species_name' => $speciesName,
            'species_slug' => $speciesSlug,
            'normalized_name' => $normalizedName,
            'aliases' => $aliasList,
            'normalized_aliases' => $normalizedAliases,
            'morph_type' => $typeNormalized,
            'description' => $values['description'] !== '' ? $values['description'] : null,
            'source_url' => $sourceUrl,
        ];

        $preparedRows[] = $prepared;
        $summary['valid']++;

        if (count($previewRows) < $previewLimit) {
            $previewRows[] = [
                'line' => $lineNumber,
                'name' => $displayName,
                'species' => $speciesName,
                'type' => $values['type'],
                'aliases' => $values['aliases'],
                'action' => 'pending',
                'note' => null,
            ];
        }
    }

    fclose($handle);

    if (!$dryRun) {
        $pdo->beginTransaction();
    }

    $existingCache = [];

    try {
        foreach ($preparedRows as $prepared) {
            if (!isset($existingCache[$prepared['species_slug']])) {
                $existingCache[$prepared['species_slug']] = build_existing_morph_index($pdo, $prepared['species_slug']);
            }

            $index =& $existingCache[$prepared['species_slug']];
            $existing = find_existing_morph($index, $prepared['normalized_name'], $prepared['normalized_aliases']);

            $aliasesJson = $prepared['aliases'] ? json_encode($prepared['aliases'], JSON_UNESCAPED_UNICODE) : null;
            $normalizedAliasesJson = $prepared['normalized_aliases'] ? json_encode($prepared['normalized_aliases']) : null;

            if ($existing === null) {
                $summary['created']++;
                if (!$dryRun) {
                    $stmt = $pdo->prepare('INSERT INTO genetic_morphs (species_slug, species_name, display_name, normalized_name, morph_type, aliases, normalized_aliases, description, source_url) VALUES (:species_slug, :species_name, :display_name, :normalized_name, :morph_type, :aliases, :normalized_aliases, :description, :source_url)');
                    $stmt->execute([
                        'species_slug' => $prepared['species_slug'],
                        'species_name' => $prepared['species_name'],
                        'display_name' => $prepared['display_name'],
                        'normalized_name' => $prepared['normalized_name'],
                        'morph_type' => $prepared['morph_type'],
                        'aliases' => $aliasesJson,
                        'normalized_aliases' => $normalizedAliasesJson,
                        'description' => $prepared['description'],
                        'source_url' => $prepared['source_url'],
                    ]);

                    $newRecord = [
                        'id' => (int)$pdo->lastInsertId(),
                        'species_slug' => $prepared['species_slug'],
                        'species_name' => $prepared['species_name'],
                        'display_name' => $prepared['display_name'],
                        'normalized_name' => $prepared['normalized_name'],
                        'morph_type' => $prepared['morph_type'],
                        'aliases' => $aliasesJson,
                        'normalized_aliases' => $normalizedAliasesJson,
                        'description' => $prepared['description'],
                        'source_url' => $prepared['source_url'],
                    ];
                    update_morph_index_entry($index, $newRecord);
                } else {
                    $newRecord = [
                        'id' => 0,
                        'species_slug' => $prepared['species_slug'],
                        'species_name' => $prepared['species_name'],
                        'display_name' => $prepared['display_name'],
                        'normalized_name' => $prepared['normalized_name'],
                        'morph_type' => $prepared['morph_type'],
                        'aliases' => $aliasesJson,
                        'normalized_aliases' => $normalizedAliasesJson,
                        'description' => $prepared['description'],
                        'source_url' => $prepared['source_url'],
                    ];
                    update_morph_index_entry($index, $newRecord);
                }

                if (count($previewRows) < $previewLimit) {
                    $previewRows[] = prepare_morph_preview_row($prepared, 'create');
                }
                continue;
            }

            $existingAliases = decode_morph_aliases($existing['aliases'] ?? '');
            $existingNormalizedAliases = decode_morph_aliases($existing['normalized_aliases'] ?? '');

            $needsUpdate = false;
            foreach (['display_name', 'species_name', 'morph_type', 'description', 'source_url'] as $field) {
                $existingValue = $existing[$field] ?? null;
                if ($existingValue != $prepared[$field]) {
                    $needsUpdate = true;
                    break;
                }
            }

            if (!$needsUpdate && ($existingAliases !== $prepared['aliases'] || $existingNormalizedAliases !== $prepared['normalized_aliases'])) {
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $summary['updated']++;
                if (!$dryRun) {
                    $stmt = $pdo->prepare('UPDATE genetic_morphs SET species_name = :species_name, display_name = :display_name, morph_type = :morph_type, aliases = :aliases, normalized_aliases = :normalized_aliases, description = :description, source_url = :source_url, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                    $stmt->execute([
                        'species_name' => $prepared['species_name'],
                        'display_name' => $prepared['display_name'],
                        'morph_type' => $prepared['morph_type'],
                        'aliases' => $aliasesJson,
                        'normalized_aliases' => $normalizedAliasesJson,
                        'description' => $prepared['description'],
                        'source_url' => $prepared['source_url'],
                        'id' => $existing['id'],
                    ]);

                    $updatedRecord = array_merge($existing, [
                        'species_name' => $prepared['species_name'],
                        'display_name' => $prepared['display_name'],
                        'morph_type' => $prepared['morph_type'],
                        'aliases' => $aliasesJson,
                        'normalized_aliases' => $normalizedAliasesJson,
                        'description' => $prepared['description'],
                        'source_url' => $prepared['source_url'],
                    ]);
                    update_morph_index_entry($index, $updatedRecord);
                } else {
                    $updatedRecord = array_merge($existing, [
                        'species_name' => $prepared['species_name'],
                        'display_name' => $prepared['display_name'],
                        'morph_type' => $prepared['morph_type'],
                        'aliases' => $aliasesJson,
                        'normalized_aliases' => $normalizedAliasesJson,
                        'description' => $prepared['description'],
                        'source_url' => $prepared['source_url'],
                    ]);
                    update_morph_index_entry($index, $updatedRecord);
                }

                if (count($previewRows) < $previewLimit) {
                    $previewRows[] = prepare_morph_preview_row($prepared, 'update');
                }
            } else {
                $summary['unchanged']++;
                if (count($previewRows) < $previewLimit) {
                    $previewRows[] = prepare_morph_preview_row($prepared, 'unchanged');
                }
            }
        }

        if (!$dryRun) {
            $pdo->commit();
        }
    } catch (Throwable $exception) {
        if (!$dryRun && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    return [
        'summary' => $summary,
        'preview' => array_slice($previewRows, 0, $previewLimit),
    ];
}
