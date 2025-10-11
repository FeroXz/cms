<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,320px)] lg:items-center">
        <div class="space-y-5">
            <h1 class="text-3xl font-semibold text-white sm:text-4xl">Impressionen aus dem FeroxZ Center</h1>
            <p class="text-base text-slate-300 leading-relaxed">
                Unsere Galerie zeigt Highlights aus Haltung, Terrariengestaltung und Zuchtprojekten. Die Aufnahmen aktualisieren sich automatisch,
                sobald im Dashboard neue Bilder hinterlegt werden.
            </p>
            <p class="text-sm text-slate-400">
                Tippe oder klicke auf eine Karte, um Bildunterschriften und weiterführende Informationen einzublenden.
            </p>
        </div>
        <div class="rounded-3xl border border-white/5 bg-night-900/80 p-6 shadow-glow shadow-brand-600/30">
            <dl class="grid grid-cols-2 gap-4 text-sm text-slate-300">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Aktive Aufnahmen</dt>
                    <dd class="mt-1 text-2xl font-semibold text-white"><?= count($galleryImages) ?></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Kuratiert seit</dt>
                    <dd class="mt-1 text-2xl font-semibold text-white">2020</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Hinweis</dt>
                    <dd class="mt-1 leading-relaxed text-slate-300">Alle Bilder stammen aus dem Vereinsarchiv oder wurden durch die Community freigegeben.</dd>
                </div>
            </dl>
        </div>
    </div>

    <?php if (empty($galleryImages)): ?>
        <div class="mt-12 rounded-3xl border border-dashed border-white/20 p-10 text-center text-slate-300">
            <p>Noch keine Galerieinhalte vorhanden. Administrator*innen können im Backend Bilder hochladen.</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid mt-12">
            <?php foreach ($galleryImages as $image): ?>
                <?php
                    $fitMode = normalize_gallery_fit_mode($image['fit_mode'] ?? 'cover');
                    $figureStyle = sprintf('--gallery-fit:%s;', $fitMode);
                ?>
                <article class="gallery-card" tabindex="0">
                    <figure class="gallery-card__figure" style="<?= $figureStyle ?>">
                        <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" loading="lazy">
                        <div class="gallery-card__overlay">
                            <h2><?= htmlspecialchars($image['title']) ?></h2>
                            <?php if (!empty($image['caption'])): ?>
                                <p><?= htmlspecialchars($image['caption']) ?></p>
                            <?php endif; ?>
                        </div>
                    </figure>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
