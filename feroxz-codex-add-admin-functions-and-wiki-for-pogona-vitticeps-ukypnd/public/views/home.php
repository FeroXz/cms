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

$primaryCtaLabel = content_value($settings, 'home_hero_primary_cta');
$primaryCtaUrl = trim((string)content_value($settings, 'home_hero_primary_cta_url')) ?: (BASE_URL . '/index.php?route=genetics');
$secondaryCtaLabel = content_value($settings, 'home_hero_secondary_cta');
$secondaryCtaUrl = trim((string)content_value($settings, 'home_hero_secondary_cta_url')) ?: (BASE_URL . '/index.php?route=care-guide');
$highlightsCta = content_value($settings, 'home_highlights_cta');
$heroTitle = content_value($settings, 'home_hero_title');
$heroIntro = content_value($settings, 'home_hero_intro');
$heroAside = content_value($settings, 'home_hero_secondary_intro');
?>
<?php if ($showHero): ?>
<section class="relative px-4 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-7xl">
        <div class="relative overflow-hidden rounded-[2.5rem] border border-white/30 bg-gradient-to-br from-indigo-500 via-sky-500 to-cyan-400 p-10 text-white shadow-[0_40px_120px_rgba(14,116,144,0.35)] lg:p-16">
            <div class="hero-aurora" aria-hidden="true"></div>
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center">
                <div class="relative space-y-8">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-white/90">
                        <?= htmlspecialchars(content_value($settings, 'home_hero_badge')) ?>
                    </span>
                    <h1 class="text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                        <?= htmlspecialchars($heroTitle !== '' ? $heroTitle : ($settings['site_title'] ?? APP_NAME)) ?>
                    </h1>
                    <div class="rich-text-content prose prose-invert max-w-none text-base leading-relaxed text-white/90">
                        <?= render_rich_text($heroIntro !== '' ? $heroIntro : ($settings['hero_intro'] ?? '')) ?>
                    </div>
                    <?php if (trim(strip_tags($heroAside)) !== ''): ?>
                        <div class="rounded-3xl border border-white/30 bg-white/20 p-6 text-sm leading-relaxed text-white/90 shadow-lg shadow-cyan-900/20">
                            <?= render_rich_text($heroAside) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="<?= htmlspecialchars($primaryCtaUrl) ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-xl shadow-cyan-900/20 transition hover:-translate-y-1 hover:bg-slate-100">
                            <?= htmlspecialchars($primaryCtaLabel) ?>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="<?= htmlspecialchars($secondaryCtaUrl) ?>" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/50 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:-translate-y-1 hover:bg-white/20">
                            <?= htmlspecialchars($secondaryCtaLabel) ?>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
                <div class="relative rounded-[2rem] border border-white/35 bg-white/15 p-8 shadow-xl shadow-cyan-900/30">
                    <h2 class="text-lg font-semibold text-white">Kennzahlen & Highlights</h2>
                    <p class="mt-2 text-sm text-white/80"><?= htmlspecialchars(content_value($settings, 'home_highlights_subtitle')) ?></p>
                    <dl class="mt-8 grid gap-4 text-sm text-white/90 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/30 bg-white/20 px-5 py-4">
                            <dt class="text-xs uppercase tracking-wide text-white/70">Aktive Tiere</dt>
                            <dd class="mt-1 text-3xl font-semibold text-white"><?= count($animals) ?></dd>
                        </div>
                        <div class="rounded-2xl border border-white/30 bg-white/20 px-5 py-4">
                            <dt class="text-xs uppercase tracking-wide text-white/70">Adoptionen</dt>
                            <dd class="mt-1 text-3xl font-semibold text-white"><?= count($listings) ?></dd>
                        </div>
                        <div class="rounded-2xl border border-white/30 bg-white/20 px-5 py-4">
                            <dt class="text-xs uppercase tracking-wide text-white/70">Neuigkeiten</dt>
                            <dd class="mt-1 text-3xl font-semibold text-white"><?= count($latestNews) ?></dd>
                        </div>
                        <div class="rounded-2xl border border-white/30 bg-white/20 px-5 py-4">
                            <dt class="text-xs uppercase tracking-wide text-white/70">Pflegeartikel</dt>
                            <dd class="mt-1 text-3xl font-semibold text-white"><?= count($careHighlights) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($showAnimals && !empty($animals)): ?>
