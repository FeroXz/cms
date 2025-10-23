<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8" id="morph-import-app" data-upload-url="<?= BASE_URL ?>/index.php?route=api/import/morphs" data-sample-url="<?= BASE_URL ?>/index.php?route=api/import/morphs/sample" data-csrf="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <h1>Morph-Import</h1>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-error" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="card morph-import">
        <header class="morph-import__intro">
            <div>
                <h2>CSV hochladen</h2>
                <p>Erwartete Spalten: <code>name</code>, <code>species</code>, <code>type</code>, <code>aliases</code>, <code>description</code>, <code>sourceUrl</code>. Über das Mapping können abweichende Kopfzeilen zugeordnet werden.</p>
            </div>
            <button type="button" class="btn btn-secondary" data-action="load-sample">Beispiel anzeigen</button>
        </header>
        <div class="morph-import__dropzone" role="button" tabindex="0" aria-label="CSV-Datei hierher ziehen oder klicken" data-action="trigger-file">
            <div class="morph-import__dropzone-inner">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="morph-import__icon"><path d="M12 3a1 1 0 0 1 .993.883L13 4v7.586l1.293-1.293 1.414 1.414L12 16.414l-3.707-3.707 1.414-1.414L11 11.586V4a1 1 0 0 1 1-1Zm7 12a1 1 0 0 1 .117 1.993L19 17h-2.05a5.5 5.5 0 0 1-9.9 0H5a1 1 0 0 1-.117-1.993L5 15h2.02a5.5 5.5 0 0 1 9.96 0H19Z" fill="currentColor"></path></svg>
                <p><strong>Datei hierher ziehen</strong> oder klicken zum Auswählen.</p>
                <p class="morph-import__filename" data-element="filename">Noch keine Datei ausgewählt.</p>
            </div>
            <input type="file" accept="text/csv" data-element="file-input" hidden>
        </div>

        <form class="morph-import__mapping" data-element="mapping-form" novalidate>
            <h3>Feldzuordnung</h3>
            <p>Bitte ordne die CSV-Spalten zu. Nicht benötigte Felder können ausgelassen werden.</p>
            <div class="morph-import__mapping-grid">
                <?php
                    $fields = [
                        'name' => 'Morph-Name',
                        'species' => 'Art',
                        'type' => 'Vererbungstyp',
                        'aliases' => 'Alias (optional)',
                        'description' => 'Beschreibung (optional)',
                        'sourceUrl' => 'Quelle (optional)',
                    ];
                ?>
                <?php foreach ($fields as $field => $label): ?>
                    <label>
                        <span><?= htmlspecialchars($label) ?></span>
                        <select data-mapping="<?= htmlspecialchars($field) ?>">
                            <option value="">&ndash; automatisch zuordnen &ndash;</option>
                        </select>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="morph-import__actions">
                <button type="button" class="btn" data-action="dry-run" disabled>Dry-Run prüfen</button>
                <button type="button" class="btn btn-secondary" data-action="import" disabled>Import starten</button>
            </div>
        </form>

        <section class="morph-import__summary" aria-live="polite" data-element="summary"></section>

        <section class="morph-import__preview">
            <header class="morph-import__preview-head">
                <h3>Vorschau (max. 10 Zeilen)</h3>
                <span class="morph-import__preview-note" data-element="preview-note"></span>
            </header>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Zeile</th>
                            <th>Name</th>
                            <th>Art</th>
                            <th>Typ</th>
                            <th>Aliasse</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody data-element="preview-body">
                        <tr>
                            <td colspan="6" class="text-muted">Noch keine Daten geladen.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
