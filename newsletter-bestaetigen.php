<?php
/* DAVINCI – Double-Opt-in für den Newsletter */
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/store.php';
require __DIR__ . '/lib/seite.php';
$t = preg_replace('/[^a-f0-9]/', '', $_GET['t'] ?? '');
$status = st_aendern('newsletter', function (&$n) use ($t) {
  if (!$t || !isset($n[$t])) return 'fehlt';
  if ($n[$t]['abgemeldet']) return 'abgemeldet';
  if ($n[$t]['bestaetigt']) return 'schon';
  $n[$t]['bestaetigt'] = date('c');
  $n[$t]['bestaetigt_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
  return 'neu';
});
if ($status === 'neu') dv_seite('Vielen Dank.', 'Ihre Anmeldung zum DAVINCI Newsletter ist bestätigt. Sie können sich jederzeit über den Link in jeder Ausgabe abmelden.', 'Zur Website', '/');
elseif ($status === 'schon') dv_seite('Bereits bestätigt.', 'Sie haben Ihre Anmeldung bereits bestätigt. Schön, dass Sie dabei sind.', 'Zur Website', '/');
elseif ($status === 'abgemeldet') dv_seite('Abgemeldet.', 'Sie hatten sich bereits abgemeldet. Wenn Sie den Newsletter wieder erhalten möchten, melden Sie sich bei einer neuen Anfrage einfach erneut an.', 'Zur Website', '/');
else { http_response_code(404); dv_seite('Link ungültig.', 'Dieser Bestätigungslink ist nicht gültig oder abgelaufen.', 'Zur Website', '/'); }
