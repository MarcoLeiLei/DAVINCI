<?php
/* DAVINCI intern – Kontakte aus Excel oder CSV importieren */
require __DIR__ . '/lib/start.php';
require __DIR__ . '/lib/tabelle.php';
$ich = seite_schuetzen();
$einst = einstellungen();
$puffer = 'import-' . $ich['id'];
const FELDER = ['' => '– nicht übernehmen –', 'vorname' => 'Vorname', 'nachname' => 'Nachname', 'name' => 'Vor- und Nachname', 'email' => 'E-Mail',
                'firma' => 'Firma', 'telefon' => 'Telefon', 'strasse' => 'Straße', 'plz' => 'PLZ', 'ort' => 'Ort', 'gruppe' => 'Gruppe'];

if (ist_post() && g('aktion') === 'hochladen') {
  $f = $_FILES['datei'] ?? null;
  try {
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Bitte eine Datei auswählen.');
    if ($f['size'] > 8 * 1024 * 1024) throw new RuntimeException('Die Datei ist größer als 8 MB.');
    $zeilen = tabelle_lesen($f['tmp_name'], $f['name']);
    $zeilen = array_values(array_filter($zeilen, fn($z) => implode('', $z) !== ''));
    if (count($zeilen) < 2) throw new RuntimeException('Die Datei enthält keine Daten (mindestens Kopfzeile und eine Zeile).');
    if (count($zeilen) > 20001) throw new RuntimeException('Bitte höchstens 20.000 Zeilen auf einmal importieren.');
    schreiben($puffer, ['name' => $f['name'], 'zeilen' => $zeilen, 'zeit' => time()]);
    weiter('import.php?schritt=2');
  } catch (Throwable $e) { meldung($e->getMessage(), 'fehler'); weiter('import.php'); }
}

if (ist_post() && g('aktion') === 'import') {
  $d = lesen($puffer);
  if (!$d) weiter('import.php');
  $zuordnung = (array) ($_POST['spalte'] ?? []);
  if (!in_array('email', $zuordnung, true) && !in_array('nachname', $zuordnung, true) && !in_array('name', $zuordnung, true)) { meldung('Bitte mindestens die Spalte E-Mail oder Name zuordnen.', 'fehler'); weiter('import.php?schritt=2'); }
  $nl = p('newsletter');
  if (in_array($nl, ['bestaetigt', 'bestandskunde'], true) && (p('nachweis') === '' || !p('bestaetigung'))) { meldung('Für den Newsletter bitte den Nachweis eintragen und bestätigen, dass die Einwilligung vorliegt.', 'fehler'); weiter('import.php?schritt=2'); }
  $gruppen = array_values(array_filter(array_map('trim', array_merge((array) ($_POST['gruppen'] ?? []), explode(',', p('gruppe_neu'))))));
  $zeilen = array_slice($d['zeilen'], p('kopfzeile') ? 1 : 0);
  $erg = ['neu' => 0, 'aktualisiert' => 0, 'uebersprungen' => 0, 'gesperrt' => 0];
  aendern('kontakte', function (&$alle) use ($zeilen, $zuordnung, $nl, $gruppen, &$erg, $d) {
    $mailIndex = [];
    foreach ($alle as $k) if ($k['email']) $mailIndex[strtolower($k['email'])] = $k['id'];
    foreach ($zeilen as $z) {
      $w = [];
      foreach ($zuordnung as $i => $feld) if ($feld !== '' && isset($z[$i]) && $z[$i] !== '') $w[$feld] = isset($w[$feld]) ? $w[$feld] . ' ' . $z[$i] : $z[$i];
      if (!empty($w['name']) && empty($w['nachname'])) [$w['vorname'], $w['nachname']] = name_teilen($w['name']);
      $w['email'] = strtolower(trim($w['email'] ?? ''));
      if ($w['email'] !== '' && !filter_var($w['email'], FILTER_VALIDATE_EMAIL)) $w['email'] = '';
      if ($w['email'] === '' && empty($w['nachname']) && empty($w['firma'])) { $erg['uebersprungen']++; continue; }
      $gr = array_merge($gruppen, !empty($w['gruppe']) ? array_map('trim', preg_split('/[,;\/]/', $w['gruppe'])) : []);
      $warDa = $w['email'] !== '' && isset($mailIndex[$w['email']]);
      $w['quelle'] = 'Import ' . $d['name'];
      $kid = kontakt_zusammenfuehren($alle, $w, $warDa ? 'Import: ergänzt aus ' . $d['name'] : 'Importiert aus ' . $d['name'], $gr);
      if ($w['email'] !== '') $mailIndex[$w['email']] = $kid;
      $erg[$warDa ? 'aktualisiert' : 'neu']++;
      if (in_array($nl, ['bestaetigt', 'bestandskunde'], true) && $w['email'] !== '') {
        $st = $alle[$kid]['newsletter']['status'];
        if ($st === 'abgemeldet' || gesperrt($w['email'])) { if ($st !== 'abgemeldet') nl_setzen($alle[$kid], 'abgemeldet', 'Adresse steht auf der Sperrliste'); $erg['gesperrt']++; }
        elseif (!in_array($st, ['bestaetigt', 'bestandskunde'], true)) nl_setzen($alle[$kid], $nl, p('nachweis') . ' (Import ' . $d['name'] . ', ' . date('d.m.Y') . ', ' . (ich()['name'] ?? '') . ')');
      }
    }
  });
  if (array_diff($gruppen, $einst['gruppen'])) aendern('einstellungen', function (&$e) use ($gruppen, $einst) { $e['gruppen'] = array_values(array_unique(array_merge($einst['gruppen'], $gruppen))); });
  @unlink(DATEN . "/$puffer.php");
  protokoll("Import {$d['name']}: {$erg['neu']} neu, {$erg['aktualisiert']} ergänzt");
  meldung("Import fertig: {$erg['neu']} neu, {$erg['aktualisiert']} ergänzt" . ($erg['uebersprungen'] ? ", {$erg['uebersprungen']} ohne Name/E-Mail übersprungen" : '') . ($erg['gesperrt'] ? ", {$erg['gesperrt']} abgemeldete Adressen ohne Newsletter" : '') . '.');
  weiter('kontakte.php');
}

