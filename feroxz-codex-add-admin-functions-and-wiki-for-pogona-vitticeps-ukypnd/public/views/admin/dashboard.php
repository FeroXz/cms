<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-white sm:text-4xl">Admin-Dashboard</h1>
    <?php include __DIR__ . '/nav.php'; ?>

    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <?php
            $metricCards = [
                ['label' => 'Aktive Tiere', 'value' => count($animals), 'accent' => 'from-cyan-400/30 to-cyan-500/10'],
                ['label' => 'Abgabe-Einträge', 'value' => count($listings), 'accent' => 'from-rose-400/30 to-rose-500/10'],
                ['label' => 'Neue Anfragen', 'value' => count($inquiries), 'accent' => 'from-amber-400/30 to-amber-500/10'],
                ['label' => 'Seiten', 'value' => count($pages), 'accent' => 'from-emerald-400/30 to-emerald-500/10'],
                ['label' => 'Neuigkeiten', 'value' => count($newsPosts), 'accent' => 'from-purple-400/30 to-purple-500/10'],
                ['label' => 'Zuchtpläne', 'value' => count($breedingPlans), 'accent' => 'from-indigo-400/30 to-indigo-500/10'],
                ['label' => 'Pflegeartikel', 'value' => count($careArticles), 'accent' => 'from-fuchsia-400/30 to-fuchsia-500/10'],
                ['label' => 'Genetik-Arten', 'value' => isset($geneticSpecies) ? count($geneticSpecies) : 0, 'accent' => 'from-sky-400/30 to-sky-500/10'],
                ['label' => 'Gene', 'value' => isset($geneticGenes) ? count($geneticGenes) : 0, 'accent' => 'from-lime-400/30 to-lime-500/10'],
            ];
        ?>
        <?php foreach ($metricCards as $metric): ?>
            <article class="rounded-3xl border border-white/5 bg-night-900/80 p-6 shadow-xl shadow-brand-600/20 transition hover:shadow-glow">
                <div class="flex items-center justify-between">
                    <p class="text-sm uppercase tracking-wide text-slate-400"><?= htmlspecialchars($metric['label']) ?></p>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br <?= $metric['accent'] ?> text-base font-semibold text-white/90"><?= htmlspecialchars($metric['value']) ?></span>
                </div>
                <div class="mt-4 h-1 rounded-full bg-gradient-to-r <?= $metric['accent'] ?>"></div>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="mt-10">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-white sm:text-3xl">Letzte Anfragen</h2>
            <a href="<?= BASE_URL ?>/index.php?route=admin/inquiries" class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:border-brand-400 hover:text-brand-100">Alle anzeigen</a>
        </div>
        <div class="mt-4 overflow-hidden rounded-3xl border border-white/5 bg-night-900/70 shadow-xl shadow-black/40">
            <?php if (empty($inquiries)): ?>
                <p class="p-6 text-sm text-slate-300">Keine Anfragen vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Tier</th>
                            <th>Name</th>
                            <th>E-Mail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($inquiries, 0, 5) as $inquiry): ?>
                            <tr>
                                <td><?= htmlspecialchars($inquiry['created_at']) ?></td>
                                <td><?= htmlspecialchars($inquiry['listing_title']) ?></td>
                                <td><?= htmlspecialchars($inquiry['sender_name']) ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($inquiry['sender_email']) ?>">Kontakt aufnehmen</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
