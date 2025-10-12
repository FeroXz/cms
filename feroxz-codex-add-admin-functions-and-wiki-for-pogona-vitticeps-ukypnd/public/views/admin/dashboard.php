<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2.5rem] border border-white/10 bg-gradient-to-br from-night-900 via-night-800 to-slate-900 shadow-2xl shadow-brand-900/40">
        <div class="grid gap-8 p-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:gap-12 lg:p-12">
            <div class="space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-brand-200">Kontrollzentrum</span>
                <h1 class="text-3xl font-semibold text-white sm:text-4xl">FeroxZ Admin-Dashboard</h1>
                <p class="text-base leading-relaxed text-slate-300">Behalte Neuigkeiten, Adoptionen und Genetikdaten an einem Ort im Blick. Nutze die Schnellzugriffe, um neue Einträge zu erstellen oder Offenes abzuschließen.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="<?= BASE_URL ?>/index.php?route=admin/animals" class="flex items-center justify-between rounded-3xl border border-white/5 bg-white/5 px-5 py-4 text-sm font-semibold text-slate-100 transition hover:border-brand-400 hover:bg-brand-500/15">
                        Tiere verwalten
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?route=admin/news&create=1" class="flex items-center justify-between rounded-3xl border border-brand-400/60 bg-brand-500/15 px-5 py-4 text-sm font-semibold text-brand-50 shadow-glow transition hover:border-brand-300 hover:bg-brand-500/25">
                        Neue News verfassen
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <?php include __DIR__ . '/nav.php'; ?>
            </div>
            <div class="rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-lg shadow-black/40">
                <?php
                    $metricCards = [
                        ['label' => 'Aktive Tiere', 'value' => count($animals), 'delta' => '+3 diese Woche'],
                        ['label' => 'Abgabe-Inserate', 'value' => count($listings), 'delta' => ''],
                        ['label' => 'Neue Anfragen', 'value' => count($inquiries), 'delta' => ''],
                        ['label' => 'Pflegeartikel', 'value' => count($careArticles), 'delta' => ''],
                    ];
                ?>
                <div class="space-y-4">
                    <?php foreach ($metricCards as $metric): ?>
                        <article class="rounded-2xl border border-white/5 bg-white/5 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400"><?= htmlspecialchars($metric['label']) ?></p>
                                    <?php if (!empty($metric['delta'])): ?>
                                        <p class="mt-1 text-xs text-emerald-300/80"><?= htmlspecialchars($metric['delta']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="text-2xl font-semibold text-white"><?= htmlspecialchars($metric['value']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
        <section class="rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-xl shadow-black/40">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-white sm:text-2xl">Letzte Anfragen</h2>
                    <p class="text-sm text-slate-400">Direktkontakt für aktuelle Adoptionseinträge</p>
                </div>
                <a href="<?= BASE_URL ?>/index.php?route=admin/inquiries" class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-brand-400 hover:text-brand-100">Alle anzeigen</a>
            </div>
            <div class="mt-6 space-y-4">
                <?php if (empty($inquiries)): ?>
                    <p class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-sm text-slate-300">Noch keine neuen Anfragen eingegangen.</p>
                <?php else: ?>
                    <?php foreach (array_slice($inquiries, 0, 5) as $inquiry): ?>
                        <article class="rounded-2xl border border-white/5 bg-white/5 px-4 py-4 transition hover:border-brand-400/60">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-white"><?= htmlspecialchars($inquiry['sender_name']) ?></p>
                                    <p class="text-xs text-slate-400">zu <?= htmlspecialchars($inquiry['listing_title']) ?></p>
                                </div>
                                <span class="rounded-full bg-brand-500/10 px-3 py-1 text-xs font-medium text-brand-100"><?= date('d.m.Y', strtotime($inquiry['created_at'])) ?></span>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-300">
                                <a href="mailto:<?= htmlspecialchars($inquiry['sender_email']) ?>" class="inline-flex items-center gap-1 rounded-full border border-white/10 px-3 py-1 transition hover:border-brand-300 hover:text-brand-100">E-Mail senden <span aria-hidden="true">→</span></a>
                                <?php if (!empty($inquiry['message'])):
                                    $messagePreview = $inquiry['message'];
                                    if (function_exists('mb_strimwidth')) {
                                        $messagePreview = mb_strimwidth($messagePreview, 0, 70, '…', 'UTF-8');
                                    } elseif (strlen($messagePreview) > 70) {
                                        $messagePreview = substr($messagePreview, 0, 67) . '…';
                                    }
                                ?>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-night-900/60 px-3 py-1">Hinweis: <?= htmlspecialchars($messagePreview) ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="space-y-6">
            <article class="rounded-3xl border border-white/5 bg-night-900/70 p-6 shadow-xl shadow-black/40">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold text-white">Projektüberblick</h2>
                    <span class="text-xs uppercase tracking-wide text-slate-400">Aktualisiert automatisch</span>
                </div>
                <dl class="mt-6 space-y-4 text-sm text-slate-200">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-slate-300"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-500/15 text-brand-100">CMS</span> Seiten</dt>
                        <dd class="text-lg font-semibold text-white"><?= count($pages) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-slate-300"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-200">NZ</span> Neuigkeiten</dt>
                        <dd class="text-lg font-semibold text-white"><?= count($newsPosts) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-slate-300"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500/10 text-indigo-200">GP</span> Genetik-Arten</dt>
                        <dd class="text-lg font-semibold text-white"><?= isset($geneticSpecies) ? count($geneticSpecies) : 0 ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-slate-300"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-fuchsia-500/10 text-fuchsia-200">GE</span> Gene & Referenzen</dt>
                        <dd class="text-lg font-semibold text-white"><?= isset($geneticGenes) ? count($geneticGenes) : 0 ?></dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-3xl border border-dashed border-white/15 bg-night-900/40 p-6 text-sm text-slate-300">
                <h3 class="text-base font-semibold text-white">Nächste Schritte</h3>
                <ul class="mt-4 space-y-2">
                    <li class="flex items-start gap-2"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-brand-400"></span><span>Galerie mit frischen Uploads bespielen, um die Startseite abwechslungsreich zu halten.</span></li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span><span>Neue Morph-Informationen prüfen und bei Bedarf ergänzen.</span></li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-rose-400"></span><span>Offene Adoptionen nachtelefonieren und Status im System anpassen.</span></li>
                </ul>
            </article>
        </section>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
