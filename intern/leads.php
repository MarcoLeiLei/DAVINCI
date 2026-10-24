<?php
/* DAVINCI intern – Projekte und Leads mit Status */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
$id = g('id');
$aktion = g('aktion');
$kontakte = kontakte();

/* Status per Ziehen im Board */
if (ist_post() && $aktion === 'status') {
  $lid = p('id'); $st = p('status');
  if (!isset(LEAD_STATUS[$st])) json_aus(['ok' => false], 422);
  $ok = aendern('leads', function (&$l) use ($lid, $st) {
    if (!isset($l[$lid])) return false;
    if ($l[$lid]['status'] !== $st) {
      verlauf($l[$lid], 'Status: ' . LEAD_STATUS[$l[$lid]['status']] . ' → ' . LEAD_STATUS[$st]);
      $l[$lid]['status'] = $st;
      $l[$lid]['abgeschlossen'] = in_array($st, ['gewonnen', 'verloren'], true) ? date('c') : null;
    }
    return true;
  });
  json_aus(['ok' => $ok]);
}

if (ist_post() && $aktion === 'speichern') {
  $kid = p('kontakt_id') ?: null;
  // Neuer Kontakt direkt im Projekt
  if (!$kid && (p('neu_name') !== '' || p('neu_email') !== '')) {
    [$vn, $nn] = name_teilen(p('neu_name'));
    $kid = aendern('kontakte', fn(&$k) => kontakt_zusammenfuehren($k, ['vorname' => $vn, 'nachname' => $nn, 'email' => strtolower(p('neu_email')), 'telefon' => p('neu_telefon'), 'quelle' => 'Projekt'], 'Kontakt angelegt (Projekt)', ['Interessent']));
  }
  $id = aendern('leads', function (&$l) use ($id, $kid) {
    $neu = !$id || !isset($l[$id]);
    $x = $neu ? leerer_lead() : $l[$id];
    $alt = $x['status'];
    foreach (['titel', 'haus', 'ort', 'naechster_schritt', 'naechster_termin', 'notizen'] as $f) $x[$f] = p($f);
    $x['wert'] = (string) (float) str_replace(['.', ','], ['', '.'], p('wert') ?: '0');
    if ($x['wert'] === '0') $x['wert'] = '';
    $x['kontakt_id'] = $kid;
    $x['verantwortlich'] = p('verantwortlich') ?: null;
    $x['status'] = isset(LEAD_STATUS[p('status')]) ? p('status') : $x['status'];
    if ($x['titel'] === '') $x['titel'] = 'Projekt ' . date('d.m.Y');
    if ($neu) verlauf($x, 'Projekt angelegt');
    elseif ($alt !== $x['status']) verlauf($x, 'Status: ' . LEAD_STATUS[$alt] . ' → ' . LEAD_STATUS[$x['status']]);
    else verlauf($x, 'Bearbeitet');
    if (in_array($x['status'], ['gewonnen', 'verloren'], true) && !$x['abgeschlossen']) $x['abgeschlossen'] = date('c');
    if (!in_array($x['status'], ['gewonnen', 'verloren'], true)) $x['abgeschlossen'] = null;
    $l[$x['id']] = $x;
    return $x['id'];
  });
  if ($kid && p('status') === 'gewonnen') aendern('kontakte', function (&$k) use ($kid) { if (isset($k[$kid]) && !in_array('Bauherr', $k[$kid]['gruppen'], true)) { $k[$kid]['gruppen'][] = 'Bauherr'; verlauf($k[$kid], 'Gruppe „Bauherr“ (Projekt gewonnen)'); } });
  meldung('Projekt gespeichert.');
  weiter('leads.php?id=' . $id);
}
if (ist_post() && $id && $aktion === 'notiz' && p('text') !== '') {
  aendern('leads', function (&$l) use ($id) { if (isset($l[$id])) verlauf($l[$id], 'Notiz: ' . p('text')); });
  weiter('leads.php?id=' . $id);
}
if (ist_post() && $id && $aktion === 'loeschen') {
  $t = leads()[$id]['titel'] ?? '';
  aendern('leads', function (&$l) use ($id) { unset($l[$id]); });
  aendern('anfragen', function (&$a) use ($id) { foreach ($a as &$x) if (($x['lead_id'] ?? null) === $id) $x['lead_id'] = null; });
  protokoll('Projekt gelöscht: ' . $t);
  meldung('Projekt gelöscht.');
  weiter('leads.php');
}

$kontaktOpt = ['' => '– Kontakt wählen –'];
$sortiert = $kontakte; uasort($sortiert, fn($a, $b) => strcasecmp(kontakt_name($a), kontakt_name($b)));
foreach ($sortiert as $k) $kontaktOpt[$k['id']] = kontakt_name($k) . ($k['email'] ? ' · ' . $k['email'] : '');

