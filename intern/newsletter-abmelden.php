<?php
/* DAVINCI intern – Newsletter abbestellen (öffentlich, ohne Anmeldung)
   GET zeigt eine Bestätigungsseite, POST meldet ab (auch „One-Click“ aus Gmail/Apple Mail). */
require __DIR__ . '/lib/start.php';
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
$t = preg_replace('/[^a-f0-9]/', '', (string) g('t'));
$kamp = preg_replace('/[^a-f0-9]/', '', (string) g('k'));
$kontakt = null;
if (strlen($t) === 32) foreach (kontakte() as $k) if (hash_equals((string) ($k['newsletter']['token'] ?? ''), $t)) { $kontakt = $k; break; }
$status = $kontakt ? (($kontakt['newsletter']['status'] ?? '') === 'abgemeldet' ? 'schon' : 'frage') : 'fehlt';

if (ist_post() && $kontakt) {
  if ($status !== 'schon') {
    aendern('kontakte', function (&$alle) use ($kontakt, $kamp) {
      $titel = $kamp ? (lesen('kampagnen')[$kamp]['betreff'] ?? '') : '';
      nl_setzen($alle[$kontakt['id']], 'abgemeldet', 'Selbst abgemeldet über den Link im Newsletter' . ($titel ? ' „' . $titel . '“' : ''));
    });
    sperren($kontakt['email']);
    website_nl_abmelden($kontakt['email']);
    if ($kamp) aendern('kampagnen', function (&$a) use ($kamp) { if (isset($a[$kamp])) $a[$kamp]['abmeldungen'] = (int) ($a[$kamp]['abmeldungen'] ?? 0) + 1; });
  }
  if (isset($_POST['List-Unsubscribe'])) { http_response_code(200); exit('OK'); }   // One-Click aus dem Mailprogramm
  $status = 'erledigt';
}
if ($status === 'fehlt') http_response_code(404);
$web = website_url();
?><!DOCTYPE html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Newsletter abbestellen · DAVINCI</title><link rel="icon" type="image/svg+xml" href="assets/favicon.svg"><link rel="stylesheet" href="assets/app.css"></head>
<body class="oeffentlich"><main class="card">
  <div class="brand"><?= SIGNET ?><span>DAVINCI</span></div>
  <?php if ($status === 'frage'): ?>
    <h1 style="font-size:30px">Newsletter abbestellen?</h1>
    <p class="muted">Sie erhalten dann keinen DAVINCI Newsletter mehr an <b><?= e($kontakt['email']) ?></b>.</p>
    <form method="post"><button class="btn" type="submit">Ja, abbestellen</button></form>
  <?php elseif ($status === 'erledigt'): ?>
    <h1 style="font-size:30px">Sie sind abgemeldet.</h1>
    <p class="muted">Sie erhalten keinen Newsletter mehr von uns. Bei Fragen zu Ihrem Haus sind wir natürlich trotzdem für Sie da.</p>
    <p><a class="btn btn--hell" href="<?= e($web) ?>">Zur Website</a></p>
  <?php elseif ($status === 'schon'): ?>
    <h1 style="font-size:30px">Bereits abgemeldet.</h1>
    <p class="muted">Diese Adresse erhält keinen Newsletter mehr.</p>
    <p><a class="btn btn--hell" href="<?= e($web) ?>">Zur Website</a></p>
  <?php else: ?>
    <h1 style="font-size:30px">Link ungültig.</h1>
    <p class="muted">Dieser Abmeldelink ist nicht gültig. Schreiben Sie uns an <a href="mailto:bauen@davinci-homes.de">bauen@davinci-homes.de</a>, dann tragen wir Sie sofort aus.</p>
  <?php endif; ?>
</main></body></html>
