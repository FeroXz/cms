(function () {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    const doc = document;

    function ensureToastStack() {
        let stack = doc.querySelector('[data-toast-stack]');
        if (!stack) {
            stack = doc.createElement('div');
            stack.className = 'toast-stack';
            stack.setAttribute('aria-live', 'polite');
            stack.setAttribute('role', 'status');
            stack.dataset.toastStack = 'true';
            doc.body.appendChild(stack);
        }
        return stack;
    }

    function createToastElement(message, type) {
        const toast = doc.createElement('div');
        toast.className = 'toast-message';
        toast.dataset.type = type || 'info';
        toast.innerHTML = `<span>${message}</span>`;
        return toast;
    }

    function showToast(message, type, timeout) {
        const stack = ensureToastStack();
        const toast = createToastElement(message, type);
        stack.appendChild(toast);
        const lifespan = typeof timeout === 'number' ? timeout : 6000;
        window.setTimeout(() => {
            toast.classList.add('is-leaving');
            window.setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, lifespan);
        return toast;
    }

    function transformLegacyAlerts() {
        const alerts = Array.from(doc.querySelectorAll('.alert'));
        if (!alerts.length) {
            return;
        }
        alerts.forEach((alert) => {
            const text = alert.textContent.trim();
            if (!text) {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
                return;
            }
            let type = 'info';
            if (alert.classList.contains('alert-success')) {
                type = 'success';
            } else if (alert.classList.contains('alert-error')) {
                type = 'error';
            } else if (alert.classList.contains('alert-warning')) {
                type = 'warning';
            }
            showToast(text, type);
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        });
    }

    class DropzoneController {
        constructor(root) {
            this.root = root;
            const selector = root.getAttribute('data-dropzone-input');
            this.input = selector ? doc.querySelector(selector) : root.querySelector('input[type="file"]');
            this.progressBar = root.querySelector('[data-dropzone-progress-bar]');
            this.statusLabel = root.querySelector('[data-dropzone-status]');
            this.bind();
        }

        bind() {
            if (!this.input) {
                return;
            }
            const prevent = (event) => {
                event.preventDefault();
                event.stopPropagation();
            };
            ['dragenter', 'dragover'].forEach((eventName) => {
                this.root.addEventListener(eventName, (event) => {
                    prevent(event);
                    this.root.classList.add('dropzone--dragover');
                });
            });
            ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
                this.root.addEventListener(eventName, (event) => {
                    prevent(event);
                    if (eventName !== 'drop') {
                        this.root.classList.remove('dropzone--dragover');
                    }
                });
            });
            this.root.addEventListener('drop', (event) => {
                const files = event.dataTransfer ? event.dataTransfer.files : null;
                this.root.classList.remove('dropzone--dragover');
                if (files && files.length) {
                    try {
                        this.input.files = files;
                    } catch (error) {
                        // Fallback: populate via FileList API is not supported everywhere.
                    }
                    this.simulateProgress(files.length);
                }
            });
            this.input.addEventListener('change', () => {
                const files = this.input.files;
                this.simulateProgress(files ? files.length : 0);
            });
        }

        simulateProgress(count) {
            if (!this.progressBar) {
                return;
            }
            const total = Math.max(1, count);
            let current = 0;
            this.progressBar.style.width = '0%';
            if (this.statusLabel) {
                if (count > 0) {
                    this.statusLabel.textContent = `${count} Datei${count === 1 ? '' : 'en'} ausgewählt`;
                } else {
                    this.statusLabel.textContent = 'Keine Datei gewählt';
                }
            }
            const step = () => {
                current += 10;
                const percent = Math.min(100, current);
                this.progressBar.style.width = `${percent}%`;
                if (percent < 100) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
    }

    function initDropzones() {
        const dropzones = Array.from(doc.querySelectorAll('[data-dropzone]'));
        dropzones.forEach((zone) => new DropzoneController(zone));
    }

    function initLightbox() {
        const triggers = Array.from(doc.querySelectorAll('[data-lightbox]'));
        if (!triggers.length) {
            return;
        }
        const overlay = doc.createElement('div');
        overlay.className = 'lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML = `
            <div class="lightbox__content">
                <button type="button" class="lightbox__close" aria-label="Schließen">×</button>
                <img alt="" loading="lazy">
                <div class="lightbox__caption"></div>
            </div>
        `;
        doc.body.appendChild(overlay);
        const content = overlay.querySelector('.lightbox__content');
        const closeButton = overlay.querySelector('.lightbox__close');
        const image = overlay.querySelector('img');
        const caption = overlay.querySelector('.lightbox__caption');

        const close = () => {
            overlay.classList.remove('is-visible');
            overlay.setAttribute('aria-hidden', 'true');
        };

        const open = (trigger) => {
            const src = trigger.getAttribute('data-lightbox-src') || trigger.getAttribute('data-image');
            const title = trigger.getAttribute('data-lightbox-title') || trigger.getAttribute('data-title') || '';
            const text = trigger.getAttribute('data-lightbox-caption') || trigger.getAttribute('data-caption') || '';
            if (!src) {
                return;
            }
            image.src = src;
            image.alt = title || text || 'Galeriebild';
            caption.innerHTML = '';
            if (title) {
                const heading = doc.createElement('h3');
                heading.textContent = title;
                caption.appendChild(heading);
            }
            if (text) {
                const paragraph = doc.createElement('p');
                paragraph.textContent = text;
                caption.appendChild(paragraph);
            }
            overlay.classList.add('is-visible');
            overlay.removeAttribute('aria-hidden');
            closeButton.focus({ preventScroll: true });
        };

        triggers.forEach((trigger) => {
            const activate = (event) => {
                event.preventDefault();
                open(trigger);
            };
            trigger.addEventListener('click', activate);
            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    activate(event);
                }
            });
        });

        overlay.addEventListener('click', (event) => {
            if (event.target === overlay || !content.contains(event.target)) {
                close();
            }
        });
        closeButton.addEventListener('click', close);
        doc.addEventListener('keydown', (event) => {
            if (overlay.classList.contains('is-visible') && event.key === 'Escape') {
                close();
            }
        });
    }

    class DatatableController {
        constructor(root) {
            this.root = root;
            this.table = root.querySelector('table');
            this.tbody = this.table ? this.table.querySelector('tbody') : null;
            if (!this.table || !this.tbody) {
                return;
            }
            this.rows = Array.from(this.tbody.querySelectorAll('tr')).map((row) => ({
                element: row,
                tokens: row.textContent.toLowerCase(),
                dataset: {
                    species: row.getAttribute('data-species') || '',
                    status: row.getAttribute('data-status') || '',
                    sex: row.getAttribute('data-sex') || '',
                }
            }));
            this.searchField = root.querySelector('[data-datatable-search]');
            this.filterFields = Array.from(root.querySelectorAll('[data-datatable-filter]'));
            this.emptyState = root.querySelector('[data-datatable-empty]');
            this.headers = Array.from(this.table.querySelectorAll('thead th'));
            this.currentSort = null;
            this.bind();
            this.apply();
        }

        bind() {
            if (this.searchField) {
                this.searchField.addEventListener('input', () => this.apply());
            }
            this.filterFields.forEach((field) => {
                field.addEventListener('change', () => this.apply());
            });
            this.headers.forEach((header, index) => {
                header.addEventListener('click', () => {
                    const key = header.getAttribute('data-sort-key');
                    if (!key) {
                        return;
                    }
                    const isActive = this.currentSort && this.currentSort.index === index;
                    const direction = isActive && this.currentSort.direction === 'asc' ? 'desc' : 'asc';
                    this.currentSort = { index, key, direction };
                    this.headers.forEach((other) => other.removeAttribute('data-sort'));
                    header.setAttribute('data-sort', direction);
                    this.apply();
                });
            });
        }

        apply() {
            const query = this.searchField ? this.searchField.value.trim().toLowerCase() : '';
            const filters = {};
            this.filterFields.forEach((field) => {
                const value = field.value;
                const key = field.getAttribute('data-datatable-filter');
                if (value) {
                    filters[key] = value;
                }
            });
            let rows = this.rows.filter((row) => {
                if (query && !row.tokens.includes(query)) {
                    return false;
                }
                return Object.keys(filters).every((key) => {
                    const filterValue = filters[key].toLowerCase();
                    const rowValue = (row.dataset[key] || '').toLowerCase();
                    if (rowValue === '') {
                        return false;
                    }
                    if (rowValue.includes('|') || rowValue.includes(',') || rowValue.includes(' ')) {
                        const parts = rowValue.split(/[|,\s]+/).filter(Boolean);
                        return parts.includes(filterValue);
                    }
                    return rowValue === filterValue;
                });
            });
            if (this.currentSort && this.currentSort.key) {
                const { key, direction } = this.currentSort;
                rows = rows.slice().sort((a, b) => {
                    const aValue = (a.element.getAttribute(`data-${key}`) || '').toLowerCase();
                    const bValue = (b.element.getAttribute(`data-${key}`) || '').toLowerCase();
                    if (aValue === bValue) {
                        return 0;
                    }
                    if (direction === 'asc') {
                        return aValue.localeCompare(bValue, 'de');
                    }
                    return bValue.localeCompare(aValue, 'de');
                });
            }
            this.render(rows);
        }

        render(rows) {
            if (!this.tbody) {
                return;
            }
            const fragment = doc.createDocumentFragment();
            rows.forEach((row) => {
                fragment.appendChild(row.element);
            });
            this.tbody.appendChild(fragment);
            if (this.emptyState) {
                this.emptyState.hidden = rows.length > 0;
            }
            this.rows.forEach((row) => {
                row.element.hidden = !rows.includes(row);
            });
        }
    }

    function initDatatables() {
        const tables = Array.from(doc.querySelectorAll('[data-datatable]'));
        tables.forEach((table) => new DatatableController(table));
    }

    class ModalController {
        constructor(root) {
            this.root = root;
            this.closeButtons = Array.from(root.querySelectorAll('[data-modal-close]'));
            this.bind();
        }

        bind() {
            this.closeButtons.forEach((button) => {
                button.addEventListener('click', () => this.hide());
            });
        }

        show() {
            this.root.classList.add('is-visible');
            this.root.setAttribute('aria-hidden', 'false');
            const focusable = this.root.querySelector('[data-modal-initial-focus]');
            if (focusable) {
                focusable.focus({ preventScroll: true });
            }
        }

        hide() {
            this.root.classList.remove('is-visible');
            this.root.setAttribute('aria-hidden', 'true');
        }
    }

    function initModals() {
        const modals = {};
        Array.from(doc.querySelectorAll('[data-modal]')).forEach((element) => {
            const id = element.getAttribute('id') || element.getAttribute('data-modal');
            if (!id) {
                return;
            }
            modals[id] = new ModalController(element);
        });
        Array.from(doc.querySelectorAll('[data-modal-target]')).forEach((trigger) => {
            const target = trigger.getAttribute('data-modal-target');
            if (!target || !modals[target]) {
                return;
            }
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                modals[target].show();
            });
        });
        doc.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                Object.values(modals).forEach((modal) => modal.hide());
            }
        });
    }

    function exposeAPI() {
        if (!window.cmsUI) {
            window.cmsUI = {};
        }
        window.cmsUI.toast = (message, type, timeout) => showToast(message, type, timeout);
    }

    function init() {
        transformLegacyAlerts();
        initDropzones();
        initLightbox();
        initDatatables();
        initModals();
        exposeAPI();
    }

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
