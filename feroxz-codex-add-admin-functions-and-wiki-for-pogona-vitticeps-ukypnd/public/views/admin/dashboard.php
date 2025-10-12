<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 pb-16 pt-6 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2.75rem] border border-slate-100/10 bg-gradient-to-r from-orange-900 via-rose-900 to-slate-950 shadow-[0_40px_80px_-40px_rgba(15,23,42,0.9)]">
        <div class="grid gap-8 p-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:gap-12 lg:p-14">
            <div class="space-y-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.35em] text-white/80">Control Hub</span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.4em] text-amber-100">Realtime Snapshot</span>
                </div>
                <div class="space-y-4">
                    <h1 class="text-3xl font-semibold text-white sm:text-4xl">Strategisches Dashboard</h1>
                    <p class="max-w-2xl text-sm leading-relaxed text-amber-100/80 sm:text-base">Plane Tagesaufgaben, verteile Verantwortlichkeiten und behalte Adoptionen, Bestände sowie neue Nachrichten im Blick. Alle Funktionen bleiben wie gewohnt erreichbar – nur das Erlebnis wurde veredelt.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="<?= BASE_URL ?>/index.php?route=admin/animals" class="group flex items-center justify-between rounded-3xl border border-white/10 bg-white/10 px-5 py-4 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:border-amber-200/80 hover:bg-white/15">
                        Tiere verwalten
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?route=admin/news&create=1" class="group flex items-center justify-between rounded-3xl border border-amber-200/80 bg-gradient-to-r from-amber-500/20 via-orange-500/20 to-rose-500/30 px-5 py-4 text-sm font-semibold text-amber-50 shadow-[0_20px_45px_-25px_rgba(248,196,113,0.8)] transition hover:-translate-y-0.5 hover:border-amber-100 hover:from-amber-500/30 hover:to-rose-500/40">
                        Neue News verfassen
                        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="rounded-3xl border border-white/10 bg-black/20 p-5 backdrop-blur">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-100/70">Direktnavigation</h2>
                    <p class="mt-2 text-xs text-white/70">Alle Admin-Module bleiben erhalten – nur das Layout wurde neu orchestriert.</p>
                    <div class="mt-4">
                        <?php include __DIR__ . '/nav.php'; ?>
                    </div>
                </div>
            </div>
            <div class="grid gap-5">
                <?php
                    $metricCards = [
                        ['label' => 'Aktive Tiere', 'value' => count($animals), 'accent' => 'from-emerald-400/70 to-emerald-500/40'],
                        ['label' => 'Abgabe-Inserate', 'value' => count($listings), 'accent' => 'from-sky-400/70 to-sky-500/40'],
                        ['label' => 'Neue Anfragen', 'value' => count($inquiries), 'accent' => 'from-amber-400/70 to-orange-500/40'],
                        ['label' => 'Pflegeartikel', 'value' => count($careArticles), 'accent' => 'from-fuchsia-400/70 to-rose-500/40'],
                    ];
                ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <?php foreach ($metricCards as $metric): ?>
                        <article class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-950/70 p-5 shadow-[0_30px_60px_-50px_rgba(248,250,252,0.7)]">
                            <div class="absolute inset-0 bg-gradient-to-br <?= $metric['accent'] ?> opacity-40"></div>
                            <div class="relative flex flex-col gap-3 text-white">
                                <p class="text-xs uppercase tracking-[0.25em] text-white/60"><?= htmlspecialchars($metric['label']) ?></p>
                                <span class="text-3xl font-semibold leading-none">
                                    <?= htmlspecialchars($metric['value']) ?>
                                </span>
                                <span class="text-[0.65rem] font-medium uppercase tracking-[0.3em] text-white/50">Live Daten</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <article class="relative overflow-hidden rounded-3xl border border-white/10 bg-black/30 p-6 text-sm text-white shadow-[0_30px_60px_-40px_rgba(15,23,42,1)]">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-white/5 to-transparent opacity-70"></div>
                    <div class="relative space-y-4">
                        <h2 class="text-lg font-semibold uppercase tracking-[0.35em] text-white/80">Arbeitslog</h2>
                        <p class="text-xs leading-relaxed text-white/70">Nutze die Links und Karten, um neue Inhalte einzupflegen, Adoptionen zu prüfen oder Pflegeanleitungen anzureichern. Alles bleibt funktional identisch – nur die Oberfläche wurde modernisiert.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <div class="mt-12 grid gap-8 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,0.95fr)]">
        <section class="rounded-[2rem] border border-white/10 bg-slate-950/70 p-8 shadow-[0_50px_80px_-60px_rgba(2,6,23,1)]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-2">
                    <h2 class="text-2xl font-semibold text-white">Letzte Anfragen</h2>
                    <p class="text-sm text-amber-100/70">Alle Kontaktaufnahmen zu aktuellen Adoptionen im Überblick.</p>
                </div>
                <a href="<?= BASE_URL ?>/index.php?route=admin/inquiries" class="inline-flex items-center gap-2 rounded-full border border-white/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white transition hover:border-amber-200 hover:text-amber-100">Alle anzeigen</a>
            </div>
            <div class="mt-6 space-y-4">
                <?php if (empty($inquiries)): ?>
                    <p class="rounded-2xl border border-dashed border-white/15 px-4 py-6 text-sm text-white/70">Noch keine neuen Anfragen eingegangen.</p>
                <?php else: ?>
                    <?php foreach (array_slice($inquiries, 0, 5) as $inquiry): ?>
                        <article class="rounded-2xl border border-white/10 bg-white/5 px-4 py-5 transition hover:-translate-y-0.5 hover:border-amber-200/70">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-white"><?= htmlspecialchars($inquiry['sender_name']) ?></p>
                                    <p class="text-xs text-white/60">zu <?= htmlspecialchars($inquiry['listing_title']) ?></p>
                                </div>
                                <span class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-medium text-amber-100"><?= date('d.m.Y', strtotime($inquiry['created_at'])) ?></span>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-white/75">
                                <a href="mailto:<?= htmlspecialchars($inquiry['sender_email']) ?>" class="inline-flex items-center gap-1 rounded-full border border-white/15 px-3 py-1 transition hover:border-amber-200 hover:text-amber-100">E-Mail senden <span aria-hidden="true">→</span></a>
                                <?php if (!empty($inquiry['message'])):
                                    $messagePreview = $inquiry['message'];
                                    if (function_exists('mb_strimwidth')) {
                                        $messagePreview = mb_strimwidth($messagePreview, 0, 70, '…', 'UTF-8');
                                    } elseif (strlen($messagePreview) > 70) {
                                        $messagePreview = substr($messagePreview, 0, 67) . '…';
                                    }
                                ?>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-black/30 px-3 py-1">Hinweis: <?= htmlspecialchars($messagePreview) ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="space-y-6">
            <article class="rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-900/80 via-slate-950/70 to-black/60 p-8 text-white shadow-[0_50px_80px_-60px_rgba(15,23,42,1)]">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold uppercase tracking-[0.25em] text-white/80">Projektüberblick</h2>
                    <span class="text-[0.65rem] font-semibold uppercase tracking-[0.35em] text-white/50">Automatisch aktualisiert</span>
                </div>
                <dl class="mt-8 space-y-5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-white/70"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-200">CMS</span> Seiten</dt>
                        <dd class="text-2xl font-semibold text-white"><?= count($pages) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-white/70"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-sky-400/15 text-sky-200">NZ</span> Neuigkeiten</dt>
                        <dd class="text-2xl font-semibold text-white"><?= count($newsPosts) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-white/70"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-fuchsia-400/15 text-fuchsia-200">GP</span> Genetik-Arten</dt>
                        <dd class="text-2xl font-semibold text-white"><?= isset($geneticSpecies) ? count($geneticSpecies) : 0 ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-white/70"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-rose-400/15 text-rose-200">GE</span> Gene & Referenzen</dt>
                        <dd class="text-2xl font-semibold text-white"><?= isset($geneticGenes) ? count($geneticGenes) : 0 ?></dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-[2rem] border border-dashed border-white/15 bg-slate-950/50 p-7 text-sm text-white/75 shadow-[0_50px_80px_-70px_rgba(2,6,23,0.9)]">
                <h3 class="text-base font-semibold uppercase tracking-[0.25em] text-white/80">Nächste Schritte</h3>
                <ul class="mt-5 space-y-3">
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span><span>Galerie mit frischen Uploads bespielen, um die Startseite abwechslungsreich zu halten.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-400"></span><span>Neue Morph-Informationen prüfen und bei Bedarf ergänzen.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-rose-400"></span><span>Offene Adoptionen nachtelefonieren und Status im System anpassen.</span></li>
                </ul>
            </article>
        </section>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
