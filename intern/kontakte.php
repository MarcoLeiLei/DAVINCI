<?php
/* DAVINCI intern – Kontakte und Kunden */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
website_abgleich();
$id = g('id');
$aktion = g('aktion');
$einst = einstellungen();

/* ---------- Export ---------- */
if ($aktion === 'export') {
  $nur = g('nl') === 'erlaubt';
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="davinci-kontakte-' . date('Y-m-d') . '.csv"');
  $out = fopen('php://output', 'w');
  fwrite($out, "\xEF\xBB\xBF");
  fputcsv($out, ['Vorname', 'Nachname', 'Firma', 'E-Mail', 'Telefon', 'Straße', 'PLZ', 'Ort', 'Gruppen', 'Newsletter', 'Newsletter seit', 'Nachweis', 'Quelle', 'Angelegt'], ';');
  foreach (kontakte() as $k) {
    if ($nur && !nl_erlaubt($k)) continue;
    fputcsv($out, [$k['vorname'], $k['nachname'], $k['firma'], $k['email'], $k['telefon'], $k['strasse'], $k['plz'], $k['ort'], implode(', ', $k['gruppen']),
      NL_STATUS[$k['newsletter']['status']] ?? '', datum($k['newsletter']['seit']), $k['newsletter']['nachweis'], $k['quelle'], datum($k['erstellt'])], ';');
  }
  protokoll('Kontakte exportiert' . ($nur ? ' (Newsletter-Empfänger)' : ''));
  exit;
}

/* ---------- Speichern ---------- */
if (ist_post()) {
  if ($aktion === 'speichern') {
    $email = strtolower(p('email'));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { meldung('Die E-Mail-Adresse ist ungültig.', 'fehler'); weiter('kontakte.php?' . ($id ? 'id=' . $id . '&' : '') . 'aktion=bearbeiten'); }
    foreach (kontakte() as $x) if ($email !== '' && $x['email'] === $email && $x['id'] !== $id) { meldung('Diese E-Mail-Adresse gehört schon zu ' . kontakt_name($x) . '.', 'fehler'); weiter('kontakte.php?id=' . $x['id']); }
    $gruppen = array_values(array_filter(array_map('trim', array_merge((array) ($_POST['gruppen'] ?? []), explode(',', p('gruppe_neu'))))));
    $id = aendern('kontakte', function (&$alle) use ($id, $email, $gruppen) {
      $neu = !$id || !isset($alle[$id]);
      $k = $neu ? leerer_kontakt() : $alle[$id];
      foreach (['vorname', 'nachname', 'firma', 'telefon', 'strasse', 'plz', 'ort', 'notiz'] as $f) $k[$f] = p($f);
      $k['email'] = $email; $k['gruppen'] = $gruppen;
      if ($neu) { $k['quelle'] = 'Manuell angelegt'; verlauf($k, 'Kontakt angelegt'); if (gesperrt($email)) nl_setzen($k, 'abgemeldet', 'Adresse steht auf der Sperrliste (früher abgemeldet)'); }
      else verlauf($k, 'Daten bearbeitet');
      $alle[$k['id']] = $k;
      return $k['id'];
    });
    // neue Gruppen in den Einstellungen merken
    if (array_diff($gruppen, $einst['gruppen'])) aendern('einstellungen', function (&$e) use ($gruppen, $einst) { $e['gruppen'] = array_values(array_unique(array_merge($einst['gruppen'], $gruppen))); });
    meldung('Kontakt gespeichert.');
    weiter('kontakte.php?id=' . $id);
  }
  $k = kontakte()[$id] ?? null;
  if (!$k) weiter('kontakte.php');
  if ($aktion === 'newsletter') {
    $st = p('status');
    if (!isset(NL_STATUS[$st])) weiter('kontakte.php?id=' . $id);
    if (in_array($st, ['bestaetigt', 'bestandskunde'], true) && p('nachweis') === '') { meldung('Bitte angeben, wie die Einwilligung vorliegt (Nachweis).', 'fehler'); weiter('kontakte.php?id=' . $id . '#newsletter'); }
    if (in_array($st, ['bestaetigt', 'bestandskunde'], true) && gesperrt($k['email']) && !p('trotzdem')) { meldung('Diese Adresse hat sich früher abgemeldet. Nur mit neuer, nachweisbarer Einwilligung wieder aufnehmen.', 'fehler'); weiter('kontakte.php?id=' . $id . '#newsletter'); }
    aendern('kontakte', function (&$alle) use ($id, $st) { nl_setzen($alle[$id], $st, p('nachweis') ?: 'Manuell geändert von ' . (ich()['name'] ?? '')); });
    if ($st === 'abgemeldet') sperren($k['email']);
    elseif (in_array($st, ['bestaetigt', 'bestandskunde'], true)) aendern('sperrliste', function (&$s) use ($k) { unset($s[hash('sha256', strtolower($k['email']))]); });
    meldung('Newsletter-Status gespeichert.');
  } elseif ($aktion === 'notiz' && p('text') !== '') {
    aendern('kontakte', function (&$alle) use ($id) { verlauf($alle[$id], 'Notiz: ' . p('text')); });
  } elseif ($aktion === 'loeschen') {
    if (($k['newsletter']['status'] ?? '') === 'abgemeldet') sperren($k['email']);
    aendern('kontakte', function (&$alle) use ($id) { unset($alle[$id]); });
    aendern('anfragen', function (&$a) use ($id) { foreach ($a as &$x) if ($x['kontakt_id'] === $id) $x['kontakt_id'] = null; });
    aendern('leads', function (&$l) use ($id) { foreach ($l as &$x) if ($x['kontakt_id'] === $id) $x['kontakt_id'] = null; });
    protokoll('Kontakt gelöscht: ' . kontakt_name($k));
    meldung('Kontakt gelöscht.');
    weiter('kontakte.php');
  }
  weiter('kontakte.php?id=' . $id);
}

