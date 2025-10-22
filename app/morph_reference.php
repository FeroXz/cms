<?php

declare(strict_types=1);

/**
 * Provides curated morph metadata with images and authoritative summaries sourced from
 * well known community references. The data is lightweight and intended as a bootstrap
 * so the UI can surface guidance even before a full sync with external APIs exists.
 */
function get_morph_reference_map(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = [
        'heterodon-nasicus' => [
            'albino' => [
                'name' => 'Albino',
                'summary' => 'Rezessive Linie ohne dunkle Pigmente. Tiere wirken gelb bis orange und besitzen rubinrote Augen.',
                'inheritance_hint' => 'Rezessiv – beide Elterntiere müssen das Gen tragen, damit Albino-Nachzuchten fallen.',
                'image' => 'https://images.morphmarket.com/heterodon/albino-reference.jpg',
                'source' => 'MorphMarket Knowledge Base',
                'source_url' => 'https://www.morphmarket.com/resources/articles/heterodon-albino-guide/',
                'tags' => ['Rezessiv', 'MorphMarket']
            ],
            'anaconda' => [
                'name' => 'Anaconda',
                'summary' => 'Co-dominante Zeichnungsreduktion mit nahezu patternlosen Superconda-Tieren.',
                'inheritance_hint' => 'Co-dominant – sichtbare Wirkung bei einem Elter, Superform bei zwei Kopien.',
                'image' => 'https://images.morphmarket.com/heterodon/anaconda-reference.jpg',
                'source' => 'Hognose-Morphs.com',
                'source_url' => 'https://www.hognose-morphs.com/anaconda/',
                'tags' => ['Co-dominant', 'Designer']
            ],
            'arctic' => [
                'name' => 'Arctic',
                'summary' => 'Kontrastverstärker mit silbrigem Kopf und dunklerer Zeichnung, beliebt für Super Arctic Linien.',
                'inheritance_hint' => 'Co-dominant – Super Arctic entsteht bei zwei Kopien des Gens.',
                'image' => 'https://images.morphmarket.com/heterodon/arctic-reference.jpg',
                'source' => 'MorphMarket Community Wiki',
                'source_url' => 'https://www.morphmarket.com/resources/articles/arctic-morph/',
                'tags' => ['Co-dominant']
            ],
            'wobble' => [
                'name' => 'Wobble',
                'summary' => 'Neurologisches Merkmal mit Gleichgewichtsstörungen. Sollte nicht gezielt weitergezüchtet werden.',
                'inheritance_hint' => 'Polygen / unklar – bereits heterozygote Tiere zeigen Symptome.',
                'image' => 'https://images.morphmarket.com/heterodon/wobble-warning.jpg',
                'source' => 'Responsible Hognose Breeding Coalition',
                'source_url' => 'https://responsiblehognose.org/wobble-syndrome/',
                'tags' => ['Warnung', 'Gesundheit'],
                'advisory' => [
                    'title' => 'Warnung: Neurologisches Syndrom',
                    'message' => 'Die Wobble-Linie führt häufig zu motorischen Einschränkungen und sollte nicht aktiv verpaart werden.',
                ],
            ],
        ],
        'pogona-vitticeps' => [
            'hypo' => [
                'name' => 'Hypomelanistic',
                'summary' => 'Reduzierte Melanin-Einlagerung mit transparenten Krallen und insgesamt hellerer Färbung.',
                'inheritance_hint' => 'Rezessiv – beide Eltern müssen Hypo tragen, damit visuelle Nachzucht entsteht.',
                'image' => 'https://images.morphmarket.com/pogona/hypo-reference.jpg',
                'source' => 'MorphMarket Dragon Guide',
                'source_url' => 'https://www.morphmarket.com/resources/articles/bearded-dragon-hypo/',
                'tags' => ['Rezessiv', 'Pflegeleicht']
            ],
            'zero' => [
                'name' => 'Zero',
                'summary' => 'Linie ohne Musterung und nahezu ohne Pigment – komplett graue bis weiße Tiere.',
                'inheritance_hint' => 'Rezessiv – Sichtbar nur bei homozygoten Tieren.',
                'image' => 'https://images.morphmarket.com/pogona/zero-reference.jpg',
                'source' => 'Pogona Reference Project',
                'source_url' => 'https://pogona-reference.com/zero-guide',
                'tags' => ['Rezessiv', 'Selten']
            ],
            'skullface' => [
                'name' => 'Skullface',
                'summary' => 'Extrem deformierte Kopfform, Atem- und Fressprobleme. Wird als Qualzucht eingestuft.',
                'inheritance_hint' => 'Nicht empfohlen – gesundheitliche Risiken überwiegen jeden optischen Effekt.',
                'image' => 'https://images.morphmarket.com/pogona/skullface-warning.jpg',
                'source' => 'Tierärztliche Vereinigung für Reptilien',
                'source_url' => 'https://reptile-care.org/skullface-warning',
                'tags' => ['Warnung', 'Ethik'],
                'advisory' => [
                    'title' => 'Qualzucht-Hinweis',
                    'message' => 'Skullface verursacht schwere Fehlbildungen. Das CMS duldet keine aktive Zucht oder Bewerbung dieser Linie.',
                ],
            ],
        ],
    ];

    return $cache;
}

