# FeroxZ – PHP Reptile CMS

**Aktuelle Version:** 3.7.1

FeroxZ ist ein PHP 8.3/SQLite basiertes CMS für Reptilienhöfe mit vollständig integriertem Admin-Backend, Medienverwaltung, Genetik-Rechner und automatisierten Deploy- sowie Import-Workflows. Der Fokus liegt auf einer dunklen UI mit orangefarbenen Akzenten, zugänglichen Formularen und einer klaren Trennung zwischen öffentlichen Seiten und geschützten Verwaltungsbereichen.

## Systemvoraussetzungen

| Komponente | Mindestanforderung |
| ---------- | ------------------ |
| PHP        | ≥ 8.3 mit PDO-SQLite, `fileinfo`, `gd`/`imagick` |
| Node.js    | ≥ 18 (für Tests & Frontend-Build) |
| Webserver  | Apache/Nginx oder PHP Built-In Server (Document-Root `public/`) |
| Rechte     | Schreibrechte für `storage/`, `uploads/` und `storage/import_queue/` |

## Installation (getestet am 2025‑10‑24 unter PHP 8.3 & Node 18)

1. **Repository klonen**
   ```bash
   git clone <repo-url> feroxz
   cd feroxz
   ```
2. **Konfiguration anlegen**
   ```bash
   cp .env.example .env
   ```
   Passe mindestens `APP_URL`, `SITE_URL`, optionale Mail-Absender und – falls gewünscht – `DB_PATH` an. Die Update-Funktion ist standardmäßig aktiv (seit Version 3.6.0); setze `ENABLE_UPDATE=false`, wenn Deploys explizit gesperrt werden sollen.
3. **Verzeichnisse vorbereiten**
   ```bash
   mkdir -p storage/import_queue
   chmod -R 775 storage uploads storage/import_queue
   ```
4. **Seeds prüfen (empfohlen)**
   ```bash
   node scripts/seed_check.mjs
   ```
   Meldet die Vollständigkeit der Morph-, News-, Wiki- und Tier-Seeds.
5. **Entwicklungsserver starten**
   ```bash
   cd public
   php -S localhost:8000
   ```
   Aufruf unter `http://localhost:8000/index.php`. Erstanmeldung mit `admin` / `12345678` (Passwort anschließend ändern).

Beim ersten Request wird `storage/database.sqlite` automatisch erzeugt und mit Basiseinträgen gefüllt.

## CSV-Import & Auto-Imports

- **Admin → CSV-Import**: Neue Seite mit getrennten Formularen für Tiere, News, Abgabelisten sowie Morph-Quick-Imports. Jeder Upload unterstützt Dry-Run und zeigt eine Vorschau der ersten Zeilen.
- **Import-Ordner**: Dateien, die in `storage/import_queue/<typ>/` abgelegt werden (z. B. `storage/import_queue/animals/neu.csv`), werden beim nächsten Request automatisch verarbeitet. Erfolgreiche Dateien landen unter `storage/import_queue/processed/<typ>/`, fehlerhafte unter `storage/import_queue/failed/<typ>/`.
- **Duplikat-Erkennung**: Tiere vergleichen Kombinationen aus Species-Slug und Namen bzw. IDs; News prüfen Slugs; Abgabelisten Titel + Species.
- **CSV-Spalten**:
  - Tiere: `name`, `species` (Pflicht) + optionale Felder wie `owner_username`, `status`, `sire_name`, `dam_name`, `admin_notes`.
  - News: `title`, `content` (Pflicht) + `slug`, `excerpt`, `is_published`, `published_at`.
  - Abgabelisten: `title` (Pflicht) + `animal_id`/`animal_name`, `species`, `status`, `price`, `gender`, `contact_email`.

## Galerie & Medienverwaltung

