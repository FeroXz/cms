<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-[3rem] border border-slate-200/60 bg-gradient-to-br from-sky-50 via-white to-amber-50 shadow-[0_40px_80px_-32px_rgba(15,23,42,0.35)]">
        <div class="pointer-events-none absolute -top-24 -right-28 h-72 w-72 rounded-full bg-gradient-to-br from-cyan-100/40 via-sky-200/40 to-blue-200/40 blur-3xl"></div>
        <div class="grid gap-10 p-8 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)] lg:p-14">
            <div class="space-y-7 text-slate-800">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-900/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Steuerpult</span>
                <div class="space-y-3">
                    <h1 class="text-3xl font-bold text-slate-900 sm:text-4xl">FeroxZ Einsatzzentrale</h1>
                    <p class="max-w-2xl text-base leading-relaxed text-slate-600">Plane deinen Tag im Handumdrehen: Alle Tiere, Inserate, News und Pflegeartikel sind hier nur einen Klick entfernt. Nutze die neuen Übersichtskarten, um Trends zu erkennen und direkt zu handeln.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="<?= BASE_URL ?>/index.php?route=admin/animals" class="group flex items-center justify-between rounded-2xl border border-amber-200 bg-white/70 px-5 py-4 text-sm font-semibold text-amber-900 transition hover:-translate-y-1 hover:border-amber-300 hover:bg-amber-100/80">
                        Tiere verwalten
                        <svg class="h-5 w-5 text-amber-500 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?route=admin/news&create=1" class="group flex items-center justify-between rounded-2xl border border-sky-200 bg-sky-100/70 px-5 py-4 text-sm font-semibold text-sky-900 transition hover:-translate-y-1 hover:border-sky-300 hover:bg-sky-100">
                        Neue News verfassen
                        <svg class="h-5 w-5 text-sky-500 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm shadow-slate-200/70 backdrop-blur">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Navigation</h2>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <?php include __DIR__ . '/nav.php'; ?>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white/75 p-6 shadow-lg shadow-slate-300/50 backdrop-blur">
                    <?php
                        $metricCards = [
                            ['label' => 'Aktive Tiere', 'value' => count($animals), 'delta' => '+3 diese Woche'],
                            ['label' => 'Abgabe-Inserate', 'value' => count($listings), 'delta' => ''],
                            ['label' => 'Neue Anfragen', 'value' => count($inquiries), 'delta' => ''],
                            ['label' => 'Pflegeartikel', 'value' => count($careArticles), 'delta' => ''],
                        ];
                    ?>
                    <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Kennzahlen heute</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <?php foreach ($metricCards as $metric): ?>
                            <article class="flex flex-col justify-between rounded-xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-4 py-5 text-slate-700 shadow-inner shadow-slate-200">
                                <div class="text-xs font-medium uppercase tracking-wide text-slate-400"><?= htmlspecialchars($metric['label']) ?></div>
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-3xl font-semibold text-slate-900"><?= htmlspecialchars($metric['value']) ?></span>
                                    <?php if (!empty($metric['delta'])): ?>
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"><?= htmlspecialchars($metric['delta']) ?></span>
                                    <?php else: ?>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Live</span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-5 text-sm text-slate-600 shadow-sm backdrop-blur">
                    <p class="font-medium text-slate-700">Tipp des Tages</p>
                    <p class="mt-2 leading-relaxed">Plane jeden Morgen ein kurzes Check-in: Aktualisiere Inserate, beantworte offene Nachrichten und notiere neue Beobachtungen aus den Terrarien.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-12 grid gap-8 xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.95fr)]">
        <section class="rounded-[2.5rem] border border-slate-200/70 bg-white/85 p-8 shadow-[0_25px_60px_-40px_rgba(15,23,42,0.55)] backdrop-blur">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Letzte Anfragen</h2>
                    <p class="text-sm text-slate-500">Direktkontakt zu aktuellen Adoptionseinträgen in chronologischer Reihenfolge.</p>
                </div>
                <a href="<?= BASE_URL ?>/index.php?route=admin/inquiries" class="inline-flex items-center gap-2 rounded-full border border-slate-300/80 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:-translate-y-0.5 hover:border-slate-400 hover:text-slate-800">Alle anzeigen</a>
            </div>
            <div class="mt-6 space-y-5">
                <?php if (empty($inquiries)): ?>
                    <p class="rounded-2xl border border-dashed border-slate-300/70 bg-slate-50/70 px-5 py-6 text-sm text-slate-600">Noch keine neuen Anfragen eingegangen.</p>
                <?php else: ?>
                    <?php foreach (array_slice($inquiries, 0, 5) as $inquiry): ?>
                        <article class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-slate-50 to-slate-100 px-5 py-5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-400/20">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-base font-semibold text-slate-900"><?= htmlspecialchars($inquiry['sender_name']) ?></p>
                                    <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">Anfrage zu <?= htmlspecialchars($inquiry['listing_title']) ?></p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"><?= date('d.m.Y', strtotime($inquiry['created_at'])) ?></span>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-600">
                                <a href="mailto:<?= htmlspecialchars($inquiry['sender_email']) ?>" class="inline-flex items-center gap-1 rounded-full border border-slate-300/80 px-3 py-1 font-medium text-slate-700 transition hover:border-slate-400 hover:bg-white">E-Mail senden <span aria-hidden="true">→</span></a>
                                <?php if (!empty($inquiry['message'])):
                                    $messagePreview = $inquiry['message'];
                                    if (function_exists('mb_strimwidth')) {
                                        $messagePreview = mb_strimwidth($messagePreview, 0, 70, '…', 'UTF-8');
                                    } elseif (strlen($messagePreview) > 70) {
                                        $messagePreview = substr($messagePreview, 0, 67) . '…';
                                    }
                                ?>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-slate-700 shadow-inner shadow-slate-200">Hinweis: <?= htmlspecialchars($messagePreview) ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="space-y-7">
            <article class="rounded-[2rem] border border-slate-200/70 bg-white/80 p-6 shadow-[0_25px_60px_-45px_rgba(15,23,42,0.55)] backdrop-blur">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Projektüberblick</h2>
                    <span class="text-xs uppercase tracking-[0.3em] text-slate-500">Live synchronisiert</span>
                </div>
                <dl class="mt-6 grid gap-5 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <dt class="flex items-center gap-3 font-medium text-slate-700"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-amber-600">CMS</span> Seiten</dt>
                        <dd class="text-xl font-semibold text-slate-900"><?= count($pages) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <dt class="flex items-center gap-3 font-medium text-slate-700"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-sky-100 text-sky-600">NZ</span> Neuigkeiten</dt>
                        <dd class="text-xl font-semibold text-slate-900"><?= count($newsPosts) ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <dt class="flex items-center gap-3 font-medium text-slate-700"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-violet-100 text-violet-600">GP</span> Genetik-Arten</dt>
                        <dd class="text-xl font-semibold text-slate-900"><?= isset($geneticSpecies) ? count($geneticSpecies) : 0 ?></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <dt class="flex items-center gap-3 font-medium text-slate-700"><span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-rose-100 text-rose-600">GE</span> Gene &amp; Referenzen</dt>
                        <dd class="text-xl font-semibold text-slate-900"><?= isset($geneticGenes) ? count($geneticGenes) : 0 ?></dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-[2rem] border border-dashed border-slate-300/70 bg-gradient-to-br from-slate-50 via-white to-emerald-50 p-6 text-sm text-slate-600 shadow-inner shadow-slate-200">
                <h3 class="text-base font-semibold text-slate-900">Nächste Schritte</h3>
                <ul class="mt-4 space-y-3">
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-400"></span><span>Galerie mit frischen Uploads bespielen, um die Startseite abwechslungsreich zu halten.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span><span>Neue Morph-Informationen prüfen und bei Bedarf ergänzen.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-1 h-2.5 w-2.5 rounded-full bg-rose-400"></span><span>Offene Adoptionen nachtelefonieren und Status im System anpassen.</span></li>
                </ul>
            </article>
        </section>
    </div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