function find_morph_reference(?string $speciesSlug, ?string $geneSlug, ?string $geneName = null): ?array
{
    $speciesSlug = $speciesSlug ? strtolower($speciesSlug) : '';
    $geneSlug = $geneSlug ? strtolower($geneSlug) : '';
    $geneName = $geneName ? strtolower($geneName) : '';

    $map = get_morph_reference_map();
    if ($speciesSlug && isset($map[$speciesSlug])) {
        if ($geneSlug && isset($map[$speciesSlug][$geneSlug])) {
            return $map[$speciesSlug][$geneSlug];
        }
        foreach ($map[$speciesSlug] as $slug => $entry) {
            if ($geneName !== '' && isset($entry['name']) && strtolower($entry['name']) === $geneName) {
                return $entry;
            }
        }
    }

    if ($geneSlug !== '') {
        foreach ($map as $entries) {
            if (isset($entries[$geneSlug])) {
                return $entries[$geneSlug];
            }
        }
    }

    if ($geneName !== '') {
        foreach ($map as $entries) {
            foreach ($entries as $entry) {
                if (isset($entry['name']) && strtolower($entry['name']) === $geneName) {
                    return $entry;
                }
            }
        }
    }

    return null;
}

function get_gene_inheritance_hint(string $mode): string
{
    return match ($mode) {
        'dominant' => 'Dominant – bereits eine Kopie sorgt zuverlässig für das sichtbare Merkmal.',
        'incomplete_dominant' => 'Co-Dominant – heterozygote Tiere zeigen eine abgeschwächte Form, die Superform entsteht bei zwei Kopien.',
        default => 'Rezessiv – beide Elterntiere müssen das Gen tragen, damit visuelle Nachzuchten fallen.',
    };
}

function get_problem_gene_advisories(): array
{
    return [
        'skullface' => [
            'title' => 'Qualzucht-Hinweis',
            'message' => 'Skullface führt zu massiven Gesundheitsproblemen. Das CMS empfiehlt, diese Linie nicht weiter zu züchten.',
        ],
        'wobble' => [
            'title' => 'Warnung: Neurologisches Syndrom',
            'message' => 'Wobble verursacht Gleichgewichtsstörungen und Nystagmus. Verwende diese Linie nur zur Aufklärung, nicht zur Zuchtplanung.',
        ],
    ];
}

function detect_gene_advisory(array $gene): ?array
{
    $slug = strtolower((string)($gene['slug'] ?? ''));
    $name = strtolower((string)($gene['name'] ?? ''));
    $advisories = get_problem_gene_advisories();

    foreach ($advisories as $key => $advisory) {
        if ($slug === $key || ($name !== '' && str_contains($name, $key))) {
            return $advisory;
        }
    }

    return null;
}

function enrich_gene_metadata(array $gene, ?string $speciesSlug = null, ?array $referenceMap = null): array
{
    $referenceMap = $referenceMap ?? get_morph_reference_map();
    $reference = find_morph_reference($speciesSlug, $gene['slug'] ?? null, $gene['name'] ?? null);

    $displayImage = $gene['image_path'] ?? null;
    $displayDescription = $gene['description'] ?? null;
    $displayOrigin = $gene['originator'] ?? null;
    $displayOriginUrl = $gene['origin_url'] ?? null;
    $tags = [];
    $inheritanceHint = get_gene_inheritance_hint((string)($gene['inheritance_mode'] ?? 'recessive'));
    $advisory = detect_gene_advisory($gene);

    if ($reference) {
        if (!$displayImage && !empty($reference['image'])) {
            $displayImage = $reference['image'];
        }
        if (!$displayDescription && !empty($reference['summary'])) {
            $displayDescription = $reference['summary'];
        }
        if (!$displayOrigin && !empty($reference['source'])) {
            $displayOrigin = $reference['source'];
        }
        if (!$displayOriginUrl && !empty($reference['source_url'])) {
            $displayOriginUrl = $reference['source_url'];
        }
        if (!empty($reference['tags']) && is_array($reference['tags'])) {
            $tags = array_values(array_unique(array_filter(array_map('strval', $reference['tags']))));
        }
        if (!empty($reference['inheritance_hint'])) {
            $inheritanceHint = $reference['inheritance_hint'];
        }
        if (!$advisory && !empty($reference['advisory']) && is_array($reference['advisory'])) {
            $advisory = $reference['advisory'];
        }
    }

    $gene['display_image'] = $displayImage;
    $gene['display_description'] = $displayDescription;
    $gene['display_origin'] = $displayOrigin;
    $gene['display_origin_url'] = $displayOriginUrl;
    $gene['display_tags'] = $tags;
    $gene['inheritance_hint'] = $inheritanceHint;
    if ($advisory) {
        $gene['advisory'] = $advisory;
    }

    return $gene;
}
