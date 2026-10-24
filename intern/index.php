<?php
/* DAVINCI intern – Übersicht */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
website_abgleich();

$anfragen = anfragen(); $kontakte = kontakte(); $leads = leads(); $aufgaben = aufgaben_sichtbar($ich); $kampagnen = lesen('kampagnen');
$jetzt = time();
$in = fn($a, $von, $bis) => ($t = strtotime($a['zeit'])) >= $von && $t < $bis;
$woche = count(array_filter($anfragen, fn($a) => $in($a, $jetzt - 7 * 86400, $jetzt + 1)));
$vorwoche = count(array_filter($anfragen, fn($a) => $in($a, $jetzt - 14 * 86400, $jetzt - 7 * 86400)));
$offen = count(array_filter($anfragen, fn($a) => ($a['status'] ?? 'neu') === 'neu'));
$aktiv = array_filter($leads, fn($l) => !in_array($l['status'], ['gewonnen', 'verloren'], true));
$volumen = array_sum(array_map(fn($l) => (float) ($l['wert'] ?? 0), $aktiv));
$gewonnen = array_filter($leads, fn($l) => $l['status'] === 'gewonnen' && substr((string) ($l['abgeschlossen'] ?? $l['geaendert']), 0, 4) === date('Y'));
$abos = count(array_filter($kontakte, 'nl_erlaubt'));
$diff = $woche - $vorwoche;

// Anfragen der letzten 12 Wochen nach Art
$farben = ['Beratungsanfrage' => '#1d1d1f', 'Terminanfrage' => '#406973', 'Termin' => '#6f97a1', 'Design-Book' => '#9dbcc4', 'Grundriss' => '#cfc8b8'];
$wochen = [];
$start = strtotime('monday this week', $jetzt) - 11 * 7 * 86400;
for ($i = 0; $i < 12; $i++) $wochen[$i] = array_fill_keys(array_keys($farben), 0);
foreach ($anfragen as $a) {
  $i = (int) floor((strtotime($a['zeit']) - $start) / (7 * 86400));
  if ($i >= 0 && $i < 12) $wochen[$i][isset($farben[$a['typ']]) ? $a['typ'] : 'Beratungsanfrage']++;
}
$max = max(4, (int) ceil(max(array_map('array_sum', $wochen)) / 4) * 4);

// Aufgaben: Team-Prios (alle) und persönliche (nur meine)
$teamPrios = array_values(array_filter($aufgaben, fn($a) => $a['art'] === 'team' && !$a['erledigt']));
$meine = array_values(array_filter($aufgaben, fn($a) => !$a['erledigt'] && (($a['art'] === 'persoenlich') || $a['zugewiesen'] === $ich['id'])));
aufgaben_sortieren($teamPrios); aufgaben_sortieren($meine);
geplante_pruefen();
$naechste = array_filter($aktiv, fn($l) => !empty($l['naechster_termin']));
usort($naechste, fn($a, $b) => strcmp($a['naechster_termin'], $b['naechster_termin']));
uasort($anfragen, fn($a, $b) => strcmp($b['zeit'], $a['zeit']));
uasort($kampagnen, fn($a, $b) => strcmp($b['geaendert'] ?? '', $a['geaendert'] ?? ''));
$letzte = reset($kampagnen) ?: null;
$stunde = (int) date('G');
$gruss = $stunde < 11 ? 'Guten Morgen' : ($stunde < 18 ? 'Guten Tag' : 'Guten Abend');
$tage = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
$mon = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
$abgleich = lesen('abgleich');

kopf('Übersicht', 'index');
seitenkopf($tage[(int) date('w')] . ', ' . date('j') . '. ' . $mon[(int) date('n')] . ' ' . date('Y'), e($gruss . ', ' . explode(' ', $ich['name'])[0]) . '.',
  '<a class="btn btn--hell" href="kontakte.php?aktion=neu">' . icon('plus') . ' Kontakt</a><a class="btn btn--hell" href="leads.php?aktion=neu">' . icon('plus') . ' Projekt</a><a class="btn" href="kampagne.php?aktion=neu">' . icon('mail') . ' Newsletter schreiben</a>');
