<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
<h1>Tiere verwalten</h1>
<?php include __DIR__ . '/nav.php'; ?>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>
<div class="grid" style="grid-template-columns:2fr 1fr;gap:2rem;align-items:start;">
    <div class="card">
        <h2>Bestand</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Eigentümer</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($animals as $animal): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($animal['name']) ?>
                            <?php if (!empty($animal['is_piebald'])): ?>
                                <span class="animal-marker" title="Geschecktes Tier" aria-label="Geschecktes Tier">⬟</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($animal['species']) ?></td>
                        <td><?= htmlspecialchars($animal['owner_name'] ?? '–') ?></td>
                        <td>
                            <?php if ($animal['is_private']): ?>
                                <span class="badge">Privat</span>
                            <?php endif; ?>
                            <?php if ($animal['is_showcased']): ?>
                                <span class="badge">Highlight</span>
                            <?php endif; ?>
                            <?php if (!empty($animal['is_piebald'])): ?>
                                <span class="badge badge-pattern">Gescheckt</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/animals&edit=<?= (int)$animal['id'] ?>">Bearbeiten</a>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?route=admin/animals&delete=<?= (int)$animal['id'] ?>" onclick="return confirm('Tier wirklich löschen?');">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <h2><?= $editAnimal ? 'Tier bearbeiten' : 'Neues Tier' ?></h2>
        <form method="post" enctype="multipart/form-data">
            <?php if ($editAnimal): ?>
                <input type="hidden" name="id" value="<?= (int)$editAnimal['id'] ?>">
            <?php endif; ?>
            <label>Name
                <input type="text" name="name" value="<?= htmlspecialchars($editAnimal['name'] ?? '') ?>" required>
            </label>
            <label>Art
                <input type="text" name="species" value="<?= htmlspecialchars($editAnimal['species'] ?? '') ?>" required>
            </label>
            <label>Alter
                <input type="text" name="age" value="<?= htmlspecialchars($editAnimal['age'] ?? '') ?>">
            </label>
            <label>Genetik
                <textarea name="genetics" class="rich-text"><?= htmlspecialchars($editAnimal['genetics'] ?? '') ?></textarea>
            </label>
            <label>Herkunft
                <input type="text" name="origin" value="<?= htmlspecialchars($editAnimal['origin'] ?? '') ?>">
            </label>
            <label>Besonderheiten
                <textarea name="special_notes" class="rich-text"><?= htmlspecialchars($editAnimal['special_notes'] ?? '') ?></textarea>
            </label>
            <label>Beschreibung
                <textarea name="description" class="rich-text"><?= htmlspecialchars($editAnimal['description'] ?? '') ?></textarea>
            </label>
            <label>Bild
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editAnimal['image_path'])): ?>
                    <input type="hidden" name="image_path" value="<?= htmlspecialchars($editAnimal['image_path']) ?>">
                    <p><a href="<?= BASE_URL . '/' . htmlspecialchars($editAnimal['image_path']) ?>" target="_blank">Aktuelles Bild anzeigen</a></p>
                <?php endif; ?>
            </label>
            <label>Besitzer
                <select name="owner_id">
                    <option value="">— keiner —</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= (int)$user['id'] ?>" <?= (($editAnimal['owner_id'] ?? '') == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_private" value="1" <?= !empty($editAnimal['is_private']) ? 'checked' : '' ?>> Privat
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_showcased" value="1" <?= !empty($editAnimal['is_showcased']) ? 'checked' : '' ?>> In Highlights anzeigen
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;">
                <input type="checkbox" name="is_piebald" value="1" <?= !empty($editAnimal['is_piebald']) ? 'checked' : '' ?>> Als gescheckt markieren
            </label>
            <button type="submit">Speichern</button>
        </form>
    </div>
</div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
