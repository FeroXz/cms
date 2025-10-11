<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <h1>Galerie verwalten</h1>
    <?php include __DIR__ . '/nav.php'; ?>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-error" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="admin-two-column" style="margin-top:2rem;">
        <div class="card">
            <h2>Bestehende Aufnahmen</h2>
            <?php if (empty($galleryImages)): ?>
                <p>Noch keine Einträge vorhanden.</p>
            <?php else: ?>
                <div class="grid cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <?php foreach ($galleryImages as $image): ?>
                        <?php
                            $fitMode = normalize_gallery_fit_mode($image['fit_mode'] ?? 'cover');
                            $focusX = normalize_gallery_focus($image['focus_x'] ?? 50);
                            $focusY = normalize_gallery_focus($image['focus_y'] ?? 50);
                            $previewStyle = sprintf('width:100%%;height:180px;object-fit:%s;object-position:%d%%%% %d%%%%;', $fitMode, $focusX, $focusY);
                        ?>
                        <article class="card" style="padding:1rem;">
                            <div style="position:relative;border-radius:1rem;overflow:hidden;margin-bottom:0.75rem;background:linear-gradient(135deg, rgba(15,23,42,0.85), rgba(30,64,175,0.35));">
                                <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" style="<?= $previewStyle ?>">
                                <?php if (empty($image['is_published'])): ?>
                                    <span class="badge" style="position:absolute;top:0.75rem;left:0.75rem;background:rgba(248,113,113,0.25);color:#fecaca;">Entwurf</span>
                                <?php endif; ?>
                            </div>
                            <h3 style="margin:0;font-size:1.1rem;"><?= htmlspecialchars($image['title']) ?></h3>
                            <?php if (!empty($image['caption'])): ?>
                                <p class="text-muted" style="font-size:0.85rem;margin-top:0.35rem;line-height:1.4;"><?= htmlspecialchars($image['caption']) ?></p>
                            <?php endif; ?>
                            <p class="text-muted" style="font-size:0.75rem;margin-top:0.5rem;">Reihenfolge: <?= (int)$image['display_order'] ?></p>
                            <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:0.35rem;margin:0.75rem 0 0;font-size:0.75rem;color:rgba(148,163,184,0.9);">
                                <div style="display:flex;flex-direction:column;gap:0.2rem;">
                                    <dt style="text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:rgba(148,163,184,0.8);">Darstellung</dt>
                                    <dd style="margin:0;font-weight:500;color:#e2e8f0;">
                                        <?= $fitMode === 'contain' ? 'Bild einpassen' : 'Bild füllen' ?>
                                    </dd>
                                </div>
                                <div style="display:flex;flex-direction:column;gap:0.2rem;">
                                    <dt style="text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:rgba(148,163,184,0.8);">Fokuspunkt</dt>
                                    <dd style="margin:0;font-weight:500;color:#e2e8f0;">
                                        <?= $focusX ?>% / <?= $focusY ?>%
                                    </dd>
                                </div>
                            </dl>
                            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.75rem;">
                                <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/gallery&amp;edit=<?= (int)$image['id'] ?>">Bearbeiten</a>
                                <form method="post" onsubmit="return confirm('Bild wirklich löschen? Dieser Schritt kann nicht rückgängig gemacht werden.');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="form_type" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$image['id'] ?>">
                                    <button type="submit" class="btn btn-secondary">Löschen</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="card">
            <h2><?= $editImage ? 'Galeriebild bearbeiten' : 'Neues Bild hinzufügen' ?></h2>
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="form_type" value="<?= $editImage ? 'update' : 'create' ?>">
                <?php if ($editImage): ?>
                    <input type="hidden" name="id" value="<?= (int)$editImage['id'] ?>">
                <?php endif; ?>
                <label>Titel
                    <input type="text" name="title" value="<?= htmlspecialchars($editImage['title'] ?? '') ?>" required>
                </label>
                <label>Bildunterschrift
                    <textarea name="caption" rows="3" placeholder="Kurze Beschreibung oder Story zum Bild."><?= htmlspecialchars($editImage['caption'] ?? '') ?></textarea>
                </label>
                <label>Reihenfolge
                    <input type="number" name="display_order" value="<?= htmlspecialchars((string)($editImage['display_order'] ?? 0)) ?>">
                </label>
                <?php
                    $formFitMode = normalize_gallery_fit_mode($editImage['fit_mode'] ?? 'cover');
                    $formFocusX = normalize_gallery_focus($editImage['focus_x'] ?? 50);
                    $formFocusY = normalize_gallery_focus($editImage['focus_y'] ?? 50);
                ?>
                <label>Bilddarstellung
                    <select name="fit_mode">
                        <option value="cover" <?= $formFitMode === 'cover' ? 'selected' : '' ?>>Bild füllen (beschnittener Ausschnitt)</option>
                        <option value="contain" <?= $formFitMode === 'contain' ? 'selected' : '' ?>>Bild einpassen (vollständig anzeigen)</option>
                    </select>
                </label>
                <div class="card" style="background:rgba(15,23,42,0.35);border-radius:1rem;padding:1rem;gap:0.75rem;display:flex;flex-direction:column;">
                    <p style="margin:0;font-size:0.85rem;color:rgba(226,232,240,0.9);">Optional: Lege den sichtbaren Ausschnitt für beschnittene Bilder fest. Der Fokuspunkt wird bei 'Bild einpassen' ignoriert.</p>
                    <label style="display:flex;flex-direction:column;gap:0.35rem;">
                        <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:rgba(148,163,184,0.85);">Horizontaler Fokus</span>
                        <input type="range" min="0" max="100" step="1" name="focus_x" value="<?= $formFocusX ?>" data-range-output="#focus-x-output">
                        <span style="font-size:0.8rem;color:rgba(226,232,240,0.9);">Position: <strong id="focus-x-output"><?= $formFocusX ?></strong>%</span>
                    </label>
                    <label style="display:flex;flex-direction:column;gap:0.35rem;">
                        <span style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:rgba(148,163,184,0.85);">Vertikaler Fokus</span>
                        <input type="range" min="0" max="100" step="1" name="focus_y" value="<?= $formFocusY ?>" data-range-output="#focus-y-output">
                        <span style="font-size:0.8rem;color:rgba(226,232,240,0.9);">Position: <strong id="focus-y-output"><?= $formFocusY ?></strong>%</span>
                    </label>
                </div>
                <label>Bild auswählen (Upload)
                    <input type="file" name="image" accept="image/*">
                </label>
                <label>oder externe Bild-URL
                    <input type="url" name="image_url" value="<?= htmlspecialchars($editImage['image_path'] ?? '') ?>" placeholder="https://…">
                </label>
                <label style="display:flex;align-items:center;gap:0.5rem;margin-top:1rem;">
                    <input type="checkbox" name="is_published" value="1" <?= !empty($editImage['is_published']) ? 'checked' : '' ?>>
                    <span>Öffentlich anzeigen</span>
                </label>
                <button type="submit" style="margin-top:1.25rem;">Speichern</button>
                <?php if ($editImage): ?>
                    <a href="<?= BASE_URL ?>/index.php?route=admin/gallery" class="btn btn-secondary" style="margin-left:0.75rem;">Abbrechen</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>
<script>
    document.querySelectorAll('input[type="range"][data-range-output]').forEach((input) => {
        const targetSelector = input.getAttribute('data-range-output');
        const target = targetSelector ? document.querySelector(targetSelector) : null;
        if (!target) {
            return;
        }
        const update = () => {
            target.textContent = input.value;
        };
        input.addEventListener('input', update);
        input.addEventListener('change', update);
    });
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
