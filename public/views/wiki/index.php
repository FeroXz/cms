<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
    <header class="max-w-3xl">
        <h1 class="text-3xl font-semibold text-white sm:text-4xl">Wissensbasis</h1>
        <p class="mt-2 text-sm text-slate-300">Pflegehinweise, Genetik-Infos und Praxiswissen rund um unsere Reptilienarten.</p>
    </header>
    <?php if (empty($articles)): ?>
        <p class="mt-10 rounded-3xl border border-white/10 bg-night-900/70 p-6 text-center text-sm text-slate-300">Noch keine Wiki-Artikel veröffentlicht.</p>
    <?php else: ?>
        <div class="mt-10 grid gap-6 sm:grid-cols-2">
            <?php foreach ($articles as $article): ?>
                <article class="flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/30 transition hover:border-brand-400/60 hover:shadow-glow">
                    <h2 class="text-xl font-semibold text-white">
                        <a href="<?= BASE_URL ?>/index.php?route=wiki&amp;slug=<?= urlencode($article['slug']) ?>" class="hover:text-brand-200">
                            <?= htmlspecialchars($article['title']) ?>
                        </a>
                    </h2>
                    <?php if (!empty($article['summary'])): ?>
                        <p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars($article['summary']) ?></p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/index.php?route=wiki&amp;slug=<?= urlencode($article['slug']) ?>" class="mt-4 inline-flex items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/20 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/30">Artikel lesen</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
