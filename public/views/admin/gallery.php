<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-white sm:text-4xl">Galerie verwalten</h1>
    <?php include __DIR__ . '/nav.php'; ?>
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success mt-6" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-error mt-6" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="admin-two-column mt-6 gap-6">
        <div class="card space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-white">Sammlungen</h2>
                <span class="text-xs uppercase tracking-wide text-slate-400">Medienpool: <?= (int)$libraryCount ?> Bilder</span>
            </div>
            <?php if (empty($collections)): ?>
                <p class="text-sm text-slate-300">Noch keine Sammlungen angelegt. Erstelle rechts deine erste Galerie.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($collections as $collection): ?>
                        <?php $isActive = $activeCollection && (int)$activeCollection['id'] === (int)$collection['id']; ?>
                        <li class="rounded-2xl border border-white/5 bg-night-900/80 p-4 shadow-sm shadow-brand-900/20">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <a href="<?= BASE_URL ?>/index.php?route=admin/gallery&amp;collection=<?= (int)$collection['id'] ?>" class="text-base font-semibold <?= $isActive ? 'text-brand-300' : 'text-slate-100 hover:text-brand-200' ?>"><?= htmlspecialchars($collection['name']) ?></a>
                                    <?php if (!empty($collection['description'])): ?>
                                        <p class="mt-1 text-sm text-slate-400"><?= nl2br(htmlspecialchars($collection['description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <span class="rounded-full bg-white/5 px-3 py-1 font-semibold text-slate-200"><?= (int)($collectionCounts[(int)$collection['id']] ?? 0) ?> Bilder</span>
                                    <form method="post" onsubmit="return confirm('Sammlung wirklich löschen? Zugeordnete Bilder werden in den Medienpool verschoben.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_collection">
                                        <input type="hidden" name="collection_id" value="<?= (int)$collection['id'] ?>">
                                        <button type="submit" class="btn-link text-red-300 hover:text-red-200">Löschen</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="card">
            <h2 class="text-lg font-semibold text-white">Neue Sammlung</h2>
            <form method="post" class="mt-4 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_collection">
                <label class="block text-sm font-medium text-slate-200">
                    <span>Name</span>
                    <input type="text" name="name" required class="mt-1 w-full rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                </label>
                <label class="block text-sm font-medium text-slate-200">
                    <span>Beschreibung (optional)</span>
                    <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40"></textarea>
                </label>
                <button type="submit" class="btn">Sammlung erstellen</button>
            </form>
        </div>
    </div>

    <?php if ($activeCollection): ?>
        <?php $uploadToken = csrf_token(); ?>
        <div class="card mt-8 space-y-6" data-gallery-admin data-library-url="<?= BASE_URL ?>/index.php?route=api/media/library" data-assign-url="<?= BASE_URL ?>/index.php?route=api/media/assign" data-owner-type="gallery" data-owner-id="<?= (int)$activeCollection['id'] ?>" data-csrf="<?= htmlspecialchars($uploadToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Sammlung „<?= htmlspecialchars($activeCollection['name']) ?>“</h2>
                    <p class="text-sm text-slate-400">Pflege Beschreibungen, Uploads und Reihenfolge für die öffentliche Galerie.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form method="post" class="flex flex-col gap-2 text-sm text-slate-200">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_collection">
                        <input type="hidden" name="collection_id" value="<?= (int)$activeCollection['id'] ?>">
                        <label>
                            <span class="font-medium">Name</span>
                            <input type="text" name="name" value="<?= htmlspecialchars($activeCollection['name']) ?>" required class="mt-1 w-full rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40">
                        </label>
                        <label>
                            <span class="font-medium">Beschreibung</span>
                            <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-night-900/60 px-3 py-2 text-slate-100 focus:border-brand-400 focus:outline-none focus:ring focus:ring-brand-500/40"><?= htmlspecialchars($activeCollection['description'] ?? '') ?></textarea>
                        </label>
                        <button type="submit" class="btn-secondary">Metadaten aktualisieren</button>
                    </form>
                </div>
            </header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-300">
                    <strong data-gallery-count><?= (int)($collectionCounts[(int)$activeCollection['id']] ?? 0) ?></strong> Bilder in dieser Sammlung &middot; Medienpool aktuell <strong data-gallery-library-count><?= (int)$libraryCount ?></strong> Bilder
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn" data-gallery-open-library>
                        Aus Medienpool hinzufügen
                    </button>
                    <button type="button" class="btn-secondary" data-action="choose-files">
                        Neue Bilder hochladen
                    </button>
                </div>
            </div>
            <?php
                $componentTitle = 'Galerie-Medien';
                $ownerType = 'gallery';
                $ownerId = (int)$activeCollection['id'];
                $mediaItems = $collectionMedia;
                $uploadToken = $uploadToken;
                include __DIR__ . '/partials/media_manager.php';
            ?>
            <div class="gallery-library hidden" data-gallery-library>
                <div class="gallery-library__backdrop" data-gallery-close></div>
                <div class="gallery-library__panel" role="dialog" aria-modal="true" aria-label="Medienpool">
                    <header class="gallery-library__header">
                        <h3>Medienpool</h3>
                        <button type="button" class="btn-link" data-gallery-close aria-label="Fenster schließen">×</button>
                    </header>
                    <div class="gallery-library__controls">
                        <label class="sr-only" for="gallery-library-search">Medien durchsuchen</label>
                        <input id="gallery-library-search" type="search" placeholder="Medien suchen…" data-gallery-search>
                    </div>
                    <div class="gallery-library__body">
                        <div class="gallery-library__grid" data-gallery-grid></div>
                        <p class="gallery-library__empty" data-gallery-empty>Keine Medien im Pool gefunden.</p>
                        <button type="button" class="gallery-library__more" data-gallery-more hidden>Mehr laden</button>
                    </div>
                    <footer class="gallery-library__footer">
                        <span class="gallery-library__status" data-gallery-status>0 ausgewählt</span>
                        <div class="gallery-library__actions">
                            <button type="button" class="btn-secondary" data-gallery-close>Abbrechen</button>
                            <button type="button" class="btn" data-gallery-attach disabled>Zur Sammlung hinzufügen</button>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="mt-8 rounded-3xl border border-dashed border-white/10 bg-night-900/50 p-10 text-center text-sm text-slate-300">
            Wähle links eine Sammlung aus oder erstelle eine neue, um Bilder zu verwalten.
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
