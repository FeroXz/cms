<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                        },
                        slate: {
                            950: '#020617',
                        },
                    },
                    boxShadow: {
                        glow: '0 30px 80px rgba(99, 102, 241, 0.25)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'ui-sans-serif', 'Segoe UI', 'sans-serif'],
                    },
                }
            }
        };
    </script>
    <?php
        $activeThemeKey = $settings['active_theme'] ?? 'aurora';
        $themeConfig = get_theme_config($activeThemeKey);
    ?>
    <link rel="stylesheet" href="<?= asset('style.css') ?>">
    <?php if (!empty($themeConfig['stylesheet'])): ?>
        <link rel="stylesheet" href="<?= asset($themeConfig['stylesheet']) ?>">
    <?php endif; ?>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 <?= htmlspecialchars($themeConfig['body_class'] ?? '') ?>">
<header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3">
            <?php $logoPath = trim((string)($settings['site_logo_path'] ?? '')); ?>
            <?php if ($logoPath !== ''): ?>
                <img src="<?= BASE_URL . '/' . htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?> Logo" class="h-12 w-12 rounded-2xl border border-slate-200 object-contain bg-white p-1" loading="lazy">
            <?php else: ?>
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500/10 text-lg font-semibold text-brand-500 ring-1 ring-brand-400/40">
                    <?= strtoupper(substr($settings['site_title'] ?? APP_NAME, 0, 2)) ?>
                </span>
            <?php endif; ?>
            <span class="hidden flex-col leading-tight sm:flex">
                <span class="text-lg font-semibold tracking-tight text-slate-900"><?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
                <span class="text-sm text-slate-500"><?= htmlspecialchars($settings['site_tagline'] ?? '') ?></span>
            </span>
        </a>
        <div class="flex items-center gap-3 lg:hidden">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-brand-400 hover:text-brand-500" data-mobile-nav-toggle>
                <span class="sr-only">Navigation umschalten</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <?php
            $navLinkBase = 'inline-flex items-center gap-1 rounded-full border border-transparent px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-brand-500';
            $navLinkActive = 'inline-flex items-center gap-1 rounded-full border border-brand-300 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-600 shadow-glow';
            $dropdownLinkBase = 'block rounded-lg px-3 py-2 text-left text-sm text-slate-600 transition hover:bg-brand-500/10 hover:text-brand-600';
            $dropdownLinkActive = 'block rounded-lg px-3 py-2 text-left bg-brand-500/10 text-brand-600 font-semibold';
            $mobileLinkBase = 'block rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-brand-300 hover:text-brand-600';
            $mobileLinkActive = 'block rounded-2xl border border-brand-300 bg-brand-500/10 px-4 py-2 text-sm font-semibold text-brand-600 shadow-glow';
            $mobileSubBase = 'block rounded-xl px-3 py-2 text-sm text-slate-600 transition hover:bg-brand-500/10 hover:text-brand-600';
            $mobileSubActive = 'block rounded-xl px-3 py-2 text-sm font-semibold text-brand-600 bg-brand-500/10';
        ?>
        <nav class="hidden items-center gap-2 lg:flex" data-desktop-nav>
            <a href="<?= BASE_URL ?>/index.php" class="<?= ($currentRoute === 'home') ? $navLinkActive : $navLinkBase ?>">Start</a>
            <a href="<?= BASE_URL ?>/index.php?route=animals" class="<?= ($currentRoute === 'animals') ? $navLinkActive : $navLinkBase ?>">Tierübersicht</a>
            <a href="<?= BASE_URL ?>/index.php?route=news" class="<?= ($currentRoute === 'news') ? $navLinkActive : $navLinkBase ?>">Neuigkeiten</a>
            <div class="relative" data-nav-group>
                <?php $isCareActive = ($currentRoute === 'care-guide' || $currentRoute === 'care-article'); ?>
                <button type="button" class="<?= $isCareActive ? $navLinkActive : $navLinkBase ?>" data-nav-trigger>
                    Pflegeleitfaden
                    <svg class="h-4 w-4 transition" data-chevron fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                    </svg>
                </button>
                <div class="nav-dropdown rounded-2xl border border-slate-200 bg-white/90 shadow-xl shadow-slate-200/70">
                    <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="<?= ($currentRoute === 'care-guide') ? $dropdownLinkActive : $dropdownLinkBase ?>">Übersicht</a>
                    <?php foreach (($navCareArticles ?? []) as $careNav): ?>
                        <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($careNav['slug']) ?>" class="<?= ($currentRoute === 'care-article' && ($activeCareSlug ?? '') === $careNav['slug']) ? $dropdownLinkActive : $dropdownLinkBase ?>"><?= htmlspecialchars($careNav['title']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?route=genetics" class="<?= ($currentRoute === 'genetics') ? $navLinkActive : $navLinkBase ?>">Genetik Rechner</a>
            <a href="<?= BASE_URL ?>/index.php?route=gallery" class="<?= ($currentRoute === 'gallery') ? $navLinkActive : $navLinkBase ?>">Galerie</a>
            <?php foreach (($navPages ?? []) as $navPage): ?>
                <?php
                    $parentActive = ($currentRoute === 'page' && ($activePageSlug ?? '') === $navPage['slug']);
                    $childActive = false;
                    foreach ($navPage['children'] ?? [] as $childPage) {
                        if ($currentRoute === 'page' && ($activePageSlug ?? '') === $childPage['slug']) {
                            $childActive = true;
                            break;
                        }
                    }
                    $isActive = $parentActive || $childActive;
                ?>
                <div class="relative" data-nav-group>
                    <a href="<?= BASE_URL ?>/index.php?route=page&amp;slug=<?= urlencode($navPage['slug']) ?>" class="<?= $isActive ? $navLinkActive : $navLinkBase ?>">
                        <?= htmlspecialchars($navPage['title']) ?>
                        <?php if (!empty($navPage['children'])): ?>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($navPage['children'])): ?>
                        <div class="nav-dropdown rounded-2xl border border-slate-200 bg-white/90 shadow-xl shadow-slate-200/70">
                            <?php foreach ($navPage['children'] as $childPage): ?>
                                <a href="<?= BASE_URL ?>/index.php?route=page&amp;slug=<?= urlencode($childPage['slug']) ?>" class="<?= ($currentRoute === 'page' && ($activePageSlug ?? '') === $childPage['slug']) ? $dropdownLinkActive : $dropdownLinkBase ?>"><?= htmlspecialchars($childPage['title']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <a href="<?= BASE_URL ?>/index.php?route=adoption" class="<?= ($currentRoute === 'adoption') ? $navLinkActive : $navLinkBase ?>">Tierabgabe</a>
            <?php if (current_user()): ?>
                <a href="<?= BASE_URL ?>/index.php?route=my-animals" class="<?= ($currentRoute === 'my-animals') ? $navLinkActive : $navLinkBase ?>">Meine Tiere</a>
                <a href="<?= BASE_URL ?>/index.php?route=breeding" class="<?= ($currentRoute === 'breeding') ? $navLinkActive : $navLinkBase ?>">Zuchtplanung</a>
                <a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="<?= str_starts_with($currentRoute, 'admin/') ? $navLinkActive : $navLinkBase ?>">Admin</a>
                <a href="<?= BASE_URL ?>/index.php?route=logout" class="<?= $navLinkBase ?>">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?route=login" class="<?= ($currentRoute === 'login') ? $navLinkActive : $navLinkBase ?>">Login</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="hidden lg:hidden" data-mobile-nav-panel>
        <nav class="mx-4 mb-4 space-y-2 rounded-3xl border border-slate-200 bg-white/95 p-4 text-sm shadow-xl shadow-slate-200/70">
            <a href="<?= BASE_URL ?>/index.php" class="<?= ($currentRoute === 'home') ? $mobileLinkActive : $mobileLinkBase ?>">Start</a>
            <a href="<?= BASE_URL ?>/index.php?route=animals" class="<?= ($currentRoute === 'animals') ? $mobileLinkActive : $mobileLinkBase ?>">Tierübersicht</a>
            <a href="<?= BASE_URL ?>/index.php?route=news" class="<?= ($currentRoute === 'news') ? $mobileLinkActive : $mobileLinkBase ?>">Neuigkeiten</a>
            <details class="group" <?= $isCareActive ? 'open' : '' ?>>
                <summary class="<?= $mobileLinkBase ?> flex cursor-pointer list-none items-center justify-between">
                    <span>Pflegeleitfaden</span>
                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>
                </summary>
                <div class="mt-2 space-y-1 pl-3 text-sm">
                    <a href="<?= BASE_URL ?>/index.php?route=care-guide" class="<?= ($currentRoute === 'care-guide') ? $mobileSubActive : $mobileSubBase ?>">Übersicht</a>
                    <?php foreach (($navCareArticles ?? []) as $careNav): ?>
                        <a href="<?= BASE_URL ?>/index.php?route=care-article&amp;slug=<?= urlencode($careNav['slug']) ?>" class="<?= ($currentRoute === 'care-article' && ($activeCareSlug ?? '') === $careNav['slug']) ? $mobileSubActive : $mobileSubBase ?>"><?= htmlspecialchars($careNav['title']) ?></a>
                    <?php endforeach; ?>
                </div>
            </details>
            <a href="<?= BASE_URL ?>/index.php?route=genetics" class="<?= ($currentRoute === 'genetics') ? $mobileLinkActive : $mobileLinkBase ?>">Genetik Rechner</a>
            <a href="<?= BASE_URL ?>/index.php?route=gallery" class="<?= ($currentRoute === 'gallery') ? $mobileLinkActive : $mobileLinkBase ?>">Galerie</a>
            <?php foreach (($navPages ?? []) as $navPage): ?>
                <?php
                    $parentActive = ($currentRoute === 'page' && ($activePageSlug ?? '') === $navPage['slug']);
                    $childActive = false;
                    foreach ($navPage['children'] ?? [] as $childPage) {
                        if ($currentRoute === 'page' && ($activePageSlug ?? '') === $childPage['slug']) {
                            $childActive = true;
                            break;
                        }
                    }
                    $isActive = $parentActive || $childActive;
                ?>
                <details class="group" <?= $isActive ? 'open' : '' ?>>
                    <summary class="<?= $mobileLinkBase ?> flex cursor-pointer list-none items-center justify-between">
                        <span><?= htmlspecialchars($navPage['title']) ?></span>
                        <?php if (!empty($navPage['children'])): ?>
                            <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>
                        <?php endif; ?>
                    </summary>
                    <?php if (!empty($navPage['children'])): ?>
                        <div class="mt-2 space-y-1 pl-3 text-sm">
                            <?php foreach ($navPage['children'] as $childPage): ?>
                                <a href="<?= BASE_URL ?>/index.php?route=page&amp;slug=<?= urlencode($childPage['slug']) ?>" class="<?= ($currentRoute === 'page' && ($activePageSlug ?? '') === $childPage['slug']) ? $mobileSubActive : $mobileSubBase ?>"><?= htmlspecialchars($childPage['title']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </details>
            <?php endforeach; ?>
            <a href="<?= BASE_URL ?>/index.php?route=adoption" class="<?= ($currentRoute === 'adoption') ? $mobileLinkActive : $mobileLinkBase ?>">Tierabgabe</a>
            <?php if (current_user()): ?>
                <a href="<?= BASE_URL ?>/index.php?route=my-animals" class="<?= ($currentRoute === 'my-animals') ? $mobileLinkActive : $mobileLinkBase ?>">Meine Tiere</a>
                <a href="<?= BASE_URL ?>/index.php?route=breeding" class="<?= ($currentRoute === 'breeding') ? $mobileLinkActive : $mobileLinkBase ?>">Zuchtplanung</a>
                <a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="<?= str_starts_with($currentRoute, 'admin/') ? $mobileLinkActive : $mobileLinkBase ?>">Admin</a>
                <a href="<?= BASE_URL ?>/index.php?route=logout" class="<?= $mobileLinkBase ?>">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php?route=login" class="<?= ($currentRoute === 'login') ? $mobileLinkActive : $mobileLinkBase ?>">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="flex-1 pb-16 pt-12">
