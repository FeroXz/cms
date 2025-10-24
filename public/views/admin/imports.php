<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
    <h1>CSV-Importe</h1>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-error" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card space-y-4">
            <h2 class="card-title">Tiere importieren</h2>
            <p class="text-sm text-slate-300">Pflichtspalten: <code>name</code>, <code>species</code>. Optionale Felder: <code>id</code>, <code>age</code>, <code>genetics</code>, <code>owner_username</code>, <code>status</code>, <code>price</code>, <code>sire_id</code>/<code>sire_name</code>, <code>dam_id</code>/<code>dam_name</code>, <code>admin_notes</code>.</p>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="entity" value="animals">
                <label class="block text-sm font-semibold">CSV-Datei
                    <input type="file" name="csv" accept="text/csv" required class="mt-1 w-full">
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="dry_run" value="1" checked>
                    Dry-Run ohne Schreiben in die Datenbank
                </label>
                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="btn">Import starten</button>
                </div>
            </form>
        </div>

        <div class="card space-y-4">
            <h2 class="card-title">News importieren</h2>
            <p class="text-sm text-slate-300">Pflichtspalten: <code>title</code>, <code>content</code>. Optional: <code>id</code>, <code>slug</code>, <code>excerpt</code>, <code>is_published</code>, <code>published_at</code>.</p>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="entity" value="news">
                <label class="block text-sm font-semibold">CSV-Datei
                    <input type="file" name="csv" accept="text/csv" required class="mt-1 w-full">
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="dry_run" value="1" checked>
                    Dry-Run ohne Schreiben in die Datenbank
                </label>
                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="btn">Import starten</button>
                </div>
            </form>
        </div>

        <div class="card space-y-4">
            <h2 class="card-title">Abgabelisten importieren</h2>
            <p class="text-sm text-slate-300">Pflichtspalten: <code>title</code>. Weitere Felder: <code>animal_id</code>/<code>animal_name</code>, <code>species</code>, <code>status</code>, <code>price</code>, <code>contact_email</code>, <code>gender</code>, <code>genetics</code>, <code>description</code>.</p>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="entity" value="adoptions">
                <label class="block text-sm font-semibold">CSV-Datei
                    <input type="file" name="csv" accept="text/csv" required class="mt-1 w-full">
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="dry_run" value="1" checked>
                    Dry-Run ohne Schreiben in die Datenbank
                </label>
                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="btn">Import starten</button>
                </div>
            </form>
        </div>

        <div class="card space-y-4">
            <h2 class="card-title">Morph-Dateien verarbeiten</h2>
            <p class="text-sm text-slate-300">Du kannst hier ebenfalls Morph-CSV-Dateien prüfen oder direkt in den automatischen Import-Ordner legen.</p>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="entity" value="morphs">
                <label class="block text-sm font-semibold">CSV-Datei
                    <input type="file" name="csv" accept="text/csv" required class="mt-1 w-full">
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="dry_run" value="1" checked>
                    Dry-Run ohne Schreiben in die Datenbank
                </label>
                <div class="flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/index.php?route=admin/genetics/import" class="btn btn-secondary">Zur Morph-Detailansicht</a>
                    <button type="submit" class="btn">Import starten</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-6 space-y-4">
        <h2 class="card-title">Automatischer Import-Ordner</h2>
        <p class="text-sm text-slate-300">Lege CSV-Dateien in den folgenden Ordner, um sie beim nächsten Request automatisch importieren zu lassen. Der Ordner wird beim Bootstrap angelegt.</p>
        <code class="block rounded bg-night-800/70 px-3 py-2 text-xs text-slate-200"><?= htmlspecialchars($queueOverview['root'] ?? '') ?></code>
        <div class="overflow-x-auto">
            <table class="table text-sm">
                <thead>
                    <tr>
                        <th>Typ</th>
                        <th>Wartend</th>
                        <th>Importiert</th>
                        <th>Fehlerhaft</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($queueOverview['types'] ?? []) as $type): ?>
                        <tr>
                            <td><?= htmlspecialchars($type['label'] ?? $type['type']) ?></td>
                            <td><?= (int)($type['waiting'] ?? 0) ?></td>
                            <td><?= (int)($type['processed'] ?? 0) ?></td>
                            <td><?= (int)($type['failed'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($manualResult)): ?>
        <div class="card mt-6 space-y-4">
            <?php $entityLabel = strtoupper($manualResult['entity'] ?? ''); ?>
            <h2 class="card-title">Letzter manueller Import (<?= htmlspecialchars($entityLabel) ?>)</h2>
            <p class="text-sm text-slate-300">Modus: <?= !empty($manualResult['dry_run']) ? 'Dry-Run' : 'Schreibend' ?></p>
            <?php $summary = $manualResult['summary'] ?? []; ?>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="stat">
                    <span class="stat-value"><?= (int)($summary['total'] ?? 0) ?></span>
                    <span class="stat-label">Zeilen gesamt</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= (int)($summary['created'] ?? 0) ?></span>
                    <span class="stat-label">Neu erstellt</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= (int)($summary['updated'] ?? 0) ?></span>
                    <span class="stat-label">Aktualisiert</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= (int)($summary['duplicates'] ?? 0) ?></span>
                    <span class="stat-label">Duplikate</span>
                </div>
            </div>
            <?php if (!empty($manualResult['preview'])): ?>
                <div class="overflow-x-auto">
                    <table class="table text-sm">
                        <thead>
                            <tr>
                                <th>Zeile</th>
                                <th>Bezeichnung</th>
                                <th>Status</th>
                                <th>Notiz</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($manualResult['preview'] as $row): ?>
                                <tr>
                                    <td><?= (int)($row['line'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(strtoupper($row['action'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <?php if (!empty($summary['errors'])): ?>
                <details class="mt-3">
                    <summary>Fehlerdetails anzeigen</summary>
                    <ul class="list-disc pl-5 text-sm text-red-300">
                        <?php foreach ($summary['errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($autoResults['items'])): ?>
        <div class="card mt-6 space-y-4">
            <h2 class="card-title">Automatische Importe</h2>
            <p class="text-sm text-slate-300">Zuletzt ausgeführt: <?= isset($autoResults['timestamp']) ? date('d.m.Y H:i', (int)$autoResults['timestamp']) : '–' ?></p>
            <div class="overflow-x-auto">
                <table class="table text-sm">
                    <thead>
                        <tr>
                            <th>Typ</th>
                            <th>Datei</th>
                            <th>Status</th>
                            <th>Hinweis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($autoResults['items'] as $item): ?>
                            <?php
                                $summary = $item['summary'] ?? [];
                                $message = $item['message'] ?? '';
                                if ($message === '' && $summary) {
                                    $message = sprintf(
                                        'Neu: %d · Aktualisiert: %d · Duplikate: %d · Fehler: %d',
                                        (int)($summary['created'] ?? 0),
                                        (int)($summary['updated'] ?? 0),
                                        (int)($summary['duplicates'] ?? 0),
                                        count($summary['errors'] ?? [])
                                    );
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['file'] ?? '') ?></td>
                                <td><?= htmlspecialchars(strtoupper($item['status'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($message) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
