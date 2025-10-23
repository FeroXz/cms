<?php
    $linkBase = 'inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-[0.7rem] font-semibold uppercase tracking-wide text-slate-200 transition hover:border-brand-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-slate-900 lg:px-5 lg:py-2.5';
    $linkActive = 'inline-flex items-center justify-center gap-2 rounded-xl border border-brand-400 bg-brand-500/20 px-4 py-2 text-[0.7rem] font-semibold uppercase tracking-wide text-brand-100 shadow-flow focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-slate-900 lg:px-5 lg:py-2.5';
    $currentUser = current_user();
?>
<div id="admin-nav-panel" class="hidden w-full lg:block">
    <nav class="mt-6 mb-8 w-full rounded-2xl border border-slate-800 bg-slate-900/70 p-4 shadow-[0_25px_70px_rgba(15,23,42,0.45)] backdrop-blur">
        <ul class="flex flex-col gap-2 lg:flex-row lg:flex-wrap lg:items-center" role="list">
            <li><a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="<?= $currentRoute === 'admin/dashboard' ? $linkActive : $linkBase ?>">Übersicht</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?route=admin/animals" class="<?= $currentRoute === 'admin/animals' ? $linkActive : $linkBase ?>">Tiere</a></li>
            <?php if (is_authorized('can_manage_animals')): ?>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/breeding" class="<?= $currentRoute === 'admin/breeding' ? $linkActive : $linkBase ?>">Zuchtplanung</a></li>
            <?php endif; ?>
            <li><a href="<?= BASE_URL ?>/index.php?route=admin/adoption" class="<?= $currentRoute === 'admin/adoption' ? $linkActive : $linkBase ?>">Tierabgabe</a></li>
            <li><a href="<?= BASE_URL ?>/index.php?route=admin/inquiries" class="<?= $currentRoute === 'admin/inquiries' ? $linkActive : $linkBase ?>">Anfragen</a></li>
            <?php if (is_authorized('can_manage_settings')): ?>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/pages" class="<?= $currentRoute === 'admin/pages' ? $linkActive : $linkBase ?>">Seiten</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/news" class="<?= $currentRoute === 'admin/news' ? $linkActive : $linkBase ?>">Neuigkeiten</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/care" class="<?= $currentRoute === 'admin/care' ? $linkActive : $linkBase ?>">Pflegeleitfaden</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/genetics" class="<?= $currentRoute === 'admin/genetics' ? $linkActive : $linkBase ?>">Genetik</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/gallery" class="<?= $currentRoute === 'admin/gallery' ? $linkActive : $linkBase ?>">Galerie</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/cms" class="<?= $currentRoute === 'admin/cms' ? $linkActive : $linkBase ?>">Nova Suite</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/settings" class="<?= $currentRoute === 'admin/settings' ? $linkActive : $linkBase ?>">Einstellungen</a></li>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/content" class="<?= $currentRoute === 'admin/content' ? $linkActive : $linkBase ?>">Texte</a></li>
            <?php endif; ?>
            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/index.php?route=admin/users" class="<?= $currentRoute === 'admin/users' ? $linkActive : $linkBase ?>">Benutzer</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
