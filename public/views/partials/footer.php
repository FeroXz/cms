    </div>
</main>
<footer class="relative border-t border-white/10 bg-slate-950/95 py-12">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom,rgba(168,85,247,0.18),transparent_60%)]"></div>
    <div class="relative mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 text-sm text-white/70 sm:flex-row sm:items-center sm:justify-between">
        <div class="max-w-2xl space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.4em] text-aurora/70"><?= htmlspecialchars($settings['cms_version'] ?? '5.3') ?> Nova</p>
            <div class="text-sm leading-relaxed text-white/80">
                <?= nl2br(htmlspecialchars($settings['footer_text'] ?? '')) ?>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.3em] text-white/60">
            <span>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
            <span aria-hidden="true">•</span>
            <span><?= htmlspecialchars(content_value($settings, 'footer_rights')) ?></span>
        </div>
    </div>
</footer>
<script src="<?= asset('ui.js') ?>" defer></script>
<script>
    (function () {
        const mobileToggle = document.querySelector('[data-mobile-nav-toggle]');
        const mobilePanel = document.querySelector('[data-mobile-nav-panel]');
        if (mobileToggle && mobilePanel) {
            mobilePanel.classList.add('hidden');
            mobileToggle.addEventListener('click', () => {
                const isHidden = mobilePanel.classList.toggle('hidden');
                mobileToggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
            });
        }
    })();
</script>
<?php if (($currentRoute ?? '') === 'genetics'): ?>
    <script src="<?= asset('genetics.js') ?>"></script>
<?php endif; ?>
<?php if (isset($currentRoute) && str_starts_with($currentRoute, 'admin/')): ?>
    <script src="<?= asset('admin.js') ?>"></script>
<?php endif; ?>
</body>
</html>
