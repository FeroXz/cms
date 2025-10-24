# FeroxZ – PHP Reptile CMS

**Aktuelle Version:** 3.5.3

FeroxZ ist ein leichtgewichtiges, auf PHP 8.3 und SQLite basierendes CMS für Reptilienhalter. Es vereint Tierverwaltung, Tierabgabe, private Tierakten sowie ein Admin-Backend mit granularen Berechtigungen. Alle Inhalte werden persistiert in einer lokalen SQLite-Datenbank gespeichert, Medien landen im Verzeichnis `uploads/`.

## Kernfunktionen

- 🦎 **Tierverwaltung** mit Art, Genetik, Herkunft, Besonderheiten, Bildern, Showcase-Flag und optionalem Besitzer.
- 🔒 **„Meine Tiere“** – angemeldete Benutzer sehen ausschließlich ihre privaten Tiere in einem separaten Bereich.
- 📨 **Tierabgabe-Workflow** mit öffentlichen Inseraten, Kontaktformular und Nachrichteneingang für Administrator*innen.
- ⚙️ **Einstellungen** für Seitentitel, Untertitel, Hero-/Abgabe-Text, Kontaktadresse und Footer (inkl. Versionshinweis).
- 👥 **Benutzer- & Rechteverwaltung**: Admins können weitere Accounts mit eingeschränkten Rechten (Tiere, Adoption, Einstellungen) anlegen.
- 📈 **Dashboard** mit Kennzahlen zu Bestand, Abgabeinträgen und neuen Anfragen.
- 💾 **Persistente Speicherung** per SQLite – keine zusätzliche Server-Software notwendig.

## Systemvoraussetzungen

| Komponente | Anforderung |
| ---------- | ----------- |
| PHP        | ≥ 8.3 mit PDO-SQLite, session, fileinfo |
| Webserver  | Apache, Nginx oder kompatibel (z. B. shared hosting) |
| Dateirechte | Schreibrechte für `storage/` und `uploads/` |

## Installation (getestet)

> Die folgende Schritt-für-Schritt-Anleitung wurde am 2025-10-24 unter PHP 8.3 und Node 18 erfolgreich durchlaufen.

1. **Repository bereitstellen**
   ```bash
   git clone <repo-url> feroxz
   cd feroxz
   ```
2. **Konfigurationsdatei anlegen**
   ```bash
   cp .env.example .env
   ```
   Passe mindestens `APP_URL`, `SITE_URL`, `MAIL_FROM_*` sowie – falls nötig – `DB_PATH` an.
3. **Schreibrechte setzen**
   ```bash
   chmod -R 775 storage uploads
   ```
4. **Seed-Vollständigkeit prüfen (optional, empfohlen)**
   ```bash
   node scripts/seed_check.mjs
   ```
   Der Befehl bestätigt das Vorhandensein aller Seed-Dateien.
5. **Webserver konfigurieren** – Setze den Document-Root deines Webservers auf `public/` oder starte lokal:
   ```bash
   cd public
   php -S localhost:8000
   ```
6. **Erstanmeldung durchführen** – Öffne `http://localhost:8000/index.php` (bzw. deine Domain) und melde dich mit `admin` / `12345678` an. Ändere das Passwort direkt im Admin-Bereich.

Beim ersten Aufruf wird automatisch `storage/database.sqlite` erzeugt und mit Basiseinträgen befüllt.

## Ordnerstruktur

```
feroxz/
├── app/                 # PHP-Logik, Datenbank, Helper
├── public/
│   ├── assets/          # Stylesheet
│   ├── index.php        # Front-Controller
│   └── views/           # Öffentliche und Admin-Templates
├── storage/             # SQLite-Datenbank (wird zur Laufzeit angelegt)
├── uploads/             # Hochgeladene Medien (per .gitignore ausgenommen)
└── README.md
```

## Adminbereich & Workflows

- **Dashboard** – Überblick über Tiere, Abgabeinträge und eingegangene Nachrichten.
- **Tiere** – CRUD für Tiere inkl. Upload und Zuordnung zu Benutzer*innen.
- **Tierabgabe** – Inserate verwalten, Tiere aus dem Bestand übernehmen, Preis/Status pflegen.
- **Anfragen** – Einsicht in alle Adoption-Anfragen, direkte Antwort via `mailto:`.
- **Einstellungen** – Seitentexte und Kontaktadresse aktualisieren.
- **Benutzer** – Nur für Admins sichtbar. Neue Benutzer mit selektiven Rechten anlegen.

## Styling

Das Theme nutzt Glas-/Neon-Akzente inspiriert von tropischen Terrarien. Anpassungen erfolgen im Stylesheet `public/assets/style.css`.

## Entwicklung (lokal)

Ein PHP-Entwicklungsserver reicht aus:

