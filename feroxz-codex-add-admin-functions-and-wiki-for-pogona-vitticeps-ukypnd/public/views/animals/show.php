<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
    <a href="<?= BASE_URL ?>/index.php?route=animals" class="inline-flex items-center gap-2 text-sm font-medium text-brand-200 hover:text-brand-100">
        &larr; Zurück zur Übersicht
    </a>
    <div class="mt-6 grid gap-10 lg:grid-cols-[2fr,1fr]">
        <div>
            <?php $primaryImage = $animal['media'][0]['urls']['medium'] ?? $animal['media'][0]['urls']['original'] ?? ($animal['image_path'] ? BASE_URL . '/' . $animal['image_path'] : null); ?>
            <?php if ($primaryImage): ?>
                <div class="overflow-hidden rounded-3xl border border-white/10">
                    <img src="<?= htmlspecialchars($primaryImage) ?>" alt="<?= htmlspecialchars($animal['name']) ?>" class="h-96 w-full object-cover" loading="lazy" data-lightbox-trigger>
                </div>
            <?php endif; ?>
            <?php if (!empty($animal['media'])): ?>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <?php foreach ($animal['media'] as $mediaItem): ?>
                        <?php $thumb = $mediaItem['urls']['thumb'] ?? $mediaItem['urls']['medium'] ?? $mediaItem['urls']['original']; ?>
                        <?php if ($thumb): ?>
                            <button type="button" class="overflow-hidden rounded-2xl border border-white/10 focus:outline-none focus:ring-2 focus:ring-brand-400" data-lightbox-src="<?= htmlspecialchars($mediaItem['urls']['original'] ?? $thumb) ?>">
                                <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($mediaItem['alt'] ?? $animal['name']) ?>" class="h-28 w-full object-cover" loading="lazy">
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <article class="mt-8 space-y-6">
                <div>
                    <h1 class="text-3xl font-semibold text-white sm:text-4xl">
                        <?= htmlspecialchars($animal['name']) ?>
                        <?php if (!empty($animal['is_piebald'])): ?>
                            <span class="ml-3 inline-flex items-center justify-center rounded-full border border-brand-400 bg-brand-500/20 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-brand-100">Gescheckt</span>
                        <?php endif; ?>
                    </h1>
                    <p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars($animal['species']) ?></p>
                </div>
                <dl class="grid gap-4 rounded-3xl border border-white/10 bg-night-900/60 p-6 text-sm text-slate-200 sm:grid-cols-2">
                    <?php if (!empty($animal['status'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Status</dt>
                            <dd><?= htmlspecialchars($animal['status']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($animal['price_amount'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Preis</dt>
                            <dd><?= htmlspecialchars(format_price_from_cents($animal['price_amount'])) ?></dd>
                        </div>
                    <?php elseif (!empty($animal['price'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Preis</dt>
                            <dd><?= htmlspecialchars($animal['price']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php $displayAge = format_partial_date($animal['age'] ?? null) ?? ($animal['age'] ?? null); ?>
                    <?php if (!empty($displayAge)): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Alter</dt>
                            <dd><?= htmlspecialchars($displayAge) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($animal['sex'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Geschlecht</dt>
                            <dd><?= htmlspecialchars($animal['sex']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($animal['origin'])): ?>
                        <div class="sm:col-span-2">
                            <dt class="font-semibold text-slate-100">Herkunft</dt>
                            <dd><?= htmlspecialchars($animal['origin']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($animal['sire_id']) || !empty($animal['dam_id'])): ?>
                        <?php if (!empty($animal['sire_id'])): ?>
                            <div>
                                <dt class="font-semibold text-slate-100">Vater</dt>
                                <dd><a href="<?= BASE_URL ?>/index.php?route=animal&amp;id=<?= (int)$animal['sire_id'] ?>" class="text-brand-200 hover:text-brand-100"><?= htmlspecialchars($animal['sire_name'] ?? 'Unbekannt') ?></a></dd>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($animal['dam_id'])): ?>
                            <div>
                                <dt class="font-semibold text-slate-100">Mutter</dt>
                                <dd><a href="<?= BASE_URL ?>/index.php?route=animal&amp;id=<?= (int)$animal['dam_id'] ?>" class="text-brand-200 hover:text-brand-100"><?= htmlspecialchars($animal['dam_name'] ?? 'Unbekannt') ?></a></dd>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </dl>
                <?php if (!empty($animal['genetics'])): ?>
                    <section>
                        <h2 class="text-xl font-semibold text-white">Genetik</h2>
                        <p class="mt-2 rounded-2xl border border-brand-400/40 bg-brand-500/10 px-4 py-3 text-sm text-brand-100"><?= nl2br(htmlspecialchars($animal['genetics'])) ?></p>
                    </section>
                <?php endif; ?>
                <?php if (!empty($animal['description'])): ?>
                    <section>
                        <h2 class="text-xl font-semibold text-white">Beschreibung</h2>
                        <div class="rich-text-content prose prose-invert mt-3 max-w-none text-slate-200">
                            <?= render_rich_text($animal['description']) ?>
                        </div>
                    </section>
                <?php endif; ?>
                <?php if (!empty($animal['special_notes'])): ?>
                    <section>
                        <h2 class="text-xl font-semibold text-white">Besonderheiten</h2>
                        <p class="mt-2 rounded-2xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100"><?= nl2br(htmlspecialchars($animal['special_notes'])) ?></p>
                    </section>
                <?php endif; ?>
                <?php if ($showAdminNotes && !empty($animal['admin_notes'])): ?>
                    <section class="rounded-3xl border border-rose-400/40 bg-rose-500/10 p-4 text-sm text-rose-100">
                        <h2 class="text-lg font-semibold">Interne Admin-Notizen</h2>
                        <p class="mt-2 whitespace-pre-line"><?= htmlspecialchars($animal['admin_notes']) ?></p>
                    </section>
                <?php endif; ?>
            </article>
        </div>
        <aside class="space-y-6">
            <div class="rounded-3xl border border-white/10 bg-night-900/60 p-6 text-sm text-slate-200">
                <h2 class="text-lg font-semibold text-white">Zusammenfassung</h2>
                <ul class="mt-4 space-y-2">
                    <?php if (!empty($animal['status'])): ?>
                        <li><span class="font-semibold text-slate-100">Status:</span> <?= htmlspecialchars($animal['status']) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($animal['price_amount'])): ?>
                        <li><span class="font-semibold text-slate-100">Preis:</span> <?= htmlspecialchars(format_price_from_cents($animal['price_amount'])) ?></li>
                    <?php elseif (!empty($animal['price'])): ?>
                        <li><span class="font-semibold text-slate-100">Preis:</span> <?= htmlspecialchars($animal['price']) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($animal['sex'])): ?>
                        <li><span class="font-semibold text-slate-100">Geschlecht:</span> <?= htmlspecialchars($animal['sex']) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($animal['origin'])): ?>
                        <li><span class="font-semibold text-slate-100">Herkunft:</span> <?= htmlspecialchars($animal['origin']) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php if (!empty($animal['media'])): ?>
                <div class="rounded-3xl border border-white/10 bg-night-900/60 p-6 text-sm text-slate-200">
                    <h2 class="text-lg font-semibold text-white">Galerie</h2>
                    <p class="mt-2 text-xs text-slate-400">Klicken Sie auf ein Bild, um es zu vergrößern.</p>
                    <div class="mt-4 grid gap-3">
                        <?php foreach ($animal['media'] as $mediaItem): ?>
                            <?php $thumb = $mediaItem['urls']['thumb'] ?? $mediaItem['urls']['medium'] ?? $mediaItem['urls']['original']; ?>
                            <?php if ($thumb): ?>
                                <button type="button" class="overflow-hidden rounded-2xl border border-white/10 focus:outline-none focus:ring-2 focus:ring-brand-400" data-lightbox-src="<?= htmlspecialchars($mediaItem['urls']['original'] ?? $thumb) ?>">
                                    <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($mediaItem['alt'] ?? $animal['name']) ?>" class="h-24 w-full object-cover" loading="lazy">
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
<div class="lightbox hidden" data-lightbox>
    <div class="lightbox-backdrop" data-lightbox-close></div>
    <div class="lightbox-content">
        <button type="button" class="lightbox-close" data-lightbox-close aria-label="Schließen">&times;</button>
        <img src="" alt="" data-lightbox-image>
    </div>
</div>
<script>
    const lightbox = document.querySelector('[data-lightbox]');
    if (lightbox) {
        const imageEl = lightbox.querySelector('[data-lightbox-image]');
        const closeEls = lightbox.querySelectorAll('[data-lightbox-close]');
        function openLightbox(src) {
            if (!src) return;
            imageEl.src = src;
            lightbox.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeLightbox() {
            lightbox.classList.add('hidden');
            imageEl.src = '';
            document.body.classList.remove('overflow-hidden');
        }
        document.querySelectorAll('[data-lightbox-src], [data-lightbox-trigger]').forEach(function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();
                const src = this.dataset.lightboxSrc || this.getAttribute('src');
                openLightbox(src);
            });
        });
        closeEls.forEach(function (button) {
            button.addEventListener('click', closeLightbox);
        });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) {
                closeLightbox();
            }
        });
    }
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
