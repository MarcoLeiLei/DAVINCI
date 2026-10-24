<?php
/* DAVINCI intern – Anfragen aus den Formularen der Website */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
website_abgleich();
$id = g('id');
$aktion = g('aktion');

if (ist_post() && $id) {
  $a = anfragen()[$id] ?? null;
  if (!$a) weiter('anfragen.php');
  if ($aktion === 'speichern') {
    aendern('anfragen', function (&$alle) use ($id, $ich) {
      $alle[$id]['status'] = array_key_exists(p('status'), ANFRAGE_STATUS) ? p('status') : $alle[$id]['status'];
      $alle[$id]['notiz'] = p('notiz');
      $alle[$id]['bearbeiter'] = p('bearbeiter') ?: null;
    });
    meldung('Anfrage gespeichert.');
  } elseif ($aktion === 'projekt') {
    // Projekt (Lead) aus der Anfrage anlegen
    $f = $a['felder'];
    $kid = $a['kontakt_id'];
    $lid = neue_id();
    aendern('leads', function (&$l) use ($lid, $a, $f, $kid, $ich) {
      $l[$lid] = leerer_lead() + [];
      $l[$lid]['id'] = $lid;
      $l[$lid]['titel'] = anfrage_name($a) . ' · ' . ($f['haus'] ?? ($f['haustyp'] ?? '') ?: $a['typ']);
      $l[$lid]['kontakt_id'] = $kid;
      $l[$lid]['haus'] = $f['haus'] ?? ($f['haustyp'] ?? '');
      $l[$lid]['ort'] = $f['adresse'] ?? ($f['grundstueck'] ?? '');
      $l[$lid]['status'] = 'neu';
      $l[$lid]['verantwortlich'] = $ich['id'];
      $l[$lid]['anfrage_id'] = $a['id'];
      $l[$lid]['notizen'] = trim(($f['nachricht'] ?? '') . (!empty($f['grundstueck']) ? "\nGrundstück: " . $f['grundstueck'] : ''));
      verlauf($l[$lid], 'Aus Anfrage angelegt (' . $a['typ'] . ' vom ' . datum($a['zeit']) . ')');
    });
    aendern('anfragen', function (&$alle) use ($id, $lid) { $alle[$id]['lead_id'] = $lid; if ($alle[$id]['status'] === 'neu') $alle[$id]['status'] = 'bearbeitung'; });
    if ($kid) aendern('kontakte', function (&$k) use ($kid) { if (isset($k[$kid])) verlauf($k[$kid], 'Projekt angelegt'); });
    meldung('Projekt angelegt.');
    weiter('leads.php?id=' . $lid);
  } elseif ($aktion === 'loeschen') {
    aendern('anfragen', function (&$alle) use ($id) { unset($alle[$id]); });
    protokoll('Anfrage gelöscht: ' . anfrage_name($a) . ' (' . $a['typ'] . ')');
    meldung('Anfrage gelöscht.');
    weiter('anfragen.php');
  }
  weiter('anfragen.php?id=' . $id);
}

