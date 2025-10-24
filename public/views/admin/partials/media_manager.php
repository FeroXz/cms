<?php
$componentTitle = $componentTitle ?? 'Medien verwalten';
$ownerType = $ownerType ?? '';
$ownerId = isset($ownerId) ? $ownerId : null;
$uploadToken = $uploadToken ?? csrf_token();
$mediaItems = $mediaItems ?? [];
$initialJson = htmlspecialchars(json_encode($mediaItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<div
    class="media-manager"
    data-media-dropzone
    data-upload-url="<?= BASE_URL ?>/index.php?route=api/upload"
    data-order-url="<?= BASE_URL ?>/index.php?route=api/media/order"
    data-meta-url="<?= BASE_URL ?>/index.php?route=api/media/meta"
    data-owner-type="<?= htmlspecialchars((string)$ownerType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
    data-owner-id="<?= $ownerId !== null ? (int)$ownerId : '' ?>"
    data-csrf="<?= htmlspecialchars($uploadToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
    data-initial-media="<?= $initialJson ?>"
>
    <div class="media-manager__header">
        <h3><?= htmlspecialchars($componentTitle) ?></h3>
        <button type="button" class="btn btn-secondary" data-action="save-order" disabled>Sortierung speichern</button>
    </div>
    <div class="media-manager__dropzone" data-media-droparea tabindex="0" role="group" aria-label="Medien hochladen">
        <p class="media-manager__hint">Ziehe Bilder hierher oder klicke auf „Dateien auswählen“.</p>
        <p class="media-manager__subhint">Erlaubt: JPG, PNG, WebP &middot; max. 20&nbsp;MB pro Datei</p>
        <div class="media-manager__actions">
            <button type="button" class="btn" data-action="choose-files">Dateien auswählen</button>
        </div>
        <div class="media-manager__disabled" data-media-disabled <?= $ownerId ? 'hidden' : '' ?>>
            <p>Speichere zuerst den Datensatz, um Medien zuzuweisen.</p>
        </div>
    </div>
    <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-media-file-input>
    <div class="media-manager__empty" data-media-empty <?= $mediaItems ? 'hidden' : '' ?>>
        <p>Noch keine Medien vorhanden.</p>
    </div>
    <div class="media-manager__list" data-media-list></div>
    <div class="media-manager__toasts" data-media-toasts aria-live="polite"></div>
    <noscript>
        <p class="media-manager__noscript">Für den Medien-Upload wird JavaScript benötigt.</p>
    </noscript>
</div>
<?php
unset($componentTitle, $ownerType, $ownerId, $uploadToken, $mediaItems, $initialJson);
?>