```bash
cd public
php -S localhost:8000
```

Danach im Browser `http://localhost:8000/index.php` öffnen.

## Tests

Syntax-Check der PHP-Dateien:

```bash
find public app -name "*.php" -print0 | xargs -0 -n1 php -l
```

TypeScript-Tests für den Genetik-Rechner:

```bash
npm test
```

## Funktions-Checkliste

- [x] Morph-CSV-Import mit Vorschau, Dry-Run und Duplikat-Erkennung
- [x] Medien-Dropzone mit Mehrfach-Upload, Varianten-Generierung und Sortierung
- [x] Genetik-Rechner mit Tier-Autocomplete, Morph-Auswahl und JSON/Text-Export
- [x] Öffentliche Tier- & Abgabeseiten mit Filtern, Detail-Galerien und Sire/Dam-Verknüpfung
- [x] Galerie mit Kategorienfilter, Lazy-Load und Lightbox
- [x] Wiki- & News-Bereiche mit Detailansicht und Inline-Medien
- [x] Command-Palette (Cmd/Ctrl+K) für Tiere, Morphs, News & Wiki
- [x] Admin-Dashboard mit KPI-Kacheln und Liste letzter Uploads
- [x] Update-&-Deploy-Button mit Changelog-Banner und Modal-Details
- [x] Update-Kontrolle mit ENV-Gating und deaktiviertem Button in DEV (Simulationsmodus)
- [x] Seed-Check-Skript lauffähig via Node.js ohne zusätzliche Loader
- [x] Installations- & Update-Anleitung detailliert dokumentiert und getestet (3.5.3)

## Standard-Login

- Benutzername: `admin`
- Passwort: `12345678`

Bitte ändere das Passwort nach der ersten Anmeldung über die Benutzerverwaltung.

## Seeds

- [x] `seed/animals.csv` mit aktuellen Bestandsdaten
- [x] Drei Morph-CSV-Dateien (`pogona`, `cornsnake`, `ballpython`)
- [x] Zehn Wiki-Artikel als Markdown im Ordner `seed/wiki/`
- [x] Acht News-Beiträge als Markdown im Ordner `seed/news/`
- [x] Seed-Check-Skript `scripts/seed_check.mjs`

## Schnellstart

1. Repository klonen und in das Projektverzeichnis wechseln.
2. `.env.example` nach `.env` kopieren und Variablen anpassen.
3. Schreibrechte für `storage/` und `uploads/` setzen.
4. Optional: `node scripts/seed_check.mjs` ausführen, um Seed-Vollständigkeit zu prüfen.
5. Webserver auf `public/` zeigen lassen und im Browser öffnen.

## Update-Anleitung (getestet)

1. **Voraussetzungen setzen**
   - `.env`: `ENABLE_UPDATE=true` (oder `APP_ENABLE_UPDATE=true` für Legacy), optional `APP_ENV=production` für echte Deploys.
   - Stelle sicher, dass `git`, `npx prisma` und `npm` auf dem Server verfügbar sind.
2. **Simulation in Entwicklungsumgebung**
   ```bash
   APP_ENV=development ENABLE_UPDATE=true php -r 'require "app/bootstrap.php"; global $pdo; var_export(perform_system_update($pdo, "3.5.3-test", "Simulationslauf CLI"));'
   ```
   Ausgabe: Status `simulated`, alle Befehle werden protokolliert, aber nicht ausgeführt.
3. **Produktiver Lauf**
   - Stelle `APP_ENV=production` sicher.
   - Öffne im Adminbereich **Einstellungen → Update & Deploy**.
   - Trage die neue Version (z. B. `3.5.3`) und optional Notizen ein.
   - Klicke **Update & Deploy**. Der Button ist nur deaktiviert, wenn `ENABLE_UPDATE` nicht gesetzt ist.
   - Nach erfolgreichem Lauf erscheint ein Banner „Aktualisiert auf vX.Y.Z – Was ist neu?“ mit Link zum Changelog.
4. **CLI-Alternative für Produktionsserver**
   ```bash
   APP_ENV=production ENABLE_UPDATE=true php -r 'require "app/bootstrap.php"; global $pdo; var_export(perform_system_update($pdo, "3.5.3", "CLI-Update"));'
   ```
   Der Rückgabewert enthält Exit-Codes und Ausgaben der drei Befehle (`git pull --rebase`, `npx prisma migrate deploy`, `npm run build`).
5. **Fehleranalyse**
   - Bei Exit-Code ≠ 0 bleibt die zuletzt erfolgreiche Version bestehen.
   - Alle Logs werden im Changelog gespeichert und können im Adminbereich eingesehen werden.

> Hinweis: Der CLI-Aufruf dient als technische Verifikation der Update-Routine. Auf Produktionssystemen sollte dennoch der Admin-Workflow bevorzugt werden.
