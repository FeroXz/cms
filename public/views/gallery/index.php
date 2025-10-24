<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
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
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-gallery-grid>
            <?php foreach ($mediaItems as $media): ?>
                <?php $thumb = $media['urls']['medium'] ?? $media['urls']['original']; ?>
                <?php if ($thumb): ?>
                    <figure class="group overflow-hidden rounded-3xl border border-white/10 bg-night-900/60">
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($media['alt'] ?? 'Galeriebild') ?>" class="h-56 w-full object-cover transition group-hover:scale-105" loading="lazy">
                        <figcaption class="px-4 py-3 text-xs text-slate-300">
                            <?= htmlspecialchars($media['alt'] ?? 'Ohne Beschreibung') ?>
                        </figcaption>
                    </figure>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php if ($hasMore): ?>
            <div class="mt-8 text-center">
                <button type="button" class="inline-flex items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/20 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/30" data-gallery-load-more>Mehr laden</button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<script>
    (function () {
        const loadMoreButton = document.querySelector('[data-gallery-load-more]');
        if (!loadMoreButton) return;
        const grid = document.querySelector('[data-gallery-grid]');
        let currentPage = <?= (int)$page ?>;
        const baseUrl = new URL(window.location.href);
        loadMoreButton.addEventListener('click', async function () {
            loadMoreButton.disabled = true;
            loadMoreButton.textContent = 'Laden…';
            const params = new URLSearchParams(baseUrl.search);
            params.set('page', String(currentPage + 1));
            params.set('format', 'json');
            try {
                const response = await fetch(`${baseUrl.pathname}?${params.toString()}`);
                if (!response.ok) throw new Error('Netzwerkfehler');
                const payload = await response.json();
                if (Array.isArray(payload.items)) {
                    payload.items.forEach(function (media) {
                        const thumb = media.urls.medium || media.urls.original;
                        if (!thumb) return;
                        const figure = document.createElement('figure');
                        figure.className = 'group overflow-hidden rounded-3xl border border-white/10 bg-night-900/60';
                        figure.innerHTML = `<img src="${thumb}" alt="${(media.alt || 'Galeriebild').replace(/"/g, '&quot;')}" class="h-56 w-full object-cover transition group-hover:scale-105" loading="lazy"><figcaption class="px-4 py-3 text-xs text-slate-300">${media.alt || 'Ohne Beschreibung'}</figcaption>`;
                        grid.appendChild(figure);
                    });
                    currentPage = payload.page || currentPage + 1;
                    if (!payload.hasMore) {
                        loadMoreButton.remove();
                    } else {
                        loadMoreButton.disabled = false;
                        loadMoreButton.textContent = 'Mehr laden';
                    }
                }
            } catch (error) {
                loadMoreButton.disabled = false;
                loadMoreButton.textContent = 'Erneut versuchen';
            }
        });
    })();
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
