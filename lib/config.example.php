<?php
/* Vorlage: auf dem Server als lib/config.php anlegen und Schlüssel dort eintragen. Die echte config.php liegt NICHT im Repository. */
/* =========================================================
   DAVINCI – Zugangsdaten für externe Dienste
   Diese Datei ist per .htaccess vor Zugriffen von außen geschützt.
   ========================================================= */

// HubSpot: Service Key (Einstellungen → Integrationen → Service Keys)
// mit den Berechtigungen crm.objects.contacts.read und crm.objects.contacts.write.
// Ein Token einer alten „Private App“ (Legacy App) funktioniert hier genauso.
// Leer lassen = keine Übertragung an HubSpot
const HUBSPOT_TOKEN = '';   // ← HIER den HubSpot-Schlüssel (pat-eu1-…) zwischen die Anführungszeichen einfügen
const HUBSPOT_API   = 'https://api.hubapi.com';
// Name der Deal-Pipeline. Wird sie nicht gefunden, nimmt die Website automatisch
// die Pipeline, die eine Phase „Anfrage Kontaktformular“ enthält.
const HS_PIPELINE   = 'DAVINCI Verkauf';
// Nur nötig, wenn im Protokoll „Pipeline nicht gefunden“ steht: IDs aus HubSpot eintragen
// (Einstellungen → Objekte → Deals → Pipelines → „Pipeline-IDs / Phasen-IDs anzeigen“).
const HS_PIPELINE_ID = '';
const HS_STAGE_IDS   = [
  // 'Anfrage Kontaktformular'      => '',
  // 'Termin gebucht'               => '',
  // 'Design-Book heruntergeladen'  => '',
  // 'telefoniert'                  => '',
  // 'Termin persönlich vereinbart' => '',
  // 'hot LEAD'                     => '',
  // 'verloren'                     => '',
];

// Google reCAPTCHA v3: geheimer Schlüssel (der Website-Schlüssel steht in assets/js/main.js)
// Leer lassen = reCAPTCHA aus, die übrigen Schutzmaßnahmen bleiben aktiv
const RECAPTCHA_SECRET    = '';
const RECAPTCHA_MIN_SCORE = 0.5;   // 0 = Bot, 1 = Mensch

// Weitere Schutzmaßnahmen
const SPAM_MIN_SEKUNDEN  = 3;      // so schnell füllt kein Mensch ein Formular aus
const SPAM_MAX_PRO_STUNDE = 20;    // Anfragen je IP-Adresse und Stunde