/* ---------- Formular ---------- */
if ($aktion === 'neu' || $aktion === 'bearbeiten') {
  $k = $id ? (kontakte()[$id] ?? leerer_kontakt()) : leerer_kontakt();
  kopf($id ? 'Kontakt bearbeiten' : 'Neuer Kontakt', 'kontakte');
  seitenkopf('Kontakte', $id ? e(kontakt_name($k)) : 'Neuer Kontakt');
  ?>
  <section class="card" style="max-width:860px">
    <form method="post" action="kontakte.php?aktion=speichern<?= $id ? '&id=' . e($id) : '' ?>" class="form form--2"><?= csrf_feld() ?>
      <label class="feld">Vorname<input name="vorname" value="<?= e($k['vorname']) ?>" autofocus></label>
      <label class="feld">Nachname<input name="nachname" value="<?= e($k['nachname']) ?>"></label>
      <label class="feld">E-Mail<input type="email" name="email" value="<?= e($k['email']) ?>"></label>
      <label class="feld">Telefon<input type="tel" name="telefon" value="<?= e($k['telefon']) ?>"></label>
      <label class="feld voll">Firma (optional)<input name="firma" value="<?= e($k['firma']) ?>"></label>
      <label class="feld voll">Straße und Hausnummer<input name="strasse" value="<?= e($k['strasse']) ?>"></label>
      <label class="feld">PLZ<input name="plz" value="<?= e($k['plz']) ?>"></label>
      <label class="feld">Ort<input name="ort" value="<?= e($k['ort']) ?>"></label>
      <div class="feld voll">Gruppen
        <div class="checks"><?php foreach (array_unique(array_merge($einst['gruppen'], $k['gruppen'])) as $gr): ?><label class="chip"><input type="checkbox" name="gruppen[]" value="<?= e($gr) ?>"<?= in_array($gr, $k['gruppen'], true) ? ' checked' : '' ?>><span><?= e($gr) ?></span></label><?php endforeach; ?></div>
        <input name="gruppe_neu" placeholder="Neue Gruppe hinzufügen (mehrere mit Komma)">
      </div>
      <label class="feld voll">Notiz<textarea name="notiz"><?= e($k['notiz']) ?></textarea></label>
      <div class="voll knopfreihe"><button class="btn" type="submit">Speichern</button><a class="btn btn--hell" href="<?= $id ? 'kontakte.php?id=' . e($id) : 'kontakte.php' ?>">Abbrechen</a></div>
    </form>
  </section>
  <?php fuss(); exit;
}

