(function () {
    if (typeof document === 'undefined') {
        return;
    }

    function createButton(label, title, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'rich-text-btn';
        button.innerHTML = label;
        button.title = title;
        button.addEventListener('click', (event) => {
            event.preventDefault();
            onClick();
        });
        return button;
    }

    function wrapTextarea(textarea) {
        if (textarea.dataset.richTextified) {
            return;
        }
        textarea.dataset.richTextified = 'true';

        const wrapper = document.createElement('div');
        wrapper.className = 'rich-text-wrapper';

        const toolbar = document.createElement('div');
        toolbar.className = 'rich-text-toolbar';

        const editor = document.createElement('div');
        editor.className = 'rich-text-editor';
        editor.contentEditable = 'true';
        editor.innerHTML = textarea.value;

        const commands = [
            { label: '<strong>B</strong>', title: 'Fett', action: () => document.execCommand('bold', false) },
            { label: '<em>I</em>', title: 'Kursiv', action: () => document.execCommand('italic', false) },
            { label: '<u>U</u>', title: 'Unterstrichen', action: () => document.execCommand('underline', false) },
            { label: '&#8226;', title: 'Aufzählung', action: () => document.execCommand('insertUnorderedList', false) },
            { label: '&#35;', title: 'Nummerierung', action: () => document.execCommand('insertOrderedList', false) },
            { label: '&#128279;', title: 'Link einfügen', action: () => {
                const url = window.prompt('Link-Adresse (inkl. https://)');
                if (url) {
                    document.execCommand('createLink', false, url);
                }
            } },
            { label: '&#9003;', title: 'Formatierung löschen', action: () => document.execCommand('removeFormat', false) }
        ];

        commands.forEach((command) => toolbar.appendChild(createButton(command.label, command.title, command.action)));

        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(toolbar);
        wrapper.appendChild(editor);
        wrapper.appendChild(textarea);

        const sync = () => {
            textarea.value = editor.innerHTML.trim();
        };

        editor.addEventListener('input', sync);
        editor.addEventListener('blur', sync);

        const form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', sync);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea.rich-text').forEach(wrapTextarea);
    });
})();

