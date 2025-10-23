export type GeneState = 'normal' | 'heterozygous' | 'homozygous';
export type InheritanceMode = 'recessive' | 'dominant' | 'codominant' | 'incomplete_dominant' | 'polygenic';

export interface GeneDefinition {
  id: number;
  slug: string;
  name: string;
  inheritanceMode: InheritanceMode;
  normalLabel?: string;
  heterozygousLabel?: string;
  homozygousLabel?: string;
}

export interface GeneSelectionMap {
  [key: string]: GeneState;
}

export interface GeneDistributionState {
  state: GeneState | 'polygenic';
  probability: number | null;
  label: string;
  isVisual: boolean;
  isCarrier: boolean;
  isPolygenic?: boolean;
}

export interface GeneResult {
  gene: GeneDefinition;
  states: GeneDistributionState[];
  parentStates: {
    parentOne: GeneState;
    parentTwo: GeneState;
  };
}

export interface CombinedResult {
  probability: number;
  phenotype: string;
  states: Record<string, GeneState>;
  labels: Record<string, string>;
}

export interface PairingResult {
  genes: Record<number, GeneResult>;
  combined: CombinedResult[];
  polygenic: GeneResult[];
}

export interface PairingInput {
  genes: GeneDefinition[];
  parentOne: GeneSelectionMap;
  parentTwo: GeneSelectionMap;
}

export function calculatePairing(input: PairingInput): PairingResult | null {
  const genesById = new Map<number, GeneDefinition>();
  const genesBySlug = new Map<string, GeneDefinition>();

  input.genes.forEach((gene) => {
    genesById.set(gene.id, gene);
    if (gene.slug) {
      genesBySlug.set(gene.slug, gene);
    }
  });

  const geneResults: Record<number, GeneResult> = {};
  const polygenicResults: Record<number, GeneResult> = {};

  for (const gene of input.genes) {
    const stateOne = getParentState(gene, input.parentOne, genesById, genesBySlug);
    const stateTwo = getParentState(gene, input.parentTwo, genesById, genesBySlug);

    if (normalizeInheritance(gene.inheritanceMode) === 'polygenic') {
      if (stateOne === 'normal' && stateTwo === 'normal') {
        continue;
      }
      const note: GeneDistributionState = {
        state: 'polygenic',
        probability: null,
        label: 'Polygenes Merkmal – keine Punnett-Berechnung verfügbar',
        isVisual: false,
        isCarrier: false,
        isPolygenic: true,
      };
      const result: GeneResult = {
        gene,
        states: [note],
        parentStates: {
          parentOne: stateOne,
          parentTwo: stateTwo,
        },
      };
      geneResults[gene.id] = result;
      polygenicResults[gene.id] = result;
      continue;
    }

    if (stateOne === 'normal' && stateTwo === 'normal') {
      continue;
    }

    const states = calculateGeneDistribution(gene, stateOne, stateTwo);
    geneResults[gene.id] = {
      gene,
      states,
      parentStates: {
        parentOne: stateOne,
        parentTwo: stateTwo,
      },
    };
  }

  const geneIds = Object.keys(geneResults);
  if (geneIds.length === 0) {
    return null;
  }

  const combined: CombinedResult[] = buildCombinedResults(geneResults, polygenicResults);

  return {
    genes: geneResults,
    combined,
    polygenic: Object.values(polygenicResults),
  };
}

function buildCombinedResults(
  geneResults: Record<number, GeneResult>,
  polygenicResults: Record<number, GeneResult>
): CombinedResult[] {
  const index = new Map<number, GeneResult>(Object.entries(geneResults).map(([id, result]) => [Number(id), result]));
  const combinedAccumulator: Array<{
    probability: number;
    states: Record<number, GeneState>;
    labels: Record<number, string>;
    visual: Record<number, string>;
    carrier: Record<number, string>;
  }> = [
    {
      probability: 1,
      states: {},
      labels: {},
      visual: {},
      carrier: {},
    },
  ];

  for (const [id, result] of index.entries()) {
    if (polygenicResults[id]) {
      continue;
    }
    const next: typeof combinedAccumulator = [];
    for (const entry of combinedAccumulator) {
      for (const state of result.states) {
        const newStates = { ...entry.states, [id]: state.state as GeneState };
        const newLabels = { ...entry.labels, [id]: state.label };
        const newVisual = { ...entry.visual };
        const newCarrier = { ...entry.carrier };
        if (state.isVisual) {
          newVisual[id] = state.label;
        } else if (state.isCarrier) {
          newCarrier[id] = state.label;
        }
        next.push({
          probability: entry.probability * (state.probability ?? 0),
          states: newStates,
          labels: newLabels,
          visual: newVisual,
          carrier: newCarrier,
        });
      }
    }
    combinedAccumulator.splice(0, combinedAccumulator.length, ...next);
  }

  const byKey = new Map<string, typeof combinedAccumulator[number]>();
  combinedAccumulator.forEach((entry) => {
    const key = JSON.stringify(entry.states);
    const existing = byKey.get(key);
    if (existing) {
      existing.probability += entry.probability;
    } else {
      byKey.set(key, entry);
    }
  });

  const slugIndex = new Map<number, string>();
  for (const [id, result] of index.entries()) {
    slugIndex.set(id, result.gene.slug || String(id));
  }

  const combined: CombinedResult[] = [];
  byKey.forEach((entry) => {
    const labelsBySlug: Record<string, string> = {};
    const statesBySlug: Record<string, GeneState> = {};
    Object.entries(entry.labels).forEach(([id, label]) => {
      const slug = slugIndex.get(Number(id)) || String(id);
      labelsBySlug[slug] = label;
    });
    Object.entries(entry.states).forEach(([id, state]) => {
      const slug = slugIndex.get(Number(id)) || String(id);
      statesBySlug[slug] = state;
    });

    const visualTraits = Object.values(entry.visual);
    const carrierTraits = Object.values(entry.carrier);
    const phenotypeParts: string[] = [];
    if (visualTraits.length) {
      phenotypeParts.push(visualTraits.join(', '));
    }
    if (carrierTraits.length) {
      phenotypeParts.push(`Träger: ${carrierTraits.join(', ')}`);
    }
    if (!phenotypeParts.length) {
      phenotypeParts.push('Wildtyp');
    }

    combined.push({
      probability: entry.probability,
      phenotype: phenotypeParts.join(' • '),
      states: statesBySlug,
      labels: labelsBySlug,
    });
  });

  combined.sort((a, b) => b.probability - a.probability);
  return combined;
}