/* ---------- Einzelansicht ---------- */
if ($id) {
  $k = kontakte()[$id] ?? null;
  if (!$k) { meldung('Kontakt nicht gefunden.', 'fehler'); weiter('kontakte.php'); }
  $seineAnfragen = array_filter(anfragen(), fn($a) => $a['kontakt_id'] === $id);
  $seineLeads = array_filter(leads(), fn($l) => $l['kontakt_id'] === $id);
  $seineAufgaben = array_filter(aufgaben(), fn($a) => ($a['bezug']['id'] ?? null) === $id && !$a['erledigt']);
  $nl = $k['newsletter'];
  kopf(kontakt_name($k), 'kontakte');
  seitenkopf($k['firma'] ?: 'Kontakt', e(kontakt_name($k)),
    '<a class="btn btn--hell" href="kontakte.php?id=' . e($id) . '&aktion=bearbeiten">Bearbeiten</a><a class="btn btn--hell" href="aufgaben.php?aktion=neu&bezug=kontakt:' . e($id) . '">' . icon('plus') . ' Aufgabe</a><a class="btn" href="leads.php?aktion=neu&kontakt=' . e($id) . '">' . icon('plus') . ' Projekt</a>');
  ?>
  <div class="grid grid--side">
    <div class="stack">
      <section class="card">
        <dl class="dl">
          <dt>E-Mail</dt><dd><?= $k['email'] ? '<a href="mailto:' . e($k['email']) . '">' . e($k['email']) . '</a>' : '–' ?></dd>
          <dt>Telefon</dt><dd><?= $k['telefon'] ? '<a href="tel:' . e(preg_replace('/[^\d+]/', '', $k['telefon'])) . '">' . e($k['telefon']) . '</a>' : '–' ?></dd>
          <dt>Adresse</dt><dd><?= e(trim($k['strasse'] . "\n" . trim($k['plz'] . ' ' . $k['ort'])) ?: '–') ?></dd>
          <dt>Gruppen</dt><dd><?php foreach ($k['gruppen'] as $gr): ?><span class="tag"><?= e($gr) ?></span><?php endforeach; ?><?= $k['gruppen'] ? '' : '–' ?></dd>
          <dt>Quelle</dt><dd><?= e($k['quelle'] ?: '–') ?></dd>
          <dt>Angelegt</dt><dd><?= datum($k['erstellt']) ?></dd>
          <?php if ($k['notiz']): ?><dt>Notiz</dt><dd><?= e($k['notiz']) ?></dd><?php endif; ?>
        </dl>
      </section>
      <?php if ($seineLeads || $seineAnfragen): ?>
      <section class="card">
        <h2 style="margin-bottom:14px">Projekte und Anfragen</h2>
        <ul class="liste">
          <?php foreach ($seineLeads as $l): ?><li><div class="liste__haupt"><a class="liste__titel" href="leads.php?id=<?= e($l['id']) ?>"><?= e($l['titel']) ?></a><span class="liste__sub">Projekt<?= $l['wert'] ? ' · ' . eur($l['wert']) : '' ?></span></div><?= status_pill($l['status'], LEAD_STATUS) ?></li><?php endforeach; ?>
          <?php foreach ($seineAnfragen as $aid => $a): ?><li><div class="liste__haupt"><a class="liste__titel" href="anfragen.php?id=<?= e($aid) ?>"><?= e($a['typ']) ?></a><span class="liste__sub"><?= datum($a['zeit'], true) ?></span></div><?= status_pill($a['status'], ANFRAGE_STATUS) ?></li><?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>
      <section class="card">
        <h2 style="margin-bottom:14px">Verlauf</h2>
        <form method="post" action="kontakte.php?id=<?= e($id) ?>&aktion=notiz" class="knopfreihe" style="margin-bottom:18px"><?= csrf_feld() ?><input name="text" placeholder="Notiz zum Verlauf, z. B. „Telefoniert, Grundstück in Ratingen“" style="flex:1" required><button class="btn btn--klein" type="submit">Eintragen</button></form>
        <ul class="verlauf"><?php foreach ($k['verlauf'] as $v): ?><li><?= e($v['text']) ?><small><?= datum($v['zeit'], true) ?> · <?= e($v['wer']) ?></small></li><?php endforeach; ?></ul>
      </section>
    </div>
    <div class="stack">
      <section class="card" id="newsletter">
        <div class="card__head"><h2>Newsletter</h2><?= status_pill($nl['status'], NL_STATUS) ?></div>
        <?php if ($nl['nachweis']): ?><p class="hinweis" style="margin-bottom:14px"><?= e($nl['nachweis']) ?><br><span class="muted">seit <?= datum($nl['seit'], true) ?></span></p><?php endif; ?>
        <form method="post" action="kontakte.php?id=<?= e($id) ?>&aktion=newsletter" class="form"><?= csrf_feld() ?>
          <label class="feld">Status ändern<?= auswahl('status', NL_STATUS, $nl['status']) ?></label>
          <label class="feld">Nachweis der Einwilligung<textarea name="nachweis" rows="3" style="min-height:80px" placeholder="z. B. „Schriftlich im Vertrag vom 12.03.2025 eingewilligt“ oder „Kunde seit 2019, Haus gekauft“"></textarea></label>
          <?php if (gesperrt($k['email'])): ?><label class="check"><input type="checkbox" name="trotzdem" value="1"><span>Adresse ist auf der Sperrliste. Eine neue Einwilligung liegt nachweisbar vor.</span></label><?php endif; ?>
          <button class="btn btn--klein" type="submit">Status speichern</button>
          <p class="muted klein">Newsletter gehen nur an „Eingewilligt“ und „Bestandskunde“. Bestandskunden (§ 7 Abs. 3 UWG) dürfen nur Werbung für eigene, ähnliche Leistungen bekommen und müssen beim Kauf auf das Widerspruchsrecht hingewiesen worden sein.</p>
        </form>
      </section>
      <?php if ($seineAufgaben): ?>
      <section class="card"><h2 style="margin-bottom:10px">Offene Aufgaben</h2>
        <?php foreach ($seineAufgaben as $a): ?><div class="aufgabe"><div class="aufgabe__haupt"><a class="aufgabe__titel" href="aufgaben.php?id=<?= e($a['id']) ?>"><?= e($a['titel']) ?></a><div class="aufgabe__meta"><?= $a['faellig'] ? 'bis ' . datum($a['faellig']) : '' ?></div></div></div><?php endforeach; ?>
      </section>
      <?php endif; ?>
      <section class="card">
        <form method="post" action="kontakte.php?id=<?= e($id) ?>&aktion=loeschen" data-bestaetigen="Kontakt endgültig löschen? Anfragen und Projekte bleiben ohne Kontakt erhalten."><?= csrf_feld() ?>
          <button class="btn btn--gefahr btn--klein" type="submit"><?= icon('trash') ?> Kontakt löschen</button></form>
      </section>
    </div>
  </div>
  <?php fuss(); exit;
}

