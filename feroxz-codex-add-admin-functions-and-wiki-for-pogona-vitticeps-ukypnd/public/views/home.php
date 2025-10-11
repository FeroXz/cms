<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$showHero = setting_enabled($settings, 'home_show_hero');
$showAnimals = setting_enabled($settings, 'home_show_animals');
$showAdoption = setting_enabled($settings, 'home_show_adoption');
$showNews = setting_enabled($settings, 'home_show_news');
$showCare = setting_enabled($settings, 'home_show_care');

$adoptionIntro = trim(strip_tags((string)($settings['adoption_intro'] ?? '')));
$hasAdoptionListings = !empty($listings);
$adoptionHasContent = $showAdoption && ($hasAdoptionListings || $adoptionIntro !== '' || !empty($settings['contact_email']));
$newsHasContent = $showNews && !empty($latestNews);
$careHasContent = $showCare && !empty($careHighlights);
?>
<?php if ($showHero): ?>
<section class="relative mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2.5rem] border border-white/10 bg-gradient-to-br from-night-900 via-night-800 to-slate-900 p-10 shadow-2xl shadow-brand-900/30 lg:p-16">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center">
            <div class="space-y-8">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-500/15 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-brand-100"><?= htmlspecialchars(content_value($settings, 'home_hero_badge')) ?></span>
                <h1 class="text-3xl font-semibold text-white sm:text-4xl lg:text-5xl"><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></h1>
                <div class="rich-text-content prose prose-invert max-w-none text-base leading-relaxed text-slate-200">
                    <?= render_rich_text($settings['hero_intro'] ?? '') ?>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="<?= BASE_URL ?>/index.php?route=genetics" class="flex items-center justify-between rounded-3xl border border-brand-400/70 bg-brand-500/15 px-5 py-4 text-sm font-semibold text-brand-50 shadow-glow transition hover:border-brand-200 hover:bg-brand-500/25">
                        <?= htmlspecialchars(content_value($settings, 'home_care_secondary_cta')) ?>
                        <span aria-hidden="true">→</span>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="flex items-center justify-between rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-sm font-semibold text-slate-100 transition hover:border-brand-300 hover:text-brand-100">
                        <?= htmlspecialchars(content_value($settings, 'home_care_primary_cta')) ?>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
            <div class="rounded-3xl border border-white/5 bg-night-900/70 p-8 shadow-xl shadow-black/40">
                <h2 class="text-lg font-semibold text-white">Highlights & Werte</h2>
                <p class="mt-2 text-sm text-slate-300"><?= htmlspecialchars(content_value($settings, 'home_highlights_subtitle')) ?></p>
                <dl class="mt-6 grid gap-4 text-sm text-slate-100 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Aktive Tiere</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white"><?= count($animals) ?></dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Adoptionen</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white"><?= count($listings) ?></dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Neuigkeiten</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white"><?= count($latestNews) ?></dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Pflegeartikel</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white"><?= count($careHighlights) ?></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($showAnimals && !empty($animals)): ?>