$d = g('schritt') === '2' ? lesen($puffer) : [];
kopf('Import', 'kontakte');
seitenkopf('Kontakte', 'Import aus Excel oder CSV', '<a class="btn btn--hell" href="kontakte.php">Zurück</a>');
if (!$d): ?>
  <div class="grid grid--2">
    <section class="card">
      <h2 style="margin-bottom:8px">1 · Datei hochladen</h2>
      <p class="muted" style="margin-bottom:18px">Excel (.xlsx) oder CSV. Die erste Zeile sollte Überschriften wie Vorname, Nachname, E-Mail, Telefon, Ort enthalten. Gibt es eine Adresse schon, werden nur leere Felder ergänzt.</p>
      <form method="post" action="import.php?aktion=hochladen" enctype="multipart/form-data" class="form"><?= csrf_feld() ?>
        <input type="file" name="datei" accept=".xlsx,.csv,.txt" required>
        <div><button class="btn" type="submit"><?= icon('upload') ?> Weiter</button></div>
      </form>
    </section>
    <section class="card">
      <h2 style="margin-bottom:8px">Newsletter und Einwilligung</h2>
      <p class="muted">Importierte Kontakte bekommen erst einen Newsletter, wenn eine Einwilligung vorliegt. Im nächsten Schritt können Sie für die ganze Liste festlegen:</p>
      <ul class="muted" style="margin:12px 0 0 18px;padding:0">
        <li><b>Eingewilligt:</b> Die Personen haben dem Newsletter nachweisbar zugestimmt, z. B. auf einem Formular oder per E-Mail.</li>
        <li><b>Bestandskunde:</b> Kunden, die bei Ihnen gekauft haben und beim Kauf auf den Widerspruch hingewiesen wurden (§ 7 Abs. 3 UWG).</li>
        <li><b>Keine Einwilligung:</b> nur Kontaktdaten, kein Newsletter.</li>
      </ul>
    </section>
  </div>
<?php else:
  $kopf = $d['zeilen'][0];
  $spalten = max(array_map('count', array_slice($d['zeilen'], 0, 50)));
  ?>
  <form method="post" action="import.php?aktion=import" class="stack"><?= csrf_feld() ?>
    <section class="card">
      <div class="card__head"><h2>2 · Spalten zuordnen</h2><span class="muted klein"><?= e($d['name']) ?> · <?= count($d['zeilen']) - 1 ?> Zeilen</span></div>
      <label class="check" style="margin-bottom:14px"><input type="checkbox" name="kopfzeile" value="1" checked><span>Die erste Zeile enthält Überschriften</span></label>
      <div class="tabelle-wrap"><table class="tabelle">
        <thead><tr><?php for ($i = 0; $i < $spalten; $i++): ?><th style="min-width:150px"><?= auswahl("spalte[$i]", FELDER, spalte_erkennen((string) ($kopf[$i] ?? ''))) ?></th><?php endfor; ?></tr></thead>
        <tbody><?php foreach (array_slice($d['zeilen'], 0, 8) as $n => $z): ?><tr<?= $n === 0 ? ' style="font-weight:400;color:var(--grey-4)"' : '' ?>><?php for ($i = 0; $i < $spalten; $i++): ?><td><?= e(mb_strimwidth((string) ($z[$i] ?? ''), 0, 40, '…')) ?></td><?php endfor; ?></tr><?php endforeach; ?></tbody>
      </table></div>
    </section>
    <section class="card">
      <h2 style="margin-bottom:16px">3 · Gruppe und Newsletter</h2>
      <div class="form">
        <div class="feld">Diese Kontakte in folgende Gruppen aufnehmen
          <div class="checks"><?php foreach ($einst['gruppen'] as $gr): ?><label class="chip"><input type="checkbox" name="gruppen[]" value="<?= e($gr) ?>"><span><?= e($gr) ?></span></label><?php endforeach; ?></div>
          <input name="gruppe_neu" placeholder="Neue Gruppe, z. B. „Messe Düsseldorf 2026“"></div>
        <label class="feld">Newsletter<?= auswahl('newsletter', ['kein' => 'Keine Einwilligung (nur Kontaktdaten)', 'bestaetigt' => 'Eingewilligt: Zustimmung liegt nachweisbar vor', 'bestandskunde' => 'Bestandskunden nach § 7 Abs. 3 UWG'], 'kein') ?></label>
        <label class="feld">Nachweis (wird bei jedem Kontakt gespeichert)<input name="nachweis" placeholder="z. B. „Anmeldeliste Tag der offenen Tür 14.06.2026, unterschrieben“"></label>
        <label class="check"><input type="checkbox" name="bestaetigung" value="1"><span>Ich bestätige, dass für alle Adressen dieser Liste die gewählte Grundlage vorliegt und nachgewiesen werden kann.</span></label>
        <p class="muted klein">Adressen, die sich früher abgemeldet haben, bekommen keinen Newsletter, auch wenn sie in der Liste stehen.</p>
        <div class="knopfreihe"><button class="btn" type="submit">Importieren</button><a class="btn btn--hell" href="import.php">Andere Datei</a></div>
      </div>
    </section>
  </form>
<?php endif; fuss();
