<?php
/* DAVINCI intern – Newsletter: Übersicht */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
website_abgleich();
geplante_pruefen();
$kampagnen = lesen('kampagnen');
uasort($kampagnen, fn($a, $b) => strcmp($b['geaendert'], $a['geaendert']));
$kontakte = kontakte();
$z = array_fill_keys(array_keys(NL_STATUS), 0);
foreach ($kontakte as $k) $z[$k['newsletter']['status']] = ($z[$k['newsletter']['status']] ?? 0) + 1;
$gruppen = [];
foreach ($kontakte as $k) if (nl_erlaubt($k)) foreach ($k['gruppen'] ?: ['Ohne Gruppe'] as $g) $gruppen[$g] = ($gruppen[$g] ?? 0) + 1;
arsort($gruppen);
kopf('Newsletter', 'newsletter');
seitenkopf('E-Mail an Kunden', 'Newsletter', '<a class="btn" href="kampagne.php?aktion=neu">' . icon('plus') . ' Neuer Newsletter</a>');
?>
<div class="kpis kpis--4">
  <a class="kpi kpi--dunkel" href="kontakte.php?nl=erlaubt"><span class="kpi__label">Empfänger</span><span class="kpi__wert"><?= $z['bestaetigt'] + $z['bestandskunde'] ?></span><span class="kpi__sub"><?= $z['bestaetigt'] ?> eingewilligt · <?= $z['bestandskunde'] ?> Bestandskunden</span></a>
  <a class="kpi" href="kontakte.php?nl=angefragt"><span class="kpi__label">Warten auf Bestätigung</span><span class="kpi__wert"><?= $z['angefragt'] ?></span><span class="kpi__sub">Double-Opt-in offen</span></a>
  <a class="kpi" href="kontakte.php?nl=abgemeldet"><span class="kpi__label">Abgemeldet</span><span class="kpi__wert"><?= $z['abgemeldet'] ?></span><span class="kpi__sub">werden nie angeschrieben</span></a>
  <div class="kpi"><span class="kpi__label">Stundenlimit frei</span><span class="kpi__wert"><?= stundenbudget() ?></span><span class="kpi__sub">von <?= (int) einstellungen()['stundenlimit'] ?> E-Mails pro Stunde</span></div>
</div>
<div class="grid grid--side">
  <section class="card">
    <h2 style="margin-bottom:14px">Ausgaben</h2>
    <?php if (!$kampagnen): ?><?= leer('Noch kein Newsletter. Legen Sie den ersten an: Betreff, Bilder und Text, dann Empfänger wählen und versenden.') ?><?php else: ?>
    <div class="tabelle-wrap"><table class="tabelle">
      <thead><tr><th>Betreff</th><th>Status</th><th class="rechts">Empfänger</th><th class="rechts">Zugestellt</th><th class="rechts">Abmeldungen</th><th>Datum</th></tr></thead>
      <tbody><?php foreach ($kampagnen as $k): $s = kampagne_zahlen($k); ?>
        <tr data-href="kampagne.php?id=<?= e($k['id']) ?>"><td><a href="kampagne.php?id=<?= e($k['id']) ?>"><?= e($k['betreff'] ?: 'Ohne Betreff') ?></a></td><td><?= status_pill($k['status'], KAMPAGNE_STATUS) ?></td>
          <td class="rechts"><?= $s['gesamt'] ?: '–' ?></td><td class="rechts"><?= $k['status'] === 'entwurf' ? '–' : $s['ok'] ?></td><td class="rechts"><?= $s['abgemeldet'] ?: '–' ?></td>
          <td class="nowrap muted klein"><?= $k['status'] === 'geplant' ? 'am ' . datum($k['geplant_fuer'], true) : ($k['fertig'] ? datum($k['fertig']) : ($k['gestartet'] ? 'seit ' . datum($k['gestartet']) : relativ($k['geaendert']))) ?></td></tr>
      <?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section>
  <div class="stack">
    <section class="card">
      <h2 style="margin-bottom:12px">Empfänger nach Gruppe</h2>
      <?php if (!$gruppen): ?><p class="muted klein">Noch keine Empfänger mit Einwilligung.</p><?php else: ?><ul class="liste"><?php foreach ($gruppen as $g => $n): ?><li><div class="liste__haupt"><?= e($g) ?></div><span><?= $n ?></span></li><?php endforeach; ?></ul><?php endif; ?>
    </section>
    <section class="card">
      <h2 style="margin-bottom:10px">So kommen Empfänger dazu</h2>
      <ul class="muted klein" style="margin:0;padding-left:18px;display:grid;gap:6px">
        <li>Über die Website: Bei Terminanfrage, Design-Book und Grundriss gibt es eine Checkbox „Newsletter“. Nach dem Klick auf den Bestätigungslink (Double-Opt-in) erscheinen die Personen hier automatisch.</li>
        <li>Per <a href="import.php">Import aus Excel oder CSV</a>, mit Nachweis der Einwilligung.</li>
        <li>Einzeln im Kontakt unter „Newsletter“.</li>
      </ul>
    </section>
  </div>
</div>
<?php fuss();
