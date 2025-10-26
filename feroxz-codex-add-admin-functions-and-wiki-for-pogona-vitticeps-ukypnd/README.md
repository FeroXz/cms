# Dragon Reptiles CMS – PHP Plattform für Terraristik

Dragon Reptiles ist ein leichtgewichtiges, auf PHP 8.3 und SQLite basierendes CMS für Reptilienhalter. Es vereint Tierverwaltung, Tierabgabe, Wiki-Inhalte sowie ein Admin-Backend mit granularen Berechtigungen. Alle Inhalte werden persistiert in einer lokalen SQLite-Datenbank gespeichert, Medien landen im Verzeichnis `uploads/`.

**Aktuelle Version:** 3.1.0

## Kernfunktionen

- 🦎 **Tierverwaltung** mit Art, Genetik, Herkunft, Besonderheiten, Bildern, Showcase-Flag und optionalem Besitzer.
- 🔒 **„Meine Tiere“** – angemeldete Benutzer sehen ausschließlich ihre privaten Tiere in einem separaten Bereich.
- 📨 **Tierabgabe-Workflow** mit öffentlichen Inseraten, Kontaktformular und Nachrichteneingang für Administrator*innen.
- ⚙️ **Einstellungen** für Seitentitel, Untertitel, Hero-/Abgabe-Text, Kontaktadresse und Footer (inkl. Versionshinweis).
- 👥 **Benutzer- & Rechteverwaltung**: Admins können weitere Accounts mit eingeschränkten Rechten (Tiere, Adoption, Einstellungen) anlegen.
- 📈 **Dashboard** mit Kennzahlen zu Bestand, Abgabeinträgen und neuen Anfragen.
- 💾 **Persistente Speicherung** per SQLite – keine zusätzliche Server-Software notwendig.
- 🖼️ **Galerie-Verwaltung** inklusive Uploads, Tags und Startseiten-Highlights.
- 🧩 **Drag-&-Drop-Startseitenlayout** für News-, Adoption-, Pflege- und Galerie-Sektionen.
- 🔄 **ZIP-Update-Manager** im Adminbereich – Updates ohne Verlust eigener Inhalte einspielen.

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

## Seed-Dateien prüfen

Mit dem Skript `scripts/seed_check.mjs` lässt sich vor einem Deployment nachvollziehen, ob alle erwarteten Seed-Dateien im Verzeichnis `storage/seeds/` vorhanden sind.

```bash
node scripts/seed_check.mjs
```

Ohne Manifest-Datei (`storage/seeds/manifest.json`) listet das Skript alle gefundenen Seed-Dateien auf und weist darauf hin, wenn keine Seeds vorhanden sind. Liegt ein Manifest mit einem `required`-Array vor, werden fehlende Einträge explizit markiert und das Skript beendet sich mit einem Fehlercode.

## Standard-Login

- Benutzername: `admin`
- Passwort: `12345678`

Bitte ändere das Passwort nach der ersten Anmeldung über die Benutzerverwaltung.
