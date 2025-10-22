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

    function normalizeGeneValue(value) {
        return value
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    class AdminGeneSelector {
        constructor(root) {
            this.root = root;
            this.speciesSlug = root.dataset.speciesGenes || '';
            this.input = root.querySelector('[data-input]');
            this.tagContainer = root.querySelector('[data-tag-container]');
            this.hiddenInputs = root.querySelector('[data-hidden-inputs]');
            this.suggestions = root.querySelector('[data-suggestions]');
            this.clearButton = root.querySelector('[data-clear]');
            this.stateMap = new Map();
            this.searchIndex = [];
            this.currentSuggestions = [];
            this.selections = new Map();

            const payload = this.parseJSON(root.dataset.genePayload) || [];
            this.enabled = Array.isArray(payload) && payload.length > 0 && this.input && this.tagContainer && this.hiddenInputs;

            if (this.enabled) {
                payload.forEach((gene) => {
                    const geneSlug = gene.slug;
                    const stateEntries = new Map();
                    (gene.states || []).forEach((state) => {
                        if (!state || !state.key) {
                            return;
                        }
                        stateEntries.set(state.key, state);
                        const tokens = Array.isArray(state.tokens) ? state.tokens : [];
                        const normalizedTokens = tokens
                            .filter(Boolean)
                            .map((token) => normalizeGeneValue(token))
                            .filter((token) => token.length > 0);
                        normalizedTokens.push(normalizeGeneValue(state.label || ''));
                        normalizedTokens.push(normalizeGeneValue(state.display || ''));
                        this.searchIndex.push({
                            geneSlug,
                            geneName: gene.name || '',
                            stateKey: state.key,
                            stateLabel: state.label || '',
                            stateDisplay: state.display || state.label || gene.name || '',
                            tokens: Array.from(new Set(normalizedTokens.filter(Boolean))),
                        });
                    });
                    this.stateMap.set(geneSlug, stateEntries);
                });

                const defaults = this.parseJSON(root.dataset.selected) || {};
                Object.entries(defaults).forEach(([slug, stateKey]) => {
                    if (this.hasState(slug, stateKey)) {
                        this.selections.set(slug, stateKey);
                    }
                });

                this.bindEvents();
                this.renderTags();
            } else if (this.input) {
                this.input.disabled = true;
            }
        }

        parseJSON(raw) {
            if (!raw) {
                return null;
            }
            try {
                return JSON.parse(raw);
            } catch (error) {
                return null;
            }
        }

        hasState(geneSlug, stateKey) {
            return this.stateMap.has(geneSlug) && this.stateMap.get(geneSlug).has(stateKey);
        }

        getState(geneSlug, stateKey) {
            if (!this.stateMap.has(geneSlug)) {
                return null;
            }
            return this.stateMap.get(geneSlug).get(stateKey) || null;
        }

        bindEvents() {
            if (!this.enabled) {
                return;
            }
            if (this.input) {
                this.input.addEventListener('input', () => {
                    const query = this.input.value.trim();
                    const normalized = normalizeGeneValue(query);
                    if (!normalized) {
                        this.hideSuggestions();
                        return;
                    }
                    const matches = this.searchIndex.filter((entry) => {
                        if (!entry.tokens.length) {
                            return false;
                        }
                        if (entry.stateKey === 'normal' && !this.selections.has(entry.geneSlug)) {
                            return false;
                        }
                        if (entry.stateKey !== 'normal' && this.selections.get(entry.geneSlug) === entry.stateKey) {
                            return false;
                        }
                        return entry.tokens.some((token) => token.includes(normalized));
                    });
                    this.renderSuggestions(matches, query);
                });

                this.input.addEventListener('focus', () => {
                    if (!this.input.value) {
                        return;
                    }
                    const normalized = normalizeGeneValue(this.input.value.trim());
                    if (!normalized) {
                        return;
                    }
                    const matches = this.searchIndex.filter((entry) =>
                        entry.tokens.some((token) => token.includes(normalized))
                    );
                    this.renderSuggestions(matches, this.input.value.trim());
                });

                this.input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        const suggestion = this.currentSuggestions[0];
                        if (suggestion) {
                            event.preventDefault();
                            this.applySuggestion(suggestion);
                        }
                    }
                    if (event.key === 'Escape') {
                        this.hideSuggestions();
                    }
                });

                this.input.addEventListener('blur', () => {
                    window.setTimeout(() => this.hideSuggestions(), 150);
                });
            }

            if (this.clearButton) {
                this.clearButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.selections.clear();
                    this.renderTags();
                    this.hideSuggestions();
                    if (this.input) {
                        this.input.value = '';
                        this.input.focus();
                    }
                });
            }
        }

        setActive(active) {
            const isActive = Boolean(active);
            this.root.hidden = !isActive;
            if (this.input) {
                this.input.disabled = !isActive || !this.enabled;
            }
            if (!isActive) {
                this.hideSuggestions();
            } else if (this.enabled) {
                this.renderTags();
            }
        }

        renderTags() {
            if (!this.enabled || !this.tagContainer || !this.hiddenInputs) {
                return;
            }

            this.tagContainer.innerHTML = '';
            this.hiddenInputs.innerHTML = '';

            if (!this.selections.size) {
                const emptyHint = document.createElement('p');
                emptyHint.className = 'gene-selector-admin__empty';
                emptyHint.textContent = 'Keine Gene ausgewählt – entspricht dem Wildtyp.';
                this.tagContainer.appendChild(emptyHint);
                return;
            }

            const entries = Array.from(this.selections.entries()).sort((a, b) => {
                const stateA = this.getState(a[0], a[1]);
                const stateB = this.getState(b[0], b[1]);
                const labelA = stateA && stateA.display ? stateA.display : stateA && stateA.label ? stateA.label : a[0];
                const labelB = stateB && stateB.display ? stateB.display : stateB && stateB.label ? stateB.label : b[0];
                return labelA.localeCompare(labelB, 'de');
            });

            entries.forEach(([slug, stateKey]) => {
                const state = this.getState(slug, stateKey);
                const label = state && (state.display || state.label) ? (state.display || state.label) : `${slug} (${stateKey})`;
                const chip = document.createElement('span');
                chip.className = 'gene-chip';
                chip.textContent = label;
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'gene-chip__remove';
                removeButton.setAttribute('aria-label', `${label} entfernen`);
                removeButton.textContent = '×';
                removeButton.addEventListener('click', () => {
                    this.selections.delete(slug);
                    this.renderTags();
                    if (this.input) {
                        this.input.focus();
                    }
                });
                chip.appendChild(removeButton);
                this.tagContainer.appendChild(chip);

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `gene_states[${slug}]`;
                hidden.value = stateKey;
                this.hiddenInputs.appendChild(hidden);
            });
        }

        renderSuggestions(matches, query) {
            if (!this.suggestions) {
                return;
            }
            this.currentSuggestions = matches.slice(0, 8);
            this.suggestions.innerHTML = '';
            if (!this.currentSuggestions.length) {
                if (query) {
                    const empty = document.createElement('div');
                    empty.className = 'gene-selector-admin__suggestion gene-selector-admin__suggestion--empty';
                    empty.textContent = 'Keine passenden Einträge gefunden.';
                    this.suggestions.appendChild(empty);
                    this.suggestions.hidden = false;
                } else {
                    this.suggestions.hidden = true;
                }
                return;
            }

            this.currentSuggestions.forEach((entry) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'gene-selector-admin__suggestion';
                const strong = document.createElement('strong');
                strong.textContent = entry.stateLabel || entry.stateDisplay || entry.stateKey;
                const meta = document.createElement('span');
                meta.textContent = entry.geneName;
                button.appendChild(strong);
                if (entry.geneName) {
                    button.appendChild(meta);
                }
                button.addEventListener('click', () => {
                    this.applySuggestion(entry);
                });
                this.suggestions.appendChild(button);
            });

            this.suggestions.hidden = false;
        }

        applySuggestion(entry) {
            if (entry.stateKey === 'normal') {
                this.selections.delete(entry.geneSlug);
            } else {
                this.selections.set(entry.geneSlug, entry.stateKey);
            }
            this.renderTags();
            this.hideSuggestions();
            if (this.input) {
                this.input.value = '';
                this.input.focus();
            }
        }

        hideSuggestions() {
            if (this.suggestions) {
                this.suggestions.hidden = true;
                this.suggestions.innerHTML = '';
            }
            this.currentSuggestions = [];
        }
    }

    function initializeGeneForms() {
        const forms = document.querySelectorAll('[data-gene-form]');
        if (!forms.length) {
            return;
        }

        forms.forEach((form) => {
            const selectors = [];
            form.querySelectorAll('[data-animal-gene-group]').forEach((group) => {
                selectors.push(new AdminGeneSelector(group));
            });

            const speciesSelect = form.querySelector('[data-species-select]');
            const update = () => {
                const activeSlug = speciesSelect ? speciesSelect.value : '';
                selectors.forEach((selector) => {
                    const shouldShow = speciesSelect ? (activeSlug !== '' && selector.speciesSlug === activeSlug) : true;
                    selector.setActive(shouldShow);
                });
            };

            if (speciesSelect) {
                speciesSelect.addEventListener('change', update);
                update();
            } else {
                selectors.forEach((selector) => selector.setActive(true));
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('textarea.rich-text').forEach(wrapTextarea);
        initializeGeneForms();
    });
})();

