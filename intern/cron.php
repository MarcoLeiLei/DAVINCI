<?php
/* =========================================================
   DAVINCI intern – zeitgesteuert: Website abgleichen und laufende Newsletter weiter versenden
   SiteGround → Entwicklung → Cron-Jobs, alle 10 Minuten:
   php /home/customer/www/intern.davinci-homes.de/public_html/cron.php
   ========================================================= */
require __DIR__ . '/lib/start.php';
if (php_sapi_name() !== 'cli') {
  $s = (string) cfg('cron_schluessel', '');
  if ($s === '' || !hash_equals($s, (string) ($_GET['schluessel'] ?? ''))) { http_response_code(403); exit('Nur per Cron-Job.'); }
}
$neu = website_abgleich(true);
$n = 0;
foreach (lesen('kampagnen') as $k) {
  if ($k['status'] !== 'laeuft') continue;
  // Pakete senden, bis das Stundenlimit erreicht ist oder nach 4 Minuten
  $ende = time() + 240;
  do { $r = versand_schritt($k['id']); $n += $r['gesendet'] ?? 0; } while (($r['status'] ?? '') === 'laeuft' && empty($r['warten']) && empty($r['laeuft_schon']) && time() < $ende && usleep(300000) === null);
}
echo date('c') . " – $neu neue Anfrage(n), $n Newsletter-E-Mail(s) versendet\n";