/* ---------- Formular ---------- */
if ($aktion === 'neu' || $aktion === 'bearbeiten') {
  $l = $id ? (leads()[$id] ?? leerer_lead()) : leerer_lead();
  if (!$id) { $l['kontakt_id'] = g('kontakt') ?: null; $l['verantwortlich'] = $ich['id']; if ($l['kontakt_id'] && isset($kontakte[$l['kontakt_id']])) $l['titel'] = kontakt_name($kontakte[$l['kontakt_id']]) . ' · '; }
  kopf($id ? 'Projekt bearbeiten' : 'Neues Projekt', 'leads');
  seitenkopf('Projekte', $id ? e($l['titel']) : 'Neues Projekt');
  ?>
  <section class="card" style="max-width:900px">
    <form method="post" action="leads.php?aktion=speichern<?= $id ? '&id=' . e($id) : '' ?>" class="form form--2"><?= csrf_feld() ?>
      <label class="feld voll">Titel<input name="titel" value="<?= e($l['titel']) ?>" placeholder="z. B. Familie Berger · Alpina in Ratingen" required autofocus></label>
      <label class="feld">Status<?= auswahl('status', LEAD_STATUS, $l['status']) ?></label>
      <label class="feld">Verantwortlich<?= auswahl('verantwortlich', benutzer_optionen(), $l['verantwortlich']) ?></label>
      <label class="feld voll">Kontakt<?= auswahl('kontakt_id', $kontaktOpt, $l['kontakt_id']) ?></label>
      <details class="voll"<?= !$l['kontakt_id'] && !$id ? '' : '' ?>><summary class="linkbtn" style="display:inline">Oder neuen Kontakt anlegen</summary>
        <div class="form form--3" style="margin-top:12px"><label class="feld">Name<input name="neu_name"></label><label class="feld">E-Mail<input type="email" name="neu_email"></label><label class="feld">Telefon<input name="neu_telefon"></label></div>
      </details>
      <label class="feld">Haus<input name="haus" value="<?= e($l['haus']) ?>" list="haeuser" placeholder="z. B. Alpina oder individuell"></label>
      <label class="feld">Volumen (€, netto)<input name="wert" inputmode="numeric" value="<?= e($l['wert'] !== '' ? number_format((float) $l['wert'], 0, ',', '.') : '') ?>" placeholder="z. B. 780.000"></label>
      <label class="feld voll">Grundstück / Ort<input name="ort" value="<?= e($l['ort']) ?>"></label>
      <label class="feld">Nächster Schritt<input name="naechster_schritt" value="<?= e($l['naechster_schritt']) ?>" placeholder="z. B. Entwurf vorstellen"></label>
      <label class="feld">Datum<input type="date" name="naechster_termin" value="<?= e($l['naechster_termin']) ?>"></label>
      <label class="feld voll">Notizen<textarea name="notizen"><?= e($l['notizen']) ?></textarea></label>
      <div class="voll knopfreihe"><button class="btn" type="submit">Speichern</button><a class="btn btn--hell" href="<?= $id ? 'leads.php?id=' . e($id) : 'leads.php' ?>">Abbrechen</a></div>
    </form>
    <datalist id="haeuser"><?php foreach (['Tre', 'Quattro', 'Piano', 'Alpina', 'Terrazza', 'Pendio', 'Corte', 'Ala', 'Vista', 'Ombra', 'Individuell'] as $h): ?><option value="<?= $h ?>"><?php endforeach; ?></datalist>
  </section>
  <?php fuss(); exit;
}

