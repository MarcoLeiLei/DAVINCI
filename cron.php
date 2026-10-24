<?php
/* =========================================================
   DAVINCI – zeitgesteuerter Versand
   In SiteGround unter Entwicklung → Cron-Jobs alle 15 Minuten ausführen:
   php /home/customer/www/davinci-homes.de/public_html/cron.php
   ========================================================= */
if (php_sapi_name() !== 'cli') { http_response_code(403); exit('Nur per Cron-Job.'); }
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/store.php';

$faellig = st_mit_sperre(function (&$k, &$q) {
  $jetzt = time(); $raus = []; $bleibt = [];
  foreach ($q as $e) {
    if ($e['faellig'] > $jetzt) { $bleibt[] = $e; continue; }
    if (isset($e['token'])) {                         // Inspirationsstrecke: nur bestätigt und nicht abgemeldet
      $kt = $k[$e['token']] ?? null;
      if (!$kt || !$kt['bestaetigt'] || $kt['abgemeldet']) continue;
      $e['daten'] = ['name' => $kt['name'], 'email' => $kt['email']];
    }
    $raus[] = $e;
  }
  $q = $bleibt;
  return $raus;
});

$n = 0;
foreach ($faellig as $e) {
  $fn = 'v_' . $e['vorlage'];
  if (!function_exists($fn)) continue;
  $links = isset($e['token']) ? ['abmelden' => DV_SEITE . '/abmelden.php?t=' . $e['token']] : [];
  [$b, $h] = $links ? $fn($e['daten'], $links) : $fn($e['daten']);
  if (m_senden($e['daten']['email'], $b, $h, DV_NAME, DV_EMPFAENGER)) $n++;
}
echo date('c') . " – $n E-Mail(s) versendet\n";
