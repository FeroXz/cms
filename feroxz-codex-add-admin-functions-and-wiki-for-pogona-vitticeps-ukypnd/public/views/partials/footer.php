    </div>
</main>
<footer class="border-t border-white/5 bg-night-900/80 py-10">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <div class="prose prose-invert max-w-none text-slate-300">
            <?= nl2br(htmlspecialchars($settings['footer_text'] ?? '')) ?>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-500">
            <span>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
            <span aria-hidden="true">•</span>
            <span>Alle Rechte vorbehalten.</span>
        </div>
    </div>
</footer>
<script>
    (function () {
        const mobileToggle = document.querySelector('[data-mobile-nav-toggle]');
        const mobilePanel = document.querySelector('[data-mobile-nav-panel]');
        if (mobileToggle && mobilePanel) {
            mobileToggle.addEventListener('click', () => {
                mobilePanel.classList.toggle('hidden');
                const expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
                mobileToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            });
        }

        document.querySelectorAll('[data-nav-group]').forEach((group) => {
            const trigger = group.querySelector('[data-nav-trigger]');
            const dropdown = group.querySelector('.nav-dropdown');
            if (!trigger || !dropdown) {
                return;
            }
            trigger.setAttribute('aria-haspopup', 'true');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                const isOpen = dropdown.classList.toggle('open');
                trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
            group.addEventListener('mouseleave', () => {
                dropdown.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            });
            group.addEventListener('keyup', (event) => {
                if (event.key === 'Escape') {
                    dropdown.classList.remove('open');
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.focus();
                }
            });
        });
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
