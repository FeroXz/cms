<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></title>
    <?php
        $metaDescription = trim((string)($settings['global_meta_description'] ?? ''));
        $metaImage = trim((string)($settings['global_meta_image'] ?? ''));
        $activeThemeKey = $settings['active_theme'] ?? 'aurora';
        $themeConfig = get_theme_config($activeThemeKey);
        $novaEnabled = setting_enabled($settings, 'nova_features_enabled');
        $primaryMenu = ($novaEnabled && !empty($navMenu)) ? $navMenu : [];
    ?>
    <?php if ($metaDescription !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>
    <?php if ($metaImage !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars(BASE_URL . '/' . ltrim($metaImage, '/')) ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Space Grotesk"', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        midnight: '#0f172a',
                        aurora: '#f97316',
                        cosmic: '#fb923c',
                        ember: '#ea580c',
                    },
                    boxShadow: {
                        horizon: '0 30px 120px rgba(15, 23, 42, 0.45)',
                    },
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('style.css') ?>">
    <?php if (!empty($themeConfig['stylesheet'])): ?>
        <link rel="stylesheet" href="<?= asset($themeConfig['stylesheet']) ?>">
    <?php endif; ?>
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 <?= htmlspecialchars($themeConfig['body_class'] ?? '') ?>">
<?php
    $activePageSlug = $activePageSlug ?? null;
    $resolveMenuUrl = static function (array $item) {
        if (!empty($item['url'])) {
            return $item['url'];
        }
        if (!empty($item['page_slug'])) {
            return BASE_URL . '/index.php?route=page&slug=' . urlencode($item['page_slug']);
        }
        return '#';
    };

    $isActiveLink = static function (array $item) use ($currentRoute, $activePageSlug): bool {
        if (!empty($item['page_slug'])) {
            return $currentRoute === 'page' && $activePageSlug === $item['page_slug'];
        }
        $url = $item['url'] ?? '';
        if (strpos($url, 'route=animals') !== false) {
            return $currentRoute === 'animals';
        }
        if (strpos($url, 'route=news') !== false) {
            return $currentRoute === 'news';
        }
        if (strpos($url, 'route=genetics') !== false) {
            return $currentRoute === 'genetics';
        }
        if (strpos($url, 'route=gallery') !== false) {
            return $currentRoute === 'gallery';
        }
        if (strpos($url, 'route=adoption') !== false) {
            return $currentRoute === 'adoption';
        }
        if ($url === BASE_URL . '/index.php') {
            return $currentRoute === 'home';
        }
        return false;
    };

    $hasCare = !empty($navCareArticles);
?>
<div class="relative flex min-h-screen flex-col">
    <header class="relative z-40 border-b border-white/10 bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(56,189,248,0.25),transparent_55%)]"></div>
        <div class="relative mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center justify-between gap-6">
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-4">
                    <?php $logoPath = trim((string)($settings['site_logo_path'] ?? '')); ?>
                    <?php if ($logoPath !== ''): ?>
                        <span class="relative flex h-16 w-16 items-center justify-center rounded-3xl bg-white/10 shadow-[0_20px_40px_rgba(59,130,246,0.35)] ring-1 ring-aurora/40">
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?>" class="h-12 w-12 object-contain" loading="lazy">
                        </span>
                    <?php else: ?>
                        <span class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-aurora/60 to-cosmic/60 text-2xl font-semibold text-slate-950 shadow-[0_20px_60px_rgba(168,85,247,0.35)]">
                            <?= strtoupper(substr($settings['site_title'] ?? APP_NAME, 0, 2)) ?>
                        </span>
                    <?php endif; ?>
                    <span class="flex flex-col">
                        <span class="text-2xl font-semibold leading-tight text-white"><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
                        <span class="text-sm uppercase tracking-[0.35em] text-aurora/70"><?= htmlspecialchars($settings['site_tagline'] ?? '') ?></span>
                    </span>
                </a>
                <button type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white transition hover:border-aurora/60 hover:text-aurora focus:outline-none focus:ring-2 focus:ring-aurora lg:hidden" data-mobile-nav-toggle>
                    <span class="sr-only">Navigation umschalten</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            <nav class="hidden flex-1 items-center justify-end gap-4 lg:flex" data-desktop-nav>
                <?php
                    $renderDesktopItem = function (array $item, bool $active) use (&$renderDesktopItem, $resolveMenuUrl, $currentRoute, $activePageSlug): string {
                        $hasChildren = !empty($item['children']);
                        $classes = 'relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white';
                        if ($active) {
                            $classes = 'relative inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]';
                        }
                        $html = '<div class="group">';
                        $html .= '<a href="' . htmlspecialchars($resolveMenuUrl($item)) . '" class="' . $classes . '"' . (!empty($item['open_in_new_tab']) ? ' target="_blank" rel="noopener"' : '') . '>';
                        $html .= htmlspecialchars($item['label']);
                        if ($hasChildren) {
                            $html .= '<svg class="h-4 w-4 transition group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>';
                        }
                        $html .= '</a>';
                        if ($hasChildren) {
                            $html .= '<div class="invisible absolute left-0 top-full z-40 mt-3 min-w-[220px] translate-y-2 rounded-3xl border border-white/15 bg-slate-950/95 p-4 opacity-0 shadow-horizon transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">';
                            foreach ($item['children'] as $child) {
                                $childActive = $currentRoute === 'page' && ($activePageSlug ?? '') === ($child['page_slug'] ?? null);
                                $childClasses = 'block rounded-2xl px-3 py-2 text-sm text-white/70 transition hover:bg-aurora/20 hover:text-white';
                                if ($childActive) {
                                    $childClasses = 'block rounded-2xl px-3 py-2 text-sm font-semibold text-white bg-aurora/30 shadow-[0_15px_40px_rgba(56,189,248,0.25)]';
                                }
                                $html .= '<a href="' . htmlspecialchars($resolveMenuUrl($child)) . '" class="' . $childClasses . '">' . htmlspecialchars($child['label']) . '</a>';
                            }
                            $html .= '</div>';
                        }
                        $html .= '</div>';
                        return $html;
                    };
                ?>
                <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.4em] text-aurora/80">
                    Navigation
                </div>
                <?php if (!empty($primaryMenu)): ?>
                    <?php foreach ($primaryMenu as $menuItem): ?>
                        <?= $renderDesktopItem($menuItem, $isActiveLink($menuItem)) ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/index.php" class="<?= $currentRoute === 'home' ? 'relative inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Start</a>
                    <a href="<?= BASE_URL ?>/index.php?route=animals" class="<?= $currentRoute === 'animals' ? 'relative inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Tierübersicht</a>
                    <a href="<?= BASE_URL ?>/index.php?route=news" class="<?= $currentRoute === 'news' ? 'relative inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Neuigkeiten</a>
                    <a href="<?= BASE_URL ?>/index.php?route=genetics" class="<?= $currentRoute === 'genetics' ? 'relative inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Genetik</a>
                    <a href="<?= BASE_URL ?>/index.php?route=gallery" class="<?= $currentRoute === 'gallery' ? 'relative inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Galerie</a>
                <?php endif; ?>
                <?php if ($hasCare): ?>
                    <div class="group relative">
                        <button type="button" class="relative inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white">
                            Pflegeleitfaden
                            <svg class="h-4 w-4 transition group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>
                        </button>
                        <div class="invisible absolute right-0 top-full z-40 mt-3 min-w-[220px] translate-y-2 rounded-3xl border border-white/15 bg-slate-950/95 p-4 opacity-0 shadow-horizon transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                            <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="<?= $currentRoute === 'care-guide' ? 'block rounded-2xl bg-aurora/20 px-3 py-2 text-sm font-semibold text-white shadow-[0_15px_40px_rgba(56,189,248,0.25)]' : 'block rounded-2xl px-3 py-2 text-sm text-white/70 transition hover:bg-aurora/20 hover:text-white' ?>">Übersicht</a>
                            <?php foreach (($navCareArticles ?? []) as $careNav): ?>
                                <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($careNav['slug']) ?>" class="<?= ($currentRoute === 'care-article' && ($activeCareSlug ?? '') === $careNav['slug']) ? 'block rounded-2xl bg-aurora/20 px-3 py-2 text-sm font-semibold text-white shadow-[0_15px_40px_rgba(56,189,248,0.25)]' : 'block rounded-2xl px-3 py-2 text-sm text-white/70 transition hover:bg-aurora/20 hover:text-white' ?>"><?= htmlspecialchars($careNav['title']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="flex items-center gap-2">
                    <?php if (current_user()): ?>
                        <a href="<?= BASE_URL ?>/index.php?route=my-animals" class="<?= $currentRoute === 'my-animals' ? 'inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Meine Tiere</a>
                        <a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="<?= str_starts_with($currentRoute, 'admin/') ? 'inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Admin</a>
                        <a href="<?= BASE_URL ?>/index.php?route=logout" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/70 transition hover:border-ember/60 hover:text-ember">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/index.php?route=login" class="<?= $currentRoute === 'login' ? 'inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.3)]' : 'inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white' ?>">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
        <div class="relative hidden lg:hidden" data-mobile-nav-panel>
            <nav class="mx-6 mb-6 space-y-3 rounded-3xl border border-white/10 bg-slate-950/90 p-6 text-sm shadow-horizon">
                <?php if (!empty($primaryMenu)): ?>
                    <?php foreach ($primaryMenu as $menuItem): ?>
                        <details class="group" <?= $isActiveLink($menuItem) ? 'open' : '' ?>>
                            <summary class="flex cursor-pointer list-none items-center justify-between rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">
                                <span><?= htmlspecialchars($menuItem['label']) ?></span>
                                <?php if (!empty($menuItem['children'])): ?>
                                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>
                                <?php endif; ?>
                            </summary>
                            <div class="mt-2 space-y-2 pl-3">
                                <a href="<?= htmlspecialchars($resolveMenuUrl($menuItem)) ?>" class="block rounded-xl px-3 py-2 text-white/70 transition hover:bg-aurora/20 hover:text-white"<?= !empty($menuItem['open_in_new_tab']) ? ' target="_blank" rel="noopener"' : '' ?>>Direkt öffnen</a>
                                <?php foreach ($menuItem['children'] ?? [] as $child): ?>
                                    <a href="<?= htmlspecialchars($resolveMenuUrl($child)) ?>" class="block rounded-xl px-3 py-2 text-white/70 transition hover:bg-aurora/20 hover:text-white"<?= !empty($child['open_in_new_tab']) ? ' target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($child['label']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/index.php" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Start</a>
                    <a href="<?= BASE_URL ?>/index.php?route=animals" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Tierübersicht</a>
                    <a href="<?= BASE_URL ?>/index.php?route=news" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Neuigkeiten</a>
                <?php endif; ?>
                <?php if ($hasCare): ?>
                    <details class="group" <?= ($currentRoute === 'care-guide' || $currentRoute === 'care-article') ? 'open' : '' ?>>
                        <summary class="flex cursor-pointer list-none items-center justify-between rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">
                            <span>Pflegeleitfaden</span>
                            <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>
                        </summary>
                        <div class="mt-2 space-y-2 pl-3 text-sm">
                            <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="block rounded-xl px-3 py-2 text-white/70 transition hover:bg-aurora/20 hover:text-white">Übersicht</a>
                            <?php foreach (($navCareArticles ?? []) as $careNav): ?>
                                <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($careNav['slug']) ?>" class="block rounded-xl px-3 py-2 text-white/70 transition hover:bg-aurora/20 hover:text-white"><?= htmlspecialchars($careNav['title']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?route=genetics" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Genetik</a>
                <a href="<?= BASE_URL ?>/index.php?route=gallery" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Galerie</a>
                <a href="<?= BASE_URL ?>/index.php?route=adoption" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Tierabgabe</a>
                <?php if (current_user()): ?>
                    <a href="<?= BASE_URL ?>/index.php?route=my-animals" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Meine Tiere</a>
                    <a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Admin</a>
                    <a href="<?= BASE_URL ?>/index.php?route=logout" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-ember">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/index.php?route=login" class="block rounded-2xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="flex-1 pb-20 pt-16">
