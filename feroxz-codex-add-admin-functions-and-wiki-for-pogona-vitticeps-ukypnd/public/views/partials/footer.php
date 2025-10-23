    </div>
</main>
<footer class="border-t border-white/5 bg-night-900/80 py-10">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <div class="prose prose-invert max-w-none text-slate-300">
            <?= nl2br(htmlspecialchars($settings['footer_text'] ?? '')) ?>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-500">
            <span>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_title'] ?? APP_NAME) ?></span>
            <span aria-hidden="true">•</span>
            <span><?= htmlspecialchars(content_value($settings, 'footer_rights')) ?></span>
        </div>
    </div>
</footer>
<?php if (!empty($updateBanner) && current_user() && is_authorized('can_manage_settings')): ?>
    <div class="update-banner" data-update-banner>
        <div class="update-banner__content">
            <span class="update-banner__title">Aktualisiert auf v<?= htmlspecialchars($updateBanner['version'] ?? '') ?></span>
            <span class="update-banner__meta"><?= htmlspecialchars($updateBanner['created_at'] ?? '') ?></span>
        </div>
        <div class="update-banner__actions">
            <button type="button" class="btn-link" data-update-modal-open>Was ist neu?</button>
            <button type="button" class="btn-link" data-update-dismiss aria-label="Banner schließen">Schließen</button>
        </div>
    </div>
    <div class="update-modal hidden" data-update-modal role="dialog" aria-modal="true" aria-labelledby="update-modal-title">
        <div class="update-modal__backdrop" data-update-modal-overlay></div>
        <div class="update-modal__panel">
            <div class="update-modal__header">
                <h2 id="update-modal-title">Was ist neu?</h2>
                <button type="button" class="btn-link" data-update-modal-close aria-label="Modal schließen">×</button>
            </div>
            <div class="update-modal__body">
                <p class="update-modal__version">Version <?= htmlspecialchars($updateBanner['version'] ?? '') ?> · <?= htmlspecialchars($updateBanner['created_at'] ?? '') ?></p>
                <?php if (!empty($updateBanner['notes'])): ?>
                    <p class="update-modal__notes"><?= nl2br(htmlspecialchars($updateBanner['notes'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($updateBanner['logs'])): ?>
                    <ul class="update-modal__log">
                        <?php foreach (($updateBanner['logs'] ?? []) as $log): ?>
                            <li>
                                <div class="update-modal__log-command">
                                    <code><?= htmlspecialchars($log['command'] ?? '') ?></code>
                                    <span>Exit <?= isset($log['exitCode']) ? (int)$log['exitCode'] : '—' ?><?= !empty($log['simulated']) ? ' · simuliert' : '' ?></span>
                                </div>
                                <?php if (!empty($log['output'])): ?>
                                    <pre><?= htmlspecialchars($log['output']) ?></pre>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="command-palette hidden" data-command-palette>
    <div class="command-backdrop" data-command-close></div>
    <div class="command-panel" role="dialog" aria-modal="true" aria-labelledby="command-title">
        <div class="command-header">
            <label for="command-input" id="command-title" class="sr-only">Schnellsuche</label>
            <input id="command-input" type="search" placeholder="Tiere, Morphe, News oder Wiki durchsuchen…" autocomplete="off" data-command-input>
            <span class="command-hint">Esc</span>
        </div>
        <div class="command-body">
            <ul class="command-results" data-command-results></ul>
            <p class="command-empty" data-command-empty>Keine Ergebnisse gefunden.</p>
        </div>
        <div class="command-footer">Tippe zum Öffnen: <kbd>⌘</kbd>/<kbd>Ctrl</kbd> + <kbd>K</kbd></div>
    </div>
</div>
<script>
    (function () {
        const BASE_URL = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
        const mobileToggle = document.querySelector('[data-mobile-nav-toggle]');
        const mobilePanel = document.querySelector('[data-mobile-nav-panel]');
        if (mobileToggle && mobilePanel) {
            mobileToggle.addEventListener('click', () => {
                mobilePanel.classList.toggle('hidden');
                const expanded = mobileToggle.getAttribute('aria-expanded') === 'true';
                mobileToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            });
        }

        const navEntries = [];

        function setExpanded(entry, expanded) {
            if (!entry) {
                return;
            }
            if (expanded) {
                entry.dropdown.classList.add('open');
            } else {
                entry.dropdown.classList.remove('open');
            }
            entry.trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (entry.chevron) {
                entry.chevron.classList.toggle('rotate-180', expanded);
            }
        }

        function closeAll(exceptEntry) {
            navEntries.forEach((entry) => {
                if (!exceptEntry || entry !== exceptEntry) {
                    setExpanded(entry, false);
                }
            });
        }

        const isMousePointer = (event) => {
            if (!event) {
                return false;
            }

            if (typeof event.pointerType === 'string') {
                return event.pointerType === 'mouse';
            }

            // Fallback for browsers without pointer events
            return !(event.type && event.type.startsWith('touch'));
        };

        document.querySelectorAll('[data-nav-group]').forEach((group) => {
            const trigger = group.querySelector('[data-nav-trigger]');
            const dropdown = group.querySelector('.nav-dropdown');
            if (!trigger || !dropdown) {
                return;
            }
            const chevron = trigger.querySelector('[data-chevron]');
            const entry = { group, trigger, dropdown, chevron };
            navEntries.push(entry);

            trigger.setAttribute('aria-haspopup', 'true');
            trigger.setAttribute('aria-expanded', dropdown.classList.contains('open') ? 'true' : 'false');

            trigger.addEventListener('click', (event) => {
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
                    return;
                }

                if (!dropdown.classList.contains('open')) {
                    event.preventDefault();
                    closeAll(entry);
                    setExpanded(entry, true);
                } else {
                    closeAll();
                }
            });

            trigger.addEventListener('keydown', (event) => {
                if ((event.key === ' ' || event.key === 'Enter') && !dropdown.classList.contains('open')) {
                    event.preventDefault();
                    closeAll(entry);
                    setExpanded(entry, true);
                }
            });

            group.addEventListener('pointerleave', (event) => {
                if (isMousePointer(event)) {
                    setExpanded(entry, false);
                }
            });

            group.addEventListener('mouseleave', (event) => {
                if (!('PointerEvent' in window) && isMousePointer(event)) {
                    setExpanded(entry, false);
                }
            });

            group.addEventListener('focusout', (event) => {
                if (!group.contains(event.relatedTarget)) {
                    setExpanded(entry, false);
                }
            });

            group.addEventListener('keyup', (event) => {
                if (event.key === 'Escape') {
                    setExpanded(entry, false);
                    trigger.focus();
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-nav-group]')) {
                closeAll();
            }
        });

        const updateBannerEl = document.querySelector('[data-update-banner]');
        if (updateBannerEl) {
            const modal = document.querySelector('[data-update-modal]');
            const openBtn = updateBannerEl.querySelector('[data-update-modal-open]');
            const dismissBtn = updateBannerEl.querySelector('[data-update-dismiss]');
            const closeBtn = modal ? modal.querySelector('[data-update-modal-close]') : null;
            const overlay = modal ? modal.querySelector('[data-update-modal-overlay]') : null;

            const toggleBodyScroll = (state) => {
                document.body.classList.toggle('overflow-hidden', state);
            };

            const closeModal = () => {
                if (modal) {
                    modal.classList.add('hidden');
                }
                toggleBodyScroll(false);
            };

            const openModal = () => {
                if (modal) {
                    modal.classList.remove('hidden');
                    const focusTarget = modal.querySelector('button, [href], input, select, textarea, [tabindex]');
                    if (focusTarget) {
                        focusTarget.focus();
                    }
                }
                toggleBodyScroll(true);
            };

            if (openBtn && modal) {
                openBtn.addEventListener('click', openModal);
            }
            if (dismissBtn) {
                dismissBtn.addEventListener('click', () => {
                    updateBannerEl.remove();
                });
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }
            if (overlay) {
                overlay.addEventListener('click', closeModal);
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        }

        const palette = document.querySelector('[data-command-palette]');
        if (palette) {
            const overlay = palette.querySelector('[data-command-close]');
            const input = palette.querySelector('[data-command-input]');
            const resultsContainer = palette.querySelector('[data-command-results]');
            const emptyState = palette.querySelector('[data-command-empty]');
            let abortController = null;

            function closePalette() {
                palette.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                if (input) {
                    input.value = '';
                }
                if (resultsContainer) {
                    resultsContainer.innerHTML = '';
                }
                if (emptyState) {
                    emptyState.classList.add('hidden');
                }
            }

            function renderResults(items) {
                if (!resultsContainer) {
                    return;
                }
                resultsContainer.innerHTML = '';
                if (!items.length) {
                    if (emptyState) {
                        emptyState.classList.remove('hidden');
                    }
                    return;
                }
                if (emptyState) {
                    emptyState.classList.add('hidden');
                }
                items.forEach((item) => {
                    const entry = document.createElement('li');
                    entry.className = 'command-item';
                    entry.innerHTML = `<a href="${item.url}"><span class="command-type">${item.type}</span><span class="command-title">${item.title}</span><span class="command-subtitle">${item.subtitle || ''}</span></a>`;
                    resultsContainer.appendChild(entry);
                });
            }

            async function performSearch(query) {
                if (!resultsContainer) {
                    return;
                }
                if (abortController) {
                    abortController.abort();
                }
                if (!query || query.length < 2) {
                    resultsContainer.innerHTML = '';
                    if (emptyState) {
                        emptyState.classList.add('hidden');
                    }
                    return;
                }
                abortController = new AbortController();
                try {
                    const response = await fetch(`${BASE_URL}/index.php?route=api/search&q=${encodeURIComponent(query)}`, {
                        signal: abortController.signal,
                    });
                    if (!response.ok) {
                        throw new Error('Suche fehlgeschlagen');
                    }
                    const data = await response.json();
                    renderResults(Array.isArray(data.items) ? data.items : []);
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    renderResults([]);
                    if (emptyState) {
                        emptyState.textContent = 'Fehler bei der Suche.';
                        emptyState.classList.remove('hidden');
                    }
                }
            }

            let debounceTimer = null;
            if (input) {
                input.addEventListener('input', function () {
                    const value = this.value.trim();
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => performSearch(value), 180);
                });
            }

            function openPalette() {
                palette.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (input) {
                    input.focus();
                    input.select();
                }
            }

            document.addEventListener('keydown', (event) => {
                const key = event.key.toLowerCase();
                if ((event.metaKey || event.ctrlKey) && key === 'k') {
                    event.preventDefault();
                    openPalette();
                }
                if (key === 'escape' && !palette.classList.contains('hidden')) {
                    closePalette();
                }
            });

            if (overlay) {
                overlay.addEventListener('click', closePalette);
            }
            palette.addEventListener('click', (event) => {
                if (event.target.dataset.commandClose !== undefined) {
                    closePalette();
                }
            });
        }
    })();
</script>
<?php if (($currentRoute ?? '') === 'genetics'): ?>
    <script src="<?= asset('genetics.js') ?>"></script>
<?php endif; ?>
<?php if (isset($currentRoute) && str_starts_with($currentRoute, 'admin/')): ?>
    <script src="<?= asset('admin.js') ?>"></script>
<?php endif; ?>
</body>
</html>
