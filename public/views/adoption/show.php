<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8">
    <a href="<?= BASE_URL ?>/index.php?route=adoption" class="inline-flex items-center gap-2 text-sm font-medium text-brand-200 hover:text-brand-100">&larr; Zurück zur Abgabeliste</a>
    <article class="mt-6 grid gap-10 lg:grid-cols-[2fr,1fr]">
        <div>
            <?php if (!empty($listing['media'])): ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <?php foreach ($listing['media'] as $mediaItem): ?>
                        <?php $src = $mediaItem['urls']['medium'] ?? $mediaItem['urls']['original']; ?>
                        <?php if ($src): ?>
                            <button type="button" class="overflow-hidden rounded-3xl border border-white/10" data-lightbox-src="<?= htmlspecialchars($mediaItem['urls']['original'] ?? $src) ?>">
                                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($mediaItem['alt'] ?? $listing['title']) ?>" class="w-full object-cover" loading="lazy">
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($listing['image_path'])): ?>
                <button type="button" class="overflow-hidden rounded-3xl border border-white/10" data-lightbox-src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>">
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" class="w-full object-cover" loading="lazy">
                </button>
            <?php endif; ?>
            <div class="mt-6 space-y-6">
                <header>
                    <h1 class="text-3xl font-semibold text-white sm:text-4xl"><?= htmlspecialchars($listing['title']) ?></h1>
                    <?php if (!empty($listing['species'])): ?>
                        <p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars($listing['species']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($listing['status'])): ?>
                        <span class="mt-3 inline-flex items-center rounded-full border border-white/10 bg-white/10 px-4 py-1 text-xs uppercase tracking-wide text-slate-200">Status: <?= htmlspecialchars($listing['status']) ?></span>
                    <?php endif; ?>
                </header>
                <dl class="grid gap-4 rounded-3xl border border-white/10 bg-night-900/60 p-6 text-sm text-slate-200 sm:grid-cols-2">
                    <?php if (!empty($listing['price_amount'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Preis</dt>
                            <dd><?= htmlspecialchars(format_price_from_cents((int)$listing['price_amount'])) ?></dd>
                        </div>
                    <?php elseif (!empty($listing['price'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Preis</dt>
                            <dd><?= htmlspecialchars($listing['price']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($listing['gender'])): ?>
                        <div>
                            <dt class="font-semibold text-slate-100">Geschlecht</dt>
                            <dd><?= htmlspecialchars($listing['gender']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($listing['genetics'])): ?>
                        <div class="sm:col-span-2">
                            <dt class="font-semibold text-slate-100">Genetik</dt>
                            <dd><?= nl2br(htmlspecialchars($listing['genetics'])) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($listing['description'])): ?>
                        <div class="sm:col-span-2">
                            <dt class="font-semibold text-slate-100">Beschreibung</dt>
                            <dd class="rich-text-content prose prose-invert mt-2 max-w-none text-slate-200"><?= render_rich_text($listing['description']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
                <?php if (!empty($listing['animal'])): ?>
                    <section class="rounded-3xl border border-white/10 bg-night-900/60 p-6 text-sm text-slate-200">
                        <h2 class="text-lg font-semibold text-white">Tierprofil</h2>
                        <p class="mt-2 text-sm">Basisdaten zum zugehörigen Tier:</p>
                        <ul class="mt-3 space-y-2">
                            <li><span class="font-semibold text-slate-100">Name:</span> <?= htmlspecialchars($listing['animal']['name']) ?></li>
                            <?php if (!empty($listing['animal']['species'])): ?>
                                <li><span class="font-semibold text-slate-100">Art:</span> <?= htmlspecialchars($listing['animal']['species']) ?></li>
                            <?php endif; ?>
                            <li><a href="<?= BASE_URL ?>/index.php?route=animal&amp;id=<?= (int)$listing['animal']['id'] ?>" class="inline-flex items-center text-brand-200 hover:text-brand-100">Zum Tierprofil &rarr;</a></li>
                        </ul>
                    </section>
                <?php endif; ?>
            </div>
        </div>
        <aside class="space-y-6">
            <?php if ($flashSuccess): ?>
                <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>
            <form method="post" class="rounded-3xl border border-brand-400/30 bg-brand-500/5 p-6 text-sm text-slate-100 shadow-inner shadow-brand-600/10">
                <?= csrf_field() ?>
                <input type="hidden" name="listing_id" value="<?= (int)$listing['id'] ?>">
                <h2 class="text-lg font-semibold text-white">Anfrage senden</h2>
                <p class="mt-1 text-xs text-slate-300">Bitte beschreiben Sie Ihre Haltung und Erfahrung so detailliert wie möglich.</p>
                <label class="mt-4 flex flex-col gap-1">
                    <span class="font-medium text-slate-200">Interessiert an</span>
                    <input type="text" name="interested_in" value="<?= htmlspecialchars($listing['title']) ?>" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                </label>
                <label class="mt-4 flex flex-col gap-1">
                    <span class="font-medium text-slate-200">Name</span>
                    <input type="text" name="name" required class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                </label>
                <label class="mt-4 flex flex-col gap-1">
                    <span class="font-medium text-slate-200">E-Mail</span>
                    <input type="email" name="email" required class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                </label>
                <label class="mt-4 flex flex-col gap-1">
                    <span class="font-medium text-slate-200">Nachricht</span>
                    <textarea name="message" required rows="6" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40" placeholder="Beschreiben Sie Haltung, Erfahrung und konkrete Fragen."></textarea>
                </label>
                <button type="submit" class="mt-6 inline-flex w-full items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/20 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/30">Anfrage absenden</button>
            </form>
        </aside>
    </article>
</section>
<div class="lightbox hidden" data-lightbox>
    <div class="lightbox-backdrop" data-lightbox-close></div>
    <div class="lightbox-content">
        <button type="button" class="lightbox-close" data-lightbox-close aria-label="Schließen">&times;</button>
        <img src="" alt="" data-lightbox-image>
    </div>
</div>
<script>
    const adoptionLightbox = document.querySelector('[data-lightbox]');
    if (adoptionLightbox) {
        const imageEl = adoptionLightbox.querySelector('[data-lightbox-image]');
        function openLightbox(src) {
            if (!src) return;
            imageEl.src = src;
            adoptionLightbox.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeLightbox() {
            adoptionLightbox.classList.add('hidden');
            imageEl.src = '';
            document.body.classList.remove('overflow-hidden');
        }
        adoptionLightbox.addEventListener('click', function (event) {
            if (event.target === adoptionLightbox || event.target.matches('[data-lightbox-close]')) {
                closeLightbox();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !adoptionLightbox.classList.contains('hidden')) {
                closeLightbox();
            }
        });
        document.querySelectorAll('[data-lightbox-src]').forEach(function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();
                openLightbox(this.dataset.lightboxSrc);
            });
        });
    }
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