/* ---------- Liste ---------- */
$q = mb_strtolower(g('q')); $gruppe = g('gruppe'); $nlf = g('nl');
$alle = kontakte();
$liste = array_filter($alle, function ($k) use ($q, $gruppe, $nlf) {
  if ($gruppe && !in_array($gruppe, $k['gruppen'], true)) return false;
  if ($nlf === 'erlaubt' && !nl_erlaubt($k)) return false;
  if ($nlf && $nlf !== 'erlaubt' && $k['newsletter']['status'] !== $nlf) return false;
  if ($q && !str_contains(mb_strtolower(implode(' ', [$k['vorname'], $k['nachname'], $k['firma'], $k['email'], $k['ort'], $k['telefon']])), $q)) return false;
  return true;
});
uasort($liste, fn($a, $b) => strcmp($b['geaendert'], $a['geaendert']));
$gruppen = [];
foreach ($alle as $k) foreach ($k['gruppen'] as $gr) $gruppen[$gr] = ($gruppen[$gr] ?? 0) + 1;
ksort($gruppen);
kopf('Kontakte', 'kontakte');
seitenkopf('Kunden und Interessenten', 'Kontakte',
  '<a class="btn btn--hell" href="import.php">' . icon('upload') . ' Import</a><a class="btn btn--hell" href="kontakte.php?aktion=export' . ($nlf === 'erlaubt' ? '&nl=erlaubt' : '') . '">Export</a><a class="btn" href="kontakte.php?aktion=neu">' . icon('plus') . ' Neuer Kontakt</a>');
