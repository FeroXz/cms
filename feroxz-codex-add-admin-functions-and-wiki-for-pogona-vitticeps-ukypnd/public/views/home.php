<?php include __DIR__ . '/partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="grid gap-10 rounded-3xl border border-white/5 bg-night-900/70 p-8 shadow-glow shadow-brand-600/20 lg:grid-cols-2">
        <div class="space-y-6">
            <h1 class="text-3xl font-semibold text-white sm:text-4xl lg:text-5xl"><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></h1>
            <div class="rich-text-content prose prose-invert max-w-none">
                <?= render_rich_text($settings['hero_intro'] ?? '') ?>
            </div>
        </div>
        <div class="space-y-6">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-400/60 bg-brand-500/10 px-4 py-2 text-sm font-semibold uppercase tracking-wide text-brand-100">Pflegeleitfaden</span>
            <p class="text-base leading-relaxed text-slate-300">
                Unsere Leitfäden decken Beleuchtung, Ernährung, Habitatgestaltung und Gesundheitsvorsorge für
                <strong>Pogona vitticeps</strong> und <strong>Heterodon nasicus</strong> ab. Registrierte Benutzer erhalten
                Zugriff auf individuelle Tierakten inklusive Genetik und Besonderheiten.
            </p>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="flex items-center justify-between rounded-2xl border border-brand-400/50 bg-brand-500/10 px-4 py-3 text-sm font-semibold text-brand-100 shadow-glow transition hover:border-brand-300 hover:bg-brand-500/20">Pflegewissen entdecken <span aria-hidden="true">→</span></a>
                <a href="<?= BASE_URL ?>/index.php?route=genetics" class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-slate-100 transition hover:border-brand-300 hover:text-brand-100">Genetik-Rechner starten <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($animals)): ?>
