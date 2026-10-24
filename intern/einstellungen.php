<?php
/* DAVINCI intern – Einstellungen (nur Administratoren) */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen(true);
$e = einstellungen();
if (ist_post()) {
  aendern('einstellungen', function (&$x) {
    $x['absender_name'] = p('absender_name') ?: 'DAVINCI';
    $x['absender'] = filter_var(p('absender'), FILTER_VALIDATE_EMAIL) ?: 'bauen@davinci-homes.de';
    $x['antwort'] = filter_var(p('antwort'), FILTER_VALIDATE_EMAIL) ?: $x['absender'];
    $x['stundenlimit'] = max(10, min(2000, (int) p('stundenlimit')));
    $x['paket'] = max(1, min(50, (int) p('paket')));
    $x['gruppen'] = array_values(array_filter(array_map('trim', explode("\n", p('gruppen')))));
  });
  protokoll('Einstellungen geändert');
  meldung('Einstellungen gespeichert.');
  weiter('einstellungen.php');
}
$abgleich = lesen('abgleich');
kopf('Einstellungen', '');
seitenkopf('Verwaltung', 'Einstellungen');
?>
<div class="grid grid--side">
  <section class="card">
    <form method="post" class="form form--2"><?= csrf_feld() ?>
      <h2 class="voll">Newsletter-Versand</h2>
      <label class="feld">Absendername<input name="absender_name" value="<?= e($e['absender_name']) ?>"></label>
      <label class="feld">Absender-Adresse<input type="email" name="absender" value="<?= e($e['absender']) ?>"></label>
      <label class="feld voll">Antworten gehen an<input type="email" name="antwort" value="<?= e($e['antwort']) ?>"></label>
      <label class="feld">Höchstens E-Mails pro Stunde<input type="number" name="stundenlimit" value="<?= (int) $e['stundenlimit'] ?>" min="10" max="2000"></label>
      <label class="feld">E-Mails pro Paket<input type="number" name="paket" value="<?= (int) $e['paket'] ?>" min="1" max="50"></label>
      <p class="muted klein voll">SiteGround begrenzt die Zahl der E-Mails pro Stunde je nach Tarif (StartUp etwa 400, GrowBig 800, GoGeek 1.200). Bleiben Sie mit etwas Abstand darunter, die Website verschickt ja auch E-Mails. Größere Listen werden automatisch über mehrere Stunden verteilt.</p>
      <h2 class="voll" style="margin-top:10px">Gruppen</h2>
      <label class="feld voll">Eine Gruppe pro Zeile<textarea name="gruppen" rows="6"><?= e(implode("\n", $e['gruppen'])) ?></textarea></label>
      <div class="voll"><button class="btn" type="submit">Speichern</button></div>
    </form>
  </section>
  <div class="stack">
    <section class="card">
      <h2 style="margin-bottom:12px">Verbindung zur Website</h2>
      <?php if (website_ordner()): ?><p class="hinweis hinweis--ok">Verbunden. Letzter Abgleich <?= relativ(date('c', (int) ($abgleich['zeit'] ?? 0))) ?>.</p>
      <?php else: ?><p class="hinweis hinweis--warn">Der Ordner data/ der Website wurde nicht gefunden. Pfad in config.php unter „website_daten“ eintragen.</p><?php endif; ?>
    </section>
    <section class="card">
      <h2 style="margin-bottom:12px">Protokoll</h2>
      <ul class="verlauf"><?php foreach (array_slice(lesen('protokoll'), 0, 25) as $p): ?><li><?= e($p['text']) ?><small><?= datum($p['zeit'], true) ?> · <?= e($p['wer']) ?></small></li><?php endforeach; ?></ul>
    </section>
  </div>
</div>
<?php fuss();
