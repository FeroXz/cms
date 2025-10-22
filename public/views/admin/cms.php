<?php include __DIR__ . '/../partials/header.php'; ?>
<?php
    $novaEnabled = setting_enabled($settings, 'nova_features_enabled');
    $activeBlueprintDefinition = [];
    if (!empty($activeBlueprint['definition'])) {
        $decodedDefinition = json_decode($activeBlueprint['definition'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDefinition)) {
            $activeBlueprintDefinition = $decodedDefinition;
        }
    }
    $editMenuId = isset($editMenuItem['id']) ? (int)$editMenuItem['id'] : null;
?>
<section class="mx-auto max-w-6xl px-4 py-10 text-slate-100">
    <div class="rounded-4xl bg-gradient-to-br from-slate-900 via-indigo-900 to-cyan-900 p-8 shadow-[0_40px_140px_rgba(15,23,42,0.45)]">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-cyan-200/80">Nova Suite</p>
                <h1 class="text-3xl font-semibold leading-tight sm:text-4xl">Erlebnis-orientierte CMS-Zentrale</h1>
                <p class="text-base text-cyan-100/80">
                    Steuere Navigation, Mediathek und Layout-Blaupausen an einem Ort. Ein Aktivitätsprotokoll hält jede Änderung nachvollziehbar fest.
                </p>
            </div>
            <form method="post" class="rounded-3xl border border-cyan-400/40 bg-slate-900/40 p-6 backdrop-blur">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_nova_features">
                <div class="flex flex-col gap-4">
                    <span class="text-sm font-medium text-cyan-100">Nova-Funktionen</span>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="nova_features_enabled" value="1" class="peer sr-only" <?= $novaEnabled ? 'checked' : '' ?>>
                        <span class="h-10 w-20 rounded-full bg-slate-700 transition peer-checked:bg-emerald-500/80"></span>
                        <span class="absolute left-1 top-1 h-8 w-8 rounded-full bg-white shadow-lg transition peer-checked:translate-x-10"></span>
                    </label>
                    <p class="text-xs text-cyan-100/70">
                        Deaktiviere bei Bedarf sämtliche neuen Navigation-, Layout- und Mediatheksfunktionen temporär für das Frontend.
                    </p>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
                        <?= $novaEnabled ? 'Nova-Module deaktivieren' : 'Nova-Module aktivieren' ?>
                    </button>
                </div>
            </form>
        </div>
        <?php if ($flashSuccess): ?>
            <div class="mt-6 rounded-3xl border border-emerald-300/60 bg-emerald-500/10 p-4 text-sm text-emerald-100">
                <?= htmlspecialchars($flashSuccess) ?>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="mt-6 rounded-3xl border border-rose-300/60 bg-rose-500/10 p-4 text-sm text-rose-100">
                <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 pb-10">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="rounded-4xl border border-slate-800 bg-slate-900/70 p-8 shadow-[0_30px_120px_rgba(8,47,73,0.45)]">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-white">Primäre Navigation</h2>
                    <p class="text-sm text-cyan-100/70">Strukturiere Menüpunkte, Unterpunkte und Zielseiten.</p>
                </div>
                <?php if ($editMenuId): ?>
                    <a href="<?= BASE_URL ?>/index.php?route=admin/cms" class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan-200/70 hover:text-cyan-100">Abbrechen</a>
                <?php endif; ?>
            </div>
            <div class="mt-6 grid gap-8 lg:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.35em] text-cyan-100/60">Struktur</h3>
                    <div class="space-y-3 text-sm text-cyan-100/80">
                        <?php if (empty($menuTree)): ?>
                            <p>Keine Navigationspunkte vorhanden.</p>
                        <?php else: ?>
                            <ul class="space-y-3">
                                <?php
                                    $renderMenu = function (array $items, int $depth = 0) use (&$renderMenu) {
                                        echo '<ul class="space-y-2 ' . ($depth > 0 ? 'pl-4 border-l border-cyan-400/30' : '') . '">';
                                        foreach ($items as $item) {
                                            echo '<li class="space-y-1">';
                                            echo '<div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-800/70 px-3 py-2">';
                                            echo '<div>';
                                            echo '<span class="text-sm font-medium text-white">' . htmlspecialchars($item['label']) . '</span>';
                                            if (!empty($item['url'])) {
                                                echo '<p class="text-xs text-cyan-200/70">' . htmlspecialchars($item['url']) . '</p>';
                                            } elseif (!empty($item['page_slug'])) {
                                                echo '<p class="text-xs text-cyan-200/70">Seite: ' . htmlspecialchars($item['page_slug']) . '</p>';
                                            }
                                            echo '</div>';
                                            echo '<div class="flex items-center gap-2 text-xs uppercase tracking-[0.25em]">';
                                            echo '<a class="rounded-full border border-cyan-400/40 px-3 py-1 text-cyan-200 hover:bg-cyan-400/10" href="' . BASE_URL . '/index.php?route=admin/cms&amp;edit_menu=' . (int)$item['id'] . '">Edit</a>';
                                            echo '<form method="post" onsubmit="return confirm(\'Eintrag wirklich löschen?\');">';
                                            echo csrf_field();
                                            echo '<input type="hidden" name="action" value="delete_menu_item">';
                                            echo '<input type="hidden" name="id" value="' . (int)$item['id'] . '">';
                                            echo '<button type="submit" class="rounded-full border border-rose-400/40 px-3 py-1 text-rose-200 hover:bg-rose-400/10">Del</button>';
                                            echo '</form>';
                                            echo '</div>';
                                            echo '</div>';
                                            if (!empty($item['children'])) {
                                                $renderMenu($item['children'], $depth + 1);
                                            }
                                            echo '</li>';
                                        }
                                        echo '</ul>';
                                    };
                                    $renderMenu($menuTree);
                                ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="post" class="rounded-3xl border border-cyan-400/20 bg-slate-800/70 p-6 shadow-inner shadow-slate-950/40">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save_menu_item">
                    <?php if ($editMenuId): ?>
                        <input type="hidden" name="id" value="<?= $editMenuId ?>">
                    <?php endif; ?>
                    <h3 class="text-sm font-semibold uppercase tracking-[0.35em] text-cyan-100/60">
                        <?= $editMenuId ? 'Navigationseintrag bearbeiten' : 'Neuen Navigationseintrag' ?>
                    </h3>
                    <label class="mt-4 block text-sm font-medium text-cyan-100">Titel
                        <input type="text" name="label" value="<?= htmlspecialchars($editMenuItem['label'] ?? '') ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none">
                    </label>
                    <label class="mt-4 block text-sm font-medium text-cyan-100">Externe URL
                        <input type="url" name="url" value="<?= htmlspecialchars($editMenuItem['url'] ?? '') ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none" placeholder="https://...">
                    </label>
                    <label class="mt-4 block text-sm font-medium text-cyan-100">Interne Seite
                        <input type="text" name="page_slug" value="<?= htmlspecialchars($editMenuItem['page_slug'] ?? '') ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none" placeholder="slug-der-seite">
                    </label>
                    <label class="mt-4 block text-sm font-medium text-cyan-100">Übergeordnetes Element
                        <select name="parent_id" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none">
                            <option value="">(Kein übergeordnetes Element)</option>
                            <?php foreach ($menuOptions as $option): ?>
                                <option value="<?= (int)$option['id'] ?>" <?= isset($editMenuItem['parent_id']) && (int)$editMenuItem['parent_id'] === (int)$option['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="mt-4 flex items-center gap-2 text-sm text-cyan-100">
                        <input type="checkbox" name="open_in_new_tab" value="1" class="h-4 w-4 rounded border-slate-700 bg-slate-900" <?= !empty($editMenuItem['open_in_new_tab']) ? 'checked' : '' ?>>
                        In neuem Tab öffnen
                    </label>
                    <label class="mt-4 block text-sm font-medium text-cyan-100">Reihenfolge
                        <input type="number" name="position" value="<?= isset($editMenuItem['position']) ? (int)$editMenuItem['position'] : 0 ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none">
                    </label>
                    <button type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-cyan-500 px-4 py-2 text-sm font-semibold uppercase tracking-[0.25em] text-slate-900 transition hover:bg-cyan-400">
                        <?= $editMenuId ? 'Eintrag aktualisieren' : 'Eintrag speichern' ?>
                    </button>
                </form>
            </div>
        </div>
        <div class="rounded-4xl border border-slate-800 bg-slate-900/70 p-8 shadow-[0_30px_120px_rgba(8,47,73,0.45)]">
            <h2 class="text-2xl font-semibold text-white">Layout-Blaupausen</h2>
            <p class="mt-2 text-sm text-cyan-100/70">Bestimme das Grundraster der Startseite.</p>
            <form method="post" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="set_blueprint">
                <?php foreach ($layoutBlueprints as $blueprint): ?>
                    <label class="flex cursor-pointer flex-col gap-2 rounded-3xl border <?= ($activeBlueprint['slug'] ?? '') === $blueprint['slug'] ? 'border-cyan-400 bg-cyan-500/10' : 'border-slate-700 bg-slate-800/70' ?> p-4 transition hover:border-cyan-400/80">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <span class="text-base font-semibold text-white"><?= htmlspecialchars($blueprint['name']) ?></span>
                                <p class="text-xs text-cyan-100/70"><?= htmlspecialchars($blueprint['description']) ?></p>
                            </div>
                            <input type="radio" name="blueprint" value="<?= htmlspecialchars($blueprint['slug']) ?>" <?= ($activeBlueprint['slug'] ?? '') === $blueprint['slug'] ? 'checked' : '' ?> class="h-5 w-5">
                        </div>
                        <?php
                            $definition = json_decode($blueprint['definition'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($definition)) {
                                echo '<div class="text-xs text-cyan-100/60">' . htmlspecialchars(implode(' • ', $definition['home_sections'] ?? [])) . '</div>';
                            }
                        ?>
                    </label>
                <?php endforeach; ?>
                <button type="submit" class="w-full rounded-full bg-white/10 px-4 py-2 text-sm font-semibold uppercase tracking-[0.25em] text-white transition hover:bg-white/20">Blaupause aktivieren</button>
            </form>
            <?php if ($activeBlueprintDefinition): ?>
                <div class="mt-6 rounded-3xl border border-cyan-400/30 bg-slate-800/70 p-4 text-xs text-cyan-100/70">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-cyan-200/80">Aktive Sequenz</h3>
                    <p class="mt-2">Abschnitte: <?= htmlspecialchars(implode(' → ', $activeBlueprintDefinition['home_sections'] ?? [])) ?></p>
                    <?php if (!empty($activeBlueprintDefinition['highlight_layout'])): ?>
                        <p>Highlight-Layout: <?= htmlspecialchars($activeBlueprintDefinition['highlight_layout']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="rounded-4xl border border-slate-800 bg-slate-900/70 p-8 shadow-[0_30px_120px_rgba(8,47,73,0.45)]">
            <h2 class="text-2xl font-semibold text-white">Meta &amp; Vorschau</h2>
            <p class="mt-2 text-sm text-cyan-100/70">SEO-Basisangaben für Social Media und Suchmaschinen.</p>
            <form method="post" enctype="multipart/form-data" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_meta">
                <label class="block text-sm font-medium text-cyan-100">Beschreibung
                    <textarea name="global_meta_description" rows="3" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none"><?= htmlspecialchars($settings['global_meta_description'] ?? '') ?></textarea>
                </label>
                <label class="block text-sm font-medium text-cyan-100">Social-Vorschau Bild (URL)
                    <input type="text" name="global_meta_image" value="<?= htmlspecialchars($settings['global_meta_image'] ?? '') ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none" placeholder="uploads/preview.jpg">
                </label>
                <label class="block text-sm font-medium text-cyan-100">oder neues Bild hochladen
                    <input type="file" name="global_meta_image_file" accept="image/*" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100">
                </label>
                <button type="submit" class="w-full rounded-full bg-aurora px-4 py-2 text-sm font-semibold uppercase tracking-[0.25em] text-slate-950 transition hover:-translate-y-1 hover:bg-aurora/90">Meta speichern</button>
            </form>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 pb-10">
    <div class="rounded-4xl border border-slate-800 bg-slate-900/70 p-8 shadow-[0_30px_120px_rgba(8,47,73,0.45)]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Mediathek</h2>
                <p class="text-sm text-cyan-100/70">Verwalte Bilder und Assets für Inhalte und Layout.</p>
            </div>
            <form method="post" enctype="multipart/form-data" class="flex flex-col gap-3 rounded-3xl border border-cyan-400/20 bg-slate-800/60 p-4 sm:flex-row sm:items-end">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upload_media">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Titel
                        <input type="text" name="title" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none">
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Datei
                        <input type="file" name="file" accept="image/*" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100">
                    </label>
                </div>
                <div class="sm:flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Alt-Text
                        <input type="text" name="alt_text" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100">
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Beschreibung
                        <input type="text" name="description" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100">
                    </label>
                </div>
                <button type="submit" class="rounded-full bg-cyan-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-900 hover:bg-cyan-400">Hochladen</button>
            </form>
        </div>
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($mediaItems as $media): ?>
                <div class="flex flex-col gap-3 rounded-3xl border border-slate-800 bg-slate-800/60 p-4">
                    <?php if (!empty($media['file_path'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($media['file_path']) ?>" alt="<?= htmlspecialchars($media['alt_text'] ?? $media['title']) ?>" class="h-40 w-full rounded-2xl object-cover" loading="lazy">
                    <?php endif; ?>
                    <form method="post" class="space-y-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_media">
                        <input type="hidden" name="id" value="<?= (int)$media['id'] ?>">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Titel
                                <input type="text" name="title" value="<?= htmlspecialchars($media['title']) ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none">
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Alt-Text
                                <input type="text" name="alt_text" value="<?= htmlspecialchars($media['alt_text'] ?? '') ?>" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none">
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.25em] text-cyan-100/60">Beschreibung
                                <textarea name="description" rows="2" class="mt-1 w-full rounded-2xl border border-slate-700 bg-slate-900/70 px-3 py-2 text-slate-100 focus:border-cyan-400 focus:outline-none"><?= htmlspecialchars($media['description'] ?? '') ?></textarea>
                            </label>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="rounded-full bg-cyan-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-900 hover:bg-cyan-400">Speichern</button>
                            <button form="delete-media-<?= (int)$media['id'] ?>" type="submit" class="rounded-full border border-rose-400/50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-rose-200 hover:bg-rose-500/10">Löschen</button>
                        </div>
                    </form>
                    <form id="delete-media-<?= (int)$media['id'] ?>" method="post" onsubmit="return confirm('Medium wirklich löschen?');" class="hidden">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_media">
                        <input type="hidden" name="id" value="<?= (int)$media['id'] ?>">
                    </form>
                </div>
            <?php endforeach; ?>
            <?php if (empty($mediaItems)): ?>
                <p class="text-sm text-cyan-100/60">Noch keine Medien vorhanden.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 pb-16">
    <div class="rounded-4xl border border-slate-800 bg-slate-900/70 p-8 shadow-[0_30px_120px_rgba(8,47,73,0.45)]">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-white">Aktivitätsprotokoll</h2>
                <p class="text-sm text-cyan-100/70">Die letzten Aktionen innerhalb der Nova Suite.</p>
            </div>
            <form method="post" onsubmit="return confirm('Protokoll wirklich leeren?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="clear_activity">
                <button type="submit" class="rounded-full border border-cyan-400/40 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200 hover:bg-cyan-400/10">Protokoll leeren</button>
            </form>
        </div>
        <div class="mt-6 space-y-3 text-sm text-cyan-100/70">
            <?php foreach ($activityEntries as $entry): ?>
                <article class="rounded-3xl border border-slate-800 bg-slate-800/60 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-200/70"><?= htmlspecialchars($entry['action']) ?></div>
                        <div class="text-xs text-cyan-200/50"><?= htmlspecialchars($entry['created_at']) ?></div>
                    </div>
                    <p class="mt-2 text-sm text-cyan-100">Ausgeführt von <?= htmlspecialchars($entry['actor'] ?? 'system') ?></p>
                    <?php if (!empty($entry['details']) && is_array($entry['details'])): ?>
                        <pre class="mt-3 overflow-x-auto rounded-2xl bg-slate-900/80 p-3 text-xs text-cyan-200/70"><?= htmlspecialchars(json_encode($entry['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                    <?php elseif (!empty($entry['details']) && is_string($entry['details'])): ?>
                        <p class="mt-3 text-xs text-cyan-200/70"><?= htmlspecialchars($entry['details']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
            <?php if (empty($activityEntries)): ?>
                <p>Keine Aktivitäten vorhanden.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
