<?php
/* DAVINCI intern – eigener Zugang */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
if (ist_post()) {
  if (!password_verify(p('alt'), $ich['hash'])) meldung('Das bisherige Passwort stimmt nicht.', 'fehler');
  elseif (mb_strlen(p('neu')) < 10) meldung('Das neue Passwort braucht mindestens 10 Zeichen.', 'fehler');
  elseif (p('neu') !== p('neu2')) meldung('Die beiden neuen Passwörter stimmen nicht überein.', 'fehler');
  else {
    aendern('benutzer', function (&$a) use ($ich) { $a[$ich['id']]['hash'] = password_hash(p('neu'), PASSWORD_DEFAULT); $a[$ich['id']]['name'] = p('name') ?: $a[$ich['id']]['name']; $a[$ich['id']]['email'] = p('email'); });
    meldung('Gespeichert.');
  }
  weiter('konto.php');
}
kopf('Mein Zugang', '');
seitenkopf('Mein Zugang', e($ich['name']));
?>
<section class="card" style="max-width:560px">
  <form method="post" class="form"><?= csrf_feld() ?>
    <label class="feld">Name<input name="name" value="<?= e($ich['name']) ?>"></label>
    <label class="feld">E-Mail (für Testversand der Newsletter)<input type="email" name="email" value="<?= e($ich['email']) ?>"></label>
    <label class="feld">Bisheriges Passwort<input type="password" name="alt" required autocomplete="current-password"></label>
    <label class="feld">Neues Passwort (mind. 10 Zeichen)<input type="password" name="neu" required minlength="10" autocomplete="new-password"></label>
    <label class="feld">Neues Passwort wiederholen<input type="password" name="neu2" required autocomplete="new-password"></label>
    <div><button class="btn" type="submit">Speichern</button></div>
    <p class="muted klein">Benutzername: <?= e($ich['benutzer']) ?> · Rolle: <?= $ich['rolle'] === 'admin' ? 'Administrator' : 'Team' ?></p>
  </form>
</section>
<?php fuss();
