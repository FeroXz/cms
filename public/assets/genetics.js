(function () {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.querySelector('[data-genetic-selector]');
    const context = window.GENETICS_PAGE_CONTEXT || {};
    const geneData = Array.isArray(context.genes) ? context.genes : [];

    if (!root || geneData.length === 0) {
        return;
    }

    const parentKeys = ['parent1', 'parent2'];
    const errorPanel = root.querySelector('[data-form-error]');

    const genesById = new Map();
    const genesBySlug = new Map();
    const geneSearchIndex = [];
    const animalsById = new Map();
    const animalSearchIndex = [];
    const morphsById = new Map();
    const morphSearchIndex = [];

    const selections = {
        parent1: buildSelectionMap(context.parentSelections?.parent1),
        parent2: buildSelectionMap(context.parentSelections?.parent2),
    };

    const morphSelections = {
        parent1: buildMorphSet(context.parentMorphSelections?.parent1),
        parent2: buildMorphSet(context.parentMorphSelections?.parent2),
    };

    const parentSources = {
        parent1: normalizeSource(context.parentSources?.parent1),
        parent2: normalizeSource(context.parentSources?.parent2),
    };

    const parentAnimals = {
        parent1: normalizeId(context.parentAnimals?.parent1),
        parent2: normalizeId(context.parentAnimals?.parent2),
    };

    const resultsExport = context.resultsExport || null;
    const resultText = typeof context.resultText === 'string' ? context.resultText : '';

    geneData.forEach((gene) => {
        genesById.set(gene.id, gene);
        if (gene.slug) {
            genesBySlug.set(gene.slug, gene);
        }
        (gene.states || []).forEach((state) => {
            const tokens = Array.isArray(state.searchTokens) ? state.searchTokens.slice() : [];
            tokens.push(gene.name || '');
            if (gene.shorthand) {
                tokens.push(gene.shorthand);
            }
            const normalizedTokens = tokens
                .filter(Boolean)
                .map((token) => normalize(token));
            geneSearchIndex.push({
                geneId: gene.id,
                stateKey: state.key,
                stateLabel: state.label,
                geneName: gene.name,
                display: `${state.label} – ${gene.name}`,
                tokens: Array.from(new Set(normalizedTokens)),
            });
        });
    });

    (Array.isArray(context.animals) ? context.animals : []).forEach((animal) => {
        const id = Number(animal.id);
        if (!Number.isFinite(id)) {
            return;
        }
        animalsById.set(id, animal);
        const tokens = [animal.name, animal.genetics, animal.species]
            .concat(Object.keys(animal.genetics_profile || {}))
            .filter(Boolean)
            .map((value) => normalize(value));
        animalSearchIndex.push({
            id,
            display: `${animal.name}${animal.genetics ? ` · ${animal.genetics}` : ''}`,
            tokens: Array.from(new Set(tokens)),
        });
    });

    (Array.isArray(context.morphs) ? context.morphs : []).forEach((morph) => {
        const id = Number(morph.id);
        if (!Number.isFinite(id)) {
            return;
        }
        morphsById.set(id, morph);
        const tokens = [morph.name, morph.type]
            .concat(morph.aliases || [])
            .filter(Boolean)
            .map((value) => normalize(value));
        morphSearchIndex.push({
            id,
            name: morph.name,
            type: morph.type,
            tokens: Array.from(new Set(tokens)),
        });
    });

    function normalize(value) {
        return value
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function normalizeStateKey(state) {
        const value = String(state || '').toLowerCase();
        if (value === 'homozygous' || value === 'super') {
            return 'homozygous';
        }
        if (value === 'heterozygous' || value === 'het') {
            return 'heterozygous';
        }
        return 'normal';
    }

    function normalizeSource(value) {
        return value === 'animal' ? 'animal' : 'manual';
    }

    function normalizeId(value) {
        const numeric = Number(value);
        return Number.isFinite(numeric) && numeric > 0 ? numeric : null;
    }

    function buildSelectionMap(defaults) {
        const map = new Map();
        if (!defaults) {
            return map;
        }
        Object.entries(defaults).forEach(([geneId, stateKey]) => {
            const id = Number(geneId);
            if (!genesById.has(id)) {
                return;
            }
            const state = findState(id, stateKey);
            if (state) {
                map.set(id, state.key);
            }
        });
        return map;
    }

    function buildMorphSet(defaults) {
        const set = new Set();
        if (!Array.isArray(defaults)) {
            return set;
        }
        defaults.forEach((value) => {
            const id = Number(value);
            if (Number.isFinite(id)) {
                set.add(id);
            }
        });
        return set;
    }

    function findState(geneId, stateKey) {
        const gene = genesById.get(geneId);
        if (!gene) {
            return null;
        }
        return (gene.states || []).find((state) => state.key === normalizeStateKey(stateKey)) || null;
    }

    function showError(message) {
        if (!errorPanel) {
            return;
        }
        errorPanel.textContent = message;
        errorPanel.hidden = false;
    }

    function clearError() {
        if (!errorPanel) {
            return;
        }
        errorPanel.textContent = '';
        errorPanel.hidden = true;
    }

    function renderTags(parentKey) {
        const parentRoot = root.querySelector(`[data-parent="${parentKey}"]`);
        if (!parentRoot) {
            return;
        }
        const container = parentRoot.querySelector('[data-tag-container]');
        const hiddenInputs = parentRoot.querySelector('[data-hidden-inputs]');
        if (!container || !hiddenInputs) {
            return;
        }
        container.innerHTML = '';
        hiddenInputs.innerHTML = '';
        const entries = Array.from(selections[parentKey].entries()).sort((a, b) => {
            const geneA = genesById.get(a[0]);
            const geneB = genesById.get(b[0]);
            const nameA = geneA ? geneA.name : '';
            const nameB = geneB ? geneB.name : '';
            return nameA.localeCompare(nameB, 'de');
        });

        entries.forEach(([geneId, stateKey]) => {
            const gene = genesById.get(geneId);
            const state = findState(geneId, stateKey);
            if (!gene || !state) {
                return;
            }
            const tag = document.createElement('span');
            tag.className = 'gene-tag';
            const label = document.createElement('span');
            label.textContent = `${gene.name}: ${state.label}`;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'gene-tag__remove';
            remove.setAttribute('aria-label', `${gene.name} entfernen`);
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                selections[parentKey].delete(geneId);
                renderTags(parentKey);
                clearError();
            });
            tag.appendChild(label);
            tag.appendChild(remove);
            container.appendChild(tag);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `${parentKey}[${geneId}]`;
            input.value = state.key;
            hiddenInputs.appendChild(input);
        });

        if (!container.children.length) {
            const emptyHint = document.createElement('p');
            emptyHint.className = 'text-muted';
            emptyHint.textContent = 'Keine Gene ausgewählt – wird als Wildtyp gewertet.';
            container.appendChild(emptyHint);
        }
    }

    function renderMorphs(parentKey) {
        const parentRoot = root.querySelector(`[data-parent="${parentKey}"]`);
        if (!parentRoot) {
            return;
        }
        const tagsContainer = parentRoot.querySelector('[data-morph-tags]');
        const hiddenInputs = parentRoot.querySelector('[data-morph-hidden-inputs]');
        const clearButton = parentRoot.querySelector('[data-morph-clear]');
        if (!tagsContainer || !hiddenInputs) {
            return;
        }
        tagsContainer.innerHTML = '';
        hiddenInputs.innerHTML = '';
        const ids = Array.from(morphSelections[parentKey]);
        ids.forEach((id) => {
            const morph = morphsById.get(id);
            if (!morph) {
                return;
            }
            const tag = document.createElement('span');
            tag.className = 'morph-tag';
            tag.dataset.morphId = String(id);
            const label = document.createElement('span');
            label.textContent = morph.name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'morph-tag__remove';
            remove.setAttribute('aria-label', `${morph.name} entfernen`);
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                morphSelections[parentKey].delete(id);
                renderMorphs(parentKey);
            });
            tag.appendChild(label);
            tag.appendChild(remove);
            tagsContainer.appendChild(tag);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `parent_morphs[${parentKey}][]`;
            input.value = String(id);
            hiddenInputs.appendChild(input);
        });

        if (!ids.length) {
            const placeholder = document.createElement('p');
            placeholder.className = 'text-muted';
            placeholder.textContent = 'Noch keine Morph-Angaben ergänzt.';
            tagsContainer.appendChild(placeholder);
        }

        if (clearButton) {
            clearButton.hidden = ids.length === 0;
        }
    }

    function renderSelectedAnimal(parentKey, animal) {
        const parentRoot = root.querySelector(`[data-parent="${parentKey}"]`);
        if (!parentRoot) {
            return;
        }
        const summary = parentRoot.querySelector('[data-animal-summary]');
        const hiddenInput = parentRoot.querySelector('[data-animal-hidden]');
        const clearButton = parentRoot.querySelector('[data-animal-clear]');
        if (hiddenInput) {
            hiddenInput.value = animal ? String(animal.id) : '';
        }
        if (summary) {
            summary.innerHTML = '';
            if (animal) {
                const paragraph = document.createElement('p');
                paragraph.innerHTML = `<strong>${escapeHtml(animal.name)}</strong><br><span class="text-muted">${escapeHtml(animal.genetics || 'Keine Genetik hinterlegt')}</span>`;
                summary.appendChild(paragraph);
            } else {
                const paragraph = document.createElement('p');
                paragraph.className = 'text-muted';
                paragraph.textContent = 'Noch kein Tier ausgewählt.';
                summary.appendChild(paragraph);
            }
        }
        if (clearButton) {
            clearButton.textContent = animal ? 'Auswahl entfernen' : 'Eingabe löschen';
        }
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    function applyAnimalProfile(parentKey, animal) {
        if (!animal) {
            selections[parentKey].clear();
            renderTags(parentKey);
            return;
        }
        selections[parentKey].clear();
        const profile = animal.genetics_profile || {};
        Object.entries(profile).forEach(([slug, state]) => {
            const gene = genesBySlug.get(slug);
            if (!gene) {
                return;
            }
            const normalizedState = normalizeStateKey(state);
            const geneState = findState(gene.id, normalizedState);
            if (geneState) {
                selections[parentKey].set(gene.id, geneState.key);
            }
        });
        renderTags(parentKey);
    }

    function clearAnimal(parentKey) {
        parentAnimals[parentKey] = null;
        renderSelectedAnimal(parentKey, null);
    }

    function toggleSource(parentKey, newSource, options) {
        const parentRoot = root.querySelector(`[data-parent="${parentKey}"]`);
        if (!parentRoot) {
            return;
        }
        const opts = options || {};
        parentSources[parentKey] = newSource;
        parentRoot.dataset.parentSource = newSource;
        const animalSection = parentRoot.querySelector('[data-animal-section]');
        const animalInput = parentRoot.querySelector('[data-animal-input]');
        if (animalSection) {
            animalSection.hidden = newSource !== 'animal';
        }
        if (animalInput) {
            animalInput.disabled = newSource !== 'animal';
        }
        if (newSource === 'manual' && !opts.skipClear) {
            clearAnimal(parentKey);
        }
    }

    function setupAnimalSearch(parentKey, parentRoot) {
        const input = parentRoot.querySelector('[data-animal-input]');
        const suggestions = parentRoot.querySelector('[data-animal-suggestions]');
        const clearButton = parentRoot.querySelector('[data-animal-clear]');
        if (!input || !suggestions) {
            return;
        }

        const closeSuggestions = () => {
            suggestions.hidden = true;
            suggestions.innerHTML = '';
        };

        input.addEventListener('input', () => {
            const value = input.value.trim();
            if (!value) {
                closeSuggestions();
                return;
            }
            const normalized = normalize(value);
            const matches = animalSearchIndex
                .filter((entry) => entry.tokens.some((token) => token.includes(normalized)))
                .slice(0, 6);
            renderAnimalSuggestions(parentKey, matches, suggestions, input, closeSuggestions);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                const value = input.value.trim();
                if (!value) {
                    return;
                }
                const normalized = normalize(value);
                const match = animalSearchIndex.find((entry) => entry.tokens.some((token) => token.includes(normalized)));
                if (match) {
                    event.preventDefault();
                    const animal = animalsById.get(match.id);
                    parentAnimals[parentKey] = animal ? animal.id : null;
                    renderSelectedAnimal(parentKey, animal);
                    applyAnimalProfile(parentKey, animal);
                    input.value = '';
                    closeSuggestions();
                    clearError();
                }
            }
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length === 0) {
                renderAnimalSuggestions(parentKey, animalSearchIndex.slice(0, 6), suggestions, input, closeSuggestions);
            }
        });

        document.addEventListener('click', (event) => {
            if (!parentRoot.contains(event.target)) {
                closeSuggestions();
            }
        });

        clearButton?.addEventListener('click', () => {
            clearAnimal(parentKey);
            input.value = '';
            closeSuggestions();
        });

        const selectedAnimal = parentAnimals[parentKey] ? animalsById.get(parentAnimals[parentKey]) : null;
        renderSelectedAnimal(parentKey, selectedAnimal || null);
        if (selectedAnimal) {
            applyAnimalProfile(parentKey, selectedAnimal);
        }
    }

    function renderAnimalSuggestions(parentKey, matches, container, input, close) {
        container.innerHTML = '';
        if (!matches.length) {
            const empty = document.createElement('div');
            empty.className = 'gene-suggestion gene-suggestion--empty';
            empty.textContent = 'Keine passenden Tiere gefunden.';
            container.appendChild(empty);
            container.hidden = false;
            return;
        }
        matches.forEach((entry) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'gene-suggestion';
            button.innerHTML = `<strong>${escapeHtml(animalsById.get(entry.id)?.name || '')}</strong><span>${escapeHtml(animalsById.get(entry.id)?.genetics || 'Keine Genetik hinterlegt')}</span>`;
            button.addEventListener('click', () => {
                const animal = animalsById.get(entry.id);
                parentAnimals[parentKey] = animal ? animal.id : null;
                renderSelectedAnimal(parentKey, animal || null);
                applyAnimalProfile(parentKey, animal || null);
                input.value = '';
                close();
                clearError();
            });
            container.appendChild(button);
        });
        container.hidden = false;
    }

    function setupMorphSearch(parentKey, parentRoot) {
        const input = parentRoot.querySelector('[data-morph-input]');
        const suggestions = parentRoot.querySelector('[data-morph-suggestions]');
        const clearButton = parentRoot.querySelector('[data-morph-clear]');
        if (!input || !suggestions) {
            return;
        }

        const closeSuggestions = () => {
            suggestions.hidden = true;
            suggestions.innerHTML = '';
        };

        input.addEventListener('input', () => {
            const value = input.value.trim();
            if (!value) {
                closeSuggestions();
                return;
            }
            const normalized = normalize(value);
            const matches = morphSearchIndex
                .filter((entry) => !morphSelections[parentKey].has(entry.id) && entry.tokens.some((token) => token.includes(normalized)))
                .slice(0, 6);
            renderMorphSuggestions(parentKey, matches, suggestions, input, closeSuggestions);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                const value = input.value.trim();
                if (!value) {
                    return;
                }
                const normalized = normalize(value);
                const match = morphSearchIndex.find((entry) => !morphSelections[parentKey].has(entry.id) && entry.tokens.some((token) => token.includes(normalized)));
                if (match) {
                    event.preventDefault();
                    morphSelections[parentKey].add(match.id);
                    renderMorphs(parentKey);
                    input.value = '';
                    closeSuggestions();
                }
            }
        });

        document.addEventListener('click', (event) => {
            if (!parentRoot.contains(event.target)) {
                closeSuggestions();
            }
        });

        clearButton?.addEventListener('click', () => {
            morphSelections[parentKey].clear();
            renderMorphs(parentKey);
            closeSuggestions();
        });

        renderMorphs(parentKey);
    }

    function renderMorphSuggestions(parentKey, matches, container, input, close) {
        container.innerHTML = '';
        if (!matches.length) {
            const empty = document.createElement('div');
            empty.className = 'gene-suggestion gene-suggestion--empty';
            empty.textContent = 'Keine passenden Morphs gefunden.';
            container.appendChild(empty);
            container.hidden = false;
            return;
        }
        matches.forEach((entry) => {
            const morph = morphsById.get(entry.id);
            if (!morph) {
                return;
            }
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'gene-suggestion';
            button.innerHTML = `<strong>${escapeHtml(morph.name)}</strong><span>${escapeHtml(morph.type || '')}</span>`;
            button.addEventListener('click', () => {
                morphSelections[parentKey].add(entry.id);
                renderMorphs(parentKey);
                input.value = '';
                close();
            });
            container.appendChild(button);
        });
        container.hidden = false;
    }

    function setupGeneSelector(parentKey, parentRoot) {
        const input = parentRoot.querySelector('[data-input]');
        const suggestionContainer = parentRoot.querySelector('[data-suggestions]');
        const clearButton = parentRoot.querySelector('[data-clear]');
        if (!input || !suggestionContainer) {
            return;
        }

        const closeSuggestions = () => {
            suggestionContainer.hidden = true;
            suggestionContainer.innerHTML = '';
        };

        input.addEventListener('input', () => {
            const value = input.value.trim();
            if (!value) {
                closeSuggestions();
                clearError();
                return;
            }
            const normalized = normalize(value);
            const matches = geneSearchIndex.filter((entry) => {
                if (selections[parentKey].get(entry.geneId) === entry.stateKey) {
                    return false;
                }
                return entry.tokens.some((token) => token.includes(normalized));
            });
            renderGeneSuggestions(parentKey, matches, suggestionContainer, input, closeSuggestions, value);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                const value = input.value.trim();
                if (!value) {
                    return;
                }
                const normalized = normalize(value);
                const match = geneSearchIndex.find((entry) => {
                    if (selections[parentKey].get(entry.geneId) === entry.stateKey) {
                        return false;
                    }
                    return entry.tokens.some((token) => token.includes(normalized));
                });
                if (match) {
                    event.preventDefault();
                    selections[parentKey].set(match.geneId, match.stateKey);
                    renderTags(parentKey);
                    closeSuggestions();
                    input.value = '';
                    clearError();
                } else {
                    showError('Eingabe konnte keinem bekannten Gen zugeordnet werden. Bitte wählen Sie einen Vorschlag aus der Liste.');
                }
            }
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length === 0) {
                const suggestions = geneSearchIndex.filter((entry) => selections[parentKey].get(entry.geneId) !== entry.stateKey);
                renderGeneSuggestions(parentKey, suggestions, suggestionContainer, input, closeSuggestions, '');
            }
        });

        document.addEventListener('click', (event) => {
            if (!parentRoot.contains(event.target)) {
                closeSuggestions();
            }
        });

        clearButton?.addEventListener('click', () => {
            selections[parentKey].clear();
            renderTags(parentKey);
            closeSuggestions();
            input.value = '';
            clearError();
        });

        renderTags(parentKey);
    }

    function renderGeneSuggestions(parentKey, suggestions, container, input, close, query) {
        container.innerHTML = '';
        if (!suggestions.length) {
            const empty = document.createElement('div');
            empty.className = 'gene-suggestion gene-suggestion--empty';
            empty.textContent = 'Keine passenden Einträge gefunden.';
            container.appendChild(empty);
            container.hidden = false;
            if (query) {
                showError('Keine Übereinstimmung gefunden. Bitte prüfen Sie die Schreibweise oder pflegen Sie das Gen im Adminbereich.');
            }
            return;
        }
        clearError();
        suggestions.slice(0, 8).forEach((entry) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'gene-suggestion';
            button.innerHTML = `<strong>${entry.stateLabel}</strong><span>${entry.geneName}</span>`;
            button.addEventListener('click', () => {
                selections[parentKey].set(entry.geneId, entry.stateKey);
                renderTags(parentKey);
                close();
                input.value = '';
                clearError();
            });
            container.appendChild(button);
        });
        container.hidden = false;
    }

    function setupParent(parentKey) {
        const parentRoot = root.querySelector(`[data-parent="${parentKey}"]`);
        if (!parentRoot) {
            return;
        }

        const sourceInputs = parentRoot.querySelectorAll(`input[name="parent_sources[${parentKey}]"]`);
        sourceInputs.forEach((radio) => {
            if (radio.value === parentSources[parentKey]) {
                radio.checked = true;
            }
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    toggleSource(parentKey, normalizeSource(radio.value));
                }
            });
        });

        toggleSource(parentKey, parentSources[parentKey], { skipClear: true });

        setupAnimalSearch(parentKey, parentRoot);
        setupMorphSearch(parentKey, parentRoot);
        setupGeneSelector(parentKey, parentRoot);
    }

    function setupCopyButtons() {
        const jsonButton = root.querySelector('[data-copy-json]');
        const textButton = root.querySelector('[data-copy-text]');
        const feedback = root.querySelector('[data-copy-feedback]');

        const showFeedback = (message) => {
            if (!feedback) {
                return;
            }
            feedback.textContent = message;
            feedback.hidden = false;
            setTimeout(() => {
                feedback.hidden = true;
            }, 2500);
        };

        const copy = async (value) => {
            if (!value) {
                showFeedback('Keine Daten vorhanden.');
                return;
            }
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(value);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = value;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'absolute';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }
                showFeedback('In Zwischenablage kopiert.');
            } catch (error) {
                showFeedback('Kopieren fehlgeschlagen.');
            }
        };

        if (jsonButton) {
            if (!resultsExport) {
                jsonButton.disabled = true;
            } else {
                jsonButton.addEventListener('click', () => {
                    copy(JSON.stringify(resultsExport, null, 2));
                });
            }
        }

        if (textButton) {
            if (!resultText.trim()) {
                textButton.disabled = true;
            } else {
                textButton.addEventListener('click', () => {
                    copy(resultText);
                });
            }
        }
    }

    parentKeys.forEach(setupParent);
    setupCopyButtons();

    root.addEventListener('submit', () => {
        clearError();
    });
})();
