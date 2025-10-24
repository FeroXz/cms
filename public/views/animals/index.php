<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <header class="max-w-3xl">
        <h1 class="text-3xl font-semibold text-white sm:text-4xl"><?= htmlspecialchars(content_value($settings, 'animals_title')) ?></h1>
        <p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars(content_value($settings, 'animals_intro')) ?></p>
    </header>
    <form method="get" class="mt-8 grid gap-4 rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-lg shadow-black/30">
        <input type="hidden" name="route" value="animals">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Art
                <select name="species" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="">Alle Arten</option>
                    <?php foreach ($filterOptions['species'] as $speciesOption): ?>
                        <option value="<?= htmlspecialchars($speciesOption['slug']) ?>" <?= ($filters['species'] ?? '') === $speciesOption['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($speciesOption['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Geschlecht
                <select name="sex" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="">Alle</option>
                    <?php foreach ($filterOptions['sexes'] as $sexOption): ?>
                        <option value="<?= htmlspecialchars($sexOption['value']) ?>" <?= ($filters['sex'] ?? '') === $sexOption['value'] ? 'selected' : '' ?>><?= htmlspecialchars($sexOption['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Status
                <select name="status" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="">Alle</option>
                    <?php foreach ($filterOptions['statuses'] as $statusOption): ?>
                        <option value="<?= htmlspecialchars($statusOption['value']) ?>" <?= ($filters['status'] ?? '') === $statusOption['value'] ? 'selected' : '' ?>><?= htmlspecialchars($statusOption['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Sortierung
                <select name="sort" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                    <option value="created_desc" <?= ($sort['key'] ?? '') === 'created_desc' ? 'selected' : '' ?>>Neueste zuerst</option>
                    <option value="created_asc" <?= ($sort['key'] ?? '') === 'created_asc' ? 'selected' : '' ?>>Älteste zuerst</option>
                    <option value="name_asc" <?= ($sort['key'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Name A–Z</option>
                    <option value="name_desc" <?= ($sort['key'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name Z–A</option>
                    <option value="price_asc" <?= ($sort['key'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Preis aufsteigend</option>
                    <option value="price_desc" <?= ($sort['key'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Preis absteigend</option>
                </select>
            </label>
            <label class="flex flex-col gap-1 text-sm font-medium text-slate-200">
                Genetik/Morph
                <input type="text" name="gene" value="<?= htmlspecialchars($filters['gene'] ?? '') ?>" placeholder="z. B. Albino" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
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
                    <a href="<?= BASE_URL ?>/index.php?route=animals" class="inline-flex items-center justify-center rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 transition hover:border-brand-400 hover:text-brand-100">Zurücksetzen</a>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($filterOptions['geneExamples'])): ?>
            <p class="text-xs text-slate-400">Beliebt: <?php foreach ($filterOptions['geneExamples'] as $index => $example): ?><?= $index > 0 ? ', ' : '' ?><button type="button" class="underline decoration-dashed decoration-brand-400 hover:text-brand-200" data-gene-suggestion="<?= htmlspecialchars($example) ?>"><?= htmlspecialchars($example) ?></button><?php endforeach; ?></p>
        <?php endif; ?>
    </form>

    <?php if (empty($animals)): ?>
        <p class="mt-10 rounded-3xl border border-white/10 bg-night-900/70 p-6 text-center text-sm text-slate-300">Keine Tiere gefunden. Passen Sie die Filter an oder schauen Sie später erneut vorbei.</p>
    <?php else: ?>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($animals as $animal): ?>
                <article class="flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/30 transition hover:border-brand-400/60 hover:shadow-glow">
                    <?php if (!empty($animal['image_path'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($animal['image_path']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>" class="mb-4 h-48 w-full rounded-2xl object-cover" loading="lazy">
                    <?php endif; ?>
                    <h3 class="text-xl font-semibold text-white">
                        <a href="<?= BASE_URL ?>/index.php?route=animal&amp;id=<?= (int)$animal['id'] ?>" class="hover:text-brand-200">
                            <?= htmlspecialchars($animal['name']) ?>
                        </a>
                        <?php if (!empty($animal['is_piebald'])): ?>
                            <span class="ml-2 inline-flex items-center justify-center rounded-full border border-brand-400 bg-brand-500/20 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-brand-100" title="Geschecktes Tier" aria-label="Geschecktes Tier">Gescheckt</span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-sm text-slate-300"><?= htmlspecialchars($animal['species']) ?></p>
                    <?php $displayAge = format_partial_date($animal['age'] ?? null) ?? ($animal['age'] ?? null); ?>
                    <?php if (!empty($displayAge)): ?>
                        <p class="mt-2 text-sm text-slate-200"><strong>Alter:</strong> <?= htmlspecialchars($displayAge) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($animal['price_amount'])): ?>
                        <p class="mt-2 text-sm text-slate-200"><strong>Preis:</strong> <?= htmlspecialchars(format_price_from_cents($animal['price_amount'])) ?></p>
                    <?php elseif (!empty($animal['price'])): ?>
                        <p class="mt-2 text-sm text-slate-200"><strong>Preis:</strong> <?= htmlspecialchars($animal['price']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($animal['genetics'])): ?>
                        <p class="mt-2 rounded-xl border border-brand-400/40 bg-brand-500/10 px-3 py-2 text-sm text-brand-100">
                            <span class="font-semibold uppercase tracking-wide">Genetik:</span>
                            <?= htmlspecialchars($animal['genetics']) ?>
                        </p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/index.php?route=animal&amp;id=<?= (int)$animal['id'] ?>" class="mt-4 inline-flex items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/20 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/30">Details ansehen</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
    document.querySelectorAll('[data-gene-suggestion]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.querySelector('input[name="gene"]');
            if (input) {
                input.value = this.dataset.geneSuggestion;
                input.form.requestSubmit();
            }
        });
    });
</script>
