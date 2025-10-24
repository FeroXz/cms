<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <header class="max-w-3xl">
        <h1 class="text-3xl font-semibold text-white sm:text-4xl"><?= htmlspecialchars(content_value($settings, 'adoption_title')) ?></h1>
        <p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars(content_value($settings, 'adoption_intro_text')) ?></p>
    </header>
    <div class="rich-text-content prose prose-invert mt-6 max-w-none text-slate-200">
        <?= render_rich_text($settings['adoption_intro'] ?? '') ?>
    </div>
    <?php if ($flashSuccess): ?>
        <div class="mt-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200" role="status" aria-live="polite">
            <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="mt-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200" role="alert" aria-live="assertive">
            <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>
    <form method="get" class="mt-8 grid gap-4 rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-lg shadow-black/30">
        <input type="hidden" name="route" value="adoption">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Art
                <select name="species" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="">Alle Arten</option>
                    <?php foreach ($filterOptions['species'] as $option): ?>
                        <option value="<?= htmlspecialchars($option['slug']) ?>" <?= ($filters['species'] ?? '') === $option['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($option['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Status
                <select name="status" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="">Alle</option>
                    <?php foreach ($filterOptions['statuses'] as $status): ?>
                        <option value="<?= htmlspecialchars($status['value']) ?>" <?= ($filters['status'] ?? '') === $status['value'] ? 'selected' : '' ?>><?= htmlspecialchars($status['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Geschlecht
                <select name="gender" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="">Alle</option>
                    <?php foreach ($filterOptions['genders'] as $gender): ?>
                        <option value="<?= htmlspecialchars($gender['value']) ?>" <?= ($filters['gender'] ?? '') === $gender['value'] ? 'selected' : '' ?>><?= htmlspecialchars($gender['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Genetik/Morph
                <input type="text" name="gene" value="<?= htmlspecialchars($filters['gene'] ?? '') ?>" placeholder="z. B. Hypo" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Preis von
                <input type="text" name="price_min" value="<?= htmlspecialchars($filters['price_min'] ? number_format($filters['price_min'] / 100, 0, ',', '.') : '') ?>" placeholder="EUR" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Preis bis
                <input type="text" name="price_max" value="<?= htmlspecialchars($filters['price_max'] ? number_format($filters['price_max'] / 100, 0, ',', '.') : '') ?>" placeholder="EUR" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
            </label>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/20 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/30">Filtern</button>
                <?php if (!empty($activeFilterCount)): ?>
                    <a href="<?= BASE_URL ?>/index.php?route=adoption" class="inline-flex items-center justify-center rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 transition hover:border-brand-400 hover:text-brand-100">Zurücksetzen</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <?php if (empty($listings)): ?>
        <p class="mt-10 rounded-3xl border border-white/10 bg-night-900/70 p-6 text-center text-sm text-slate-300">Zurzeit sind keine passenden Abgabetiere verfügbar.</p>
    <?php else: ?>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($listings as $listing): ?>
                <article class="flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/30 transition hover:border-brand-400/60 hover:shadow-glow">
                    <?php if (!empty($listing['image_path'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" class="mb-4 h-48 w-full rounded-2xl object-cover" loading="lazy">
                    <?php endif; ?>
                    <h3 class="text-xl font-semibold text-white">
                        <a href="<?= BASE_URL ?>/index.php?route=adoption&amp;listing=<?= (int)$listing['id'] ?>" class="hover:text-brand-200">
                            <?= htmlspecialchars($listing['title']) ?>
                        </a>
                    </h3>
                    <?php if (!empty($listing['species'])): ?>
                        <p class="text-sm text-slate-300"><?= htmlspecialchars($listing['species']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($listing['status'])): ?>
                        <p class="mt-2 inline-flex w-fit items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs uppercase tracking-wide text-slate-200">Status: <?= htmlspecialchars($listing['status']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($listing['price_amount'])): ?>
                        <p class="mt-2 text-sm text-slate-200"><strong>Preis:</strong> <?= htmlspecialchars(format_price_from_cents((int)$listing['price_amount'])) ?></p>
                    <?php elseif (!empty($listing['price'])): ?>
                        <p class="mt-2 text-sm text-slate-200"><strong>Preis:</strong> <?= htmlspecialchars($listing['price']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($listing['genetics'])): ?>
                        <p class="mt-2 rounded-xl border border-brand-400/40 bg-brand-500/10 px-3 py-2 text-sm text-brand-100">
                            <span class="font-semibold uppercase tracking-wide">Genetik:</span>
                            <?= htmlspecialchars($listing['genetics']) ?>
                        </p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/index.php?route=adoption&amp;listing=<?= (int)$listing['id'] ?>" class="mt-4 inline-flex items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/20 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/30">Details &amp; Anfrage</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