/* ---------- Einzelansicht ---------- */
if ($id) {
  $l = leads()[$id] ?? null;
  if (!$l) { meldung('Projekt nicht gefunden.', 'fehler'); weiter('leads.php'); }
  $k = $l['kontakt_id'] ? ($kontakte[$l['kontakt_id']] ?? null) : null;
  $auf = array_filter(aufgaben_sichtbar($ich), fn($a) => ($a['bezug']['id'] ?? null) === $id);
  kopf($l['titel'], 'leads');
  seitenkopf('Projekt', e($l['titel']), ($k && $k['email'] ? '<a class="btn btn--hell" href="email.php?lead=' . e($id) . '">' . icon('send') . ' E-Mail</a>' : '') . '<a class="btn btn--hell" href="leads.php">Alle Projekte</a><a class="btn btn--hell" href="aufgaben.php?aktion=neu&bezug=lead:' . e($id) . '">' . icon('plus') . ' Aufgabe</a><a class="btn" href="leads.php?id=' . e($id) . '&aktion=bearbeiten">Bearbeiten</a>');
  ?>
  <div class="grid grid--side">
    <div class="stack">
      <section class="card">
        <div class="card__head"><h2>Überblick</h2><?= status_pill($l['status'], LEAD_STATUS) ?></div>
        <dl class="dl">
          <dt>Kontakt</dt><dd><?= $k ? '<a href="kontakte.php?id=' . e($k['id']) . '">' . e(kontakt_name($k)) . '</a>' . ($k['telefon'] ? ' · ' . e($k['telefon']) : '') : '–' ?></dd>
          <dt>Haus</dt><dd><?= e($l['haus'] ?: '–') ?></dd>
          <dt>Grundstück / Ort</dt><dd><?= e($l['ort'] ?: '–') ?></dd>
          <dt>Volumen</dt><dd><?= $l['wert'] ? eur($l['wert']) : '–' ?></dd>
          <dt>Verantwortlich</dt><dd><?= e(benutzer_name($l['verantwortlich'])) ?></dd>
          <dt>Nächster Schritt</dt><dd><?= e($l['naechster_schritt'] ?: '–') ?><?= $l['naechster_termin'] ? ' · ' . datum($l['naechster_termin']) : '' ?></dd>
          <?php if ($l['anfrage_id']): ?><dt>Ursprung</dt><dd><a href="anfragen.php?id=<?= e($l['anfrage_id']) ?>">Website-Anfrage ansehen</a></dd><?php endif; ?>
          <?php if ($l['notizen']): ?><dt>Notizen</dt><dd><?= e($l['notizen']) ?></dd><?php endif; ?>
        </dl>
      </section>
      <section class="card">
        <h2 style="margin-bottom:14px">Verlauf</h2>
        <form method="post" action="leads.php?id=<?= e($id) ?>&aktion=notiz" class="knopfreihe" style="margin-bottom:18px"><?= csrf_feld() ?><input name="text" placeholder="Notiz eintragen …" style="flex:1" required><button class="btn btn--klein" type="submit">Eintragen</button></form>
        <ul class="verlauf"><?php foreach ($l['verlauf'] as $v): ?><li><?= e($v['text']) ?><small><?= datum($v['zeit'], true) ?> · <?= e($v['wer']) ?></small></li><?php endforeach; ?></ul>
      </section>
    </div>
    <div class="stack">
      <section class="card">
        <h2 style="margin-bottom:12px">Status</h2>
        <div class="checks"><?php foreach (LEAD_STATUS as $s => $t): ?>
          <form method="post" action="leads.php?id=<?= e($id) ?>&aktion=speichern" style="display:inline"><?= csrf_feld() ?>
            <?php foreach (['titel', 'haus', 'ort', 'naechster_schritt', 'naechster_termin', 'notizen', 'kontakt_id', 'verantwortlich', 'wert'] as $f): ?><input type="hidden" name="<?= $f ?>" value="<?= e($l[$f] ?? '') ?>"><?php endforeach; ?>
            <input type="hidden" name="status" value="<?= $s ?>"><label class="chip"><input type="radio" <?= $l['status'] === $s ? 'checked' : '' ?> onchange="this.form.submit()"><span><?= $t ?></span></label></form>
        <?php endforeach; ?></div>
      </section>
      <section class="card">
        <div class="card__head"><h2>Aufgaben</h2><a href="aufgaben.php?aktion=neu&bezug=lead:<?= e($id) ?>">+ Neu</a></div>
        <?php if (!$auf): ?><p class="muted klein">Keine Aufgaben.</p><?php else: foreach ($auf as $a): ?>
          <div class="aufgabe<?= $a['erledigt'] ? ' ist-erledigt' : '' ?>">
            <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($a['id']) ?>&zurueck=<?= rawurlencode('leads.php?id=' . $id) ?>"><?= csrf_feld() ?><button class="aufgabe__haken"></button></form>
            <div class="aufgabe__haupt"><a class="aufgabe__titel" href="aufgaben.php?id=<?= e($a['id']) ?>" style="text-decoration:none"><?= e($a['titel']) ?></a><div class="aufgabe__meta"><?= $a['faellig'] ? 'bis ' . datum($a['faellig']) : '' ?> <?= $a['zugewiesen'] ? '· ' . e(benutzer_name($a['zugewiesen'])) : '' ?></div></div>
          </div>
        <?php endforeach; endif; ?>
      </section>
      <section class="card"><form method="post" action="leads.php?id=<?= e($id) ?>&aktion=loeschen" data-bestaetigen="Projekt endgültig löschen?"><?= csrf_feld() ?><button class="btn btn--gefahr btn--klein"><?= icon('trash') ?> Projekt löschen</button></form></section>
    </div>
  </div>
  <?php fuss(); exit;
}

