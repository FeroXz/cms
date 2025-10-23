import { existsSync, readdirSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const seedDir = join(root, 'seed');

if (!existsSync(seedDir)) {
  console.error('Seed-Verzeichnis fehlt.');
  process.exit(1);
}

const requiredMorphFiles = [
  'pogona_morphs_minimal.csv',
  'cornsnake_morphs_minimal.csv',
  'ballpython_morphs_minimal.csv',
];

let ok = true;

for (const file of requiredMorphFiles) {
  const filePath = join(seedDir, file);
  if (!existsSync(filePath)) {
    console.error(`Fehlende Morph-Datei: ${file}`);
    ok = false;
  }
}

const wikiDir = join(seedDir, 'wiki');
const newsDir = join(seedDir, 'news');
const animalsCsv = join(seedDir, 'animals.csv');

if (!existsSync(animalsCsv)) {
  console.error('animals.csv fehlt.');
  ok = false;
}

if (!existsSync(wikiDir)) {
  console.error('Wiki-Verzeichnis fehlt.');
  ok = false;
} else {
  const wikiFiles = readdirSync(wikiDir).filter((file) => file.endsWith('.md'));
  if (wikiFiles.length < 10) {
    console.error(`Zu wenige Wiki-Artikel: ${wikiFiles.length} gefunden.`);
    ok = false;
  } else {
    console.log(`Wiki-Artikel: ${wikiFiles.length}`);
  }
}

if (!existsSync(newsDir)) {
  console.error('News-Verzeichnis fehlt.');
  ok = false;
} else {
  const newsFiles = readdirSync(newsDir).filter((file) => file.endsWith('.md'));
  if (newsFiles.length < 8) {
    console.error(`Zu wenige News-Beiträge: ${newsFiles.length} gefunden.`);
    ok = false;
  } else {
    console.log(`News-Beiträge: ${newsFiles.length}`);
  }
}

if (ok) {
  console.log('Seed-Check erfolgreich.');
  process.exit(0);
} else {
  process.exit(1);
}
