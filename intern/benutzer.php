<?php
/* DAVINCI intern – Zugänge verwalten (nur Administratoren) */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen(true);
$aktion = g('aktion');
if (ist_post()) {
  if ($aktion === 'neu') {
    $bn = strtolower(p('benutzer'));
    $fehler = !preg_match('/^[a-z0-9._-]{2,40}$/', $bn) ? 'Benutzername: nur Kleinbuchstaben, Ziffern, Punkt, Bindestrich.' : (mb_strlen(p('passwort')) < 10 ? 'Das Startpasswort braucht mindestens 10 Zeichen.' : '');
    foreach (benutzer_alle() as $b) if ($b['benutzer'] === $bn) $fehler = 'Diesen Benutzernamen gibt es schon.';
    if ($fehler) { meldung($fehler, 'fehler'); weiter('benutzer.php'); }
    aendern('benutzer', function (&$alle) use ($bn) {
      $id = neue_id();
      $alle[$id] = ['id' => $id, 'benutzer' => $bn, 'name' => p('name') ?: $bn, 'email' => p('email'), 'rolle' => p('rolle') === 'admin' ? 'admin' : 'team',
                    'hash' => password_hash(p('passwort'), PASSWORD_DEFAULT), 'erstellt' => date('c'), 'letzter_login' => null];
    });
    protokoll('Zugang angelegt: ' . $bn);
    meldung('Zugang angelegt. Bitte das Startpasswort persönlich weitergeben.');
  } elseif ($aktion === 'aendern') {
    $id = p('id');
    $admins = count(array_filter(benutzer_alle(), fn($b) => $b['rolle'] === 'admin'));
    $alle = benutzer_alle();
    if (!isset($alle[$id])) weiter('benutzer.php');
    if (p('loeschen')) {
      if ($id === $ich['id']) { meldung('Den eigenen Zugang können Sie nicht löschen.', 'fehler'); weiter('benutzer.php'); }
      aendern('benutzer', function (&$a) use ($id) { unset($a[$id]); });
      protokoll('Zugang gelöscht: ' . $alle[$id]['benutzer']);
      meldung('Zugang gelöscht.');
    } else {
      $rolle = p('rolle') === 'admin' ? 'admin' : 'team';
      if ($alle[$id]['rolle'] === 'admin' && $rolle !== 'admin' && $admins < 2) { meldung('Es muss mindestens einen Administrator geben.', 'fehler'); weiter('benutzer.php'); }
      if (p('passwort') !== '' && mb_strlen(p('passwort')) < 10) { meldung('Das neue Passwort braucht mindestens 10 Zeichen.', 'fehler'); weiter('benutzer.php'); }
      aendern('benutzer', function (&$a) use ($id, $rolle) {
        $a[$id]['name'] = p('name') ?: $a[$id]['name']; $a[$id]['email'] = p('email'); $a[$id]['rolle'] = $rolle;
        if (p('passwort') !== '') $a[$id]['hash'] = password_hash(p('passwort'), PASSWORD_DEFAULT);
      });
      meldung('Zugang gespeichert.');
    }
  }
  weiter('benutzer.php');
}
kopf('Benutzer', '');
seitenkopf('Verwaltung', 'Benutzer');
?>
<div class="grid grid--side">
  <section class="card">
    <div class="tabelle-wrap"><table class="tabelle">
      <thead><tr><th>Name</th><th>Benutzername</th><th>E-Mail</th><th>Rolle</th><th>Neues Passwort</th><th>Letzte Anmeldung</th><th></th></tr></thead>
      <tbody><?php foreach (benutzer_alle() as $b): $f = 'u-' . e($b['id']); ?>
        <tr>
          <td><form id="<?= $f ?>" method="post" action="benutzer.php?aktion=aendern"><?= csrf_feld() ?><input type="hidden" name="id" value="<?= e($b['id']) ?>"></form><input form="<?= $f ?>" name="name" value="<?= e($b['name']) ?>"></td>
          <td><?= e($b['benutzer']) ?></td><td><input form="<?= $f ?>" type="email" name="email" value="<?= e($b['email']) ?>"></td>
          <td><?= auswahl('rolle', ['team' => 'Team', 'admin' => 'Administrator'], $b['rolle'], 'form="' . $f . '"') ?></td>
          <td><input form="<?= $f ?>" type="password" name="passwort" placeholder="unverändert" autocomplete="new-password"></td>
          <td class="muted klein nowrap"><?= $b['letzter_login'] ? relativ($b['letzter_login']) : 'noch nie' ?></td>
          <td class="nowrap"><button form="<?= $f ?>" class="btn btn--klein" type="submit">Speichern</button> <?php if ($b['id'] !== $ich['id']): ?><button form="<?= $f ?>" class="btn btn--gefahr btn--klein" name="loeschen" value="1" type="submit" onclick="return confirm('Zugang löschen?')"><?= icon('trash') ?></button><?php endif; ?></td>
        </tr>
      <?php endforeach; ?></tbody></table></div>
  </section>
  <section class="card">
    <h2 style="margin-bottom:14px">Neuer Zugang</h2>
    <form method="post" action="benutzer.php?aktion=neu" class="form"><?= csrf_feld() ?>
      <label class="feld">Name<input name="name" required></label>
      <label class="feld">E-Mail<input type="email" name="email"></label>
      <label class="feld">Benutzername<input name="benutzer" required autocapitalize="none" placeholder="z. B. mueller"></label>
      <label class="feld">Startpasswort (mind. 10 Zeichen)<input name="passwort" required minlength="10" autocomplete="new-password"></label>
      <label class="feld">Rolle<?= auswahl('rolle', ['team' => 'Team', 'admin' => 'Administrator'], 'team') ?></label>
      <p class="muted klein">Administratoren können zusätzlich Zugänge und Einstellungen verwalten.</p>
      <button class="btn" type="submit">Zugang anlegen</button>
    </form>
  </section>
</div>
<?php fuss();