/* ---------- Board und Liste ---------- */
$ansicht = g('ansicht') === 'liste' ? 'liste' : 'board';
$wer = g('wer'); $statusF = g('status');
$alle = array_filter(leads(), fn($l) => (!$wer || $l['verantwortlich'] === $wer) && (!$statusF || $l['status'] === $statusF));
uasort($alle, fn($a, $b) => strcmp($b['geaendert'], $a['geaendert']));
$aktiv = array_filter($alle, fn($l) => !in_array($l['status'], ['gewonnen', 'verloren'], true));
kopf('Projekte', 'leads', ['body' => 'breit']);
seitenkopf('Leads und Projekte', 'Projekte', '<a class="btn" href="leads.php?aktion=neu">' . icon('plus') . ' Neues Projekt</a>');
?>
<div class="filter">
  <div class="segment"><a href="leads.php?ansicht=board<?= $wer ? '&wer=' . e($wer) : '' ?>" class="<?= $ansicht === 'board' ? 'is-active' : '' ?>">Board</a><a href="leads.php?ansicht=liste<?= $wer ? '&wer=' . e($wer) : '' ?>" class="<?= $ansicht === 'liste' ? 'is-active' : '' ?>">Liste</a></div>
  <form method="get"><input type="hidden" name="ansicht" value="<?= $ansicht ?>"><?= auswahl('wer', ['' => 'Alle Verantwortlichen'] + benutzer_optionen(false), $wer, 'data-auto') ?></form>
  <span class="muted klein"><?= count($aktiv) ?> aktiv · <?= eur(array_sum(array_map(fn($l) => (float) ($l['wert'] ?: 0), $aktiv))) ?> Volumen</span>
</div>
<?php if ($ansicht === 'board'): ?>
  <div class="kanban" data-kanban>
    <?php foreach (LEAD_STATUS as $s => $t): $spalte = array_filter($alle, fn($l) => $l['status'] === $s); $summe = array_sum(array_map(fn($l) => (float) ($l['wert'] ?: 0), $spalte)); ?>
      <div class="spalte" data-status="<?= $s ?>">
        <div class="spalte__kopf"><b><?= $t ?></b><span><?= count($spalte) ?><?= $summe ? ' · ' . number_format($summe, 0, ',', '.') . ' €' : '' ?></span></div>
        <div class="spalte__karten">
          <?php foreach ($spalte as $l): $k = $l['kontakt_id'] ? ($kontakte[$l['kontakt_id']] ?? null) : null; $ueber = $l['naechster_termin'] && $l['naechster_termin'] < date('Y-m-d'); ?>
            <a class="karte" href="leads.php?id=<?= e($l['id']) ?>" data-id="<?= e($l['id']) ?>" data-wert="<?= e((string) (float) ($l['wert'] ?: 0)) ?>">
              <span class="karte__titel"><?= e($l['titel']) ?></span>
              <span class="karte__sub"><?= e($k ? kontakt_name($k) : '') ?><?= $l['haus'] ? ($k ? ' · ' : '') . e($l['haus']) : '' ?></span>
              <span class="karte__fuss"><span class="<?= $ueber ? 'faellig' : '' ?>"><?= $l['naechster_termin'] ? datum($l['naechster_termin']) . ' · ' . e(mb_strimwidth($l['naechster_schritt'], 0, 22, '…')) : '' ?></span><span><?= $l['wert'] ? eur($l['wert']) : '' ?></span></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (!$alle): ?><p class="muted klein" style="margin-top:10px">Noch keine Projekte. Legen Sie eines an oder übernehmen Sie eine Website-Anfrage als Projekt. Karten lassen sich zwischen den Spalten ziehen.</p><?php endif; ?>
<?php else: ?>
  <section class="card">
    <?php if (!$alle): ?><?= leer('Keine Projekte.') ?><?php else: ?>
    <div class="tabelle-wrap"><table class="tabelle">
      <thead><tr><th>Projekt</th><th>Kontakt</th><th>Status</th><th class="rechts">Volumen</th><th>Nächster Schritt</th><th>Verantwortlich</th></tr></thead>
      <tbody><?php foreach ($alle as $l): $k = $l['kontakt_id'] ? ($kontakte[$l['kontakt_id']] ?? null) : null; ?>
        <tr data-href="leads.php?id=<?= e($l['id']) ?>"><td><a href="leads.php?id=<?= e($l['id']) ?>"><?= e($l['titel']) ?></a></td><td><?= e($k ? kontakt_name($k) : '–') ?></td><td><?= status_pill($l['status'], LEAD_STATUS) ?></td>
          <td class="rechts nowrap"><?= $l['wert'] ? eur($l['wert']) : '' ?></td><td><?= e($l['naechster_schritt']) ?><?= $l['naechster_termin'] ? ' <span class="muted klein">' . datum($l['naechster_termin']) . '</span>' : '' ?></td><td><?= e(benutzer_name($l['verantwortlich'])) ?></td></tr>
      <?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section>
<?php endif; fuss();
