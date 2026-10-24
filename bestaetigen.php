<?php
/* DAVINCI – Double-Opt-in für die Inspirationsstrecke */
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/store.php';
require __DIR__ . '/lib/seite.php';
require __DIR__ . '/lib/hubspot.php';
$t = preg_replace('/[^a-f0-9]/', '', $_GET['t'] ?? '');
$status = st_mit_sperre(function (&$k, &$q) use ($t) {
  if (!$t || !isset($k[$t])) return ['fehlt'];
  if ($k[$t]['abgemeldet']) return ['abgemeldet'];
  if ($k[$t]['bestaetigt']) return ['schon'];
  $k[$t]['bestaetigt'] = date('c');
  $k[$t]['bestaetigt_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
  foreach (DV_STRECKE as $vorlage => $tage) {
    $q[] = ['id' => st_token(), 'faellig' => strtotime("+$tage days 09:30"), 'vorlage' => $vorlage, 'token' => $t];   // jeweils um 9:30 Uhr
  }
  return ['neu', $k[$t]];
});
if ($status[0] === 'neu') {
  $d = ['name' => $status[1]['name'], 'email' => $status[1]['email']];
  [$b, $h] = v_willkommen($d, ['abmelden' => DV_SEITE . '/abmelden.php?t=' . $t]);
  m_senden($d['email'], $b, $h);
  hs_kontakt_uebertragen(['email' => $d['email']], hs_notiz('Inspirations-E-Mails bestätigt (Double-Opt-in)', ['Angefordert' => $status[1]['angefordert'], 'Bestätigt' => $status[1]['bestaetigt']]));
  dv_seite('Vielen Dank.', 'Ihre Anmeldung ist bestätigt. In den nächsten Wochen erhalten Sie von uns vier E-Mails mit Inspiration für Ihr Haus. Sie können sich jederzeit abmelden.', 'Zur Website', '/');
} elseif ($status[0] === 'schon') {
  dv_seite('Bereits bestätigt.', 'Sie haben Ihre Anmeldung bereits bestätigt. Wir freuen uns, dass Sie dabei sind.', 'Zur Website', '/');
} elseif ($status[0] === 'abgemeldet') {
  dv_seite('Abgemeldet.', 'Sie hatten sich bereits abgemeldet. Wenn Sie wieder Inspiration erhalten möchten, fordern Sie das Design-Book einfach erneut an.', 'Zur Website', '/');
} else {
  http_response_code(404);
  dv_seite('Link ungültig.', 'Dieser Bestätigungslink ist nicht gültig oder abgelaufen.', 'Zur Website', '/');
}
