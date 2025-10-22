<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
<h1>Tierabgabe verwalten</h1>
<?php include __DIR__ . '/nav.php'; ?>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<div class="admin-two-column">
    <div class="card">
        <h2>Inserate</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Status</th>
                    <th>Preis</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?= htmlspecialchars($listing['title']) ?></td>
                        <td><?= htmlspecialchars($listing['status']) ?></td>
                        <td><?= htmlspecialchars($listing['price'] ?? 'n/a') ?></td>
                        <td>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/adoption&edit=<?= (int)$listing['id'] ?>">Bearbeiten</a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Eintrag löschen?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_listing">
                                <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                                <button type="submit" class="btn btn-secondary">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h2><?= $editListing ? 'Inserat bearbeiten' : 'Neues Inserat' ?></h2>
        <?php
            $currentSpeciesSlug = $editListing['species_slug'] ?? ($speciesList[0]['slug'] ?? null);
            $currentGeneStates = [];
            foreach (($editListing['gene_states'] ?? []) as $geneSlug => $stateKey) {
                if (in_array($stateKey, ['heterozygous', 'homozygous'], true)) {
                    $currentGeneStates[$geneSlug] = $stateKey;
                }
            }
        ?>
        <form method="post" enctype="multipart/form-data" class="admin-animal-form" data-gene-form>
            <?= csrf_field() ?>
            <?php if ($editListing): ?>
                <input type="hidden" name="id" value="<?= (int)$editListing['id'] ?>">
            <?php endif; ?>
            <label>Titel
                <input type="text" name="title" value="<?= htmlspecialchars($editListing['title'] ?? '') ?>" required>
            </label>
            <label>Tier aus Bestand
                <select name="animal_id">
                    <option value="">— unabhängig —</option>
                    <?php foreach ($animals as $animal): ?>
                        <option value="<?= (int)$animal['id'] ?>" <?= (($editListing['animal_id'] ?? '') == $animal['id']) ? 'selected' : '' ?>><?= htmlspecialchars($animal['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="form-field">
                <label for="adoption-species">Art</label>
                <?php if (empty($speciesList)): ?>
                    <p class="form-hint">Bitte lege unter <a href="<?= BASE_URL ?>/index.php?route=admin/genetics">Genetik</a> zunächst mindestens eine Art an.</p>
                <?php endif; ?>
                <select id="adoption-species" name="species_slug" <?= empty($speciesList) ? 'disabled' : '' ?> data-species-select>
                    <option value="">— Art auswählen —</option>
                    <?php foreach ($speciesList as $species): ?>
                        <option value="<?= htmlspecialchars($species['slug']) ?>" <?= ($currentSpeciesSlug === $species['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($species['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <label>Preis
                <input type="text" name="price" value="<?= htmlspecialchars($editListing['price'] ?? '') ?>">
            </label>
            <fieldset class="form-field">
                <legend>Genetik</legend>
                <?php if (empty($speciesList)): ?>
                    <p class="form-hint">Gene stehen zur Auswahl, sobald mindestens eine Art vorhanden ist.</p>
                <?php else: ?>
                    <p class="form-hint">Nutze die Texteingabe, um Morphen oder Trägereigenschaften zu hinterlegen. Vorschläge helfen bei der Auswahl.</p>
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
                                        $geneName = $gene['name'] ?? '';
                                        $normalLabel = trim((string)($gene['normal_label'] ?? '')) ?: ($geneName ?: '');
                                        $heteroLabel = trim((string)($gene['heterozygous_label'] ?? ''));
                                        $homoLabel = trim((string)($gene['homozygous_label'] ?? '')) ?: ($geneName ?: '');
                                        $displayStates = [];
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
                                        $normalDisplay = $normalLabel !== '' ? $normalLabel : ($geneName ?: 'Wildtyp');
                                        $displayStates[] = [
                                            'key' => 'normal',
                                            'label' => $normalLabel !== '' ? $normalLabel : ($geneName ?: 'Wildtyp'),
                                        'display' => $normalDisplay,
                                            'tokens' => array_values(array_filter(array_unique([
                                                $geneName,
                                                $gene['shorthand'] ?? '',
                                                $normalLabel,
                                                $normalDisplay,
                                            ]))),
                                        ];
                                        $payload[] = [
                                            'slug' => $geneSlug,
                                            'name' => $geneName,
                                            'states' => $displayStates,
                                        ];
                                        if (isset($currentGeneStates[$geneSlug])) {
                                            $selectedStates[$geneSlug] = $currentGeneStates[$geneSlug];
                                        }
                                    }
                                }
                            ?>
                            <div class="gene-selector-admin__species" data-animal-gene-group data-species-genes="<?= htmlspecialchars($species['slug']) ?>" data-gene-payload='<?= htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>' data-selected='<?= htmlspecialchars(json_encode($isActive ? $selectedStates : [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>' <?= $isActive ? '' : 'hidden' ?>>
                                <?php if (empty($genes)): ?>
                                    <p class="form-hint">Für diese Art sind noch keine Gene gepflegt. <a href="<?= BASE_URL ?>/index.php?route=admin/genetics&amp;species=<?= urlencode($species['slug']) ?>">Jetzt ergänzen</a>.</p>
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
            <label>Beschreibung
                <textarea name="description" class="rich-text"><?= htmlspecialchars($editListing['description'] ?? '') ?></textarea>
            </label>
            <label>Status
                <select name="status">
                    <?php foreach (['available' => 'verfügbar', 'reserved' => 'reserviert', 'adopted' => 'vermittelt'] as $key => $label): ?>
                        <option value="<?= $key ?>" <?= (($editListing['status'] ?? 'available') === $key) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Kontakt E-Mail
                <input type="email" name="contact_email" value="<?= htmlspecialchars($editListing['contact_email'] ?? $settings['contact_email'] ?? '') ?>">
            </label>
            <label>Bild
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editListing['image_path'])): ?>
                    <input type="hidden" name="image_path" value="<?= htmlspecialchars($editListing['image_path']) ?>">
                    <p><a href="<?= BASE_URL . '/' . htmlspecialchars($editListing['image_path']) ?>" target="_blank">Aktuelles Bild</a></p>
                <?php endif; ?>
            </label>
            <button type="submit" <?= empty($speciesList) ? 'disabled' : '' ?>>Speichern</button>
        </form>
    </div>
</div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
