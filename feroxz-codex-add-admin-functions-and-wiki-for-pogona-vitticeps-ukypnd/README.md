# FeroxZ – PHP Reptile CMS

FeroxZ ist ein leichtgewichtiges, auf PHP 8.3 und SQLite basierendes CMS für Reptilienhalter. Es vereint Tierverwaltung, Tierabgabe, private Tierakten sowie ein Admin-Backend mit granularen Berechtigungen. Alle Inhalte werden persistiert in einer lokalen SQLite-Datenbank gespeichert, Medien landen im Verzeichnis `uploads/`.

## Kernfunktionen

- 🦎 **Tierverwaltung** mit Art, Genetik, Herkunft, Besonderheiten, Bildern, Showcase-Flag und optionalem Besitzer.
- 🔒 **„Meine Tiere“** – angemeldete Benutzer sehen ausschließlich ihre privaten Tiere in einem separaten Bereich.
- 📨 **Tierabgabe-Workflow** mit öffentlichen Inseraten, Kontaktformular und Nachrichteneingang für Administrator*innen.
- ⚙️ **Einstellungen** für Seitentitel, Untertitel, Hero-/Abgabe-Text, Kontaktadresse und Footer (inkl. Versionshinweis).
- 👥 **Benutzer- & Rechteverwaltung**: Admins können weitere Accounts mit eingeschränkten Rechten (Tiere, Adoption, Einstellungen) anlegen.
- 📈 **Dashboard** mit Kennzahlen zu Bestand, Abgabeinträgen und neuen Anfragen.
- 💾 **Persistente Speicherung** per SQLite – keine zusätzliche Server-Software notwendig.
- 🖼️ **Animierte Galerie** mit Upload direkt im Adminbereich inklusive Bildunterschriften und Veröffentlichungsstatus.

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

## Änderungs-Checkliste

- [x] Beispiel-Datensätze für News, Tierübersicht und Tierabgabe integriert.
- [x] Fehlende Heterodon-nasicus-Morphen inklusive Swiss Chocolate ergänzt.
- [x] Morphkarten mit Quellen- und Bildnachweisen ausgestattet.
- [x] Tailwind-basiertes Dashboard sowie öffentliches Galerie-Layout implementiert.
- [x] Admin-Dashboard nach Tailwind-Vorbild neu aufgebaut inkl. Schnellzugriffen und KPI-Panels.
- [x] Öffentliches Seitentemplate mit Tailwind neu komponiert (Hero, Tiere, Adoption, News, Pflege).
- [x] Heterodon-Datenbestand um Jaguar, Goldenrod, Frosted, Pink Pastel, Lightning Line und Red Albino erweitert.
- [x] Hochgeladene Galerie-Bilder werden automatisch ins öffentliche `public/uploads`-Verzeichnis verschoben und ausgeliefert.
- [x] Genetikrechner-Ergebnisanzeige wiederhergestellt – Wahrscheinlichkeiten erscheinen direkt unter dem Formular.
- [x] Genetikrechner stellt Wahrscheinlichkeiten und Morphenbezeichnungen wieder korrekt dar.

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

## Standard-Login

- Benutzername: `admin`
- Passwort: `12345678`

Bitte ändere das Passwort nach der ersten Anmeldung über die Benutzerverwaltung.
