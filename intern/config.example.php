<?php
/* =========================================================
   DAVINCI intern – Konfiguration
   Auf dem Server als config.php anlegen. Die echte config.php liegt NICHT im Repository.
   ========================================================= */
return [
  // Einmaliger Schlüssel zum Anlegen des ersten Zugangs (danach wirkungslos). Bitte ändern!
  'einrichtungs_schluessel' => 'HIER-EINEN-LANGEN-ZUFAELLIGEN-SCHLUESSEL-EINTRAGEN',
  // Adresse dieses Bereichs (für Abmeldelinks und Bilder im Newsletter)
  'url' => 'https://intern.davinci-homes.de',
  // Adresse der Website (für Bilder aus der Website)
  'website_url' => 'https://davinci-homes.de',
  // Absender der Newsletter (Postfach muss bei SiteGround existieren)
  'absender' => 'bauen@davinci-homes.de',
  // Ordner data/ der Website. Leer lassen = automatisch suchen.
  'website_daten' => '',
  // Schlüssel für den Cron-Aufruf per Web (php cron.php über die Kommandozeile braucht keinen)
  'cron_schluessel' => '',
];
