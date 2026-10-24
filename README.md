# DAVINCI · davinci-homes.de

Website von DAVINCI, Häuser aus Holz und Glas. PHP ohne Datenbank, gehostet bei SiteGround.

## Aufbau
- `*.php` im Hauptordner: die deutschen Seiten, `en/` die englischen
- `inc/site.php`: gemeinsames Gerüst, Häuser (`PRODUKTE`) und Preise
- `assets/`: CSS, JavaScript (inkl. GSAP unter `assets/js/gsap/`), Bilder, Schrift
- `send.php`, `lib/`: Formulare und E-Mails
- `downloads/`: Design-Book und Grundrisse (`downloads/grundrisse/<haus>.pdf`)

## Deployment
Jeder Push auf `main` überträgt die Website per SSH zu SiteGround (`.github/workflows/deploy.yml`).
Dateien werden nur hinzugefügt oder überschrieben, nie gelöscht.

Nicht im Repository und auf dem Server nicht überschrieben:
`lib/config.php`, `zugang.php`, `data/`, `diag.php`. Vorlagen: `lib/config.example.php`, `zugang.example.php`.

Einmalige Einrichtung: SSH-Schlüssel in SiteGround hinterlegen und die Secrets
`SG_HOST`, `SG_USER`, `SG_PORT`, `SG_PATH`, `SG_KEY` unter Settings → Secrets and variables → Actions anlegen.

## Interner Bereich (intern.davinci-homes.de)

Ordner `intern/`, wird vom Workflow in `www/intern.davinci-homes.de/public_html` übertragen (oder in den Ordner aus dem Secret `SG_PATH_INTERN`).

- Module: Übersicht mit Kennzahlen, Anfragen der Website, Kontakte (inkl. Import aus Excel/CSV), Projekte als Board, Aufgaben, Newsletter, Benutzer, Einstellungen.
- Daten liegen als JSON in `intern/daten/` auf dem Server (nicht im Repository).
- `intern/config.php` wird beim ersten Deploy aus `config.example.php` angelegt, wenn das Secret `INTERN_SCHLUESSEL` gesetzt ist.
- Die Website legt Anfragen in `data/anfragen.php` und Newsletter-Anmeldungen in `data/newsletter.php` ab; der interne Bereich liest beide Dateien.
- Cron-Job (SiteGround, alle 10 Minuten): `php /home/customer/www/intern.davinci-homes.de/public_html/cron.php`
