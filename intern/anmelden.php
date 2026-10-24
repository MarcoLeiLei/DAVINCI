<?php
/* DAVINCI intern – Anmelden, Abmelden, ersten Zugang einrichten */
require __DIR__ . '/lib/start.php';
sitzung_start();
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');
$fehler = '';
$einrichten = !benutzer_alle();

if (g('aktion') === 'abmelden' && ist_post()) {
  csrf_pruefen();
  $_SESSION = []; session_regenerate_id(true);
  weiter('anmelden.php');
}
if (ich()) weiter('index.php');

if (ist_post()) {
  csrf_pruefen();
  if (!versuche_ok()) $fehler = 'Zu viele Versuche. Bitte in 15 Minuten erneut versuchen.';
  elseif ($einrichten) {
    $schl = (string) cfg('einrichtungs_schluessel', '');
    if ($schl === '' || str_contains($schl, 'HIER-')) $fehler = 'In config.php ist noch kein Einrichtungsschlüssel eingetragen.';
    elseif (!hash_equals($schl, p('schluessel'))) { versuch_merken(); $fehler = 'Der Einrichtungsschlüssel stimmt nicht.'; }
    elseif (mb_strlen(p('passwort')) < 10) $fehler = 'Das Passwort braucht mindestens 10 Zeichen.';
    elseif (!preg_match('/^[a-z0-9._-]{2,40}$/', strtolower(p('benutzer')))) $fehler = 'Benutzername: nur Buchstaben, Ziffern, Punkt, Bindestrich.';
    else {
      $id = neue_id();
      aendern('benutzer', function (&$b) use ($id) {
        $b[$id] = ['id' => $id, 'benutzer' => strtolower(p('benutzer')), 'name' => p('name') ?: p('benutzer'), 'email' => p('email'), 'rolle' => 'admin',
                   'hash' => password_hash(p('passwort'), PASSWORD_DEFAULT), 'erstellt' => date('c'), 'letzter_login' => date('c')];
      });
      session_regenerate_id(true);
      $_SESSION['uid'] = $id; $_SESSION['seit'] = time();
      protokoll('Ersten Zugang angelegt');
      weiter('index.php');
    }
  } else {
    $b = null;
    foreach (benutzer_alle() as $x) if ($x['benutzer'] === strtolower(p('benutzer'))) $b = $x;
    if ($b && password_verify(p('passwort'), $b['hash'])) {
      session_regenerate_id(true);
      $_SESSION['uid'] = $b['id']; $_SESSION['seit'] = time();
      aendern('benutzer', function (&$alle) use ($b) {
        $alle[$b['id']]['letzter_login'] = date('c');
        if (password_needs_rehash($b['hash'], PASSWORD_DEFAULT)) $alle[$b['id']]['hash'] = password_hash(p('passwort'), PASSWORD_DEFAULT);
      });
      $z = (string) g('zurueck');
      weiter($z !== '' && $z[0] === '/' && !str_starts_with($z, '//') && !str_contains($z, 'anmelden') ? $z : 'index.php');
    }
    versuch_merken();
    $fehler = 'Benutzername oder Passwort stimmt nicht.';
  }
}
$web = rtrim((string) cfg('website_url', 'https://davinci-homes.de'), '/');
?><!DOCTYPE html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Anmelden · DAVINCI intern</title><link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
<link rel="stylesheet" href="assets/app.css?v=<?= @filemtime(__DIR__ . '/assets/app.css') ?>"></head>
<body class="login">
<div class="login__bg"><img src="<?= e($web) ?>/assets/img/echt-luft-landschaft.jpg" alt=""></div>
<main class="login__card">
  <div class="brand brand--gross"><?= SIGNET ?><span>DAVINCI</span></div>
  <p class="eyebrow eyebrow--hell"><?= $einrichten ? 'Ersten Zugang anlegen' : 'Interner Bereich' ?></p>
  <?php if ($fehler): ?><p class="login__fehler" role="alert"><?= e($fehler) ?></p><?php endif; ?>
  <form method="post" class="login__form">
    <?= csrf_feld() ?>
    <?php if ($einrichten): ?>
      <label>Einrichtungsschlüssel<input name="schluessel" type="password" required autocomplete="off"></label>
      <label>Ihr Name<input name="name" required value="<?= e(p('name')) ?>" autocomplete="name"></label>
      <label>E-Mail<input name="email" type="email" value="<?= e(p('email')) ?>" autocomplete="email"></label>
    <?php endif; ?>
    <label>Benutzername<input name="benutzer" required autocomplete="username" value="<?= e(p('benutzer')) ?>" autocapitalize="none"></label>
    <label>Passwort<input name="passwort" type="password" required autocomplete="<?= $einrichten ? 'new-password' : 'current-password' ?>"<?= $einrichten ? ' minlength="10"' : '' ?>></label>
    <button class="btn btn--weiss" type="submit"><?= $einrichten ? 'Zugang anlegen' : 'Anmelden' ?></button>
  </form>
  <p class="login__claim">Designed to stay.</p>
</main>
</body></html>
