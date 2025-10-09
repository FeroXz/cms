<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
<h1>Tierabgabe verwalten</h1>
<?php include __DIR__ . '/nav.php'; ?>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<div class="grid" style="grid-template-columns:2fr 1fr;gap:2rem;align-items:start;">
    <div class="card">
        <h2>Inserate</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Status</th>
                    <th>Preis</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?= htmlspecialchars($listing['title']) ?></td>
                        <td><?= htmlspecialchars($listing['status']) ?></td>
                        <td><?= htmlspecialchars($listing['price'] ?? 'n/a') ?></td>
                        <td>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/adoption&edit=<?= (int)$listing['id'] ?>">Bearbeiten</a>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/adoption&delete=<?= (int)$listing['id'] ?>" onclick="return confirm('Eintrag löschen?');">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h2><?= $editListing ? 'Inserat bearbeiten' : 'Neues Inserat' ?></h2>
        <form method="post" enctype="multipart/form-data">
            <?php if ($editListing): ?>
                <input type="hidden" name="id" value="<?= (int)$editListing['id'] ?>">
            <?php endif; ?>
            <label>Titel
                <input type="text" name="title" value="<?= htmlspecialchars($editListing['title'] ?? '') ?>" required>
            </label>
            <label>Tier aus Bestand
                <select name="animal_id">
                    <option value="">— unabhängig —</option>
                    <?php foreach ($animals as $animal): ?>
                        <option value="<?= (int)$animal['id'] ?>" <?= (($editListing['animal_id'] ?? '') == $animal['id']) ? 'selected' : '' ?>><?= htmlspecialchars($animal['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Art
                <input type="text" name="species" value="<?= htmlspecialchars($editListing['species'] ?? '') ?>">
            </label>
            <label>Preis
                <input type="text" name="price" value="<?= htmlspecialchars($editListing['price'] ?? '') ?>">
            </label>
            <label>Genetik
                <textarea name="genetics" class="rich-text"><?= htmlspecialchars($editListing['genetics'] ?? '') ?></textarea>
            </label>
            <label>Beschreibung
                <textarea name="description" class="rich-text"><?= htmlspecialchars($editListing['description'] ?? '') ?></textarea>
            </label>
            <label>Status
                <select name="status">
                    <?php foreach (['available' => 'verfügbar', 'reserved' => 'reserviert', 'adopted' => 'vermittelt'] as $key => $label): ?>
                        <option value="<?= $key ?>" <?= (($editListing['status'] ?? 'available') === $key) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Kontakt E-Mail
                <input type="email" name="contact_email" value="<?= htmlspecialchars($editListing['contact_email'] ?? $settings['contact_email'] ?? '') ?>">
            </label>
            <label>Bild
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editListing['image_path'])): ?>
                    <input type="hidden" name="image_path" value="<?= htmlspecialchars($editListing['image_path']) ?>">
                    <p><a href="<?= BASE_URL . '/' . htmlspecialchars($editListing['image_path']) ?>" target="_blank">Aktuelles Bild</a></p>
                <?php endif; ?>
            </label>
            <button type="submit">Speichern</button>
        </form>
    </div>
</div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
