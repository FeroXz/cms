<?php
    $linkBase = 'inline-flex items-center gap-1 rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-brand-400 hover:bg-brand-500/10 hover:text-brand-100';
    $linkActive = 'inline-flex items-center gap-1 rounded-full border border-brand-400 bg-brand-500/20 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-brand-100 shadow-glow';
?>
<nav class="mt-6 flex flex-wrap gap-2">
    <a href="<?= BASE_URL ?>/index.php?route=admin/dashboard" class="<?= $currentRoute === 'admin/dashboard' ? $linkActive : $linkBase ?>">Übersicht</a>
    <a href="<?= BASE_URL ?>/index.php?route=admin/animals" class="<?= $currentRoute === 'admin/animals' ? $linkActive : $linkBase ?>">Tiere</a>
    <?php if (is_authorized('can_manage_animals')): ?>
        <a href="<?= BASE_URL ?>/index.php?route=admin/breeding" class="<?= $currentRoute === 'admin/breeding' ? $linkActive : $linkBase ?>">Zuchtplanung</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/index.php?route=admin/adoption" class="<?= $currentRoute === 'admin/adoption' ? $linkActive : $linkBase ?>">Tierabgabe</a>
    <a href="<?= BASE_URL ?>/index.php?route=admin/inquiries" class="<?= $currentRoute === 'admin/inquiries' ? $linkActive : $linkBase ?>">Anfragen</a>
    <?php if (is_authorized('can_manage_settings')): ?>
        <a href="<?= BASE_URL ?>/index.php?route=admin/pages" class="<?= $currentRoute === 'admin/pages' ? $linkActive : $linkBase ?>">Seiten</a>
        <a href="<?= BASE_URL ?>/index.php?route=admin/news" class="<?= $currentRoute === 'admin/news' ? $linkActive : $linkBase ?>">Neuigkeiten</a>
        <a href="<?= BASE_URL ?>/index.php?route=admin/care" class="<?= $currentRoute === 'admin/care' ? $linkActive : $linkBase ?>">Pflegeleitfaden</a>
        <a href="<?= BASE_URL ?>/index.php?route=admin/genetics" class="<?= $currentRoute === 'admin/genetics' ? $linkActive : $linkBase ?>">Genetik</a>
        <a href="<?= BASE_URL ?>/index.php?route=admin/settings" class="<?= $currentRoute === 'admin/settings' ? $linkActive : $linkBase ?>">Einstellungen</a>
    <?php endif; ?>
    <?php if (current_user()['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/index.php?route=admin/users" class="<?= $currentRoute === 'admin/users' ? $linkActive : $linkBase ?>">Benutzer</a>
    <?php endif; ?>
</nav>