?>
<?php if (!empty($abgleich['fehler'])): ?><p class="hinweis hinweis--warn" style="margin-bottom:20px">Die Anfragen der Website konnten nicht gelesen werden: <?= e($abgleich['fehler']) ?>. In config.php unter „website_daten“ den Pfad zum Ordner data/ der Website eintragen.</p><?php endif; ?>
<div class="kpis">
  <a class="kpi kpi--dunkel" href="anfragen.php"><span class="kpi__label">Anfragen · 7 Tage</span><span class="kpi__wert"><?= $woche ?></span>
    <span class="kpi__sub"><?php if ($vorwoche || $woche): ?><span class="<?= $diff >= 0 ? 'plus' : 'minus' ?>"><?= $diff >= 0 ? '+' : '' ?><?= $diff ?></span> zur Vorwoche<?php else: ?>noch keine<?php endif; ?></span></a>
  <a class="kpi" href="anfragen.php?status=neu"><span class="kpi__label">Unbearbeitet</span><span class="kpi__wert"><?= $offen ?></span><span class="kpi__sub">Anfragen mit Status „Neu“</span></a>
  <a class="kpi" href="leads.php"><span class="kpi__label">Aktive Projekte</span><span class="kpi__wert"><?= count($aktiv) ?></span><span class="kpi__sub"><?= $volumen ? eur($volumen) . ' Volumen' : 'ohne Volumen' ?></span></a>
  <a class="kpi" href="leads.php?status=gewonnen&ansicht=liste"><span class="kpi__label">Gewonnen <?= date('Y') ?></span><span class="kpi__wert"><?= count($gewonnen) ?></span><span class="kpi__sub"><?= eur(array_sum(array_map(fn($l) => (float) ($l['wert'] ?? 0), $gewonnen))) ?></span></a>
  <a class="kpi" href="kontakte.php?nl=erlaubt"><span class="kpi__label">Newsletter-Empfänger</span><span class="kpi__wert"><?= $abos ?></span><span class="kpi__sub">von <?= count($kontakte) ?> Kontakten</span></a>
</div>