/* ---------- Einzelansicht ---------- */
if ($id) {
  $a = anfragen()[$id] ?? null;
  if (!$a) { meldung('Anfrage nicht gefunden.', 'fehler'); weiter('anfragen.php'); }
  $k = $a['kontakt_id'] ? (kontakte()[$a['kontakt_id']] ?? null) : null;
  $lead = $a['lead_id'] ? (leads()[$a['lead_id']] ?? null) : null;
  kopf(anfrage_name($a), 'anfragen');
  seitenkopf($a['typ'] . ' · ' . datum($a['zeit'], true) . ' Uhr', e(anfrage_name($a)), '<a class="btn btn--hell" href="anfragen.php">Alle Anfragen</a>');
  ?>
  <div class="grid grid--side">
    <div class="stack">
      <section class="card">
        <div class="card__head"><h2>Angaben</h2><?= status_pill($a['status'], ANFRAGE_STATUS) ?></div>
        <dl class="dl">
          <?php foreach (anfrage_felder($a) as $l => $v): ?><dt><?= e($l) ?></dt><dd><?php if ($l === 'E-Mail'): ?><a href="mailto:<?= e($v) ?>"><?= e($v) ?></a><?php elseif ($l === 'Telefon'): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $v)) ?>"><?= e($v) ?></a><?php else: ?><?= e($v) ?><?php endif; ?></dd><?php endforeach; ?>
          <?php if (!empty($a['quelle'])): ?><dt>Herkunft</dt><dd><?= e(implode(' · ', array_map(fn($k, $v) => "$k: $v", array_keys($a['quelle']), $a['quelle']))) ?></dd><?php endif; ?>
        </dl>
      </section>
      <section class="card">
        <h2 style="margin-bottom:16px">Bearbeitung</h2>
        <form method="post" action="anfragen.php?id=<?= e($id) ?>&aktion=speichern" class="form form--2"><?= csrf_feld() ?>
          <label class="feld">Status<?= auswahl('status', ANFRAGE_STATUS, $a['status']) ?></label>
          <label class="feld">Bearbeitet von<?= auswahl('bearbeiter', benutzer_optionen(), $a['bearbeiter']) ?></label>
          <label class="feld voll">Notiz<textarea name="notiz" placeholder="z. B. Rückruf am Freitag vereinbart"><?= e($a['notiz']) ?></textarea></label>
          <div class="voll knopfreihe"><button class="btn" type="submit">Speichern</button></div>
        </form>
      </section>
    </div>
    <div class="stack">
      <section class="card">
        <h2 style="margin-bottom:14px">Kontakt</h2>
        <?php if ($k): ?>
          <p><a href="kontakte.php?id=<?= e($k['id']) ?>"><?= e(kontakt_name($k)) ?></a></p>
          <p class="muted klein" style="margin:4px 0 12px"><?= e($k['email']) ?></p>
          <p><?= status_pill($k['newsletter']['status'], NL_STATUS) ?></p>
        <?php else: ?><p class="muted">Ohne E-Mail-Adresse, kein Kontakt angelegt.</p><?php endif; ?>
      </section>
      <section class="card">
        <h2 style="margin-bottom:14px">Projekt</h2>
        <?php if ($lead): ?>
          <p><a href="leads.php?id=<?= e($lead['id']) ?>"><?= e($lead['titel']) ?></a></p><p style="margin-top:8px"><?= status_pill($lead['status'], LEAD_STATUS) ?></p>
        <?php else: ?>
          <p class="muted klein" style="margin-bottom:14px">Wird aus der Anfrage ein konkretes Hausprojekt, legen Sie es hier an. Name, Haus und Nachricht werden übernommen.</p>
          <form method="post" action="anfragen.php?id=<?= e($id) ?>&aktion=projekt"><?= csrf_feld() ?><button class="btn btn--akzent" type="submit"><?= icon('board') ?> Projekt anlegen</button></form>
        <?php endif; ?>
      </section>
      <section class="card">
        <form method="post" action="anfragen.php?id=<?= e($id) ?>&aktion=loeschen" data-bestaetigen="Diese Anfrage endgültig löschen? Der Kontakt bleibt erhalten."><?= csrf_feld() ?>
          <p class="muted klein" style="margin-bottom:12px">Löschwunsch nach DSGVO? Löschen Sie die Anfrage hier und den Kontakt in seiner Ansicht.</p>
          <button class="btn btn--gefahr btn--klein" type="submit"><?= icon('trash') ?> Anfrage löschen</button></form>
      </section>
    </div>
  </div>
  <?php fuss(); exit;
}

/* ---------- Liste ---------- */
$typ = g('typ'); $status = g('status'); $q = mb_strtolower(g('q'));
$liste = array_filter(anfragen(), function ($a) use ($typ, $status, $q) {
  if ($typ && $a['typ'] !== $typ) return false;
  if ($status && ($a['status'] ?? 'neu') !== $status) return false;
  if ($q && !str_contains(mb_strtolower(json_encode($a['felder'], JSON_UNESCAPED_UNICODE)), $q)) return false;
  return true;
});
uasort($liste, fn($a, $b) => strcmp($b['zeit'], $a['zeit']));
$typen = ['' => 'Alle Arten'] + array_combine(ANFRAGE_TYPEN, ANFRAGE_TYPEN);
kopf('Anfragen', 'anfragen');
seitenkopf('Website', 'Anfragen', '<span class="muted klein">' . count($liste) . ' Einträge</span>');
?>
<form class="filter" method="get">
  <input type="search" name="q" value="<?= e(g('q')) ?>" placeholder="Name, E-Mail, Haus …">
  <?= auswahl('typ', $typen, $typ, 'data-auto') ?>
  <?= auswahl('status', ['' => 'Jeder Status'] + ANFRAGE_STATUS, $status, 'data-auto') ?>
  <?php if ($typ || $status || $q): ?><a class="linkbtn" href="anfragen.php">Filter zurücksetzen</a><?php endif; ?>
</form>
<section class="card">
  <?php if (!$liste): ?><?= leer($typ || $status || $q ? 'Keine Anfragen für diesen Filter.' : 'Noch keine Anfragen. Sobald jemand ein Formular auf davinci-homes.de abschickt, erscheint es hier.') ?><?php else: ?>
  <div class="tabelle-wrap"><table class="tabelle">
    <thead><tr><th>Eingang</th><th>Name</th><th>Art</th><th>Haus / Thema</th><th>Newsletter</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($liste as $id => $a): $f = $a['felder']; ?>
      <tr data-href="anfragen.php?id=<?= e($id) ?>" class="<?= ($a['status'] ?? 'neu') === 'neu' ? 'ist-neu' : '' ?>">
        <td class="nowrap"><?= datum($a['zeit'], true) ?></td>
        <td><a href="anfragen.php?id=<?= e($id) ?>"><?= e(anfrage_name($a)) ?></a><br><span class="muted klein"><?= e($f['email'] ?? '') ?></span></td>
        <td><span class="typ"><?= e($a['typ']) ?></span></td>
        <td><?= e($f['haus'] ?? ($f['haustyp'] ?? (isset($f['nachricht']) ? mb_strimwidth($f['nachricht'], 0, 60, '…') : ''))) ?></td>
        <td><?= !empty($f['newsletter']) ? '<span class="pill pill--angefragt">angemeldet</span>' : '' ?></td>
        <td><?= status_pill($a['status'] ?? 'neu', ANFRAGE_STATUS) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php fuss();
