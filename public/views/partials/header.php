<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $isAdminView = isset($currentRoute) && str_starts_with($currentRoute, 'admin/');
        $metaDescription = trim((string)($settings['global_meta_description'] ?? ''));
        $metaImage = trim((string)($settings['global_meta_image'] ?? ''));
        $activeThemeKey = $settings['active_theme'] ?? 'aurora';
        $themeConfig = $isAdminView ? ['body_class' => ''] : get_theme_config($activeThemeKey);
        $novaEnabled = !$isAdminView && setting_enabled($settings, 'nova_features_enabled');
        $primaryMenu = ($novaEnabled && !empty($navMenu)) ? $navMenu : [];
        $pageTitle = ($isAdminView ? 'Admin • ' : '') . ($settings['site_title'] ?? APP_NAME);
    ?>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <?php if ($metaDescription !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>
    <?php if ($metaImage !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars(BASE_URL . '/' . ltrim($metaImage, '/')) ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                        brand: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                    },
                    boxShadow: {
                        horizon: '0 30px 120px rgba(15, 23, 42, 0.45)',
                        flow: '0 24px 60px rgba(14, 165, 233, 0.25)',
                    },
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if ($isAdminView): ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.4.1/flowbite.min.css" integrity="sha512-FcI+z6PtvP9d0kv7dySlqCpHaW4J7CR5oZ97U2RhpMi2dnhIP6j0O/gw8XRsBKsn0hYSz5M6T0DhoUmykNXqRA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <?php else: ?>
        <link rel="stylesheet" href="<?= asset('style.css') ?>">
        <?php if (!empty($themeConfig['stylesheet'])): ?>
            <link rel="stylesheet" href="<?= asset($themeConfig['stylesheet']) ?>">
        <?php endif; ?>
    <?php endif; ?>
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 <?= $isAdminView ? '' : htmlspecialchars($themeConfig['body_class'] ?? '') ?>">
<?php if ($isAdminView): ?>
    <?php $currentUser = current_user(); ?>
    <div class="flex min-h-screen flex-col bg-slate-950">
        <header class="sticky top-0 z-50 border-b border-slate-800 bg-slate-900/80 backdrop-blur supports-[backdrop-filter]:bg-slate-900/60">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center justify-between gap-4">
                    <a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="flex items-center gap-3">
                        <?php $logoPath = trim((string)($settings['site_logo_path'] ?? '')); ?>
                        <?php if ($logoPath !== ''): ?>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/70 ring-2 ring-brand-500/40">
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?>" class="h-10 w-10 object-contain" loading="lazy">
                            </span>
                        <?php else: ?>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500/80 to-brand-600/80 text-lg font-semibold text-slate-950 shadow-flow">
                                <?= strtoupper(substr($settings['site_title'] ?? APP_NAME, 0, 2)) ?>
                            </span>
                        <?php endif; ?>
                        <span class="flex flex-col">
                            <span class="text-lg font-semibold leading-tight text-white"><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
                            <span class="text-xs uppercase tracking-[0.4em] text-brand-200">Adminbereich</span>
                        </span>
                    </a>
                    <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-700 bg-slate-900/80 text-slate-200 transition hover:border-brand-500 hover:text-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500 lg:hidden" data-collapse-toggle="admin-nav-panel" aria-controls="admin-nav-panel" aria-label="Admin-Navigation umschalten">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-3 sm:justify-end">
                    <span class="hidden text-xs font-semibold uppercase tracking-[0.4em] text-brand-200 sm:inline-flex">Version <?= htmlspecialchars($settings['cms_version'] ?? '5.6') ?></span>
                    <?php if ($currentUser): ?>
                        <div class="flex items-center gap-3">
                            <span class="hidden text-sm text-slate-300 sm:block">Willkommen, <?= htmlspecialchars($currentUser['name'] ?? $currentUser['email'] ?? 'Admin') ?></span>
                            <div class="relative">
                                <button type="button" class="inline-flex items-center justify-center rounded-full bg-brand-500 p-2 text-white shadow-flow transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-400/40 dark:focus:ring-brand-400/20" data-dropdown-toggle="admin-user-menu" aria-haspopup="true" aria-expanded="false">
                                    <span class="sr-only">Benutzermenü öffnen</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0" />
                                    </svg>
                                </button>
                                <div id="admin-user-menu" class="z-50 hidden w-56 divide-y divide-slate-800 overflow-hidden rounded-xl border border-slate-800 bg-slate-900 shadow-xl">
                                    <div class="px-4 py-3 text-sm text-slate-200">
                                        <div class="font-semibold"><?= htmlspecialchars($currentUser['name'] ?? $currentUser['email'] ?? 'Admin') ?></div>
                                        <?php if (!empty($currentUser['email'])): ?>
                                            <div class="text-xs text-slate-400"><?= htmlspecialchars($currentUser['email']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <ul class="space-y-1 px-2 py-2 text-sm text-slate-200">
                                        <li>
                                            <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2 rounded-lg px-3 py-2 transition hover:bg-slate-800/80">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9 2v10" />
                                                </svg>
                                                Zur Website
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= BASE_URL ?>/index.php?route=logout" class="flex items-center gap-2 rounded-lg px-3 py-2 text-rose-300 transition hover:bg-rose-500/10">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                                </svg>
                                                Logout
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/index.php?route=login" class="inline-flex items-center gap-2 rounded-xl border border-brand-500/40 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-200 transition hover:bg-brand-500/20 focus:outline-none focus:ring-2 focus:ring-brand-500/60">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        <main class="flex-1 bg-slate-950/95 pb-20 pt-10">
<?php else: ?>
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
        $dropdownCounter = 0;
        $currentUser = current_user();

        $renderSimpleLink = static function (string $label, string $url, bool $active, bool $external = false): string {
            $baseClasses = 'inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white';
            if ($active) {
                $baseClasses = 'inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.28)]';
            }
            $attrs = $external ? ' target="_blank" rel="noopener"' : '';
            return '<a href="' . htmlspecialchars($url) . '" class="' . $baseClasses . '"' . $attrs . '>' . htmlspecialchars($label) . '</a>';
        };

        $renderDesktopItem = static function (array $item, bool $active) use (&$dropdownCounter, $resolveMenuUrl): string {
            $hasChildren = !empty($item['children']);
            $url = $resolveMenuUrl($item);
            $baseClasses = 'inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white';
            if ($active) {
                $baseClasses = 'inline-flex items-center gap-2 rounded-full border border-aurora/60 bg-aurora/20 px-4 py-2 text-sm font-semibold text-white shadow-[0_20px_60px_rgba(56,189,248,0.28)]';
            }
            $openInNewTab = !empty($item['open_in_new_tab']);
            if (!$hasChildren) {
                return '<a href="' . htmlspecialchars($url) . '" class="' . $baseClasses . '"' . ($openInNewTab ? ' target="_blank" rel="noopener"' : '') . '>' . htmlspecialchars($item['label']) . '</a>';
            }

            $dropdownCounter++;
            $buttonId = 'hs-nav-trigger-' . $dropdownCounter;
            $chevron = '<svg class="h-4 w-4 transition group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>';
            $html = '<div class="hs-dropdown relative [--strategy:absolute] [--trigger:hover]">';
            $html .= '<button id="' . $buttonId . '" type="button" class="group ' . $baseClasses . '" aria-haspopup="true" aria-expanded="false">' . htmlspecialchars($item['label']) . $chevron . '</button>';
            $html .= '<div class="hs-dropdown-menu hidden min-w-[220px] rounded-2xl border border-white/15 bg-slate-950/95 p-3 shadow-horizon" aria-labelledby="' . $buttonId . '">';
            $html .= '<a href="' . htmlspecialchars($url) . '" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-white/70 transition hover:bg-aurora/20 hover:text-white"' . ($openInNewTab ? ' target="_blank" rel="noopener"' : '') . '>Direkt öffnen</a>';
            foreach ($item['children'] as $child) {
                $childUrl = $resolveMenuUrl($child);
                $childAttrs = !empty($child['open_in_new_tab']) ? ' target="_blank" rel="noopener"' : '';
                $html .= '<a href="' . htmlspecialchars($childUrl) . '" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-white/70 transition hover:bg-aurora/20 hover:text-white"' . $childAttrs . '>' . htmlspecialchars($child['label']) . '</a>';
            }
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        };
    ?>
    <div class="relative flex min-h-screen flex-col bg-slate-950">
        <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/80 backdrop-blur supports-[backdrop-filter]:bg-slate-950/60">
            <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-6 px-6 py-6">
                <div class="flex w-full items-center justify-between gap-4 lg:w-auto">
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
                    <button id="primary-navigation-trigger" type="button" class="hs-collapse-toggle inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-white transition hover:border-aurora/60 hover:text-aurora focus:outline-none focus:ring-2 focus:ring-aurora lg:hidden" data-hs-collapse='{"target":"#primary-navigation"}' aria-controls="primary-navigation" aria-expanded="false" aria-label="Navigation umschalten">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <div id="primary-navigation" class="hs-collapse hidden w-full flex-col gap-6 overflow-hidden transition-all duration-300 lg:flex lg:w-auto lg:flex-row lg:items-center lg:justify-between" aria-labelledby="primary-navigation-trigger">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-3">
                        <span class="inline-flex items-center rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.38em] text-aurora/80">Navigation</span>
                        <?php if (!empty($primaryMenu)): ?>
                            <?php foreach ($primaryMenu as $menuItem): ?>
                                <?= $renderDesktopItem($menuItem, $isActiveLink($menuItem)) ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?= $renderSimpleLink('Start', BASE_URL . '/index.php', $currentRoute === 'home') ?>
                            <?= $renderSimpleLink('Tierübersicht', BASE_URL . '/index.php?route=animals', $currentRoute === 'animals') ?>
                            <?= $renderSimpleLink('Neuigkeiten', BASE_URL . '/index.php?route=news', $currentRoute === 'news') ?>
                        <?php endif; ?>
                        <?php if ($hasCare): ?>
                            <?php $dropdownCounter++; $careDropdownId = 'hs-care-dropdown-' . $dropdownCounter; ?>
                            <div class="hs-dropdown relative [--strategy:absolute] [--trigger:hover]">
                                <button id="<?= $careDropdownId ?>" type="button" class="group inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/80 transition hover:border-aurora/60 hover:text-white" aria-haspopup="true" aria-expanded="false">
                                    Pflegeleitfaden
                                    <svg class="h-4 w-4 transition group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                    </svg>
                                </button>
                                <div class="hs-dropdown-menu hidden min-w-[220px] rounded-2xl border border-white/15 bg-slate-950/95 p-3 shadow-horizon" aria-labelledby="<?= $careDropdownId ?>">
                                    <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm <?= $currentRoute === 'care-guide' ? 'font-semibold text-white bg-aurora/20' : 'text-white/70 transition hover:bg-aurora/20 hover:text-white' ?>">Übersicht</a>
                                    <?php foreach (($navCareArticles ?? []) as $careNav): ?>
                                        <?php $careActive = $currentRoute === 'care-article' && ($activeCareSlug ?? '') === $careNav['slug']; ?>
                                        <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($careNav['slug']) ?>" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm <?= $careActive ? 'font-semibold text-white bg-aurora/20' : 'text-white/70 transition hover:bg-aurora/20 hover:text-white' ?>"><?= htmlspecialchars($careNav['title']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?= $renderSimpleLink('Genetik', BASE_URL . '/index.php?route=genetics', $currentRoute === 'genetics') ?>
                        <?= $renderSimpleLink('Galerie', BASE_URL . '/index.php?route=gallery', $currentRoute === 'gallery') ?>
                        <?= $renderSimpleLink('Tierabgabe', BASE_URL . '/index.php?route=adoption', $currentRoute === 'adoption') ?>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-white/10 pt-4 lg:flex-row lg:items-center lg:gap-3 lg:border-none lg:pt-0">
                        <?php if ($currentUser): ?>
                            <?= $renderSimpleLink('Meine Tiere', BASE_URL . '/index.php?route=my-animals', $currentRoute === 'my-animals') ?>
                            <?= $renderSimpleLink('Admin', BASE_URL . '/index.php?route=admin/dashboard', str_starts_with((string)($currentRoute ?? ''), 'admin/')) ?>
                            <a href="<?= BASE_URL ?>/index.php?route=logout" class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white/70 transition hover:border-ember/60 hover:text-ember">Logout</a>
                        <?php else: ?>
                            <?= $renderSimpleLink('Login', BASE_URL . '/index.php?route=login', $currentRoute === 'login') ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 pb-20 pt-16">
<?php endif; ?>
