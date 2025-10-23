<?php $isAdminView = isset($currentRoute) && str_starts_with($currentRoute, 'admin/'); ?>
<?php if ($isAdminView): ?>
        </main>
        <footer class="border-t border-slate-800 bg-slate-900/80">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-6 py-8 text-sm text-slate-300 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.4em] text-brand-200">Flowbite Control Deck</p>
                    <p class="text-sm text-slate-400">Alle Module bleiben aktiv – Inhalte, Tiere, Adoptionen und die Nova Suite stehen bereit.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.3em] text-slate-500">
                    <span>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
                    <span aria-hidden="true">•</span>
                    <span>Version <?= htmlspecialchars($settings['cms_version'] ?? '5.5') ?></span>
                </div>
            </div>
        </footer>
    </div>
    <script src="<?= asset('admin.js') ?>" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.4.1/flowbite.min.js" integrity="sha512-SGXasLCPZr7+Ae2/QM8XgP4DoEcSkDN8q80v3Ye6WZLHi1q3YVb7UgVHoj2jXFQ+eIIGb6V9I3R8fq7Pt2B8rg==" crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>
    <script src="<?= asset('ui.js') ?>" defer></script>
</body>
</html>
<?php else: ?>
        </main>
    </div>
    <footer class="relative border-t border-white/10 bg-slate-950/92">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom,rgba(56,189,248,0.18),transparent_65%)]"></div>
        <div class="relative mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-12 text-sm text-white/70 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.38em] text-aurora/70">Version <?= htmlspecialchars($settings['cms_version'] ?? '5.5') ?> • Preline Interface</p>
                <div class="prose prose-invert max-w-none text-white/80">
                    <?= nl2br(htmlspecialchars($settings['footer_text'] ?? '')) ?>
                </div>
            </div>
            <div class="flex flex-col items-start gap-3 text-xs uppercase tracking-[0.3em] text-white/60 sm:items-end">
                <div class="flex flex-wrap items-center gap-2">
                    <span>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
                    <span aria-hidden="true">•</span>
                    <span><?= htmlspecialchars(content_value($settings, 'footer_rights')) ?></span>
                </div>
                <div class="flex items-center gap-3 text-white/50">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.4em] text-white/70">Powered by Preline UI</span>
                </div>
            </div>
        </div>
    </footer>
    <script src="<?= asset('ui.js') ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/preline@2.0.3/dist/preline.min.js" defer></script>
    <?php if (($currentRoute ?? '') === 'genetics'): ?>
        <script src="<?= asset('genetics.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
<?php endif; ?>
