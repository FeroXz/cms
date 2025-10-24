# FeroxZ – PHP Reptile CMS

**Aktuelle Version:** 3.5.2

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

## Installation

1. **Dateien hochladen** – den Inhalt dieses Repositories auf den Webspace kopieren (z. B. via FTP oder Git-Deploy).
2. **Verzeichnisse beschreibbar machen**:
   ```bash
   chmod -R 775 storage uploads
   ```
3. **Aufruf im Browser** – `index.php` unter `public/` dient als Front-Controller. Richte den Dokumentenstamm deines Webservers auf `public/` aus.
4. **Erstanmeldung** – Standard-Zugangsdaten: Benutzername `admin`, Passwort `12345678`. Nach dem Login können weitere Benutzer erstellt und Passwörter geändert werden.

> Hinweis: Beim ersten Start wird automatisch eine SQLite-Datenbank unter `storage/database.sqlite` angelegt sowie ein Admin-Benutzer erzeugt.

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

## Update-Funktion

- Die Update-Funktion kann serverseitig `git pull --rebase`, optionale Migrationen und einen Build-Lauf triggern.
- Aktiviere sie per `ENABLE_UPDATE=true` (oder rückwärtskompatibel `APP_ENABLE_UPDATE=true`) in der `.env`.
- In lokalen Entwicklungsumgebungen bleibt der Button deaktiviert; etwaige Aufrufe werden als Simulationslauf protokolliert, ohne echte Befehle auszuführen.
- Nach erfolgreichem Lauf erscheint ein Banner mit der neuesten Version inklusive „Was ist neu?“-Modal und Changelog-Eintrag.
