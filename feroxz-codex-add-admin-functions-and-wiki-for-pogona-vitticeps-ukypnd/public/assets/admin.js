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
