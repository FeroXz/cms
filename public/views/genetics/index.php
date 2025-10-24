<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
<h1 class="text-3xl font-semibold text-white sm:text-4xl"><?= htmlspecialchars(content_value($settings, 'genetics_title')) ?></h1>
<p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars(content_value($settings, 'genetics_intro')) ?></p>

<?php if (empty($speciesList)): ?>
    <div class="card">
        <p><?= htmlspecialchars(content_value($settings, 'genetics_empty_notice')) ?></p>
    </div>
<?php else: ?>
    <div class="card" style="margin-bottom:2rem;">
        <form method="get" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="route" value="genetics">
            <label>Art auswählen
                <select name="species" onchange="this.form.submit()">
                    <?php foreach ($speciesList as $species): ?>
                        <option value="<?= htmlspecialchars($species['slug']) ?>" <?= ($selectedSpeciesSlug === $species['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($species['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <noscript>
                <button type="submit">Wechseln</button>
            </noscript>
        </form>
        <?php if ($selectedSpecies): ?>
            <p style="margin-top:0.5rem;">Aktuelle Art: <strong><?= htmlspecialchars($selectedSpecies['name']) ?></strong><?php if (!empty($selectedSpecies['scientific_name'])): ?> (<em><?= htmlspecialchars($selectedSpecies['scientific_name']) ?></em>)<?php endif; ?></p>
            <?php if (!empty($selectedSpecies['description'])): ?>
                <div class="rich-text-content" style="margin-top:0.75rem;">
                    <?= render_rich_text($selectedSpecies['description']) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php
        $parentSources = $parentSources ?? ['parent1' => 'manual', 'parent2' => 'manual'];
        $parentAnimals = $parentAnimals ?? ['parent1' => null, 'parent2' => null];
        $parentAnimalSelections = $parentAnimalSelections ?? ['parent1' => null, 'parent2' => null];
        $parentMorphDetails = $parentMorphDetails ?? ['parent1' => [], 'parent2' => []];
        $parentSlugSelections = $parentSlugSelections ?? ['parent1' => [], 'parent2' => []];
        $speciesAnimals = $speciesAnimals ?? [];
        $speciesMorphs = $speciesMorphs ?? [];
    ?>

    <?php if ($selectedSpecies && !empty($genes)): ?>
        <?php
            $toLower = static function (string $value): string {
                return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
            };
            $modeLabels = [
                'recessive' => 'rezessiv',
                'dominant' => 'dominant',
                'incomplete_dominant' => 'inkomplett dominant',
                'codominant' => 'co-dominant',
            ];
            $geneStatePayload = [];
            foreach ($genes as $gene) {
                $geneId = (int)$gene['id'];
                $stateEntries = [];
                foreach (['normal', 'heterozygous', 'homozygous'] as $stateKey) {
                    $label = gene_state_label($gene, $stateKey);
                    $tokens = [$toLower($label), $toLower($gene['name'])];
                    if (!empty($gene['shorthand'])) {
                        $tokens[] = $toLower($gene['shorthand']);
                    }
                    if ($stateKey === 'normal') {
                        $tokens[] = 'wildtyp';
                        $tokens[] = 'normal ' . $toLower($gene['name']);
                    } elseif ($stateKey === 'heterozygous') {
                        $tokens[] = 'het ' . $toLower($gene['name']);
                        $tokens[] = 'träger ' . $toLower($gene['name']);
                    } else {
                        $tokens[] = 'visual ' . $toLower($gene['name']);
                    }
                    $stateEntries[] = [
                        'key' => $stateKey,
                        'label' => $label,
                        'searchTokens' => array_values(array_unique($tokens)),
                    ];
                }
                $geneStatePayload[] = [
                    'id' => $geneId,
                    'slug' => $gene['slug'],
                    'name' => $gene['name'],
                    'shorthand' => $gene['shorthand'],
                    'inheritance' => $modeLabels[$gene['inheritance_mode']] ?? $gene['inheritance_mode'],
                    'inheritance_mode' => $gene['inheritance_mode'],
                    'description' => $gene['description'],
                    'states' => $stateEntries,
                ];
            }
        ?>
        <form method="post" class="card gene-selector" data-genetic-selector>
            <input type="hidden" name="species_slug" value="<?= htmlspecialchars($selectedSpecies['slug']) ?>">
            <div class="gene-selector__intro">
                <p><strong>Eingabehilfe:</strong> Wählen Sie je Elternteil entweder ein Tier aus dem Bestand oder pflegen Sie die Gene und Morphs manuell. Tippen Sie einen Gen-Namen oder Trägerstatus (z.&nbsp;B. „Albino“, „het Toffee“, „Super Anaconda“). Nicht ausgewählte Gene werden automatisch als Wildtyp gewertet.</p>
            </div>
            <div class="alert alert-error" data-form-error hidden role="alert" aria-live="assertive"></div>
            <div class="gene-selector__parents">
                <?php
                    $parentLabels = ['parent1' => 'Elter 1', 'parent2' => 'Elter 2'];
                    $parentDescriptions = [
                        'parent1' => 'Fügen Sie alle sichtbaren Morphe sowie Trägereigenschaften hinzu.',
                        'parent2' => 'Bestimmen Sie visuelle Merkmale oder Heterozygotie wie „het Albino“. Sie können Angaben aus dem Bestand überschreiben.',
                    ];
                ?>
                <?php foreach ($parentLabels as $parentKey => $parentLabel): ?>
                    <?php
                        $source = $parentSources[$parentKey] ?? 'manual';
                        $selectedAnimal = $parentAnimals[$parentKey] ?? null;
                        $selectedAnimalId = $parentAnimalSelections[$parentKey] ?? null;
                        $morphDetails = $parentMorphDetails[$parentKey] ?? [];
                    ?>
                    <section class="gene-parent" data-parent="<?= $parentKey ?>" data-parent-source="<?= htmlspecialchars($source) ?>">
                        <h2><?= htmlspecialchars($parentLabel) ?></h2>
                        <p class="text-muted"><?= htmlspecialchars($parentDescriptions[$parentKey]) ?></p>
                        <div class="gene-parent__mode" role="group" aria-label="Eingabequelle wählen">
                            <label class="gene-parent__mode-option">
                                <input type="radio" name="parent_sources[<?= $parentKey ?>]" value="manual" <?= $source === 'manual' ? 'checked' : '' ?>>
                                <span>Manuell</span>
                            </label>
                            <label class="gene-parent__mode-option">
                                <input type="radio" name="parent_sources[<?= $parentKey ?>]" value="animal" <?= $source === 'animal' ? 'checked' : '' ?>>
                                <span>Tier aus DB</span>
                            </label>
                        </div>
                        <div class="gene-parent__animal" data-animal-section <?= $source === 'animal' ? '' : 'hidden' ?>>
                            <label class="gene-parent__animal-label" for="<?= $parentKey ?>-animal-input">Tier aus Datenbank auswählen</label>
                            <div class="gene-parent__animal-input">
                                <input type="text" id="<?= $parentKey ?>-animal-input" placeholder="Tiername, Morph oder Genetik suchen …" autocomplete="off" data-animal-input>
                                <button type="button" class="btn btn-secondary" data-animal-clear><?= $selectedAnimal ? 'Auswahl entfernen' : 'Eingabe löschen' ?></button>
                            </div>
                            <div class="gene-parent__suggestions" data-animal-suggestions hidden></div>
                            <div class="gene-parent__animal-summary" data-animal-summary>
                                <?php if ($selectedAnimal): ?>
                                    <p><strong><?= htmlspecialchars($selectedAnimal['name']) ?></strong><br><span class="text-muted"><?= htmlspecialchars($selectedAnimal['genetics'] ?: 'Keine Genetik hinterlegt') ?></span></p>
                                <?php else: ?>
                                    <p class="text-muted">Noch kein Tier ausgewählt.</p>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="parent_animals[<?= $parentKey ?>]" value="<?= $selectedAnimalId ?? '' ?>" data-animal-hidden>
                        </div>
                        <div class="gene-parent__tags" data-tag-container></div>
                        <div class="gene-parent__input" data-manual-input>
                            <input type="text" placeholder="Gen oder Bezeichnung eingeben …" data-input autocomplete="off">
                            <button type="button" class="btn btn-secondary" data-clear>Zurücksetzen</button>
                        </div>
                        <div class="gene-parent__suggestions" data-suggestions hidden></div>
                        <div data-hidden-inputs></div>
                        <div class="gene-parent__morphs" data-morph-section>
                            <div class="gene-parent__morph-header">
                                <span>Zusätzliche Morph-Angaben (optional)</span>
                                <button type="button" class="btn btn-secondary btn-small" data-morph-clear <?= empty($morphDetails) ? 'hidden' : '' ?>>Alle entfernen</button>
                            </div>
                            <div class="gene-parent__morph-input">
                                <input type="text" placeholder="Morph suchen …" data-morph-input autocomplete="off">
                            </div>
                            <div class="gene-parent__suggestions" data-morph-suggestions hidden></div>
                            <div class="gene-parent__morph-tags" data-morph-tags>
                                <?php if (!empty($morphDetails)): ?>
                                    <?php foreach ($morphDetails as $morph): ?>
                                        <span class="morph-tag" data-morph-id="<?= (int)$morph['id'] ?>">
                                            <span><?= htmlspecialchars($morph['name']) ?></span>
                                            <button type="button" class="morph-tag__remove" data-remove-morph aria-label="<?= htmlspecialchars($morph['name']) ?> entfernen">&times;</button>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Noch keine Morph-Angaben ergänzt.</p>
                                <?php endif; ?>
                            </div>
                            <div data-morph-hidden-inputs>
                                <?php foreach ($morphDetails as $morph): ?>
                                    <input type="hidden" name="parent_morphs[<?= $parentKey ?>][]" value="<?= (int)$morph['id'] ?>">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn" style="margin-top:1.5rem;align-self:flex-start;"><?= htmlspecialchars(content_value($settings, 'genetics_submit')) ?></button>
        </form>
        <section class="gene-reference">
            <h2>Verfügbare Gene</h2>
            <div class="grid cards">
                <?php foreach ($genes as $gene): ?>
                    <article class="card gene-reference__card">
                        <header class="gene-reference__header">
                            <div>
                                <h3><?= htmlspecialchars($gene['name']) ?></h3>
                                <?php if (!empty($gene['shorthand'])): ?>
                                    <span class="badge">Kürzel: <?= htmlspecialchars($gene['shorthand']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge"><?= htmlspecialchars($modeLabels[$gene['inheritance_mode']] ?? $gene['inheritance_mode']) ?></span>
                        </header>
                        <dl class="gene-reference__states">
                            <div><dt>Wildtyp</dt><dd><?= htmlspecialchars(gene_state_label($gene, 'normal')) ?></dd></div>
                            <div><dt>Träger</dt><dd><?= htmlspecialchars(gene_state_label($gene, 'heterozygous')) ?></dd></div>
                            <div><dt>Visuell</dt><dd><?= htmlspecialchars(gene_state_label($gene, 'homozygous')) ?></dd></div>
                        </dl>
                        <?php if (!empty($gene['description'])): ?>
                            <p class="text-muted" style="line-height:1.5;"><?= htmlspecialchars($gene['description']) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php if (!empty($referenceGenes ?? [])): ?>
            <section class="gene-reference" style="margin-top:2.5rem;">
                <h2>Kombinations-Referenzen</h2>
                <p class="text-muted" style="margin-bottom:1rem;line-height:1.5;">
                    Diese Einträge dokumentieren bestätigte Morph-Kombinationen. Für Berechnungen im Genetik-Rechner
                    bitte die jeweiligen Basismorphe auswählen – die Karten dienen ausschließlich als Nachschlagewerk.
                </p>
                <div class="grid cards">
                    <?php foreach ($referenceGenes as $gene): ?>
                        <article class="card gene-reference__card">
                            <header class="gene-reference__header">
                                <div>
                                    <h3><?= htmlspecialchars($gene['name']) ?></h3>
                                    <?php if (!empty($gene['shorthand'])): ?>
                                        <span class="badge">Kürzel: <?= htmlspecialchars($gene['shorthand']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="badge badge-pattern">Referenz</span>
                            </header>
                            <dl class="gene-reference__states">
                                <div><dt>Basis</dt><dd><?= htmlspecialchars($gene['normal_label']) ?></dd></div>
                                <div><dt>Teil-Kombi</dt><dd><?= htmlspecialchars($gene['heterozygous_label']) ?></dd></div>
                                <div><dt>Komplett</dt><dd><?= htmlspecialchars($gene['homozygous_label']) ?></dd></div>
                            </dl>
                            <?php if (!empty($gene['description'])): ?>
                                <p class="text-muted" style="line-height:1.5;">
                                    <?= htmlspecialchars($gene['description']) ?>
                                </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
        <?php
            $pageContext = [
                'speciesSlug' => $selectedSpecies['slug'],
                'genes' => $geneStatePayload,
                'parentSelections' => $parentSelections,
                'parentSlugSelections' => $parentSlugSelections,
                'parentSources' => $parentSources,
                'parentAnimals' => $parentAnimalSelections,
                'animals' => $speciesAnimals,
                'morphs' => $speciesMorphs,
                'parentMorphSelections' => $parentMorphSelections,
                'resultsExport' => $resultsExport,
                'resultText' => $resultText,
            ];
        ?>
        <script>
            window.GENETICS_PAGE_CONTEXT = <?= json_encode($pageContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        </script>
    <?php elseif ($selectedSpecies): ?>
        <div class="card" style="margin-bottom:2rem;">
            <p>Für diese Art wurden bislang keine Gene hinterlegt.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <section style="margin-bottom:3rem;">
            <h2>Gesamtauswertung</h2>
            <div class="card">
                <div class="result-actions">
                    <button type="button" class="btn btn-secondary" data-copy-json>Als JSON kopieren</button>
                    <button type="button" class="btn btn-secondary" data-copy-text>Als Text kopieren</button>
                    <span class="copy-feedback" data-copy-feedback hidden>In Zwischenablage kopiert</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Wahrscheinlichkeit</th>
                            <th>Ausprägung</th>
                            <th>Genotyp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['combined'] as $entry): ?>
                            <tr>
                                <td><?= number_format($entry['probability'] * 100, 1, ',', '.') ?>%</td>
                                <td><?= htmlspecialchars($entry['phenotype']) ?></td>
                                <td>
                                    <?php foreach ($entry['labels'] as $label): ?>
                                        <div><?= htmlspecialchars($label) ?></div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if (!empty($polygenicNotices)): ?>
            <section class="polygenic-notes" style="margin-bottom:2rem;">
                <h3>Polygen-Hinweise</h3>
                <ul>
                    <?php foreach ($polygenicNotices as $note): ?>
                        <?php $gene = $note['gene']; ?>
                        <li>
                            <strong><?= htmlspecialchars($gene['name']) ?></strong>
                            – Elter 1: <?= htmlspecialchars(gene_state_label($gene, $note['parent_states']['parent_one'])) ?>,
                            Elter 2: <?= htmlspecialchars(gene_state_label($gene, $note['parent_states']['parent_two'])) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="text-muted">Polygenes Merkmal – Vererbung ist komplex und wird nicht prozentual berechnet.</p>
            </section>
        <?php endif; ?>

        <section style="margin-bottom:3rem;">
            <h2>Genbezogene Verteilung</h2>
            <div class="grid cards">
                <?php foreach ($results['genes'] as $geneResult): ?>
                    <?php $gene = $geneResult['gene']; ?>
                    <article class="card">
                        <h3><?= htmlspecialchars($gene['name']) ?></h3>
                        <p class="text-muted" style="font-size:0.9rem;">Elter 1: <?= htmlspecialchars(gene_state_label($gene, $geneResult['parent_states']['parent_one'])) ?> · Elter 2: <?= htmlspecialchars(gene_state_label($gene, $geneResult['parent_states']['parent_two'])) ?></p>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Genotyp</th>
                                    <th>Wahrscheinlichkeit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($geneResult['states'] as $state): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($state['label']) ?>
                                            <?php if ($state['is_visual']): ?>
                                                <span class="tag tag-visual">visuell</span>
                                            <?php elseif ($state['is_carrier']): ?>
                                                <span class="tag tag-carrier">Träger</span>
                                            <?php else: ?>
                                                <span class="tag tag-normal">Wildtyp</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= number_format($state['probability'] * 100, 1, ',', '.') ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedSpecies): ?>
        <div class="card" style="margin-bottom:3rem;">
            <p>Bitte wählen Sie mindestens ein Gen mit Träger- oder visueller Ausprägung aus, um eine Auswertung zu erhalten.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