(function () {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.getElementById('morph-import-app');
    if (!root) {
        return;
    }

    const uploadUrl = root.dataset.uploadUrl;
    const sampleUrl = root.dataset.sampleUrl;
    const csrfToken = root.dataset.csrf;
    const fileInput = root.querySelector('[data-element="file-input"]');
    const dropzone = root.querySelector('.morph-import__dropzone');
    const filenameLabel = root.querySelector('[data-element="filename"]');
    const mappingForm = root.querySelector('[data-element="mapping-form"]');
    const mappingSelects = mappingForm ? Array.from(mappingForm.querySelectorAll('select[data-mapping]')) : [];
    const summaryElement = root.querySelector('[data-element="summary"]');
    const previewBody = root.querySelector('[data-element="preview-body"]');
    const previewNote = root.querySelector('[data-element="preview-note"]');
    const dryRunButton = root.querySelector('[data-action="dry-run"]');
    const importButton = root.querySelector('[data-action="import"]');
    const sampleButton = root.querySelector('[data-action="load-sample"]');

    let currentFile = null;
    let isBusy = false;

    const actionLabels = {
        create: 'Neu',
        update: 'Aktualisiert',
        unchanged: 'Unverändert',
        duplicate: 'Duplikat',
        pending: 'Ausstehend',
        sample: 'Beispiel',
        error: 'Fehler'
    };

    function setBusy(state) {
        isBusy = state;
        const disable = state || !currentFile;
        if (dryRunButton) {
            dryRunButton.disabled = disable;
        }
        if (importButton) {
            importButton.disabled = disable;
        }
        if (state) {
            root.classList.add('is-busy');
        } else {
            root.classList.remove('is-busy');
        }
    }

    function parseCsvLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            if (char === '"') {
                if (inQuotes && line[i + 1] === '"') {
                    current += '"';
                    i++;
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        result.push(current.trim());
        return result;
    }

    function readHeaders(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onerror = () => reject(new Error('Datei konnte nicht gelesen werden.'));
            reader.onload = () => {
                const text = typeof reader.result === 'string' ? reader.result : '';
                const lines = text.split(/\r?\n/).filter((line) => line.trim() !== '');
                if (lines.length === 0) {
                    reject(new Error('Datei enthält keine Zeilen.'));
                    return;
                }
                const headers = parseCsvLine(lines[0]).map((value) => value.trim());
                resolve(headers);
            };
            reader.readAsText(file.slice(0, 65536));
        });
    }

    function resetPreview(message) {
        previewBody.innerHTML = `<tr><td colspan="6" class="text-muted">${message}</td></tr>`;
        previewNote.textContent = '';
    }

    function populateMapping(headers) {
        mappingSelects.forEach((select) => {
            const currentValue = select.value;
            while (select.options.length > 1) {
                select.remove(1);
            }
            headers.forEach((header) => {
                const option = document.createElement('option');
                option.value = header;
                option.textContent = header || 'Unbenannte Spalte';
                select.appendChild(option);
            });
            const field = (select.dataset.mapping || '').toLowerCase();
            const defaultHeader = headers.find((header) => header.toLowerCase() === field);
            if (currentValue && headers.includes(currentValue)) {
                select.value = currentValue;
            } else if (defaultHeader) {
                select.value = defaultHeader;
            } else {
                select.value = '';
            }
        });
    }

    function setFilename(name) {
        filenameLabel.textContent = name ? `Ausgewählt: ${name}` : 'Noch keine Datei ausgewählt.';
    }

    function collectMapping() {
        const mapping = {};
        mappingSelects.forEach((select) => {
            const key = select.dataset.mapping;
            const value = select.value;
            if (key && value) {
                mapping[key] = value;
            }
        });
        return mapping;
    }

    function renderSummary(summary, isDryRun) {
        if (!summaryElement) {
            return;
        }
        if (!summary || typeof summary !== 'object') {
            summaryElement.textContent = '';
            return;
        }
        const entries = [
            ['Gesamtzeilen', summary.total ?? 0],
            ['Valide', summary.valid ?? 0],
            ['Neu', summary.created ?? 0],
            ['Aktualisiert', summary.updated ?? 0],
            ['Unverändert', summary.unchanged ?? 0],
            ['Duplikate', summary.duplicates ?? 0],
            ['Übersprungen', summary.skipped ?? 0],
        ];
        const title = isDryRun ? 'Dry-Run Ergebnis' : 'Import Ergebnis';
        const warnings = Array.isArray(summary.warnings) ? summary.warnings : [];
        const errors = Array.isArray(summary.errors) ? summary.errors : [];
        const listItems = entries.map(([label, value]) => `<li><strong>${label}:</strong> ${value}</li>`).join('');
        let details = '';
        if (warnings.length) {
            details += `<div class="text-muted">${warnings.map((item) => `<div>⚠️ ${item}</div>`).join('')}</div>`;
        }
        if (errors.length) {
            details += `<div class="text-error">${errors.map((item) => `<div>❌ ${item}</div>`).join('')}</div>`;
        }
        summaryElement.innerHTML = `<h4>${title}</h4><ul>${listItems}</ul>${details}`;
    }

    function renderPreview(rows, noteText) {
        if (!Array.isArray(rows) || rows.length === 0) {
            resetPreview('Keine Vorschau verfügbar.');
            return;
        }
        const html = rows.map((row) => {
            const action = row.action || 'pending';
            const actionLabel = actionLabels[action] || action;
            const statusClass = `morph-import__status-${action}`;
            const note = row.note ? `<small class="text-muted">${row.note}</small>` : '';
            return `<tr>
                <td>${row.line ?? ''}</td>
                <td>${row.name ? escapeHtml(row.name) : ''}</td>
                <td>${row.species ? escapeHtml(row.species) : ''}</td>
                <td>${row.type ? escapeHtml(row.type) : ''}</td>
                <td>${row.aliases ? escapeHtml(row.aliases) : ''}</td>
                <td><span class="${statusClass}">${actionLabel}</span> ${note}</td>
            </tr>`;
        }).join('');
        previewBody.innerHTML = html;
        previewNote.textContent = noteText || '';
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function showError(message) {
        if (summaryElement) {
            summaryElement.innerHTML = `<p class="text-error">${message}</p>`;
        }
    }

    async function submitImport(isDryRun) {
        if (!currentFile || !uploadUrl) {
            return;
        }
        setBusy(true);
        const mapping = collectMapping();
        const formData = new FormData();
        formData.append('file', currentFile);
        formData.append('_token', csrfToken);
        formData.append('dryRun', String(isDryRun));
        Object.entries(mapping).forEach(([key, value]) => {
            formData.append(`mapping[${key}]`, value);
        });

        try {
            const targetUrl = new URL(uploadUrl, window.location.origin || window.location.href);
            if (isDryRun) {
                targetUrl.searchParams.set('dryRun', 'true');
            } else {
                targetUrl.searchParams.delete('dryRun');
            }
            const response = await fetch(targetUrl.toString(), {
                method: 'POST',
                body: formData,
            });
            const payload = await response.json();
            if (!response.ok || payload.status !== 'ok') {
                throw new Error(payload.message || 'Import fehlgeschlagen.');
            }
            renderSummary(payload.summary, isDryRun);
            renderPreview(payload.preview, isDryRun ? 'Dry-Run Vorschau' : 'Importvorschau');
        } catch (error) {
            console.error(error);
            showError(error.message);
            resetPreview('Keine Vorschau verfügbar.');
        } finally {
            setBusy(false);
        }
    }

    async function loadSamplePreview() {
        if (!sampleUrl) {
            return;
        }
        setBusy(true);
        try {
            const response = await fetch(sampleUrl);
            const payload = await response.json();
            if (!response.ok || payload.status !== 'ok') {
                throw new Error(payload.message || 'Sample konnte nicht geladen werden.');
            }
            renderSummary(null, true);
            renderPreview(payload.rows, 'Beispieldaten');
        } catch (error) {
            console.error(error);
            showError(error.message);
        } finally {
            setBusy(false);
        }
    }

    function onFileSelected(file) {
        currentFile = file;
        setFilename(file ? file.name : '');
        if (!file) {
            setBusy(false);
            resetPreview('Noch keine Daten geladen.');
            return;
        }
        setBusy(true);
        readHeaders(file)
            .then((headers) => {
                populateMapping(headers);
                renderSummary(null, true);
                resetPreview('Datei geladen. Bitte Dry-Run ausführen.');
                setBusy(false);
            })
            .catch((error) => {
                console.error(error);
                showError(error.message);
                setBusy(false);
                currentFile = null;
                setFilename('');
            });
    }

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('drag-active');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('drag-active');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('drag-active');
        if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) {
            onFileSelected(event.dataTransfer.files[0]);
        }
    });

    dropzone.addEventListener('click', () => {
        fileInput?.click();
    });

    dropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput?.click();
        }
    });

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files.length) {
                onFileSelected(fileInput.files[0]);
            }
        });
    }

    if (dryRunButton) {
        dryRunButton.addEventListener('click', () => {
            if (!isBusy) {
                submitImport(true);
            }
        });
    }

    if (importButton) {
        importButton.addEventListener('click', () => {
            if (!isBusy) {
                submitImport(false);
            }
        });
    }

    if (sampleButton) {
        sampleButton.addEventListener('click', () => {
            if (!isBusy) {
                loadSamplePreview();
            }
        });
    }

    loadSamplePreview();
})();

