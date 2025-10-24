<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8">
    <a href="<?= BASE_URL ?>/index.php?route=wiki" class="inline-flex items-center gap-2 text-sm font-medium text-brand-200 hover:text-brand-100">&larr; Zurück zur Wissensbasis</a>
    <article class="mt-6 rounded-3xl border border-white/10 bg-night-900/70 p-8 shadow-lg shadow-black/30">
        <h1 class="text-3xl font-semibold text-white sm:text-4xl"><?= htmlspecialchars($article['title']) ?></h1>
        <?php if (!empty($article['summary'])): ?>
            <p class="mt-2 text-sm italic text-slate-300"><?= htmlspecialchars($article['summary']) ?></p>
        <?php endif; ?>
        <div class="rich-text-content prose prose-invert mt-6 max-w-none text-slate-100">
            <?= render_rich_text($article['content']) ?>
        </div>
        <?php if (!empty($mediaItems)): ?>
            <section class="mt-8">
                <h2 class="text-lg font-semibold text-white">Inline-Bilder</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <?php foreach ($mediaItems as $media): ?>
                        <?php $thumb = $media['urls']['medium'] ?? $media['urls']['original']; ?>
                        <?php if ($thumb): ?>
                            <figure class="overflow-hidden rounded-2xl border border-white/10">
                                <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($media['alt'] ?? $article['title']) ?>" class="w-full object-cover" loading="lazy">
                                <?php if (!empty($media['alt'])): ?>
                                    <figcaption class="bg-night-900/80 px-3 py-2 text-xs text-slate-300"><?= htmlspecialchars($media['alt']) ?></figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </article>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
