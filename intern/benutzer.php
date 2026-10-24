<?php
/* DAVINCI intern – Zugänge verwalten (nur Administratoren) */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen(true);
$aktion = g('aktion');
const PROFIL = ['name', 'email', 'position', 'telefon', 'mobil'];
if (ist_post()) {
  if ($aktion === 'neu') {
    $bn = strtolower(p('benutzer'));
    $fehler = !preg_match('/^[a-z0-9._-]{2,40}$/', $bn) ? 'Benutzername: nur Kleinbuchstaben, Ziffern, Punkt, Bindestrich.' : (mb_strlen(p('passwort')) < 10 ? 'Das Startpasswort braucht mindestens 10 Zeichen.' : '');
    foreach (benutzer_alle() as $b) if ($b['benutzer'] === $bn) $fehler = 'Diesen Benutzernamen gibt es schon.';
    if ($fehler) { meldung($fehler, 'fehler'); weiter('benutzer.php'); }
    aendern('benutzer', function (&$alle) use ($bn) {
      $id = neue_id();
      $alle[$id] = ['id' => $id, 'benutzer' => $bn, 'rolle' => p('rolle') === 'admin' ? 'admin' : 'team',
                    'hash' => password_hash(p('passwort'), PASSWORD_DEFAULT), 'erstellt' => date('c'), 'letzter_login' => null];
      foreach (PROFIL as $f) $alle[$id][$f] = p($f);
      $alle[$id]['name'] = $alle[$id]['name'] ?: $bn;
    });
    protokoll('Zugang angelegt: ' . $bn);
    meldung('Zugang angelegt. Bitte das Startpasswort persönlich weitergeben.');
  } elseif ($aktion === 'aendern') {
    $id = p('id');
    $alle = benutzer_alle();
    if (!isset($alle[$id])) weiter('benutzer.php');
    if (p('loeschen')) {
      if ($id === $ich['id']) { meldung('Den eigenen Zugang können Sie nicht löschen.', 'fehler'); weiter('benutzer.php'); }
      aendern('benutzer', function (&$a) use ($id) { unset($a[$id]); });
      protokoll('Zugang gelöscht: ' . $alle[$id]['benutzer']);
      meldung('Zugang gelöscht.');
    } else {
      $rolle = p('rolle') === 'admin' ? 'admin' : 'team';
      $admins = count(array_filter($alle, fn($b) => $b['rolle'] === 'admin'));
      if ($alle[$id]['rolle'] === 'admin' && $rolle !== 'admin' && $admins < 2) { meldung('Es muss mindestens einen Administrator geben.', 'fehler'); weiter('benutzer.php'); }
      if (p('passwort') !== '' && mb_strlen(p('passwort')) < 10) { meldung('Das neue Passwort braucht mindestens 10 Zeichen.', 'fehler'); weiter('benutzer.php'); }
      aendern('benutzer', function (&$a) use ($id, $rolle) {
        foreach (PROFIL as $f) $a[$id][$f] = p($f);
        $a[$id]['name'] = $a[$id]['name'] ?: $a[$id]['benutzer'];
        $a[$id]['rolle'] = $rolle;
        if (p('passwort') !== '') $a[$id]['hash'] = password_hash(p('passwort'), PASSWORD_DEFAULT);
      });
      meldung('Zugang gespeichert.');
    }
  }
  weiter('benutzer.php');
}
$felder = function (array $b = []) { ?>
  <label class="feld">Name<input name="name" value="<?= e($b['name'] ?? '') ?>" required></label>
  <label class="feld">Rolle im Team (für die Signatur)<input name="position" value="<?= e($b['position'] ?? '') ?>" placeholder="z. B. Gründer · Architektur und Vertrieb"></label>
  <label class="feld">E-Mail<input type="email" name="email" value="<?= e($b['email'] ?? '') ?>" placeholder="name@davinci-homes.de"></label>
  <label class="feld">Telefon<input name="telefon" value="<?= e($b['telefon'] ?? '') ?>" placeholder="+49 211 …"></label>
  <label class="feld">Mobil<input name="mobil" value="<?= e($b['mobil'] ?? '') ?>" placeholder="+49 157 …"></label>
<?php };
kopf('Benutzer', '');
seitenkopf('Verwaltung', 'Benutzer');
?>
<div class="grid grid--side">
  <div class="stack">
    <?php foreach (benutzer_alle() as $b): ?>
      <section class="card">
        <div class="card__head"><h2><?= e($b['name']) ?></h2><span class="muted klein"><?= e($b['benutzer']) ?> · <?= $b['letzter_login'] ? 'zuletzt ' . relativ($b['letzter_login']) : 'noch nie angemeldet' ?></span></div>
        <form method="post" action="benutzer.php?aktion=aendern" class="form form--3"><?= csrf_feld() ?><input type="hidden" name="id" value="<?= e($b['id']) ?>">
          <?php $felder($b); ?>
          <label class="feld">Zugriff<?= auswahl('rolle', ['team' => 'Team', 'admin' => 'Administrator'], $b['rolle']) ?></label>
          <label class="feld voll">Neues Passwort (leer = unverändert)<input type="password" name="passwort" autocomplete="new-password"></label>
          <div class="voll knopfreihe"><button class="btn btn--klein" type="submit">Speichern</button>
            <?php if ($b['id'] !== $ich['id']): ?><button class="btn btn--gefahr btn--klein" name="loeschen" value="1" type="submit" onclick="return confirm('Zugang löschen?')"><?= icon('trash') ?> Löschen</button><?php endif; ?></div>
        </form>
        <details style="margin-top:14px"><summary class="linkbtn">Signatur ansehen</summary><div style="margin-top:12px;padding:16px;border:1px solid var(--line);border-radius:12px;background:#fff"><?= signatur_html($b + ['position' => '', 'telefon' => '', 'mobil' => '', 'email' => '']) ?></div></details>
      </section>
    <?php endforeach; ?>
  </div>
  <section class="card">
    <h2 style="margin-bottom:14px">Neuer Zugang</h2>
    <form method="post" action="benutzer.php?aktion=neu" class="form"><?= csrf_feld() ?>
      <?php $felder(); ?>
      <label class="feld">Benutzername<input name="benutzer" required autocapitalize="none" placeholder="z. B. mueller"></label>
      <label class="feld">Startpasswort (mind. 10 Zeichen)<input name="passwort" required minlength="10" autocomplete="new-password"></label>
      <label class="feld">Zugriff<?= auswahl('rolle', ['team' => 'Team', 'admin' => 'Administrator'], 'team') ?></label>
      <p class="muted klein">Die Rolle im Team erscheint in der E-Mail-Signatur. Administratoren können zusätzlich Zugänge und Einstellungen verwalten.</p>
      <button class="btn" type="submit">Zugang anlegen</button>
    </form>
  </section>
</div>
<?php fuss();
