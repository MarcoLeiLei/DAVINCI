<?php
/* DAVINCI intern – Anfragen aus den Formularen der Website */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
website_abgleich();
/** Grundriss-Anfragen ab Einführung der Messung (ältere haben keine Messung). */
function mail_gemessen(array $a): bool { return ($a['typ'] ?? '') === 'Grundriss' && (isset($a['mail']) || (strpos((string) $a['id'], 'gr-') !== 0 && ($a['zeit'] ?? '') >= '2026-11-06T22:45')); }
function mail_pills(array $a): string {
  if (!mail_gemessen($a)) return '';
  $m = $a['mail'] ?? [];
  if (!empty($m['ziele']['pdf']))  return '<span class="pill pill--bestaetigt" title="Grundriss geladen am ' . e(datum($m['ziele']['pdf'], true)) . ' Uhr">PDF geladen</span>';
  if (!empty($m['geklickt']))      return '<span class="pill pill--bestaetigt">geklickt</span>';
  if (!empty($m['geoeffnet']))     return '<span class="pill pill--angefragt" title="' . e(datum($m['geoeffnet'], true)) . ' Uhr">geöffnet</span>';
  return '<span class="muted klein">ungeöffnet</span>';
}
/** Alles, was mit dieser Person passiert ist: Anfragen, E-Mails der Website, persönliche E-Mails, Newsletter, Verlauf. Neueste zuerst. */
function kommunikation(array $a, ?array $k): array {
  $mail = strtolower(trim((string) ($k['email'] ?? ($a['felder']['email'] ?? ''))));
  $o = [];
  foreach (anfragen() as $x) if ($mail !== '' && strtolower((string) ($x['felder']['email'] ?? '')) === $mail)
    $o[] = ['zeit' => $x['zeit'], 'art' => 'anfrage', 'titel' => 'Anfrage über die Website: ' . $x['typ'] . (!empty($x['felder']['haus']) ? ' · ' . $x['felder']['haus'] : ''),
            'link' => $x['id'] === $a['id'] ? null : 'anfragen.php?id=' . $x['id'], 'info' => $x['id'] === $a['id'] ? 'diese Anfrage' : ''];
  foreach (web_mails_fuer($mail) as $m)
    $o[] = ['zeit' => $m['zeit'], 'art' => 'mail', 'titel' => 'E-Mail an Kunden: ' . $m['betreff'], 'info' => 'automatisch von der Website', 'link' => 'anfragen.php?id=' . $a['id'] . '&mail=' . $m['id'] . '#mail'];
  foreach (lesen('mails') as $m) if ($mail !== '' && in_array($mail, array_merge($m['an'] ?? [], $m['cc'] ?? []), true))
    $o[] = ['zeit' => $m['zeit'], 'art' => 'mail', 'titel' => 'E-Mail an Kunden: ' . $m['betreff'], 'info' => 'persönlich von ' . benutzer_name($m['von']), 'link' => 'email.php?id=' . $m['id']];
  if ($k) foreach (lesen('kampagnen') as $kid => $kamp) {
    $e = $kamp['empfaenger'][$k['id']] ?? null;
    if (!$e || ($e['status'] ?? '') !== 'ok') continue;
    $st = nl_statistik((string) $kid)[$k['id']] ?? [];
    $o[] = ['zeit' => $e['zeit'] ?: ($kamp['gesendet'] ?? ''), 'art' => 'mail', 'titel' => 'Newsletter: ' . ($kamp['betreff'] ?: 'Ohne Betreff'),
            'info' => !empty($st['k']) ? 'geklickt' : (!empty($st['o']) ? 'geöffnet' : ''), 'link' => 'kampagne.php?id=' . $kid];
  }
  foreach ($a['mail']['ziele'] ?? [] as $z => $t) $o[] = ['zeit' => $t, 'art' => 'klick', 'titel' => $z === 'pdf' ? 'Grundriss aus der E-Mail heruntergeladen' : 'Terminbuchung aus der E-Mail aufgerufen', 'info' => '', 'link' => null];
  if (!empty($a['mail']['geoeffnet'])) $o[] = ['zeit' => $a['mail']['geoeffnet'], 'art' => 'klick', 'titel' => 'Grundriss-E-Mail geöffnet', 'info' => '', 'link' => null];
  if ($k) foreach ($k['verlauf'] ?? [] as $v) {
    if (str_starts_with($v['text'], 'E-Mail gesendet:') || str_starts_with($v['text'], 'Website: ')) continue;   // steht schon oben
    $o[] = ['zeit' => $v['zeit'], 'art' => 'notiz', 'titel' => $v['text'], 'info' => $v['wer'], 'link' => null];
  }
  usort($o, fn($x, $y) => strcmp((string) $y['zeit'], (string) $x['zeit']));
  return $o;
}
/** Die eigentliche Antwort auf die Anfrage (nicht die Newsletter-Bestätigung). */
function haupt_mail(array $mails): ?string {
  foreach (array_reverse($mails, true) as $mid => $m) if (!preg_match('/newsletter|bestätig/i', (string) $m['betreff'])) return (string) $mid;
  return $mails ? (string) array_key_last($mails) : null;
}
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
    anfragen_loeschen([$id]);
    protokoll('Anfrage gelöscht: ' . anfrage_name($a) . ' (' . $a['typ'] . ')');
    meldung('Anfrage gelöscht.');
    weiter('anfragen.php');
  }
  weiter('anfragen.php?id=' . $id);
}

