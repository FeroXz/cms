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
$heroTitle = content_value($settings, 'home_hero_title');
$heroIntro = content_value($settings, 'home_hero_intro');
$heroAside = content_value($settings, 'home_hero_secondary_intro');

$sectionOrder = $layoutDefinition['home_sections'] ?? ['hero', 'animals', 'news', 'care', 'adoption'];
$renderedSections = [];
?>
<div class="relative mx-auto flex w-full max-w-7xl flex-col gap-16 px-6">
    <?php foreach ($sectionOrder as $section): ?>
        <?php if ($section === 'hero' && $showHero && !in_array('hero', $renderedSections, true)): ?>
            <?php $renderedSections[] = 'hero'; ?>
            <section class="relative overflow-hidden rounded-[3rem] border border-white/10 bg-gradient-to-br from-slate-950 via-indigo-900 to-purple-900 px-8 py-12 shadow-[0_50px_160px_rgba(15,23,42,0.6)] sm:px-12 sm:py-16">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.45),transparent_55%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,rgba(168,85,247,0.35),transparent_65%)]"></div>
                <div class="relative grid gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] lg:items-center">
                    <div class="space-y-6">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.35em] text-aurora"><?= htmlspecialchars(content_value($settings, 'home_hero_badge')) ?></span>
                        <h1 class="text-4xl font-semibold leading-tight text-white sm:text-5xl">
                            <?= htmlspecialchars($heroTitle !== '' ? $heroTitle : ($settings['site_title'] ?? APP_NAME)) ?>
                        </h1>
                        <div class="rich-text-content prose prose-invert max-w-none text-base leading-relaxed text-white/80">
                            <?= render_rich_text($heroIntro !== '' ? $heroIntro : ($settings['hero_intro'] ?? '')) ?>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="<?= htmlspecialchars($primaryCtaUrl) ?>" class="inline-flex items-center justify-center gap-2 rounded-full border border-aurora/50 bg-aurora px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-slate-950 shadow-[0_25px_60px_rgba(56,189,248,0.35)] transition hover:-translate-y-1 hover:bg-aurora/90">
                                <?= htmlspecialchars($primaryCtaLabel) ?>
                                <span aria-hidden="true">↗</span>
                            </a>
                            <a href="<?= htmlspecialchars($secondaryCtaUrl) ?>" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-white transition hover:-translate-y-1 hover:bg-white/15">
                                <?= htmlspecialchars($secondaryCtaLabel) ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                    <div class="relative rounded-[2.5rem] border border-white/10 bg-white/5 p-8 text-white shadow-[0_35px_120px_rgba(59,130,246,0.3)]">
                        <h2 class="text-xl font-semibold text-white">Status der Station</h2>
                        <p class="mt-2 text-sm text-white/70">Ein Blick auf unsere aktuellen Projekte und Bewohner.</p>
                        <dl class="mt-8 grid gap-4 text-sm text-white/80 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                                <dt class="text-xs uppercase tracking-[0.25em] text-white/60">Aktive Tiere</dt>
                                <dd class="mt-1 text-3xl font-semibold text-white"><?= count($animals) ?></dd>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                                <dt class="text-xs uppercase tracking-[0.25em] text-white/60">Adoptionen</dt>
                                <dd class="mt-1 text-3xl font-semibold text-white"><?= count($listings) ?></dd>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                                <dt class="text-xs uppercase tracking-[0.25em] text-white/60">Neuigkeiten</dt>
                                <dd class="mt-1 text-3xl font-semibold text-white"><?= count($latestNews) ?></dd>
                            </div>
                            <div class="rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                                <dt class="text-xs uppercase tracking-[0.25em] text-white/60">Pflegeartikel</dt>
                                <dd class="mt-1 text-3xl font-semibold text-white"><?= count($careHighlights) ?></dd>
                            </div>
                        </dl>
                        <?php if (trim(strip_tags($heroAside)) !== ''): ?>
                            <div class="mt-8 rounded-3xl border border-white/15 bg-white/10 p-6 text-sm leading-relaxed text-white/80">
                                <?= render_rich_text($heroAside) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php elseif ($section === 'animals' && $showAnimals && !in_array('animals', $renderedSections, true) && !empty($animals)): ?>
            <?php $renderedSections[] = 'animals'; ?>
            <section class="space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-[0.4em] text-aurora/80">Kollektionsblicke</span>
                        <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars(content_value($settings, 'home_highlights_title')) ?></h2>
                        <p class="text-sm text-white/60"><?= htmlspecialchars(content_value($settings, 'home_highlights_subtitle')) ?></p>
                    </div>
                    <a href="<?= BASE_URL ?>/index.php?route=animals" class="inline-flex items-center gap-2 rounded-full border border-aurora/40 bg-aurora/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-aurora transition hover:-translate-y-1 hover:bg-aurora/20">
                        <?= htmlspecialchars(content_value($settings, 'home_highlights_cta')) ?>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($animals as $animal): ?>
                        <article class="group flex h-full flex-col overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/5 shadow-[0_30px_120px_rgba(56,189,248,0.12)] transition hover:-translate-y-2 hover:border-aurora/40">
                            <?php if (!empty($animal['image_path'])): ?>
                                <div class="relative h-60 w-full overflow-hidden">
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($animal['image_path']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent p-6">
                                        <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($animal['name']) ?></h3>
                                        <p class="text-xs uppercase tracking-[0.3em] text-white/60"><?= htmlspecialchars($animal['species']) ?></p>
                                    </div>
                                    <?php if (!empty($animal['is_piebald'])): ?>
                                        <span class="absolute right-4 top-4 inline-flex items-center rounded-full bg-aurora/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-900" title="Geschecktes Tier">Pattern</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex flex-1 flex-col gap-4 p-6 text-sm text-white/70">
                                <?php if (!empty($animal['genetics'])): ?>
                                    <div class="inline-flex items-center gap-2 rounded-full border border-aurora/40 bg-aurora/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-aurora">
                                        Genetik: <?= htmlspecialchars($animal['genetics']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($animal['special_notes'])): ?>
                                    <div class="rich-text-content prose prose-invert max-w-none text-sm leading-relaxed text-white/70">
                                        <?= render_rich_text($animal['special_notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php elseif ($section === 'news' && $newsHasContent && !in_array('news', $renderedSections, true)): ?>
            <?php $renderedSections[] = 'news'; ?>
            <section class="space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-[0.4em] text-aurora/80">Logbuch</span>
                        <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars(content_value($settings, 'home_news_title')) ?></h2>
                        <p class="text-sm text-white/60">Aktuelle Meldungen aus der Station.</p>
                    </div>
                    <a class="inline-flex items-center gap-2 rounded-full border border-aurora/40 bg-aurora/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-aurora transition hover:-translate-y-1 hover:bg-aurora/20" href="<?= BASE_URL ?>/index.php?route=news">Mehr erfahren</a>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($latestNews as $post): ?>
                        <article class="flex h-full flex-col gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-6 text-white/80 shadow-[0_25px_100px_rgba(59,130,246,0.18)]">
                            <header class="space-y-2">
                                <time class="text-xs uppercase tracking-[0.3em] text-white/50" datetime="<?= htmlspecialchars($post['published_at']) ?>">
                                    <?= $post['published_at'] ? date('d.m.Y', strtotime($post['published_at'])) : '' ?>
                                </time>
                                <h3 class="text-lg font-semibold text-white">
                                    <a href="<?= BASE_URL ?>/index.php?route=news&amp;slug=<?= urlencode($post['slug']) ?>" class="hover:text-aurora transition">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                            </header>
                            <?php if (!empty($post['excerpt'])): ?>
                                <p class="text-sm text-white/70"><?= htmlspecialchars($post['excerpt']) ?></p>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/index.php?route=news&amp;slug=<?= urlencode($post['slug']) ?>" class="mt-auto inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-aurora hover:text-white">Weiterlesen<span aria-hidden="true">→</span></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php elseif ($section === 'care' && $careHasContent && !in_array('care', $renderedSections, true)): ?>
            <?php $renderedSections[] = 'care'; ?>
            <section class="space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-[0.4em] text-aurora/80">Wissenskanal</span>
                        <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars(content_value($settings, 'home_care_title')) ?: 'Pflege Highlights' ?></h2>
                        <p class="text-sm text-white/60">Guides und Artikel für verantwortungsvolle Haltung.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="inline-flex items-center gap-2 rounded-full border border-aurora/40 bg-aurora/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-aurora transition hover:-translate-y-1 hover:bg-aurora/20">Zur Übersicht<span aria-hidden="true">↗</span></a>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <?php foreach ($careHighlights as $article): ?>
                        <article class="rounded-[2rem] border border-white/10 bg-white/5 p-6 text-white/80 shadow-[0_25px_100px_rgba(168,85,247,0.15)]">
                            <h3 class="text-lg font-semibold text-white">
                                <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($article['slug']) ?>" class="hover:text-aurora transition">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h3>
                            <?php if (!empty($article['summary'])): ?>
                                <p class="mt-3 text-sm text-white/70"><?= htmlspecialchars($article['summary']) ?></p>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($article['slug']) ?>" class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-aurora hover:text-white">Artikel lesen<span aria-hidden="true">→</span></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php elseif ($section === 'adoption' && $adoptionHasContent && !in_array('adoption', $renderedSections, true)): ?>
            <?php $renderedSections[] = 'adoption'; ?>
            <section class="space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-[0.4em] text-aurora/80">Adoption</span>
                        <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars(content_value($settings, 'home_adoption_title')) ?></h2>
                        <p class="text-sm text-white/60"><?= htmlspecialchars(content_value($settings, 'home_adoption_subtitle')) ?></p>
                    </div>
                    <?php if (!empty($settings['contact_email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>" class="inline-flex items-center gap-2 rounded-full border border-aurora/40 bg-aurora px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-950 shadow-[0_25px_60px_rgba(56,189,248,0.35)] transition hover:-translate-y-1 hover:bg-aurora/90">Kontakt aufnehmen</a>
                    <?php endif; ?>
                </div>
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
                    <div class="rounded-[2.5rem] border border-white/10 bg-white/5 p-8 text-white/80 shadow-[0_35px_120px_rgba(59,130,246,0.2)]">
                        <div class="rich-text-content prose prose-invert max-w-none">
                            <?= render_rich_text($settings['adoption_intro'] ?? '') ?>
                        </div>
                        <?php if ($hasAdoptionListings): ?>
                            <div class="mt-8 grid gap-6 sm:grid-cols-2">
                                <?php foreach ($listings as $listing): ?>
                                    <article class="flex h-full flex-col gap-3 rounded-3xl border border-white/10 bg-white/10 p-5 text-sm text-white/80">
                                        <?php if (!empty($listing['image_path'])): ?>
                                            <img src="<?= BASE_URL . '/' . htmlspecialchars($listing['image_path']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>" class="h-36 w-full rounded-2xl object-cover" loading="lazy">
                                        <?php endif; ?>
                                        <header>
                                            <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($listing['title']) ?></h3>
                                            <?php if (!empty($listing['species'])): ?>
                                                <p class="text-xs uppercase tracking-[0.3em] text-white/60"><?= htmlspecialchars($listing['species']) ?></p>
                                            <?php endif; ?>
                                        </header>
                                        <?php if (!empty($listing['genetics'])): ?>
                                            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-aurora/40 bg-aurora/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-aurora">Genetik: <?= htmlspecialchars($listing['genetics']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($listing['price'])): ?>
                                            <p class="text-sm text-white/70"><strong>Preis:</strong> <?= htmlspecialchars($listing['price']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($listing['description'])): ?>
                                            <div class="rich-text-content prose prose-invert max-w-none text-sm text-white/70">
                                                <?= render_rich_text($listing['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($settings['contact_email'])): ?>
                                            <a class="mt-auto inline-flex items-center justify-center gap-2 rounded-full border border-aurora/40 bg-aurora/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-aurora transition hover:-translate-y-1 hover:bg-aurora/20" href="mailto:<?= htmlspecialchars($settings['contact_email']) ?>?subject=Anfrage%20<?= urlencode($listing['title']) ?>">Direkt anfragen</a>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($newsHasContent): ?>
                        <div class="rounded-[2.5rem] border border-white/10 bg-white/5 p-6 text-white/80 shadow-[0_35px_120px_rgba(15,23,42,0.45)]">
                            <h3 class="text-lg font-semibold text-white">Neuigkeiten im Überblick</h3>
                            <div class="mt-4 space-y-4">
                                <?php foreach ($latestNews as $post): ?>
                                    <article class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-white/70">
                                        <a href="<?= BASE_URL ?>/index.php?route=news&amp;slug=<?= urlencode($post['slug']) ?>" class="text-base font-semibold text-white hover:text-aurora transition"><?= htmlspecialchars($post['title']) ?></a>
                                        <?php if (!empty($post['excerpt'])): ?>
                                            <p class="mt-2 text-xs text-white/60"><?= htmlspecialchars($post['excerpt']) ?></p>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
