<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2.5rem] border border-white/10 bg-gradient-to-br from-night-900 via-night-800 to-slate-900 p-10 shadow-2xl shadow-brand-900/30 lg:p-16">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center">
            <div class="space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-brand-500/15 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-brand-100"><?= htmlspecialchars(content_value($settings, 'genetics_badge') ?: 'Genetik Navigator') ?></span>
                <h1 class="text-3xl font-semibold text-white sm:text-4xl lg:text-5xl"><?= htmlspecialchars(content_value($settings, 'genetics_title')) ?></h1>
                <p class="text-base leading-relaxed text-slate-200">
                    <?= htmlspecialchars(content_value($settings, 'genetics_intro')) ?>
                </p>
            </div>
            <div class="rounded-3xl border border-white/5 bg-night-900/70 p-8 shadow-xl shadow-black/40">
                <h2 class="text-lg font-semibold text-white">So funktioniert der Rechner</h2>
                <p class="mt-3 text-sm text-slate-300">
                    Wähle zuerst eine Art, gib anschließend pro Elter die sichtbaren Morphen oder Trägerzustände ein.
                    Nicht angegebene Gene gelten automatisch als Wildtyp.
                </p>
                <ul class="mt-6 space-y-2 text-sm text-slate-200">
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-brand-400"></span><span>Autocomplete hilft bei der richtigen Schreibweise der Morphen.</span></li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span><span>Du kannst mehrere Gene kombinieren, Ergebnisse erscheinen direkt unter dem Formular.</span></li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-rose-400"></span><span>Bild- und Quellenangaben helfen bei der Zuordnung der Referenzkarten.</span></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if (empty($speciesList)): ?>
        <div class="mt-12 rounded-3xl border border-dashed border-white/20 px-6 py-10 text-center text-slate-200">
            <p><?= htmlspecialchars(content_value($settings, 'genetics_empty_notice')) ?></p>
        </div>
    <?php else: ?>
        <div class="mt-12 grid gap-8 lg:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)] lg:items-start">
            <div class="space-y-6">
                <div class="rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-xl shadow-black/40">
                    <form method="get" class="space-y-4">
                        <input type="hidden" name="route" value="genetics">
                        <label class="block text-sm font-medium text-slate-300">
                            <span class="mb-2 block text-xs uppercase tracking-wide text-slate-400">Art auswählen</span>
                            <select name="species" class="w-full rounded-2xl border border-white/10 bg-night-900/70 px-3 py-2 text-sm text-white focus:border-brand-400 focus:ring-brand-400" onchange="this.form.submit()">
                                <?php foreach ($speciesList as $species): ?>
                                    <option value="<?= htmlspecialchars($species['slug']) ?>" <?= ($selectedSpeciesSlug === $species['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($species['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <noscript>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-100">Wechseln</button>
                        </noscript>
                    </form>
                    <?php if ($selectedSpecies): ?>
                        <div class="mt-6 space-y-3 text-sm text-slate-200">
                            <div class="flex flex-col">
                                <span class="text-xs uppercase tracking-wide text-slate-400">Aktuelle Art</span>
                                <strong class="text-lg text-white"><?= htmlspecialchars($selectedSpecies['name']) ?></strong>
                                <?php if (!empty($selectedSpecies['scientific_name'])): ?>
                                    <span class="italic text-slate-400"><?= htmlspecialchars($selectedSpecies['scientific_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($selectedSpecies['description'])): ?>
                                <div class="rich-text-content prose prose-invert max-w-none text-sm leading-relaxed text-slate-200">
                                    <?= render_rich_text($selectedSpecies['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <div class="space-y-10">
                <?php if ($selectedSpecies && !empty($genes)): ?>
                    <?php
                        $toLower = static function (string $value): string {
                            return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
                        };
                        $modeLabels = [
                            'recessive' => 'rezessiv',
                            'dominant' => 'dominant',
                            'incomplete_dominant' => 'inkomplett dominant',
                        ];
                        $geneStatePayload = [];
                        foreach ($genes as $gene) {
                            $geneId = (int)$gene['id'];
                            $stateEntries = [];
                            foreach (['normal', 'heterozygous', 'homozygous'] as $stateKey) {
                                $label = gene_state_label($gene, $stateKey);
                                $tokens = [$toLower($label), $toLower($gene['name'])];
                                if (!empty($gene['shorthand'])) {
                                    $tokens[] = $toLower($gene['shorthand']);
                                }
                                if ($stateKey === 'normal') {
                                    $tokens[] = 'wildtyp';
                                    $tokens[] = 'normal ' . $toLower($gene['name']);
                                } elseif ($stateKey === 'heterozygous') {
                                    $tokens[] = 'het ' . $toLower($gene['name']);
                                    $tokens[] = 'träger ' . $toLower($gene['name']);
                                } else {
                                    $tokens[] = 'visual ' . $toLower($gene['name']);
                                }
                                $stateEntries[] = [
                                    'key' => $stateKey,
                                    'label' => $label,
                                    'searchTokens' => array_values(array_unique($tokens)),
                                ];
                            }
                            $geneStatePayload[] = [
                                'id' => $geneId,
                                'name' => $gene['name'],
                                'shorthand' => $gene['shorthand'],
                                'inheritance' => $modeLabels[$gene['inheritance_mode']] ?? $gene['inheritance_mode'],
                                'inheritanceHint' => $gene['inheritance_hint'] ?? null,
                                'description' => $gene['description'],
                                'states' => $stateEntries,
                            ];
                        }
                    ?>
                    <div class="space-y-8 rounded-3xl border border-white/10 bg-night-900/70 p-6 shadow-xl shadow-black/40">
                        <h2 class="text-xl font-semibold text-white">Genetikrechner</h2>
                        <p class="text-sm text-slate-300">Trage pro Elter sichtbare Morphen oder Heterozygotie ein. Vorschläge erscheinen automatisch während der Eingabe.</p>
                        <form method="post" class="gene-selector space-y-8" data-genetic-selector>
                            <input type="hidden" name="species_slug" value="<?= htmlspecialchars($selectedSpecies['slug']) ?>">
                            <div class="gene-selector__intro text-sm text-slate-200">
                                <strong class="text-white">Eingabehilfe:</strong> Tippe einen Gen-Namen oder Trägerstatus (z.&nbsp;B. „Albino“, „het Toffee“, „Super Anaconda“).
                            </div>
                            <div class="alert alert-error" data-form-error hidden role="alert" aria-live="assertive"></div>
                            <div class="gene-selector__parents grid gap-6 md:grid-cols-2">
                                <section class="gene-parent rounded-3xl border border-white/10 bg-white/5 p-4" data-parent="parent1">
                                    <h3 class="text-base font-semibold text-white">Elter 1</h3>
                                    <p class="text-xs text-slate-300">Füge alle sichtbaren Morphen sowie Trägereigenschaften hinzu.</p>
                                    <div class="gene-parent__tags" data-tag-container></div>
                                    <div class="gene-parent__input">
                                        <input type="text" placeholder="Gen oder Bezeichnung eingeben …" data-input>
                                        <button type="button" class="btn btn-secondary" data-clear>Zurücksetzen</button>
                                    </div>
                                    <div class="gene-parent__suggestions" data-suggestions hidden></div>
                                    <div data-hidden-inputs></div>
                                </section>
                                <section class="gene-parent rounded-3xl border border-white/10 bg-white/5 p-4" data-parent="parent2">
                                    <h3 class="text-base font-semibold text-white">Elter 2</h3>
                                    <p class="text-xs text-slate-300">Bestimme visuelle Merkmale oder Heterozygotie wie „het Albino“.</p>
                                    <div class="gene-parent__tags" data-tag-container></div>
                                    <div class="gene-parent__input">
                                        <input type="text" placeholder="Gen oder Bezeichnung eingeben …" data-input>
                                        <button type="button" class="btn btn-secondary" data-clear>Zurücksetzen</button>
                                    </div>
                                    <div class="gene-parent__suggestions" data-suggestions hidden></div>
                                    <div data-hidden-inputs></div>
                                </section>
                            </div>
                            <button type="submit" class="btn">Ergebnis berechnen</button>
                        </form>
                        <script>
                            window.GENETIC_GENE_DATA = <?= json_encode($geneStatePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                            window.GENETIC_PARENT_SELECTIONS = <?= json_encode($parentSelections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                        </script>

                        <?php if (!empty($results)): ?>
                            <section class="space-y-6" aria-live="polite">
                                <h3 class="text-lg font-semibold text-white">Ergebnisse</h3>
                                <div class="grid gap-6 lg:grid-cols-2">
                                    <?php foreach ($results as $result): ?>
                                        <article class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-lg shadow-black/30">
                                            <header class="flex items-start justify-between gap-4">
                                                <div>
                                                    <h4 class="text-base font-semibold text-white"><?= htmlspecialchars($result['label']) ?></h4>
                                                    <p class="text-xs text-slate-300">Wahrscheinlichkeit der Nachzucht</p>
                                                </div>
                                                <span class="rounded-full bg-brand-500/20 px-3 py-1 text-sm font-semibold text-brand-100"><?= number_format($result['probability'] * 100, 1, ',', '.') ?>%</span>
                                            </header>
                                            <table class="mt-4 w-full text-left text-xs text-slate-200">
                                                <thead>
                                                    <tr class="text-slate-400">
                                                        <th class="pb-2 pr-3 font-medium">Gen</th>
                                                        <th class="pb-2 pr-3 font-medium">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-white/10">
                                                    <?php if (!empty($result['states'])): ?>
                                                        <?php foreach ($result['states'] as $state): ?>
                                                            <tr>
                                                                <td class="py-1 pr-3 text-white"><?= htmlspecialchars($state['gene']) ?></td>
                                                                <td class="py-1 text-slate-200">
                                                                    <span class="tag tag-<?= htmlspecialchars($state['state']) ?>"><?= htmlspecialchars($state['label']) ?></span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="2" class="py-2 text-center text-slate-300">Keine relevanten Genkombinationen für diese Ausprägung.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            <p class="rounded-2xl border border-dashed border-white/10 px-4 py-4 text-sm text-slate-200">Bitte wähle mindestens ein Gen mit Träger- oder visueller Ausprägung aus.</p>
                        <?php endif; ?>
                    </div>

                    <section class="space-y-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-white">Verfügbare Gene</h2>
                                <p class="text-sm text-slate-300">Alle Basis-Morphen inklusive Medien und Quellenangaben</p>
                            </div>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            <?php foreach ($genes as $gene): ?>
                                <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-white/10 bg-night-900/60 shadow-xl shadow-black/40">
                                    <?php if (!empty($gene['display_image'])): ?>
                                        <figure class="relative h-48 w-full overflow-hidden">
                                            <img src="<?= htmlspecialchars($gene['display_image']) ?>" alt="<?= htmlspecialchars($gene['name']) ?> Morph" class="h-full w-full object-cover" loading="lazy">
                                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-night-900/90 to-transparent px-5 py-4">
                                                <h3 class="flex items-center gap-2 text-lg font-semibold text-white">
                                                    <?= htmlspecialchars($gene['name']) ?>
                                                    <button type="button" class="gene-tooltip" aria-label="Genetische Erläuterung" title="<?= htmlspecialchars($gene['inheritance_hint'] ?? get_gene_inheritance_hint($gene['inheritance_mode'])) ?>">?</button>
                                                </h3>
                                                <p class="text-xs uppercase tracking-wide text-slate-300"><?= htmlspecialchars($modeLabels[$gene['inheritance_mode']] ?? $gene['inheritance_mode']) ?></p>
                                            </div>
                                        </figure>
                                    <?php else: ?>
                                        <header class="px-5 py-4">
                                            <h3 class="flex items-center gap-2 text-lg font-semibold text-white">
                                                <?= htmlspecialchars($gene['name']) ?>
                                                <button type="button" class="gene-tooltip" aria-label="Genetische Erläuterung" title="<?= htmlspecialchars($gene['inheritance_hint'] ?? get_gene_inheritance_hint($gene['inheritance_mode'])) ?>">?</button>
                                            </h3>
                                            <p class="text-xs uppercase tracking-wide text-slate-300"><?= htmlspecialchars($modeLabels[$gene['inheritance_mode']] ?? $gene['inheritance_mode']) ?></p>
                                        </header>
                                    <?php endif; ?>
                                    <div class="flex flex-1 flex-col gap-4 px-5 py-4 text-sm text-slate-200">
                                        <?php if (!empty($gene['display_description'])): ?>
                                            <p class="leading-relaxed text-slate-200"><?= htmlspecialchars($gene['display_description']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($gene['display_tags'])): ?>
                                            <ul class="flex flex-wrap gap-2 text-xs">
                                                <?php foreach ($gene['display_tags'] as $tag): ?>
                                                    <li class="rounded-full bg-white/10 px-3 py-1 text-slate-100/80"><?= htmlspecialchars($tag) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <?php if (!empty($gene['advisory'])): ?>
                                            <div class="gene-warning" role="alert">
                                                <strong><?= htmlspecialchars($gene['advisory']['title'] ?? 'Warnung') ?>:</strong>
                                                <span><?= htmlspecialchars($gene['advisory']['message'] ?? '') ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <dl class="grid gap-2 text-xs text-slate-300">
                                            <div class="flex items-center justify-between gap-3"><dt class="font-semibold text-slate-200">Wildtyp</dt><dd><?= htmlspecialchars(gene_state_label($gene, 'normal')) ?></dd></div>
                                            <div class="flex items-center justify-between gap-3"><dt class="font-semibold text-slate-200">Träger</dt><dd><?= htmlspecialchars(gene_state_label($gene, 'heterozygous')) ?></dd></div>
                                            <div class="flex items-center justify-between gap-3"><dt class="font-semibold text-slate-200">Visuell</dt><dd><?= htmlspecialchars(gene_state_label($gene, 'homozygous')) ?></dd></div>
                                        </dl>
                                        <?php if (!empty($gene['display_origin']) || !empty($gene['display_origin_url'])): ?>
                                            <footer class="mt-auto text-xs text-slate-400">
                                                Quelle: <?= htmlspecialchars($gene['display_origin'] ?? 'n/a') ?>
                                                <?php if (!empty($gene['display_origin_url'])): ?>
                                                    — <a href="<?= htmlspecialchars($gene['display_origin_url']) ?>" target="_blank" rel="noopener" class="text-brand-100 underline decoration-dotted underline-offset-2">Original ansehen</a>
                                                <?php endif; ?>
                                            </footer>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <?php if (!empty($referenceGenes ?? [])): ?>
                        <section class="space-y-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h2 class="text-2xl font-semibold text-white">Designer-Kombinationen</h2>
                                    <p class="text-sm text-slate-300">Bestätigte Referenztiere mit Herkunft und Bildern</p>
                                </div>
                            </div>
                            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                                <?php foreach ($referenceGenes as $gene): ?>
                                    <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-white/10 bg-night-900/60 shadow-xl shadow-black/40">
                                        <?php if (!empty($gene['image_path'])): ?>
                                            <figure class="relative h-48 w-full overflow-hidden">
                                                <img src="<?= htmlspecialchars($gene['image_path']) ?>" alt="<?= htmlspecialchars($gene['name']) ?> Referenz" class="h-full w-full object-cover" loading="lazy">
                                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-night-900/90 to-transparent px-5 py-4">
                                                    <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($gene['name']) ?></h3>
                                                    <p class="text-xs uppercase tracking-wide text-brand-100">Referenz</p>
                                                </div>
                                            </figure>
                                        <?php else: ?>
                                            <header class="px-5 py-4">
                                                <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($gene['name']) ?></h3>
                                                <p class="text-xs uppercase tracking-wide text-brand-100">Referenz</p>
                                            </header>
                                        <?php endif; ?>
                                        <div class="flex flex-1 flex-col gap-4 px-5 py-4 text-sm text-slate-200">
                                        <?php if (!empty($gene['display_description'])): ?>
                                            <p class="leading-relaxed text-slate-200"><?= htmlspecialchars($gene['display_description']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($gene['display_tags'])): ?>
                                            <ul class="flex flex-wrap gap-2 text-xs">
                                                <?php foreach ($gene['display_tags'] as $tag): ?>
                                                    <li class="rounded-full bg-white/10 px-3 py-1 text-slate-100/80"><?= htmlspecialchars($tag) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <?php if (!empty($gene['advisory'])): ?>
                                            <div class="gene-warning" role="alert">
                                                <strong><?= htmlspecialchars($gene['advisory']['title'] ?? 'Warnung') ?>:</strong>
                                                <span><?= htmlspecialchars($gene['advisory']['message'] ?? '') ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <dl class="grid gap-2 text-xs text-slate-300">
                                            <div class="flex items-center justify-between gap-3"><dt class="font-semibold text-slate-200">Basis</dt><dd><?= htmlspecialchars($gene['normal_label']) ?></dd></div>
                                            <div class="flex items-center justify-between gap-3"><dt class="font-semibold text-slate-200">Teil-Kombi</dt><dd><?= htmlspecialchars($gene['heterozygous_label']) ?></dd></div>
                                            <div class="flex items-center justify-between gap-3"><dt class="font-semibold text-slate-200">Komplett</dt><dd><?= htmlspecialchars($gene['homozygous_label']) ?></dd></div>
                                        </dl>
                                        <?php if (!empty($gene['display_origin']) || !empty($gene['display_origin_url'])): ?>
                                            <footer class="mt-auto text-xs text-slate-400">
                                                Quelle: <?= htmlspecialchars($gene['display_origin'] ?? 'n/a') ?>
                                                <?php if (!empty($gene['display_origin_url'])): ?>
                                                    — <a href="<?= htmlspecialchars($gene['display_origin_url']) ?>" target="_blank" rel="noopener" class="text-brand-100 underline decoration-dotted underline-offset-2">Original ansehen</a>
                                                <?php endif; ?>
                                            </footer>
                                        <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php elseif ($selectedSpecies): ?>
                    <div class="rounded-3xl border border-dashed border-white/15 px-6 py-8 text-sm text-slate-200">
                        Für diese Art wurden bislang keine Gene hinterlegt.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../partials/footer.php'; ?>