/* ---------- Mehrere löschen ---------- */
if (ist_post() && !$id && $aktion === 'mehrere_loeschen') {
  $ids = array_values(array_filter((array) ($_POST['ids'] ?? []), 'is_string'));
  $n = anfragen_loeschen($ids);
  if ($n) protokoll("$n Anfragen gelöscht");
  meldung($n === 1 ? '1 Anfrage gelöscht.' : "$n Anfragen gelöscht.");
  weiter('anfragen.php?' . http_build_query(array_filter(['typ' => g('typ'), 'status' => g('status'), 'q' => g('q')])));
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
          <?php if (mail_gemessen($a)): $m = $a['mail'] ?? []; ?><dt>Grundriss-E-Mail</dt><dd><?= !empty($m['geoeffnet']) ? 'geöffnet am ' . datum($m['geoeffnet'], true) . ' Uhr' . (($m['oeffnungen'] ?? 0) > 1 ? ' (' . (int) $m['oeffnungen'] . '×)' : '') : 'noch nicht geöffnet' ?><?php if (!empty($m['ziele']['pdf'])): ?><br>Grundriss geladen am <?= datum($m['ziele']['pdf'], true) ?> Uhr<?php endif; ?><?php if (!empty($m['ziele']['termin'])): ?><br>Terminbuchung aufgerufen am <?= datum($m['ziele']['termin'], true) ?> Uhr<?php endif; ?></dd><?php endif; ?>
          <?php if (!empty($a['quelle'])): ?><dt>Herkunft</dt><dd><?= e(implode(' · ', array_map(fn($k, $v) => "$k: $v", array_keys($a['quelle']), $a['quelle']))) ?></dd><?php endif; ?>
        </dl>
      </section>
      <?php
        $kundenMails = array_filter(web_mails_fuer((string) ($a['felder']['email'] ?? '')), fn($m) => ($m['anfrage'] ?? null) === $a['id']);
        $zeigen = g('mail') && web_mail(g('mail')) ? web_mail(g('mail')) : (($m0 = haupt_mail($kundenMails)) ? web_mail($m0) : null);
        if ($zeigen && strtolower($zeigen['an']) !== strtolower((string) ($a['felder']['email'] ?? '')) && (!$k || strtolower($zeigen['an']) !== strtolower($k['email']))) $zeigen = null;
      ?>
      <section class="card" id="mail">
        <div class="card__head"><h2>E-Mail an den Kunden</h2><?php if ($zeigen): ?><span class="muted klein"><?= datum($zeigen['zeit'], true) ?> Uhr</span><?php endif; ?></div>
        <?php if ($zeigen): ?>
          <dl class="dl" style="margin-bottom:14px"><dt>Betreff</dt><dd><?= e($zeigen['betreff']) ?></dd><dt>An</dt><dd><?= e($zeigen['an']) ?></dd></dl>
          <?php if (count($kundenMails) > 1): ?><p class="klein" style="margin-bottom:12px">Zu dieser Anfrage: <?php foreach (array_reverse($kundenMails, true) as $mid => $m): ?><a href="anfragen.php?id=<?= e($id) ?>&mail=<?= e($mid) ?>#mail" style="margin-right:12px;<?= $mid === $zeigen['id'] ? 'font-weight:600' : '' ?>"><?= e($m['betreff']) ?></a><?php endforeach; ?></p><?php endif; ?>
          <iframe title="E-Mail an den Kunden" sandbox srcdoc="<?= e($zeigen['html']) ?>" style="width:100%;height:720px;border:1px solid var(--line);border-radius:10px;background:#f0f0ee"></iframe>
        <?php else: ?>
          <p class="muted klein">Für diese Anfrage liegt keine Kopie der E-Mail vor. Kopien werden seit dem 06.11.2026 gespeichert.</p>
        <?php endif; ?>
      </section>
      <section class="card">
        <h2 style="margin-bottom:16px">Kommunikationsverlauf</h2>
        <?php $verlauf = kommunikation($a, $k); if (!$verlauf): ?><p class="muted klein">Noch nichts.</p><?php else: ?>
        <ul class="verlauf verlauf--komm"><?php foreach ($verlauf as $v): ?>
          <li class="verlauf--<?= e($v['art']) ?>"><?php if ($v['link']): ?><a href="<?= e($v['link']) ?>"><?= e($v['titel']) ?></a><?php else: ?><?= e($v['titel']) ?><?php endif; ?>
            <small><?= $v['zeit'] ? datum($v['zeit'], true) . ' Uhr' : '' ?><?= $v['info'] ? ' · ' . e($v['info']) : '' ?></small></li>
        <?php endforeach; ?></ul><?php endif; ?>
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
  <form method="post" id="auswahl" action="anfragen.php?<?= e(http_build_query(array_filter(['aktion' => 'mehrere_loeschen', 'typ' => $typ, 'status' => $status, 'q' => $q]))) ?>" data-bestaetigen="Die markierten Anfragen endgültig löschen? Die Kontakte bleiben erhalten."><?= csrf_feld() ?>
  <div class="auswahl-leiste" hidden><span data-anzahl>0 markiert</span><button class="btn btn--klein btn--gefahr" type="submit">Markierte löschen</button></div>
  </form>
  <div class="tabelle-wrap"><table class="tabelle">
    <thead><tr><th class="spalte-wahl"><input type="checkbox" data-alle aria-label="Alle markieren"></th><th>Eingang</th><th>Name</th><th>Art</th><th>Haus / Thema</th><th>E-Mail</th><th>Newsletter</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($liste as $id => $a): $f = $a['felder']; ?>
      <tr data-href="anfragen.php?id=<?= e($id) ?>" class="<?= ($a['status'] ?? 'neu') === 'neu' ? 'ist-neu' : '' ?>">
        <td class="spalte-wahl"><input type="checkbox" name="ids[]" value="<?= e($id) ?>" form="auswahl" aria-label="Markieren"></td>
        <td class="nowrap"><?= datum($a['zeit'], true) ?></td>
        <td><a href="anfragen.php?id=<?= e($id) ?>"><?= e(anfrage_name($a)) ?></a><br><span class="muted klein"><?= e($f['email'] ?? '') ?></span></td>
        <td><span class="typ"><?= e($a['typ']) ?></span></td>
        <td><?= e($f['haus'] ?? ($f['haustyp'] ?? (isset($f['nachricht']) ? mb_strimwidth($f['nachricht'], 0, 60, '…') : ''))) ?></td>
        <td class="nowrap"><?= mail_pills($a) ?></td>
        <td><?= !empty($f['newsletter']) ? '<span class="pill pill--angefragt">angemeldet</span>' : '' ?></td>
        <td><?= status_pill($a['status'] ?? 'neu', ANFRAGE_STATUS) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php fuss();
