<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
<h1>Tiere verwalten</h1>
<?php include __DIR__ . '/nav.php'; ?>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<div class="admin-two-column">
    <div class="card">
        <h2>Bestand</h2>
        <?php
            $speciesFilters = [];
            foreach ($animals as $animalRow) {
                $speciesName = trim((string)($animalRow['species'] ?? ''));
                if ($speciesName !== '' && !in_array($speciesName, $speciesFilters, true)) {
                    $speciesFilters[] = $speciesName;
                }
            }
            sort($speciesFilters, SORT_NATURAL | SORT_FLAG_CASE);
        ?>
        <div class="datatable" data-datatable>
            <div class="datatable__controls">
                <div class="datatable__search">
                    <label class="sr-only" for="animal-search">Bestand durchsuchen</label>
                    <input type="search" id="animal-search" placeholder="Suchen nach Namen, Morphs oder Besitzer" data-datatable-search>
                </div>
                <div class="datatable__filters">
                    <label class="sr-only" for="animal-filter-species">Art filtern</label>
                    <select id="animal-filter-species" data-datatable-filter="species">
                        <option value="">Alle Arten</option>
                        <?php foreach ($speciesFilters as $speciesName): ?>
                            <option value="<?= htmlspecialchars(strtolower($speciesName)) ?>"><?= htmlspecialchars($speciesName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="sr-only" for="animal-filter-status">Status filtern</label>
                    <select id="animal-filter-status" data-datatable-filter="status">
                        <option value="">Alle Status</option>
                        <option value="highlight">Highlight</option>
                        <option value="privat">Privat</option>
                        <option value="piebald">Gescheckt</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th data-sort-key="name">Name</th>
                            <th data-sort-key="species">Species</th>
                            <th data-sort-key="owner">Eigentümer</th>
                            <th data-sort-key="status">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animals as $animal): ?>
                            <?php
                                $ownerName = trim((string)($animal['owner_name'] ?? ''));
                                $statusTokens = [];
                                if (!empty($animal['is_showcased'])) {
                                    $statusTokens[] = 'highlight';
                                }
                                if (!empty($animal['is_private'])) {
                                    $statusTokens[] = 'privat';
                                }
                                if (!empty($animal['is_piebald'])) {
                                    $statusTokens[] = 'piebald';
                                }
                                $statusAttribute = implode('|', $statusTokens);
                                $nameKey = function_exists('mb_strtolower') ? mb_strtolower($animal['name']) : strtolower($animal['name']);
                                $speciesKey = function_exists('mb_strtolower') ? mb_strtolower($animal['species']) : strtolower($animal['species']);
                                $ownerKey = function_exists('mb_strtolower') ? mb_strtolower($ownerName) : strtolower($ownerName);
                                $statusKey = function_exists('mb_strtolower') ? mb_strtolower($statusAttribute) : strtolower($statusAttribute);
                            ?>
                            <tr data-name="<?= htmlspecialchars($nameKey) ?>"
                                data-species="<?= htmlspecialchars($speciesKey) ?>"
                                data-owner="<?= htmlspecialchars($ownerKey) ?>"
                                data-status="<?= htmlspecialchars($statusKey) ?>">
                                <td>
                                    <?= htmlspecialchars($animal['name']) ?>
                                    <?php if (!empty($animal['is_piebald'])): ?>
                                        <span class="animal-marker" title="Geschecktes Tier" aria-label="Geschecktes Tier">⬟</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($animal['species']) ?></td>
                                <td><?= htmlspecialchars($ownerName !== '' ? $ownerName : '–') ?></td>
                                <td>
                                    <?php if ($animal['is_private']): ?>
                                        <span class="badge">Privat</span>
                                    <?php endif; ?>
                                    <?php if ($animal['is_showcased']): ?>
                                        <span class="badge">Highlight</span>
                                    <?php endif; ?>
                                    <?php if (!empty($animal['is_piebald'])): ?>
                                        <span class="badge badge-pattern">Gescheckt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/animals&edit=<?= (int)$animal['id'] ?>">Bearbeiten</a>
                                    <form method="post" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="duplicate_animal">
                                        <input type="hidden" name="animal_id" value="<?= (int)$animal['id'] ?>">
                                        <button type="submit" class="btn btn-secondary">Duplizieren</button>
                                    </form>
                                    <form method="post" style="display:inline" onsubmit="return confirm('Tier wirklich löschen?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_animal">
                                        <input type="hidden" name="animal_id" value="<?= (int)$animal['id'] ?>">
                                        <button type="submit" class="btn btn-secondary">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="datatable__empty" data-datatable-empty hidden>Keine Treffer für die aktuelle Filterung.</p>
        </div>
    </div>
    <div class="card">
        <h2><?= $editAnimal ? 'Tier bearbeiten' : 'Neues Tier' ?></h2>
        <?php
            $currentSpeciesSlug = $editAnimal['species_slug'] ?? ($speciesList[0]['slug'] ?? null);
            $ageParts = $editAnimal['age_parts'] ?? parse_partial_date($editAnimal['age'] ?? null);
            $currentGeneStates = [];
            foreach (($editAnimal['gene_states'] ?? []) as $geneSlug => $stateKey) {
                if (in_array($stateKey, ['heterozygous', 'homozygous'], true)) {
                    $currentGeneStates[$geneSlug] = $stateKey;
                }
            }
            $currentYear = (int)date('Y');
        ?>
        <form method="post" enctype="multipart/form-data" class="admin-animal-form" data-gene-form>
            <?= csrf_field() ?>
            <?php if ($editAnimal): ?>
                <input type="hidden" name="id" value="<?= (int)$editAnimal['id'] ?>">
            <?php endif; ?>
            <label>Name
                <input type="text" name="name" value="<?= htmlspecialchars($editAnimal['name'] ?? '') ?>" required>
            </label>
            <div class="form-field">
                <label for="species-select">Art</label>
                <?php if (empty($speciesList)): ?>
                    <p class="form-hint">Bitte lege zuerst unter <a href="<?= BASE_URL ?>/index.php?route=admin/genetics">Genetik</a> mindestens eine Art an.</p>
                <?php endif; ?>
                <select id="species-select" name="species_slug" <?= empty($speciesList) ? 'disabled' : '' ?> data-species-select>
                    <option value="">— Art auswählen —</option>
                    <?php foreach ($speciesList as $species): ?>
                        <option value="<?= htmlspecialchars($species['slug']) ?>" <?= ($currentSpeciesSlug === $species['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($species['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="form-hint">Arten lassen sich im Bereich <a href="<?= BASE_URL ?>/index.php?route=admin/genetics">Genetikverwaltung</a> ergänzen oder bearbeiten.</p>
            </div>
            <fieldset class="form-field">
                <legend>Schlupfdatum / Alter</legend>
                <div class="age-picker">
                    <label>
                        <span class="sr-only">Jahr</span>
                        <select name="age_year">
                            <option value="">Jahr auswählen</option>
                            <?php for ($year = $currentYear + 1; $year >= 1950; $year--): ?>
                                <option value="<?= $year ?>" <?= ((string)$year === ($ageParts['year'] ?? '')) ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                    <label>
                        <span class="sr-only">Monat</span>
                        <select name="age_month">
                            <option value="">Monat</option>
                            <?php
                                $monthNames = [1=>'Januar',2=>'Februar',3=>'März',4=>'April',5=>'Mai',6=>'Juni',7=>'Juli',8=>'August',9=>'September',10=>'Oktober',11=>'November',12=>'Dezember'];
                                foreach ($monthNames as $number => $label):
                            ?>
                                <option value="<?= $number ?>" <?= ((int)($ageParts['month'] ?? 0) === $number) ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span class="sr-only">Tag</span>
                        <select name="age_day">
                            <option value="">Tag</option>
                            <?php for ($day = 1; $day <= 31; $day++): ?>
                                <option value="<?= $day ?>" <?= ((int)($ageParts['day'] ?? 0) === $day) ? 'selected' : '' ?>><?= $day ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                </div>
                <p class="form-hint">Es genügt, nur Jahr oder Jahr + Monat auszuwählen, falls das genaue Datum nicht bekannt ist.</p>
            </fieldset>
            <fieldset class="form-field">
                <legend>Genetik</legend>
                <?php if (empty($speciesList)): ?>
                    <p class="form-hint">Gene werden verfügbar, sobald eine Art ausgewählt werden kann.</p>
                <?php else: ?>
                    <p class="form-hint">Tippe den Morph oder Trägerstatus (z.&nbsp;B. „Het Hypo“). Vorschläge erscheinen automatisch, die Auswahl lässt sich jederzeit entfernen.</p>
                    <div class="gene-selector-admin" data-gene-selector-root>
                        <?php foreach ($speciesList as $species): ?>
                            <?php
                                $isActive = $currentSpeciesSlug === $species['slug'];
                                $genes = $speciesGenes[$species['slug']] ?? [];
                                $payload = [];
                                $selectedStates = [];
                                if (!empty($genes)) {
                                    foreach ($genes as $gene) {
                                        $geneSlug = $gene['slug'];
                                        $displayStates = [];
                                        $geneName = $gene['name'] ?? '';
                                        $normalLabel = trim((string)($gene['normal_label'] ?? '')) ?: ($geneName ?: '');
                                        $heteroLabel = trim((string)($gene['heterozygous_label'] ?? ''));
                                        $homoLabel = trim((string)($gene['homozygous_label'] ?? '')) ?: ($geneName ?: '');
                                        if ($heteroLabel !== '' || $geneName !== '') {
                                            $heteroDisplay = trim(($normalLabel !== '' ? $normalLabel . ' ' : '') . ($heteroLabel !== '' ? $heteroLabel : ($geneName ? 'het ' . $geneName : '')));
                                            $displayStates[] = [
                                                'key' => 'heterozygous',
                                                'label' => $heteroLabel !== '' ? $heteroLabel : ($geneName ? 'het ' . $geneName : ''),
                                                'display' => $heteroDisplay,
                                                'tokens' => array_values(array_filter(array_unique([
                                                    $geneName,
                                                    $gene['shorthand'] ?? '',
                                                    $heteroLabel,
                                                    $normalLabel,
                                                    $heteroDisplay,
                                                ]))),
                                            ];
                                        }
                                        if ($homoLabel !== '' || $geneName !== '') {
                                            $homoDisplay = $homoLabel !== '' ? $homoLabel : $geneName;
                                            $displayStates[] = [
                                                'key' => 'homozygous',
                                                'label' => $homoLabel !== '' ? $homoLabel : $geneName,
                                                'display' => $homoDisplay,
                                                'tokens' => array_values(array_filter(array_unique([
                                                    $geneName,
                                                    $gene['shorthand'] ?? '',
                                                    $homoLabel,
                                                    $homoDisplay,
                                                ]))),
                                            ];
                                        }
                                        $payload[] = [
                                            'slug' => $geneSlug,
                                            'name' => $geneName,
                                            'states' => $displayStates,
                                            'inheritance_hint' => $gene['inheritance_hint'] ?? null,
                                        ];
                                        if (isset($currentGeneStates[$geneSlug])) {
                                            $selectedStates[$geneSlug] = $currentGeneStates[$geneSlug];
                                        }
                                    }
                                }
                                $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                $encodedPayload = base64_encode($jsonPayload !== false ? $jsonPayload : '[]');
                            ?>
                            <div class="gene-selector-admin__species" data-animal-gene-group data-species-genes="<?= htmlspecialchars($species['slug']) ?>" data-gene-payload='<?= htmlspecialchars($jsonPayload !== false ? $jsonPayload : '[]', ENT_QUOTES, 'UTF-8') ?>' data-gene-payload-b64="<?= htmlspecialchars($encodedPayload, ENT_QUOTES, 'UTF-8') ?>" data-selected='<?= htmlspecialchars(json_encode($isActive ? $selectedStates : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>' <?= $isActive ? '' : 'hidden' ?>>
                                <?php if (empty($genes)): ?>
                                    <p class="form-hint">Für diese Art wurden noch keine Gene gepflegt. <a href="<?= BASE_URL ?>/index.php?route=admin/genetics&amp;species=<?= urlencode($species['slug']) ?>">Jetzt anlegen</a>.</p>
                                <?php else: ?>
                                    <div class="gene-selector-admin__tags" data-tag-container></div>
                                    <div class="gene-selector-admin__input">
                                        <input type="text" placeholder="Gen oder Bezeichnung eingeben …" data-input <?= $isActive ? '' : 'disabled' ?>>
                                        <button type="button" class="btn btn-secondary" data-clear>Zurücksetzen</button>
                                    </div>
                                    <div class="gene-selector-admin__suggestions" data-suggestions hidden></div>
                                    <div data-hidden-inputs></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </fieldset>
            <label>Herkunft
                <input type="text" name="origin" value="<?= htmlspecialchars($editAnimal['origin'] ?? '') ?>">
            </label>
            <label>Besonderheiten
                <textarea name="special_notes" class="rich-text"><?= htmlspecialchars($editAnimal['special_notes'] ?? '') ?></textarea>
            </label>
            <label>Beschreibung
                <textarea name="description" class="rich-text"><?= htmlspecialchars($editAnimal['description'] ?? '') ?></textarea>
            </label>
            <label>Bild
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editAnimal['image_path'])): ?>
                    <input type="hidden" name="image_path" value="<?= htmlspecialchars($editAnimal['image_path']) ?>">
                    <p><a href="<?= BASE_URL . '/' . htmlspecialchars($editAnimal['image_path']) ?>" target="_blank">Aktuelles Bild anzeigen</a></p>
                <?php endif; ?>
            </label>
            <label>Besitzer
                <select name="owner_id">
                    <option value="">— keiner —</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user['id'] ?>" <?= (($editAnimal['owner_id'] ?? '') == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_private" value="1" <?= !empty($editAnimal['is_private']) ? 'checked' : '' ?>> Privat
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_showcased" value="1" <?= !empty($editAnimal['is_showcased']) ? 'checked' : '' ?>> In Highlights anzeigen
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_piebald" value="1" <?= !empty($editAnimal['is_piebald']) ? 'checked' : '' ?>> Als gescheckt markieren
            </label>
            <button type="submit" <?= empty($speciesList) ? 'disabled' : '' ?>>Speichern</button>
        </form>
    </div>
</div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
