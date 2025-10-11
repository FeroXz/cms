<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8">
<h1>Einstellungen</h1>
<?php include __DIR__ . '/nav.php'; ?>
<?php if ($flashSuccess): ?>
    <div class="alert alert-success" role="status" aria-live="polite"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<div class="card">
    <form method="post">
        <?= csrf_field() ?>
        <?php $themes = get_available_themes(); ?>
        <label>Seitentitel
            <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>">
        </label>
        <label>Untertitel
            <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>">
        </label>
        <label>Hero-Einleitung
            <textarea name="hero_intro" class="rich-text"><?= htmlspecialchars($settings['hero_intro'] ?? '') ?></textarea>
        </label>
        <label>Abgabe Intro
            <textarea name="adoption_intro" class="rich-text"><?= htmlspecialchars($settings['adoption_intro'] ?? '') ?></textarea>
        </label>
        <label>Footer Text
            <textarea name="footer_text" class="rich-text"><?= htmlspecialchars($settings['footer_text'] ?? '') ?></textarea>
        </label>
        <label>Kontakt E-Mail
            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
        </label>
        <label>Design
            <select name="active_theme">
                <?php foreach ($themes as $key => $theme): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= (($settings['active_theme'] ?? 'aurora') === $key) ? 'selected' : '' ?>><?= htmlspecialchars($theme['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <fieldset class="form-fieldset">
            <legend>Startseiten-Sektionen</legend>
            <p class="text-muted" style="font-size:0.85rem;margin-bottom:0.75rem;">Lege fest, welche Bereiche auf der öffentlichen Startseite sichtbar sind.</p>
            <?php
                $homeSectionFlags = [
                    'home_show_hero' => 'Hero-Bereich & Highlights',
                    'home_show_animals' => 'Tier-Highlights',
                    'home_show_adoption' => 'Adoption & Abgabe',
                    'home_show_news' => 'Neuigkeiten',
                    'home_show_care' => 'Pflegeartikel',
                ];
            ?>
            <div class="grid" style="gap:0.5rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <?php foreach ($homeSectionFlags as $flag => $label): ?>
                    <label style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="hidden" name="<?= htmlspecialchars($flag) ?>" value="0">
                        <input type="checkbox" name="<?= htmlspecialchars($flag) ?>" value="1" <?= setting_enabled($settings, $flag) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <button type="submit">Speichern</button>
    </form>
</div>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
