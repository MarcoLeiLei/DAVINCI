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