<section class="mx-auto mt-24 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-400">Highlights</span>
            <h2 class="mt-2 text-3xl font-semibold text-slate-900 sm:text-4xl"><?= htmlspecialchars(content_value($settings, 'home_highlights_title')) ?></h2>
            <p class="mt-2 text-base text-slate-500"><?= htmlspecialchars(content_value($settings, 'home_highlights_subtitle')) ?></p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?route=animals" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:border-indigo-400 hover:text-indigo-500">
            <?= htmlspecialchars($highlightsCta) ?>
            <span aria-hidden="true">→</span>
        </a>
    </div>
    <div class="mt-12 grid gap-8 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($animals as $animal): ?>
            <article class="group flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-xl shadow-indigo-100/60 transition hover:-translate-y-2 hover:border-indigo-300 hover:shadow-2xl">
                <?php if (!empty($animal['image_path'])): ?>
                    <div class="relative h-60 w-full overflow-hidden">
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($animal['image_path']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent p-6">
                            <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($animal['name']) ?></h3>
                            <p class="text-xs uppercase tracking-wide text-white/70"><?= htmlspecialchars($animal['species']) ?></p>
                        </div>
                        <?php if (!empty($animal['is_piebald'])): ?>
                            <span class="absolute right-4 top-4 inline-flex items-center rounded-full bg-indigo-500/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white" title="Geschecktes Tier">Gescheckt</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="flex flex-1 flex-col gap-4 p-6">
                    <?php if (!empty($animal['genetics'])): ?>
                        <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-600">
                            Genetik: <?= htmlspecialchars($animal['genetics']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($animal['special_notes'])): ?>
                        <div class="rich-text-content prose max-w-none text-sm leading-relaxed text-slate-600">
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
<section class="mx-auto mt-24 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <?php
        $sectionClass = 'grid gap-12';
        if ($adoptionHasContent && ($newsHasContent || $careHasContent)) {
            $sectionClass .= ' lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.9fr)] lg:items-start';
        }
    ?>
    <div class="<?= $sectionClass ?>">
        <?php if ($adoptionHasContent): ?>
            <div class="rounded-[2rem] border border-slate-200 bg-white p-10 shadow-xl shadow-slate-200/60">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-400">Adoption</span>
                        <h2 class="mt-2 text-3xl font-semibold text-slate-900 sm:text-4xl"><?= htmlspecialchars(content_value($settings, 'home_adoption_title')) ?></h2>
                        <p class="mt-2 text-base text-slate-500"><?= htmlspecialchars(content_value($settings, 'home_adoption_subtitle')) ?></p>
                    </div>
                    <?php if (!empty($settings['contact_email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>" class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-1 hover:bg-indigo-400">Kontakt aufnehmen</a>
                    <?php endif; ?>
                </div>
                <div class="rich-text-content prose mt-6 max-w-none text-slate-600">
                    <?= render_rich_text($settings['adoption_intro'] ?? '') ?>
                </div>
                <?php if ($hasAdoptionListings): ?>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2">
                        <?php foreach ($listings as $listing): ?>
                            <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-inner shadow-slate-200">
                                <?php if (!empty($listing['image_path'])): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" class="mb-4 h-44 w-full rounded-2xl object-cover" loading="lazy">
                                <?php endif; ?>
                                <div class="flex flex-1 flex-col gap-3">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($listing['title']) ?></h3>
                                        <?php if (!empty($listing['species'])): ?>
                                            <p class="text-xs uppercase tracking-wide text-slate-500"><?= htmlspecialchars($listing['species']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($listing['genetics'])): ?>
                                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">Genetik: <?= htmlspecialchars($listing['genetics']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($listing['price'])): ?>
                                        <p class="text-sm text-slate-600"><strong>Preis:</strong> <?= htmlspecialchars($listing['price']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($listing['description'])): ?>
                                        <div class="rich-text-content prose max-w-none text-sm leading-relaxed text-slate-600">
                                            <?= render_rich_text($listing['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($settings['contact_email'])): ?>
                                    <a class="mt-4 inline-flex items-center justify-center gap-2 rounded-full border border-indigo-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-600 transition hover:-translate-y-1 hover:border-indigo-400 hover:text-indigo-700" href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>?subject=Anfrage%20<?= urlencode($listing['title']) ?>">Direkt anfragen</a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($newsHasContent || $careHasContent): ?>
            <aside class="space-y-8">
                <?php if ($newsHasContent): ?>
                    <article class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">News</span>
                                <h2 class="mt-2 text-xl font-semibold text-slate-900"><?= htmlspecialchars(content_value($settings, 'home_news_title')) ?></h2>
                            </div>
                            <a class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:border-indigo-300 hover:text-indigo-500" href="<?= BASE_URL ?>/index.php?route=news"><?= htmlspecialchars(content_value($settings, 'home_news_cta')) ?></a>
                        </div>
                        <div class="mt-6 space-y-4">
                            <?php foreach ($latestNews as $post): ?>
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:-translate-y-1 hover:border-indigo-300">
                                    <h3 class="text-base font-semibold text-slate-900"><?= htmlspecialchars($post['title']) ?></h3>
                                    <?php if (!empty($post['published_at'])): ?>
                                        <p class="text-xs text-slate-500"><?= date('d.m.Y', strtotime($post['published_at'])) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($post['excerpt'])): ?>
                                        <p class="mt-2 text-xs text-slate-600"><?= nl2br(htmlspecialchars($post['excerpt'])) ?></p>
                                    <?php endif; ?>
                                    <a class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-indigo-600" href="<?= BASE_URL ?>/index.php?route=news&amp;slug=<?= urlencode($post['slug']) ?>"><?= htmlspecialchars(content_value($settings, 'home_news_post_cta')) ?> →</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ($careHasContent): ?>
                    <article class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Pflegewissen</span>
                                <h2 class="mt-2 text-xl font-semibold text-slate-900"><?= htmlspecialchars(content_value($settings, 'home_care_title')) ?></h2>
                            </div>
                            <a class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-1 hover:bg-indigo-400" href="<?= BASE_URL ?>/index.php?route=care-guide"><?= htmlspecialchars(content_value($settings, 'home_care_cta')) ?></a>
                        </div>
                        <div class="mt-6 space-y-4">
                            <?php foreach ($careHighlights as $article): ?>
                                <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <h3 class="text-base font-semibold text-slate-900"><?= htmlspecialchars($article['title']) ?></h3>
                                    <?php if (!empty($article['summary'])): ?>
                                        <p class="mt-2 text-xs text-slate-600"><?= nl2br(htmlspecialchars($article['summary'])) ?></p>
                                    <?php endif; ?>
                                    <a class="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-indigo-600" href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($article['slug']) ?>"><?= htmlspecialchars(content_value($settings, 'home_care_article_cta')) ?> →</a>
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