- **React-Galerie**: Die öffentliche `/galerie`-Seite rendert vollständig clientseitig über React 18. Filterchips, animierte Karten und eine modale Lightbox sorgen für eine moderne Benutzererfahrung, während ein No-JS-Fallback die Inhalte serverseitig bereitstellt.
- **Lightbox & Navigation**: Bilder lassen sich in voller Auflösung betrachten, durch Pfeiltasten oder Buttons wechseln und als Original in einem neuen Tab öffnen.
- **Admin → Galerie**: Neue Verwaltungsseite mit Sammlungslisten, Beschreibungspflege sowie Dropzone. Medien können aus dem bestehenden Upload per Drag&Drop oder über den Medienpool zugewiesen werden.
- **Medienpool**: Ein modaler Picker listet ungebundene Uploads, erlaubt Mehrfachauswahl mit Preview, Suche und „Mehr laden“-Pagination. Ausgewählte Bilder werden sortiert eingefügt, die Zählstände aktualisieren sich sofort.

## Update-Anleitung

1. **Admin-Workflow (empfohlen)**
   - Navigation: **Einstellungen → Update & Deploy**.
   - Version (`vX.Y.Z`) und optionale Notizen eintragen.
   - Button klicken – in nicht-produktiven Umgebungen (APP_ENV ≠ `production`) werden Befehle simuliert, Logs erscheinen dennoch im Changelog.
   - Nach Erfolg zeigt ein Banner „Aktualisiert auf vX.Y.Z – Was ist neu?“ inklusive Modal mit den letzten Änderungen.
2. **CLI-Simulation / Produktionslauf**
   ```bash
   php -r 'require "app/bootstrap.php"; global $pdo; var_export(perform_system_update($pdo, "3.7.1", "CLI Test"));'
   ```
   - Ohne gesetzte ENV-Variablen läuft der Befehl im Simulationsmodus (APP_ENV default `production`, Befehle aber real ausgeführt – passe APP_ENV nach Bedarf an).
   - `ENABLE_UPDATE=false` deaktiviert die Funktion vollständig; true oder leer aktiviert sie.
3. **Logging & Fehlerfall**
   - Jeder Lauf erzeugt einen Changelog-Eintrag (`Einstellungen → Update & Deploy → Changelog`).
   - Bei Fehlern bleiben bestehende Daten erhalten, Exit-Codes stehen im Protokoll.

## Tests & Qualitätschecks

Alle Änderungen werden mit folgenden Befehlen verifiziert:

```bash
# PHP-Linter
find public app -name "*.php" -print0 | xargs -0 -n1 php -l

# TypeScript/Vitest Suite (Genetik-Rechner)
npm test

# Seed-Prüfung
node scripts/seed_check.mjs
```

## Seeds & Beispieldaten

- `seed/animals.csv` – Beispielbestand.
- Morph-Seeds für Bartagame, Kornnatter, Königspython unter `seed/*_morphs_minimal.csv`.
- Zehn Wiki-Artikel (`seed/wiki/*.md`).
- Acht News-Beiträge (`seed/news/*.md`).

## Standard-Zugangsdaten

- Benutzername: `admin`
- Passwort: `12345678`

## Funktions-Checkliste

- [x] Morph-CSV-Import mit Vorschau, Dry-Run und Duplikat-Erkennung
- [x] CSV-Import für Tiere, News und Abgabelisten inkl. Auto-Deduplikation
- [x] Automatischer Import-Ordner (`storage/import_queue/…`) mit Erfolgs-/Fehler-Historie
- [x] Medien-Dropzone mit Mehrfach-Upload, Varianten-Generierung und Sortierung
- [x] Genetik-Rechner mit Tier-Autocomplete, Morph-Auswahl und JSON/Text-Export (inkl. Vitest-Suite)
- [x] Öffentliche Tier- & Abgabeseiten mit Filtern, Detail-Galerien und Sire/Dam-Verknüpfung
- [x] React-Galerie mit Kategorienfiltern, animierten Karten, Fehlermeldungen (Aria-Live) und Lightbox
- [x] Admin-Galerie mit Medienpool-Auswahl, Sortierung, Beschreibungen und Fokusfalle für Barrierefreiheit
- [x] Wiki- & News-Bereiche mit Detailansicht und Inline-Medien
- [x] Command-Palette (Cmd/Ctrl+K) für Tiere, Morphs, News & Wiki
- [x] Admin-Dashboard mit KPI-Kacheln und Liste letzter Uploads
- [x] Update-&-Deploy-Button mit Changelog-Banner und standardmäßig aktivierter Update-Funktion
- [x] Installations-, Update- und Test-Anleitung mit verifizierten Befehlen (3.7.1)