<section class="mx-auto mt-16 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-white sm:text-3xl">Unsere Highlights</h2>
        <span class="text-sm text-slate-400">Ausgewählte Tiere aus dem Bestand</span>
    </div>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($animals as $animal): ?>
            <article class="group flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 shadow-xl shadow-black/40 transition hover:border-brand-400/60 hover:shadow-glow">
                <?php if (!empty($animal['image_path'])): ?>
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($animal['image_path']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>" class="h-52 w-full rounded-t-3xl object-cover" loading="lazy">
                <?php endif; ?>
                <div class="flex flex-1 flex-col gap-3 p-6">
                    <h3 class="text-xl font-semibold text-white">
                        <?= htmlspecialchars($animal['name']) ?>
                        <?php if (!empty($animal['is_piebald'])): ?>
                            <span class="ml-2 inline-flex items-center justify-center rounded-full border border-brand-400 bg-brand-500/20 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-brand-100" title="Geschecktes Tier" aria-label="Geschecktes Tier">Gescheckt</span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-sm text-slate-300"><?= htmlspecialchars($animal['species']) ?></p>
                    <?php if (!empty($animal['genetics'])): ?>
                        <div class="rounded-2xl border border-brand-400/30 bg-brand-500/5 px-3 py-2 text-sm text-brand-100">
                            <span class="font-semibold uppercase tracking-wide">Genetik:</span>
                            <?= htmlspecialchars($animal['genetics']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($animal['special_notes'])): ?>
                        <div class="rich-text-content prose prose-invert max-w-none text-sm text-slate-200">
                            <?= render_rich_text($animal['special_notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="mx-auto mt-16 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-white sm:text-3xl">Tiervermittlung</h2>
            <p class="text-sm text-slate-400">Aktuelle Vermittlungstiere und Kontakte</p>
        </div>
        <?php if (!empty($settings['contact_email'])): ?>
            <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>" class="inline-flex items-center gap-2 rounded-full border border-brand-400/60 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-100 shadow-glow transition hover:border-brand-300 hover:bg-brand-500/20">
                Kontakt aufnehmen
            </a>
        <?php endif; ?>
    </div>
    <div class="rich-text-content prose prose-invert mt-6 max-w-none text-slate-200">
        <?= render_rich_text($settings['adoption_intro'] ?? '') ?>
    </div>
    <div class="mt-10 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($listings as $listing): ?>
            <article class="flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/40 transition hover:border-brand-400/60 hover:shadow-glow">
                <?php if (!empty($listing['image_path'])): ?>
                    <img src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" class="mb-4 h-48 w-full rounded-2xl object-cover" loading="lazy">
                <?php endif; ?>
                <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($listing['title']) ?></h3>
                <?php if (!empty($listing['species'])): ?>
                    <p class="text-sm text-slate-300"><?= htmlspecialchars($listing['species']) ?></p>
                <?php endif; ?>
                <?php if (!empty($listing['genetics'])): ?>
                    <p class="mt-2 rounded-xl border border-brand-400/40 bg-brand-500/10 px-3 py-2 text-sm text-brand-100">
                        <span class="font-semibold uppercase tracking-wide">Genetik:</span>
                        <?= htmlspecialchars($listing['genetics']) ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($listing['price'])): ?>
                    <p class="mt-2 text-sm text-slate-200"><strong>Preis:</strong> <?= htmlspecialchars($listing['price']) ?></p>
                <?php endif; ?>
                <?php if (!empty($listing['description'])): ?>
                    <div class="rich-text-content prose prose-invert mt-3 max-w-none text-sm text-slate-200">
                        <?= render_rich_text($listing['description']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($settings['contact_email'])): ?>
                    <a class="mt-4 inline-flex items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-100 shadow-glow transition hover:border-brand-300 hover:bg-brand-500/20" href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>?subject=Anfrage%20<?= urlencode($listing['title']) ?>">Direkt anfragen</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php if (!empty($latestNews)): ?>
<section class="mx-auto mt-16 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-white sm:text-3xl">Neuigkeiten</h2>
            <p class="text-sm text-slate-400">Aktuelle Meldungen aus Verein und Bestand</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-brand-400 hover:text-brand-100" href="<?= BASE_URL ?>/index.php?route=news">Alle Meldungen anzeigen</a>
    </div>
    <div class="mt-8 grid gap-6 md:grid-cols-3">
        <?php foreach ($latestNews as $post): ?>
            <article class="flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/40 transition hover:border-brand-400/60 hover:shadow-glow">
                <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($post['title']) ?></h3>
                <?php if (!empty($post['published_at'])): ?>
                    <p class="text-sm text-slate-400"><?= date('d.m.Y', strtotime($post['published_at'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($post['excerpt'])): ?>
                    <p class="mt-3 text-sm text-slate-200"><?= nl2br(htmlspecialchars($post['excerpt'])) ?></p>
                <?php endif; ?>
                <a class="mt-4 inline-flex items-center gap-2 rounded-full border border-brand-400/60 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-100 transition hover:border-brand-300 hover:bg-brand-500/20" href="<?= BASE_URL ?>/index.php?route=news&amp;slug=<?= urlencode($post['slug']) ?>">Details ansehen</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($careHighlights)): ?>
<section class="mx-auto mt-16 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-white sm:text-3xl">Pflegewissen</h2>
            <p class="text-sm text-slate-400">Vertiefende Artikel für verantwortungsvolle Haltung</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-full border border-brand-400/60 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-100 shadow-glow transition hover:border-brand-300 hover:bg-brand-500/20" href="<?= BASE_URL ?>/index.php?route=care-guide">Zur Wissenssammlung</a>
    </div>
    <div class="mt-8 grid gap-6 md:grid-cols-3">
        <?php foreach ($careHighlights as $article): ?>
            <article class="flex h-full flex-col rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/40 transition hover:border-brand-400/60 hover:shadow-glow">
                <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($article['title']) ?></h3>
                <?php if (!empty($article['summary'])): ?>
                    <p class="mt-3 text-sm text-slate-200"><?= nl2br(htmlspecialchars($article['summary'])) ?></p>
                <?php endif; ?>
                <a class="mt-auto inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-brand-400 hover:text-brand-100" href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($article['slug']) ?>">Leitfaden öffnen</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
