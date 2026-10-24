<?php
/* DAVINCI – Abmeldung von der Inspirationsstrecke */
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/store.php';
require __DIR__ . '/lib/seite.php';
require __DIR__ . '/lib/hubspot.php';
$t = preg_replace('/[^a-f0-9]/', '', $_GET['t'] ?? '');
$ok = st_mit_sperre(function (&$k, &$q) use ($t) {
  if (!$t || !isset($k[$t])) return false;
  $k[$t]['abgemeldet'] = date('c');
  $GLOBALS['abm_email'] = $k[$t]['email'];
  $q = array_values(array_filter($q, fn($e) => ($e['token'] ?? null) !== $t));
  return true;
});
if ($ok) hs_kontakt_uebertragen(['email' => $GLOBALS['abm_email']], hs_notiz('Von Inspirations-E-Mails abgemeldet', ['Zeitpunkt' => date('d.m.Y, H:i') . ' Uhr']));
if ($ok) dv_seite('Sie sind abgemeldet.', 'Sie erhalten keine weiteren Inspirations-E-Mails von uns. Wenn Sie Fragen zu Ihrem Hausprojekt haben, sind wir natürlich trotzdem für Sie da.', 'Zur Website', '/');
else { http_response_code(404); dv_seite('Link ungültig.', 'Dieser Abmeldelink ist nicht gültig. Schreiben Sie uns gern an ' . DV_EMPFAENGER . ', dann tragen wir Sie manuell aus.', 'Zur Website', '/'); }
