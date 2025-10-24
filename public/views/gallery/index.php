<?php
$galleryPayload = [
    'baseUrl' => BASE_URL . '/index.php?route=gallery',
    'collections' => array_map(static function ($collection) {
        return [
            'id' => (int)$collection['id'],
            'name' => $collection['name'],
            'slug' => $collection['slug'],
            'description' => $collection['description'] ?? null,
        ];
    }, $collections),
    'selectedSlug' => $selectedCollection['slug'] ?? 'all',
    'items' => $mediaItems,
    'page' => (int)$page,
    'hasMore' => (bool)$hasMore,
    'meta' => [
        'title' => 'Galerie',
        'subtitle' => 'Einblicke in unsere Tiere, Terrarien und Projekte.',
    ],
];
?>
<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
    <div id="react-gallery-root" class="react-gallery" data-enhanced="true"></div>
    <noscript>
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-white sm:text-4xl">Galerie</h1>
                <p class="mt-2 text-sm text-slate-300">Einblicke in unsere Tiere, Terrarien und Projekte.</p>
            </div>
            <form method="get" class="flex items-center gap-3 text-sm">
                <input type="hidden" name="route" value="gallery">
                <label class="flex items-center gap-2 text-slate-200">
                    <span>Kategorie</span>
                    <select name="collection" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                        <option value="">Alle Bilder</option>
                        <?php foreach ($collections as $collection): ?>
                            <option value="<?= htmlspecialchars($collection['slug']) ?>" <?= (!empty($selectedCollection['slug']) && $selectedCollection['slug'] === $collection['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($collection['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>
        </header>
        <?php if (empty($mediaItems)): ?>
            <p class="mt-10 rounded-3xl border border-white/10 bg-night-900/70 p-6 text-center text-sm text-slate-300">Keine Medien in dieser Kategorie vorhanden.</p>
        <?php else: ?>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($mediaItems as $media): ?>
                    <?php $thumb = $media['urls']['medium'] ?? $media['urls']['original']; ?>
                    <?php if ($thumb): ?>
                        <figure class="overflow-hidden rounded-3xl border border-white/10 bg-night-900/60">
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($media['alt'] ?? 'Galeriebild') ?>" class="h-56 w-full object-cover" loading="lazy">
                            <figcaption class="px-4 py-3 text-xs text-slate-300">
                                <?= htmlspecialchars($media['alt'] ?? 'Ohne Beschreibung') ?>
                            </figcaption>
                        </figure>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </noscript>
</section>
<script>
    window.__GALLERY_DATA__ = <?= json_encode($galleryPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin="anonymous"></script>
<script src="<?= asset('gallery.js') ?>?v=<?= rawurlencode($settings['app_version'] ?? '3.7.1') ?>" defer></script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
