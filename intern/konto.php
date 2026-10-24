<?php
/* DAVINCI intern – eigener Zugang und Signatur */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
if (ist_post()) {
  if (g('aktion') === 'profil') {
    aendern('benutzer', function (&$a) use ($ich) { foreach (['name', 'email', 'position', 'telefon', 'mobil'] as $f) $a[$ich['id']][$f] = p($f); $a[$ich['id']]['name'] = $a[$ich['id']]['name'] ?: $ich['benutzer']; });
    meldung('Profil und Signatur gespeichert.');
  } elseif (!password_verify(p('alt'), $ich['hash'])) meldung('Das bisherige Passwort stimmt nicht.', 'fehler');
  elseif (mb_strlen(p('neu')) < 10) meldung('Das neue Passwort braucht mindestens 10 Zeichen.', 'fehler');
  elseif (p('neu') !== p('neu2')) meldung('Die beiden neuen Passwörter stimmen nicht überein.', 'fehler');
  else { aendern('benutzer', function (&$a) use ($ich) { $a[$ich['id']]['hash'] = password_hash(p('neu'), PASSWORD_DEFAULT); }); meldung('Passwort geändert.'); }
  weiter('konto.php');
}
kopf('Mein Zugang', '');
seitenkopf('Mein Zugang', e($ich['name']));
?>
<div class="grid grid--2">
  <section class="card">
    <h2 style="margin-bottom:14px">Profil und Signatur</h2>
    <form method="post" action="konto.php?aktion=profil" class="form form--2"><?= csrf_feld() ?>
      <label class="feld">Name<input name="name" value="<?= e($ich['name']) ?>" required></label>
      <label class="feld">Rolle im Team<input name="position" value="<?= e($ich['position'] ?? '') ?>" placeholder="z. B. Gründer · Vertrieb"></label>
      <label class="feld voll">E-Mail (Absender, Antworten und Testversand)<input type="email" name="email" value="<?= e($ich['email']) ?>"></label>
      <label class="feld">Telefon<input name="telefon" value="<?= e($ich['telefon'] ?? '') ?>"></label>
      <label class="feld">Mobil<input name="mobil" value="<?= e($ich['mobil'] ?? '') ?>"></label>
      <div class="voll"><button class="btn" type="submit">Speichern</button></div>
    </form>
    <p class="eyebrow" style="margin-top:26px">So sieht Ihre Signatur aus</p>
    <div style="padding:18px;border:1px solid var(--line);border-radius:12px;background:#fff"><?= signatur_html($ich + ['position' => '', 'telefon' => '', 'mobil' => '']) ?></div>
  </section>
  <section class="card">
    <h2 style="margin-bottom:14px">Passwort ändern</h2>
    <form method="post" class="form"><?= csrf_feld() ?>
      <label class="feld">Bisheriges Passwort<input type="password" name="alt" required autocomplete="current-password"></label>
      <label class="feld">Neues Passwort (mind. 10 Zeichen)<input type="password" name="neu" required minlength="10" autocomplete="new-password"></label>
      <label class="feld">Neues Passwort wiederholen<input type="password" name="neu2" required autocomplete="new-password"></label>
      <div><button class="btn" type="submit">Passwort ändern</button></div>
      <p class="muted klein">Benutzername: <?= e($ich['benutzer']) ?> · Zugriff: <?= $ich['rolle'] === 'admin' ? 'Administrator' : 'Team' ?></p>
    </form>
  </section>
</div>
<?php fuss();