(function () {
    if (typeof document === 'undefined') {
        return;
    }
    const containers = document.querySelectorAll('[data-media-dropzone]');
    if (!containers.length) {
        return;
    }

    function formatBytes(bytes) {
        if (!bytes || bytes <= 0) {
            return '0 B';
        }
        const units = ['B', 'KB', 'MB', 'GB'];
        let value = bytes;
        let unitIndex = 0;
        while (value >= 1024 && unitIndex < units.length - 1) {
            value /= 1024;
            unitIndex += 1;
        }
        const decimals = unitIndex === 0 ? 0 : 1;
        return `${value.toFixed(decimals)} ${units[unitIndex]}`;
    }

    function createToast(container, type, message) {
        if (!container) {
            return;
        }
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.add('is-visible');
        });
        setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 200);
        }, 4000);
    }

    containers.forEach((container) => {
        const uploadUrl = container.dataset.uploadUrl;
        const orderUrl = container.dataset.orderUrl;
        const metaUrl = container.dataset.metaUrl;
        const ownerType = container.dataset.ownerType || '';
        const ownerId = container.dataset.ownerId || '';
        const csrf = container.dataset.csrf || '';
        const list = container.querySelector('[data-media-list]');
        const emptyState = container.querySelector('[data-media-empty]');
        const droparea = container.querySelector('[data-media-droparea]');
        const fileInput = container.querySelector('[data-media-file-input]');
        const chooseButton = container.querySelector('[data-action="choose-files"]');
        const orderButton = container.querySelector('[data-action="save-order"]');
        const toasts = container.querySelector('[data-media-toasts]');
        const disabledBanner = container.querySelector('[data-media-disabled]');

        let mediaItems = [];
        let orderDirty = false;

        try {
            const parsed = JSON.parse(container.dataset.initialMedia || '[]');
            if (Array.isArray(parsed)) {
                mediaItems = parsed;
            }
        } catch (error) {
            mediaItems = [];
        }

        const sortMedia = () => {
            mediaItems.sort((a, b) => {
                const orderA = typeof a.order === 'number' ? a.order : 0;
                const orderB = typeof b.order === 'number' ? b.order : 0;
                if (orderA === orderB) {
                    return (a.id || 0) - (b.id || 0);
                }
                return orderA - orderB;
            });
        };

        const refreshOrderButton = () => {
            if (orderButton) {
                orderButton.disabled = !orderDirty;
            }
        };

        const render = () => {
            if (!list) {
                return;
            }
            list.innerHTML = '';
            if (!mediaItems.length) {
                if (emptyState) {
                    emptyState.hidden = false;
                }
                refreshOrderButton();
                return;
            }
            if (emptyState) {
                emptyState.hidden = true;
            }
            sortMedia();
            mediaItems.forEach((item, index) => {
                item.order = index + 1;
                list.appendChild(createMediaItem(item));
            });
            refreshOrderButton();
        };

        const createMediaItem = (item) => {
            const element = document.createElement('div');
            element.className = 'media-manager__item';
            element.dataset.mediaId = String(item.id);

            const previewUrl = (item.urls && (item.urls.thumb || item.urls.medium || item.urls.original)) || '';
            if (previewUrl) {
                const preview = document.createElement('img');
                preview.className = 'media-manager__thumb';
                preview.src = previewUrl;
                preview.alt = item.alt || '';
                element.appendChild(preview);
            }

            const body = document.createElement('div');
            body.className = 'media-manager__body';

            const meta = document.createElement('div');
            meta.className = 'media-manager__meta';
            meta.textContent = `${item.type || 'Bild'} · ${formatBytes(item.size || 0)}`;
            body.appendChild(meta);

            const altLabel = document.createElement('label');
            altLabel.className = 'media-manager__alt';
            altLabel.textContent = 'Alt-Text';
            const altInput = document.createElement('input');
            altInput.type = 'text';
            altInput.value = item.alt || '';
            altInput.placeholder = 'Beschreibung für Screenreader';
            altInput.addEventListener('change', () => {
                saveAlt(item, altInput.value);
            });
            altLabel.appendChild(altInput);
            body.appendChild(altLabel);

            const controls = document.createElement('div');
            controls.className = 'media-manager__controls';

            const upButton = document.createElement('button');
            upButton.type = 'button';
            upButton.className = 'btn-icon';
            upButton.setAttribute('aria-label', 'Nach oben');
            upButton.textContent = '▲';
            upButton.addEventListener('click', () => moveItem(item.id, -1));
            controls.appendChild(upButton);

            const downButton = document.createElement('button');
            downButton.type = 'button';
            downButton.className = 'btn-icon';
            downButton.setAttribute('aria-label', 'Nach unten');
            downButton.textContent = '▼';
            downButton.addEventListener('click', () => moveItem(item.id, 1));
            controls.appendChild(downButton);

            if (item.urls && item.urls.original) {
                const viewLink = document.createElement('a');
                viewLink.href = item.urls.original;
                viewLink.target = '_blank';
                viewLink.rel = 'noopener noreferrer';
                viewLink.className = 'btn-link';
                viewLink.textContent = 'Anzeigen';
                controls.appendChild(viewLink);
            }

            body.appendChild(controls);
            element.appendChild(body);

            return element;
        };

        const createPlaceholder = (file) => {
            const element = document.createElement('div');
            element.className = 'media-manager__item is-uploading';
            const body = document.createElement('div');
            body.className = 'media-manager__body';
            const meta = document.createElement('div');
            meta.className = 'media-manager__meta';
            meta.textContent = `Lade ${file.name} hoch…`;
            body.appendChild(meta);
            const progress = document.createElement('div');
            progress.className = 'media-manager__progress';
            const bar = document.createElement('div');
            bar.className = 'media-manager__progress-bar';
            progress.appendChild(bar);
            body.appendChild(progress);
            element.appendChild(body);
            return {
                element,
                setProgress(percent) {
                    bar.style.width = `${Math.min(100, Math.max(0, percent))}%`;
                },
            };
        };

        const moveItem = (id, direction) => {
            const index = mediaItems.findIndex((entry) => entry.id === id);
            if (index === -1) {
                return;
            }
            const targetIndex = index + direction;
            if (targetIndex < 0 || targetIndex >= mediaItems.length) {
                return;
            }
            const [item] = mediaItems.splice(index, 1);
            mediaItems.splice(targetIndex, 0, item);
            orderDirty = true;
            render();
        };

        const handleFiles = (files) => {
            files.forEach((file) => {
                if (!(file instanceof File)) {
                    return;
                }
                const placeholder = createPlaceholder(file);
                if (list) {
                    list.prepend(placeholder.element);
                }
                uploadFile(file, placeholder)
                    .then((items) => {
                        placeholder.element.remove();
                        if (Array.isArray(items) && items.length) {
                            items.forEach((item) => mediaItems.push(item));
                            orderDirty = false;
                            render();
                            createToast(toasts, 'success', `${items.length} Datei${items.length === 1 ? '' : 'en'} hochgeladen.`);
                        }
                    })
                    .catch((error) => {
                        placeholder.element.remove();
                        createToast(toasts, 'error', error.message || 'Upload fehlgeschlagen.');
                    });
            });
        };

        const uploadFile = (file, placeholder) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.responseType = 'json';
            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    const percent = Math.round((event.loaded / event.total) * 100);
                    placeholder.setProgress(percent);
                }
            });
            xhr.onload = () => {
                const response = xhr.response;
                if (xhr.status >= 200 && xhr.status < 300 && response && response.status === 'ok') {
                    resolve(response.items || []);
                } else {
                    reject(new Error(response && response.message ? response.message : 'Upload fehlgeschlagen.'));
                }
            };
            xhr.onerror = () => reject(new Error('Netzwerkfehler beim Upload.'));
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('ownerType', ownerType);
            formData.append('ownerId', ownerId);
            formData.append('files[]', file, file.name);
            xhr.send(formData);
        });

        const saveOrder = () => {
            if (!orderDirty) {
                return;
            }
            const payload = {
                _token: csrf,
                ownerType,
                ownerId,
                items: mediaItems.map((item, index) => ({ id: item.id, order: index + 1 })),
            };
            fetch(orderUrl, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })
                .then((response) => response.json().then((json) => ({ json, ok: response.ok })))
                .then(({ json, ok }) => {
                    if (!ok || !json || json.status !== 'ok') {
                        throw new Error(json && json.message ? json.message : 'Sortierung konnte nicht gespeichert werden.');
                    }
                    if (Array.isArray(json.items)) {
                        mediaItems = json.items;
                    }
                    orderDirty = false;
                    render();
                    createToast(toasts, 'success', 'Sortierung gespeichert.');
                })
                .catch((error) => {
                    createToast(toasts, 'error', error.message || 'Sortierung fehlgeschlagen.');
                });
        };

        const saveAlt = (item, alt) => {
            fetch(metaUrl, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ _token: csrf, id: item.id, alt }),
            })
                .then((response) => response.json().then((json) => ({ json, ok: response.ok })))
                .then(({ json, ok }) => {
                    if (!ok || !json || json.status !== 'ok') {
                        throw new Error(json && json.message ? json.message : 'Alt-Text konnte nicht gespeichert werden.');
                    }
                    if (json.item) {
                        const index = mediaItems.findIndex((entry) => entry.id === json.item.id);
                        if (index !== -1) {
                            mediaItems[index] = json.item;
                            render();
                        }
                    }
                    createToast(toasts, 'success', 'Alt-Text aktualisiert.');
                })
                .catch((error) => {
                    createToast(toasts, 'error', error.message || 'Alt-Text konnte nicht gespeichert werden.');
                });
        };

        if (!ownerId) {
            if (fileInput) {
                fileInput.disabled = true;
            }
            if (chooseButton) {
                chooseButton.disabled = true;
            }
            if (droparea) {
                droparea.setAttribute('aria-disabled', 'true');
            }
            if (disabledBanner) {
                disabledBanner.hidden = false;
            }
            render();
            return;
        }

        if (disabledBanner) {
            disabledBanner.hidden = true;
        }

        if (chooseButton) {
            chooseButton.addEventListener('click', () => {
                fileInput?.click();
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                if (fileInput.files) {
                    handleFiles(Array.from(fileInput.files));
                    fileInput.value = '';
                }
            });
        }

        if (droparea) {
            droparea.addEventListener('dragover', (event) => {
                event.preventDefault();
                container.classList.add('media-manager--drag');
            });
            droparea.addEventListener('dragleave', () => {
                container.classList.remove('media-manager--drag');
            });
            droparea.addEventListener('drop', (event) => {
                event.preventDefault();
                container.classList.remove('media-manager--drag');
                if (event.dataTransfer && event.dataTransfer.files) {
                    handleFiles(Array.from(event.dataTransfer.files));
                }
            });
            droparea.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    chooseButton?.click();
                }
            });
        }

        if (orderButton) {
            orderButton.addEventListener('click', saveOrder);
        }

        render();
    });
})();