<section class="mx-auto mt-20 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-white sm:text-3xl"><?= htmlspecialchars(content_value($settings, 'home_highlights_title')) ?></h2>
            <p class="text-sm text-slate-400">Kuratiert aus unserem Bestand</p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?route=animals" class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-brand-400 hover:text-brand-100">Alle Tiere ansehen</a>
    </div>
    <div class="mt-10 grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($animals as $animal): ?>
            <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-white/10 bg-night-900/70 shadow-xl shadow-black/40 transition hover:border-brand-400/70 hover:shadow-glow">
                <?php if (!empty($animal['image_path'])): ?>
                    <div class="relative h-56 w-full overflow-hidden">
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($animal['image_path']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-night-900/80 via-night-900/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 flex flex-col">
                            <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($animal['name']) ?></h3>
                            <p class="text-xs uppercase tracking-wide text-slate-300"><?= htmlspecialchars($animal['species']) ?></p>
                        </div>
                        <?php if (!empty($animal['is_piebald'])): ?>
                            <span class="absolute right-4 top-4 inline-flex items-center rounded-full bg-brand-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-brand-100" title="Geschecktes Tier">Gescheckt</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="flex flex-1 flex-col gap-4 p-6">
                    <?php if (!empty($animal['genetics'])): ?>
                        <div class="rounded-2xl border border-brand-400/30 bg-brand-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-brand-100">Genetik: <?= htmlspecialchars($animal['genetics']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($animal['special_notes'])): ?>
                        <div class="rich-text-content prose prose-invert max-w-none text-sm leading-relaxed text-slate-200">
                            <?= render_rich_text($animal['special_notes']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if ($adoptionHasContent || $newsHasContent || $careHasContent): ?>
<section class="mx-auto mt-20 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <?php
        $sectionClass = 'grid gap-10';
        if ($adoptionHasContent && ($newsHasContent || $careHasContent)) {
            $sectionClass .= ' lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] lg:items-start';
        }
    ?>
    <div class="<?= $sectionClass ?>">
        <?php if ($adoptionHasContent): ?>
            <div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-white sm:text-3xl"><?= htmlspecialchars(content_value($settings, 'home_adoption_title')) ?></h2>
                        <p class="text-sm text-slate-400"><?= htmlspecialchars(content_value($settings, 'home_adoption_subtitle')) ?></p>
                    </div>
                    <?php if (!empty($settings['contact_email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>" class="inline-flex items-center gap-2 rounded-full border border-brand-400/60 bg-brand-500/15 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-brand-100 shadow-glow transition hover:border-brand-200 hover:bg-brand-500/25">Kontakt aufnehmen</a>
                    <?php endif; ?>
                </div>
                <div class="rich-text-content prose prose-invert mt-6 max-w-none text-slate-200">
                    <?= render_rich_text($settings['adoption_intro'] ?? '') ?>
                </div>
                <?php if ($hasAdoptionListings): ?>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2">
                        <?php foreach ($listings as $listing): ?>
                            <article class="flex h-full flex-col rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-xl shadow-black/40 transition hover:border-brand-400/70 hover:shadow-glow">
                                <?php if (!empty($listing['image_path'])): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" class="mb-4 h-48 w-full rounded-2xl object-cover" loading="lazy">
                                <?php endif; ?>
                                <div class="flex flex-1 flex-col gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($listing['title']) ?></h3>
                                        <?php if (!empty($listing['species'])): ?>
                                            <p class="text-xs uppercase tracking-wide text-slate-400"><?= htmlspecialchars($listing['species']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($listing['genetics'])): ?>
                                        <p class="rounded-xl border border-brand-400/30 bg-brand-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-brand-100">Genetik: <?= htmlspecialchars($listing['genetics']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($listing['price'])): ?>
                                        <p class="text-sm text-slate-200"><strong>Preis:</strong> <?= htmlspecialchars($listing['price']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($listing['description'])): ?>
                                        <div class="rich-text-content prose prose-invert max-w-none text-sm leading-relaxed text-slate-200">
                                            <?= render_rich_text($listing['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($settings['contact_email'])): ?>
                                    <a class="mt-4 inline-flex items-center justify-center rounded-full border border-brand-400/60 bg-brand-500/15 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-brand-100 transition hover:border-brand-200 hover:bg-brand-500/25" href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>?subject=Anfrage%20<?= urlencode($listing['title']) ?>">Direkt anfragen</a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($newsHasContent || $careHasContent): ?>
            <aside class="space-y-6">
                <?php if ($newsHasContent): ?>
                    <article class="rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-xl shadow-black/40">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-white"><?= htmlspecialchars(content_value($settings, 'home_news_title')) ?></h2>
                            <a class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-brand-300 hover:text-brand-100" href="<?= BASE_URL ?>/index.php?route=news"><?= htmlspecialchars(content_value($settings, 'home_news_cta')) ?></a>
                        </div>
                        <div class="mt-6 space-y-4">
                            <?php foreach ($latestNews as $post): ?>
                                <article class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:border-brand-400/60">
                                    <h3 class="text-base font-semibold text-white"><?= htmlspecialchars($post['title']) ?></h3>
                                    <?php if (!empty($post['published_at'])): ?>
                                        <p class="text-xs text-slate-400"><?= date('d.m.Y', strtotime($post['published_at'])) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($post['excerpt'])): ?>
                                        <p class="mt-2 text-xs text-slate-200"><?= nl2br(htmlspecialchars($post['excerpt'])) ?></p>
                                    <?php endif; ?>
                                    <a class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-100" href="<?= BASE_URL ?>/index.php?route=news&amp;slug=<?= urlencode($post['slug']) ?>"><?= htmlspecialchars(content_value($settings, 'home_news_post_cta')) ?> →</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ($careHasContent): ?>
                    <article class="rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-xl shadow-black/40">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-white"><?= htmlspecialchars(content_value($settings, 'home_care_title')) ?></h2>
                            <a class="inline-flex items-center gap-2 rounded-full border border-brand-400/60 bg-brand-500/15 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-brand-50 transition hover:border-brand-200 hover:bg-brand-500/25" href="<?= BASE_URL ?>/index.php?route=care-guide"><?= htmlspecialchars(content_value($settings, 'home_care_cta')) ?></a>
                        </div>
                        <div class="mt-6 space-y-4">
                            <?php foreach ($careHighlights as $article): ?>
                                <article class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                    <h3 class="text-base font-semibold text-white"><?= htmlspecialchars($article['title']) ?></h3>
                                    <?php if (!empty($article['summary'])): ?>
                                        <p class="mt-2 text-xs text-slate-200"><?= nl2br(htmlspecialchars($article['summary'])) ?></p>
                                    <?php endif; ?>
                                    <a class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-100 transition hover:text-brand-100" href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($article['slug']) ?>"><?= htmlspecialchars(content_value($settings, 'home_care_article_cta')) ?> →</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>
            </aside>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