<div class="grid grid--side">
  <div class="stack">
    <section class="card">
      <div class="card__head"><h2>Anfragen der letzten 12 Wochen</h2><a href="anfragen.php">Alle Anfragen →</a></div>
      <svg class="chart" viewBox="0 0 720 210" role="img" aria-label="Anfragen pro Woche">
        <?php for ($y = 0; $y <= 4; $y++): $yy = 180 - $y * 40; ?><line x1="30" x2="720" y1="<?= $yy ?>" y2="<?= $yy ?>" stroke="#ecebe7"/><text x="22" y="<?= $yy + 4 ?>" text-anchor="end"><?= round($max * $y / 4) ?></text><?php endfor; ?>
        <?php foreach ($wochen as $i => $w): $x = 42 + $i * 56; $y = 180; ?>
          <?php foreach ($w as $typ => $n): if (!$n) continue; $h = $n / $max * 160; $y -= $h; ?><rect x="<?= $x ?>" y="<?= round($y, 1) ?>" width="38" height="<?= round($h, 1) ?>" rx="3" fill="<?= $farben[$typ] ?>"><title><?= e($typ) ?>: <?= $n ?></title></rect><?php endforeach; ?>
          <text x="<?= $x + 19 ?>" y="200" text-anchor="middle"><?= date('d.m.', $start + $i * 7 * 86400) ?></text>
        <?php endforeach; ?>
      </svg>
      <div class="legende"><?php foreach ($farben as $t => $f): ?><span><i style="background:<?= $f ?>"></i><?= e($t) ?></span><?php endforeach; ?></div>
    </section>

    <section class="card">
      <div class="card__head"><h2>Neueste Anfragen</h2><a href="anfragen.php">Alle →</a></div>
      <?php if (!$anfragen): ?><?= leer('Noch keine Anfragen. Neue Anfragen der Website erscheinen hier automatisch.') ?><?php else: ?>
      <ul class="liste">
        <?php foreach (array_slice($anfragen, 0, 6, true) as $id => $a): ?>
          <li><div class="liste__haupt"><a class="liste__titel" href="anfragen.php?id=<?= e($id) ?>"><?= e(anfrage_name($a)) ?></a><span class="liste__sub"><span class="typ"><?= e($a['typ']) ?></span> · <?= e(relativ($a['zeit'])) ?><?= !empty($a['felder']['haus']) ? ' · ' . e($a['felder']['haus']) : (!empty($a['felder']['haustyp']) ? ' · ' . e($a['felder']['haustyp']) : '') ?></span></div><?= status_pill($a['status'] ?? 'neu', ANFRAGE_STATUS) ?></li>
        <?php endforeach; ?>
      </ul><?php endif; ?>
    </section>

    <section class="card">
      <div class="card__head"><h2>Nächste Schritte in Projekten</h2><a href="leads.php">Projekte →</a></div>
      <?php if (!$naechste): ?><?= leer('Keine geplanten Schritte. Tragen Sie in einem Projekt „Nächster Schritt“ mit Datum ein.') ?><?php else: ?>
      <ul class="liste">
        <?php foreach (array_slice($naechste, 0, 6) as $l): $ueber = $l['naechster_termin'] < date('Y-m-d'); ?>
          <li><div class="liste__haupt"><a class="liste__titel" href="leads.php?id=<?= e($l['id']) ?>"><?= e($l['titel']) ?></a><span class="liste__sub"><?= e($l['naechster_schritt'] ?: 'Nächster Schritt') ?></span></div>
            <span class="klein nowrap" style="color:<?= $ueber ? 'var(--err)' : 'var(--grey-4)' ?>"><?= datum($l['naechster_termin']) ?></span><?= status_pill($l['status'], LEAD_STATUS) ?></li>
        <?php endforeach; ?>
      </ul><?php endif; ?>
    </section>
  </div>

  <div class="stack">
    <?php foreach ([['Team-Prios', $teamPrios, 'team', 'Neue Team-Aufgabe …', 'Keine offenen Team-Aufgaben.'], ['Meine Aufgaben', $meine, 'persoenlich', 'Für mich notieren …', 'Alles erledigt.']] as [$titel, $liste, $art, $ph, $leerText]): ?>
    <section class="card">
      <div class="card__head"><h2><?= $titel ?></h2><a href="aufgaben.php">Alle →</a></div>
      <form method="post" action="aufgaben.php?aktion=schnell&zurueck=index.php" class="knopfreihe" style="margin-bottom:12px"><?= csrf_feld() ?><input type="hidden" name="art" value="<?= $art ?>">
        <input name="titel" placeholder="<?= $ph ?>" required style="flex:1"><button class="btn btn--klein" type="submit"><?= icon('plus') ?></button></form>
      <?php if (!$liste): ?><?= leer($leerText) ?><?php else: foreach (array_slice($liste, 0, 6) as $a): ?>
        <div class="aufgabe<?= $a['art'] === 'team' ? ' aufgabe--p' . $a['prio'] : '' ?>">
          <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($a['id']) ?>&zurueck=index.php"><?= csrf_feld() ?><button class="aufgabe__haken" title="Erledigt"></button></form>
          <div class="aufgabe__haupt"><a class="aufgabe__titel" href="aufgaben.php?id=<?= e($a['id']) ?>" style="text-decoration:none"><?= e($a['titel']) ?></a>
            <div class="aufgabe__meta"><?php if ($a['art'] === 'team'): ?><span class="<?= $a['prio'] === 1 ? 'wichtig' : '' ?>"><?= PRIOS[$a['prio']] ?></span><span><?= $a['zugewiesen'] ? e(benutzer_name($a['zugewiesen'])) : 'Team' ?></span><?php else: ?><span>persönlich</span><?php endif; ?><?php if ($a['faellig']): ?><span class="<?= $a['faellig'] < date('Y-m-d') ? 'faellig' : '' ?>">bis <?= datum($a['faellig']) ?></span><?php endif; ?></div></div>
        </div>
      <?php endforeach; endif; ?>
    </section>
    <?php endforeach; ?>

    <section class="card">
      <div class="card__head"><h2>Newsletter</h2><a href="newsletter.php">Alle →</a></div>
      <?php if ($letzte): ?>
        <p class="typ" style="margin-bottom:6px"><?= e(KAMPAGNE_STATUS[$letzte['status']] ?? $letzte['status']) ?></p>
        <h3 style="margin-bottom:6px"><a href="kampagne.php?id=<?= e($letzte['id']) ?>" style="text-decoration:none"><?= e($letzte['betreff'] ?: 'Ohne Betreff') ?></a></h3>
        <p class="muted klein"><?php $st = kampagne_zahlen($letzte); ?><?= $letzte['status'] === 'geplant' ? 'Geplant für ' . datum($letzte['geplant_fuer'], true) . ' Uhr' : ($letzte['status'] === 'entwurf' ? 'Zuletzt bearbeitet ' . e(relativ($letzte['geaendert'])) : $st['ok'] . ' von ' . $st['gesamt'] . ' versendet' . ($st['abgemeldet'] ? ' · ' . $st['abgemeldet'] . ' Abmeldungen' : '')) ?></p>
      <?php else: ?><?= leer('Noch kein Newsletter.') ?><?php endif; ?>
      <p style="margin-top:16px"><a class="btn btn--hell btn--klein" href="kampagne.php?aktion=neu"><?= icon('plus') ?> Neuer Newsletter</a></p>
    </section>
  </div>
</div>
<?php fuss();