function getParentState(
  gene: GeneDefinition,
  selection: GeneSelectionMap,
  genesById: Map<number, GeneDefinition>,
  genesBySlug: Map<string, GeneDefinition>
): GeneState {
  const fromId = selection[String(gene.id)];
  if (fromId) {
    return sanitizeGeneState(fromId);
  }
  const fromSlug = selection[gene.slug];
  if (fromSlug) {
    return sanitizeGeneState(fromSlug);
  }
  for (const [key, value] of Object.entries(selection)) {
    const numeric = Number(key);
    if (Number.isFinite(numeric) && genesById.get(numeric)?.slug === gene.slug) {
      return sanitizeGeneState(value);
    }
    if (genesBySlug.get(key)?.id === gene.id) {
      return sanitizeGeneState(value);
    }
  }
  return 'normal';
}

export function calculateGeneDistribution(
  gene: GeneDefinition,
  parentOne: GeneState,
  parentTwo: GeneState
): GeneDistributionState[] {
  const allelesOne = geneStateToAlleles(parentOne);
  const allelesTwo = geneStateToAlleles(parentTwo);
  const distribution: Record<GeneState, number> = { normal: 0, heterozygous: 0, homozygous: 0 };

  allelesOne.forEach((alleleA) => {
    allelesTwo.forEach((alleleB) => {
      const state = alleleSumToState(alleleA + alleleB);
      distribution[state] += 1;
    });
  });

  const total = allelesOne.length * allelesTwo.length;
  const entries: GeneDistributionState[] = [];
  (Object.keys(distribution) as GeneState[]).forEach((stateKey) => {
    const probability = distribution[stateKey] / total;
    const label = geneStateLabel(gene, stateKey);
    entries.push({
      state: stateKey,
      probability,
      label,
      isVisual: geneStateIsVisual(gene, stateKey),
      isCarrier: geneStateIsCarrier(gene, stateKey),
    });
  });

  entries.sort((a, b) => (b.probability ?? 0) - (a.probability ?? 0));
  return entries;
}

export function sanitizeGeneState(state: GeneState | string | undefined): GeneState {
  const value = String(state || '').toLowerCase();
  if (value === 'homozygous' || value === 'super') {
    return 'homozygous';
  }
  if (value === 'heterozygous' || value === 'het') {
    return 'heterozygous';
  }
  return 'normal';
}

function geneStateToAlleles(state: GeneState): number[] {
  switch (state) {
    case 'homozygous':
      return [1, 1];
    case 'heterozygous':
      return [1, 0];
    default:
      return [0, 0];
  }
}

function alleleSumToState(sum: number): GeneState {
  if (sum >= 2) {
    return 'homozygous';
  }
  if (sum === 1) {
    return 'heterozygous';
  }
  return 'normal';
}

function geneStateLabel(gene: GeneDefinition, state: GeneState): string {
  const mode = normalizeInheritance(gene.inheritanceMode);
  const name = gene.name;
  const heteroDefault = gene.heterozygousLabel || (mode === 'recessive' ? `het ${name}` : name);
  const homoDefault = gene.homozygousLabel
    || (mode === 'recessive' ? name : mode === 'dominant' ? `${name} (Super)` : `Super ${name}`);
  const normalDefault = gene.normalLabel || (mode === 'recessive' ? 'Normal' : 'Wildtyp');

  switch (state) {
    case 'heterozygous':
      return heteroDefault;
    case 'homozygous':
      return homoDefault;
    default:
      return normalDefault;
  }
}

function geneStateIsVisual(gene: GeneDefinition, state: GeneState): boolean {
  const mode = normalizeInheritance(gene.inheritanceMode);
  if (mode === 'recessive') {
    return state === 'homozygous';
  }
  if (mode === 'dominant' || mode === 'codominant') {
    return state !== 'normal';
  }
  return false;
}

function geneStateIsCarrier(gene: GeneDefinition, state: GeneState): boolean {
  return normalizeInheritance(gene.inheritanceMode) === 'recessive' && state === 'heterozygous';
}

function normalizeInheritance(mode: InheritanceMode): InheritanceMode {
  if (mode === 'incomplete_dominant') {
    return 'codominant';
  }
  return mode;
}
