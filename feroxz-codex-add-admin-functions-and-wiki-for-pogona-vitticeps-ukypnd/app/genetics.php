<?php

const GENETIC_INHERITANCE_MODES = ['recessive', 'dominant', 'incomplete_dominant'];

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
    $stmt = $pdo->prepare('SELECT * FROM genetic_genes WHERE species_id = :species ORDER BY display_order ASC, name ASC');
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

    $stmt = $pdo->prepare('INSERT INTO genetic_genes(species_id, name, slug, shorthand, inheritance_mode, description, normal_label, heterozygous_label, homozygous_label, display_order) VALUES (:species_id, :name, :slug, :shorthand, :inheritance_mode, :description, :normal_label, :heterozygous_label, :homozygous_label, :display_order)');
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

    $stmt = $pdo->prepare('UPDATE genetic_genes SET name = :name, slug = :slug, shorthand = :shorthand, inheritance_mode = :inheritance_mode, description = :description, normal_label = :normal_label, heterozygous_label = :heterozygous_label, homozygous_label = :homozygous_label, display_order = :display_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'slug' => $slug,
        'shorthand' => trim($data['shorthand'] ?? '') ?: null,
        'inheritance_mode' => $inheritance,
        'description' => trim($data['description'] ?? '') ?: null,
        'normal_label' => trim($data['normal_label'] ?? '') ?: null,
        'heterozygous_label' => trim($data['heterozygous_label'] ?? '') ?: null,
        'homozygous_label' => trim($data['homozygous_label'] ?? '') ?: null,
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
        'dominant', 'incomplete_dominant' => in_array($state, ['heterozygous', 'homozygous'], true),
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

    foreach ($genes as $gene) {
        $geneId = (int)$gene['id'];
        $stateOne = sanitize_gene_state($parentOneSelections[$geneId] ?? null);
        $stateTwo = sanitize_gene_state($parentTwoSelections[$geneId] ?? null);

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
    ];
}

function ensure_default_genetics(PDO $pdo): void
{
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

    $sampleGenes = [
        [
            'name' => 'Albino',
            'slug' => 'albino',
            'inheritance_mode' => 'recessive',
            'description' => 'Amelanistischer Farbschlag, der einen kräftigen Gelbton mit rosa Akzenten zeigt. Nur homozygote Tiere sind visuell.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Albino',
            'homozygous_label' => 'Albino',
            'display_order' => 1,
        ],
        [
            'name' => 'Axanthic',
            'slug' => 'axanthic',
            'inheritance_mode' => 'recessive',
            'description' => 'Reduziert gelbe Pigmente für einen grauen Grundton. Als heterozygotes Tier lediglich Träger.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Axanthic',
            'homozygous_label' => 'Axanthic',
            'display_order' => 2,
        ],
        [
            'name' => 'Toffee',
            'slug' => 'toffee',
            'inheritance_mode' => 'recessive',
            'description' => 'Variante mit karamellfarbener Grundfarbe. Wird in Kombinationen oft mit Albino oder Toffeeglow genutzt.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Toffee',
            'homozygous_label' => 'Toffee',
            'display_order' => 3,
        ],
        [
            'name' => 'Anaconda',
            'slug' => 'anaconda',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Inkomplett dominanter Zeichnungsmorph mit verengten Sattelflecken; Superform als Superconda nahezu patternless.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Anaconda',
            'homozygous_label' => 'Super Anaconda',
            'display_order' => 4,
        ],
        [
            'name' => 'Hypo',
            'slug' => 'hypo',
            'inheritance_mode' => 'recessive',
            'description' => 'Reduzierter Schwarzanteil sorgt für hellere Grundfarbe und klarere Zeichnung.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Hypo',
            'homozygous_label' => 'Hypo',
            'display_order' => 5,
        ],
        [
            'name' => 'Toffeebelly',
            'slug' => 'toffeebelly',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Belly-Pattern-Variante mit karamellfarbenem Bauch. Superform verstärkt Kontrast und reduziert Zeichnung.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Toffeebelly',
            'homozygous_label' => 'Super Toffeebelly',
            'display_order' => 6,
        ],
        [
            'name' => 'Arctic',
            'slug' => 'arctic',
            'inheritance_mode' => 'incomplete_dominant',
            'description' => 'Erhöht Kontrast und reduziert schwarze Pigmente; Super Arctic liefert stark aufgehellte Tiere.',
            'normal_label' => 'Wildtyp',
            'heterozygous_label' => 'Arctic',
            'homozygous_label' => 'Super Arctic',
            'display_order' => 7,
        ],
        [
            'name' => 'Sable',
            'slug' => 'sable',
            'inheritance_mode' => 'recessive',
            'description' => 'Dunkler Morphpartner mit kräftiger Zeichnung. Rezessiv vererbter Kontrastverstärker.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Sable',
            'homozygous_label' => 'Sable',
            'display_order' => 8,
        ],
        [
            'name' => 'Lavender',
            'slug' => 'lavender',
            'inheritance_mode' => 'recessive',
            'description' => 'Pastellfarbene Variante mit violettem Grundton, beliebt in Kombination mit Albino und Arctic.',
            'normal_label' => 'Normal',
            'heterozygous_label' => 'het Lavender',
            'homozygous_label' => 'Lavender',
            'display_order' => 9,
        ],
    ];

    foreach ($sampleGenes as $sample) {
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

