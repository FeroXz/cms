import { describe, it, expect } from 'vitest';
import { calculatePairing, GeneDefinition, GeneDistributionState } from '../lib/genetics';

describe('calculatePairing', () => {
  const albino: GeneDefinition = {
    id: 1,
    slug: 'albino',
    name: 'Albino',
    inheritanceMode: 'recessive',
    normalLabel: 'Normal',
    heterozygousLabel: 'het Albino',
    homozygousLabel: 'Albino'
  };

  const dunner: GeneDefinition = {
    id: 2,
    slug: 'dunner',
    name: 'Dunner',
    inheritanceMode: 'codominant',
    normalLabel: 'Normal',
    heterozygousLabel: 'Dunner',
    homozygousLabel: 'Super Dunner'
  };

  const pied: GeneDefinition = {
    id: 3,
    slug: 'pied',
    name: 'Pied',
    inheritanceMode: 'recessive',
    normalLabel: 'Normal',
    heterozygousLabel: 'het Pied',
    homozygousLabel: 'Pied'
  };

  function stateMap(states: GeneDistributionState[]): Record<string, number> {
    return states.reduce<Record<string, number>>((acc, entry) => {
      if (entry.probability != null) {
        acc[entry.state] = entry.probability;
      }
      return acc;
    }, {});
  }

  it('produces recessive distribution for het x het', () => {
    const result = calculatePairing({
      genes: [albino],
      parentOne: { albino: 'heterozygous' },
      parentTwo: { albino: 'heterozygous' }
    });

    expect(result).not.toBeNull();
    const gene = result!.genes[albino.id];
    expect(gene).toBeDefined();
    const map = stateMap(gene.states);
    expect(map.homozygous).toBeCloseTo(0.25, 5);
    expect(map.heterozygous).toBeCloseTo(0.5, 5);
    expect(map.normal).toBeCloseTo(0.25, 5);
  });

  it('handles codominant het pairings', () => {
    const result = calculatePairing({
      genes: [dunner],
      parentOne: { dunner: 'heterozygous' },
      parentTwo: { dunner: 'heterozygous' }
    });

    expect(result).not.toBeNull();
    const gene = result!.genes[dunner.id];
    expect(gene).toBeDefined();
    const map = stateMap(gene.states);
    expect(map.homozygous).toBeCloseTo(0.25, 5);
    expect(map.heterozygous).toBeCloseTo(0.5, 5);
    expect(map.normal).toBeCloseTo(0.25, 5);
  });

  it('calculates recessive het x visual outcomes', () => {
    const result = calculatePairing({
      genes: [pied],
      parentOne: { pied: 'heterozygous' },
      parentTwo: { pied: 'homozygous' }
    });

    expect(result).not.toBeNull();
    const gene = result!.genes[pied.id];
    expect(gene).toBeDefined();
    const map = stateMap(gene.states);
    expect(map.homozygous).toBeCloseTo(0.5, 5);
    expect(map.heterozygous).toBeCloseTo(0.5, 5);
    expect(map.normal ?? 0).toBeCloseTo(0, 5);
  });
});