?>
<form class="filter" method="get">
  <input type="search" name="q" value="<?= e(g('q')) ?>" placeholder="Name, E-Mail, Ort …">
  <?= auswahl('gruppe', ['' => 'Alle Gruppen'] + array_combine(array_keys($gruppen), array_map(fn($g, $n) => "$g ($n)", array_keys($gruppen), $gruppen)), $gruppe, 'data-auto') ?>
  <?= auswahl('nl', ['' => 'Newsletter: alle', 'erlaubt' => 'Darf Newsletter bekommen'] + NL_STATUS, $nlf, 'data-auto') ?>
  <span class="muted klein"><?= count($liste) ?> von <?= count($alle) ?></span>
</form>
<section class="card">
  <?php if (!$liste): ?><?= leer($alle ? 'Keine Kontakte für diesen Filter.' : 'Noch keine Kontakte. Sie entstehen automatisch aus Website-Anfragen, per Import oder manuell.') ?><?php else: ?>
  <div class="tabelle-wrap"><table class="tabelle">
    <thead><tr><th>Name</th><th>E-Mail</th><th>Ort</th><th>Gruppen</th><th>Newsletter</th><th>Geändert</th></tr></thead>
    <tbody>
    <?php foreach ($liste as $k): ?>
      <tr data-href="kontakte.php?id=<?= e($k['id']) ?>">
        <td><a href="kontakte.php?id=<?= e($k['id']) ?>"><?= e(kontakt_name($k)) ?></a><?= $k['firma'] && trim($k['vorname'] . $k['nachname']) ? '<br><span class="muted klein">' . e($k['firma']) . '</span>' : '' ?></td>
        <td><?= e($k['email']) ?></td>
        <td><?= e($k['ort']) ?></td>
        <td><?php foreach (array_slice($k['gruppen'], 0, 3) as $gr): ?><span class="tag"><?= e($gr) ?></span><?php endforeach; ?></td>
        <td><?= status_pill($k['newsletter']['status'], ['kein' => '–', 'angefragt' => 'wartet', 'bestaetigt' => 'eingewilligt', 'bestandskunde' => 'Bestandskunde', 'abgemeldet' => 'abgemeldet']) ?></td>
        <td class="nowrap muted klein"><?= relativ($k['geaendert']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php fuss();
