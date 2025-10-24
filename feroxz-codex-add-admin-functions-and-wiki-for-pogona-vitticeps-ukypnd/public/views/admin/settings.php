<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
    <h1>Einstellungen</h1>
    <?php include __DIR__ . '/nav.php'; ?>
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-error" role="status" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="card space-y-4">
            <h2 class="card-title">Allgemeine Einstellungen</h2>
            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <?php $themes = get_available_themes(); ?>
                <label>Seitentitel
                    <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" required>
                </label>
                <label>Untertitel
                    <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>">
                </label>
                <label>Hero-Einleitung
                    <textarea name="hero_intro" class="rich-text" rows="4"><?= htmlspecialchars($settings['hero_intro'] ?? '') ?></textarea>
                </label>
                <label>Abgabe Intro
                    <textarea name="adoption_intro" class="rich-text" rows="4"><?= htmlspecialchars($settings['adoption_intro'] ?? '') ?></textarea>
                </label>
                <label>Footer Text
                    <textarea name="footer_text" class="rich-text" rows="3"><?= htmlspecialchars($settings['footer_text'] ?? '') ?></textarea>
                </label>
                <label>Kontakt E-Mail
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                </label>
                <label>Design
                    <select name="active_theme">
                        <?php foreach ($themes as $key => $theme): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= (($settings['active_theme'] ?? 'aurora') === $key) ? 'selected' : '' ?>><?= htmlspecialchars($theme['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn">Speichern</button>
                </div>
            </form>
        </div>

        <div class="card space-y-4">
            <h2 class="card-title">Update &amp; Deploy</h2>
            <?php
                $updateEnabled = (bool)($updateCapabilities['enabled'] ?? false);
                $environment = $updateCapabilities['environment'] ?? 'production';
                $isSimulation = $environment !== 'production';
                $buttonDisabled = (bool)($updateCapabilities['buttonDisabled'] ?? false);
                $currentVersion = $settings['app_version'] ?? ($settings['footer_text'] ?? '');
            ?>
            <p class="text-sm text-slate-300">Führe einen Aktualisierungslauf inklusive <code>git pull --rebase</code>, <code>prisma migrate deploy</code> und <code>npm run build</code> aus. In nicht-produktiven Umgebungen werden die Befehle lediglich simuliert.</p>
            <form method="post" action="<?= BASE_URL ?>/index.php?route=admin/settings/update" class="space-y-4">
                <?= csrf_field() ?>
                <div class="grid gap-4 md:grid-cols-2">
                    <label>Version
                        <input type="text" name="version" value="<?= htmlspecialchars(is_string($currentVersion) ? $currentVersion : '') ?>" placeholder="z. B. 3.5.0" required>
                    </label>
                    <label>Notizen (optional)
                        <input type="text" name="notes" placeholder="Änderungen oder Hinweise">
                    </label>
                </div>
                <div class="rounded-lg border border-white/10 bg-night-800/60 p-4 text-xs text-slate-300">
                    <p><strong>Umgebung:</strong> <?= htmlspecialchars(strtoupper($environment)) ?><?= $isSimulation ? ' (Simulationsmodus)' : '' ?></p>
                    <p><strong>Status:</strong> <?= $updateEnabled ? 'Aktiviert' : 'Deaktiviert' ?> – Schalte per <code>ENABLE_UPDATE=true</code> frei.</p>
                    <?php if ($isSimulation): ?>
                        <p><strong>Hinweis:</strong> In nicht-produktiven Umgebungen werden alle Befehle nur simuliert, die Ergebnisse erscheinen dennoch im Changelog.</p>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-400">Button ist deaktiviert, solange die Funktion nicht freigeschaltet ist.</p>
                    <button type="submit" class="btn" <?= !$buttonDisabled ? '' : 'disabled aria-disabled="true"' ?>>Update &amp; Deploy</button>
                </div>
            </form>

            <?php if (!empty($updateSummary)): ?>
                <?php $statusClass = match ($updateSummary['status'] ?? '') {
                    'failed' => 'alert-error',
                    'simulated' => 'alert-info',
                    default => 'alert-success'
                }; ?>
                <div class="alert <?= $statusClass ?> mt-4" role="status">
                    <strong>Status:</strong> <?= htmlspecialchars(strtoupper($updateSummary['status'] ?? 'unbekannt')) ?>
                    <?php if (!empty($updateSummary['message'])): ?>
                        <br><?= htmlspecialchars($updateSummary['message']) ?>
                    <?php endif; ?>
                </div>
                <details class="update-logs">
                    <summary>Protokoll anzeigen</summary>
                    <ul>
                        <?php foreach (($updateSummary['logs'] ?? []) as $log): ?>
                            <li>
                                <code><?= htmlspecialchars($log['command'] ?? '') ?></code>
                                <span class="ml-2 text-xs text-slate-400">Exit: <?= isset($log['exitCode']) ? (int)$log['exitCode'] : '—' ?><?= !empty($log['simulated']) ? ' · simuliert' : '' ?></span>
                                <?php if (!empty($log['output'])): ?>
                                    <pre><?= htmlspecialchars($log['output']) ?></pre>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-6">
        <h2 class="card-title">Changelog</h2>
        <?php if (empty($changelogEntries)): ?>
            <p class="text-sm text-slate-400">Noch keine Einträge vorhanden.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="table text-sm">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Datum</th>
                            <th>Notizen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($changelogEntries as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['version'] ?? '') ?></td>
                                <td><?= htmlspecialchars(strtoupper($entry['status'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($entry['created_at'] ?? '') ?></td>
                                <td><?= htmlspecialchars($entry['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
