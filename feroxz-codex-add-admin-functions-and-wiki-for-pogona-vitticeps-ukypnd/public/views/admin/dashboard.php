<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
<h1>Admin-Dashboard</h1>
<?php include __DIR__ . '/nav.php'; ?>
<div class="grid cards dashboard-kpis">
    <div class="card">
        <h3>Tiere gesamt</h3>
        <p class="kpi-value"><?= count($animals) ?></p>
        <p class="kpi-meta">davon <?= (int)($animalStatusCounts['available'] ?? 0) ?> verfügbar</p>
    </div>
    <div class="card">
        <h3>Abgabe offen</h3>
        <p class="kpi-value"><?= (int)($listingStatusCounts['available'] ?? 0) ?></p>
        <p class="kpi-meta">reserviert: <?= (int)($listingStatusCounts['reserved'] ?? 0) ?></p>
    </div>
    <div class="card">
        <h3>Vermittelt</h3>
        <p class="kpi-value"><?= (int)($listingStatusCounts['adopted'] ?? 0) ?></p>
        <p class="kpi-meta">verkauft: <?= (int)($animalStatusCounts['sold'] ?? 0) ?></p>
    </div>
    <div class="card">
        <h3>Neue Medien</h3>
        <p class="kpi-value"><?= (int)$recentMediaCount ?></p>
        <p class="kpi-meta">letzte 7 Tage</p>
    </div>
</div>

<section style="margin-top:2rem;">
    <h2>Letzte Uploads</h2>
    <div class="card">
        <?php if (empty($recentMedia)): ?>
            Keine neuen Uploads in den letzten sieben Tagen.
        <?php else: ?>
            <div class="recent-media-grid">
                <?php foreach ($recentMedia as $media): ?>
                    <article class="recent-media-item">
                        <?php $thumb = $media['urls']['thumb'] ?? $media['urls']['medium'] ?? $media['urls']['original']; ?>
                        <?php if ($thumb): ?>
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($media['alt'] ?? $media['fileName']) ?>" loading="lazy">
                        <?php endif; ?>
                        <div class="recent-media-meta">
                            <h3><?= htmlspecialchars($media['fileName']) ?></h3>
                            <?php if (!empty($media['ownerType'])): ?>
                                <p><?= htmlspecialchars(ucfirst($media['ownerType'])) ?><?= $media['ownerId'] ? ' #' . (int)$media['ownerId'] : '' ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section style="margin-top:2rem;">
    <h2>Letzte Anfragen</h2>
    <div class="card">
        <?php if (empty($inquiries)): ?>
            Keine Anfragen vorhanden.
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
