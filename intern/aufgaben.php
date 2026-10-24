<?php
/* DAVINCI intern – Aufgaben und Notizen fürs Team */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
$id = g('id');
$aktion = g('aktion');

function bezug_lesen(string $s): ?array {
  if (!preg_match('/^(kontakt|lead):([a-f0-9]+)$/', $s, $m)) return null;
  return ['typ' => $m[1], 'id' => $m[2]];
}
function bezug_text(?array $b): string {
  if (!$b) return '';
  if ($b['typ'] === 'kontakt') { $k = kontakte()[$b['id']] ?? null; return $k ? '<a href="kontakte.php?id=' . e($b['id']) . '">' . e(kontakt_name($k)) . '</a>' : ''; }
  $l = leads()[$b['id']] ?? null; return $l ? '<a href="leads.php?id=' . e($b['id']) . '">' . e($l['titel']) . '</a>' : '';
}
$zurueck = (string) g('zurueck');
$ziel = $zurueck !== '' && preg_match('/^[a-z]+\.php(\?[\w=&%.:-]*)?$/', $zurueck) ? $zurueck : 'aufgaben.php';

if (ist_post()) {
  if ($aktion === 'schnell') {
    aendern('aufgaben', function (&$a) use ($ich) { $x = leere_aufgabe(); $x['titel'] = p('titel'); $x['zugewiesen'] = $ich['id']; $a[$x['id']] = $x; });
    meldung('Aufgabe angelegt.');
    weiter($_SERVER['HTTP_REFERER'] ?? 'aufgaben.php');
  }
  if ($aktion === 'haken' && $id) {
    aendern('aufgaben', function (&$a) use ($id) { if (isset($a[$id])) $a[$id]['erledigt'] = $a[$id]['erledigt'] ? null : date('c'); });
    weiter($ziel);
  }
  if ($aktion === 'speichern') {
    $id = aendern('aufgaben', function (&$a) use ($id) {
      $x = $id && isset($a[$id]) ? $a[$id] : leere_aufgabe();
      $x['titel'] = p('titel') ?: 'Aufgabe';
      $x['text'] = p('text');
      $x['faellig'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', p('faellig')) ? p('faellig') : '';
      $x['wichtig'] = (bool) p('wichtig');
      $x['zugewiesen'] = p('zugewiesen') ?: null;
      $x['bezug'] = bezug_lesen(p('bezug'));
      $a[$x['id']] = $x;
      return $x['id'];
    });
    meldung('Aufgabe gespeichert.');
    $b = bezug_lesen(p('bezug'));
    weiter($b ? ($b['typ'] === 'lead' ? 'leads.php?id=' : 'kontakte.php?id=') . $b['id'] : 'aufgaben.php');
  }
  if ($aktion === 'loeschen' && $id) {
    aendern('aufgaben', function (&$a) use ($id) { unset($a[$id]); });
    meldung('Aufgabe gelöscht.');
    weiter('aufgaben.php');
  }
}

if ($aktion === 'neu' || $id) {
  $a = $id ? (aufgaben()[$id] ?? null) : leere_aufgabe();
  if (!$a) weiter('aufgaben.php');
  if (!$id) { $a['bezug'] = bezug_lesen((string) g('bezug')); $a['zugewiesen'] = $ich['id']; }
  $bez = ['' => '– kein Bezug –'];
  foreach (leads() as $l) if (!in_array($l['status'], ['gewonnen', 'verloren'], true) || ($a['bezug']['id'] ?? '') === $l['id']) $bez['lead:' . $l['id']] = 'Projekt: ' . $l['titel'];
  $ks = kontakte(); uasort($ks, fn($x, $y) => strcasecmp(kontakt_name($x), kontakt_name($y)));
  foreach ($ks as $k) $bez['kontakt:' . $k['id']] = 'Kontakt: ' . kontakt_name($k);
  kopf($id ? 'Aufgabe' : 'Neue Aufgabe', 'aufgaben');
  seitenkopf('Aufgaben', $id ? e($a['titel']) : 'Neue Aufgabe');
  ?>
  <section class="card" style="max-width:820px">
    <form method="post" action="aufgaben.php?aktion=speichern<?= $id ? '&id=' . e($id) : '' ?>" class="form form--2"><?= csrf_feld() ?>
      <label class="feld voll">Aufgabe<input name="titel" value="<?= e($a['titel']) ?>" required autofocus placeholder="z. B. Grundstücksunterlagen anfordern"></label>
      <label class="feld voll">Details<textarea name="text"><?= e($a['text']) ?></textarea></label>
      <label class="feld">Fällig am<input type="date" name="faellig" value="<?= e($a['faellig']) ?>"></label>
      <label class="feld">Zuständig<?= auswahl('zugewiesen', benutzer_optionen(), $a['zugewiesen']) ?></label>
      <label class="feld voll">Bezug<?= auswahl('bezug', $bez, $a['bezug'] ? $a['bezug']['typ'] . ':' . $a['bezug']['id'] : '') ?></label>
      <label class="check voll"><input type="checkbox" name="wichtig" value="1"<?= $a['wichtig'] ? ' checked' : '' ?>><span>Wichtig (steht in der Übersicht oben)</span></label>
      <div class="voll knopfreihe"><button class="btn" type="submit">Speichern</button><a class="btn btn--hell" href="aufgaben.php">Abbrechen</a></div>
    </form>
    <?php if ($id): ?>
      <div class="knopfreihe" style="margin-top:18px;padding-top:18px;border-top:1px solid var(--line)">
        <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($id) ?>"><?= csrf_feld() ?><button class="btn btn--hell btn--klein"><?= $a['erledigt'] ? 'Wieder öffnen' : 'Als erledigt markieren' ?></button></form>
        <form method="post" action="aufgaben.php?aktion=loeschen&id=<?= e($id) ?>" data-bestaetigen="Aufgabe löschen?"><?= csrf_feld() ?><button class="btn btn--gefahr btn--klein"><?= icon('trash') ?> Löschen</button></form>
        <span class="muted klein">Angelegt <?= datum($a['erstellt'], true) ?> von <?= e(benutzer_name($a['von'])) ?></span>
      </div>
    <?php endif; ?>
  </section>
  <?php fuss(); exit;
}

$filter = g('f') ?: 'meine';
$alle = aufgaben();
$offen = array_filter($alle, fn($a) => !$a['erledigt'] && ($filter === 'alle' || !$a['zugewiesen'] || $a['zugewiesen'] === $ich['id']));
usort($offen, fn($a, $b) => [(int) !$a['wichtig'], $a['faellig'] ?: '9999', $a['erstellt']] <=> [(int) !$b['wichtig'], $b['faellig'] ?: '9999', $b['erstellt']]);
$erledigt = array_filter($alle, fn($a) => $a['erledigt']);
usort($erledigt, fn($a, $b) => strcmp($b['erledigt'], $a['erledigt']));
kopf('Aufgaben', 'aufgaben');
seitenkopf('Team', 'Aufgaben', '<a class="btn" href="aufgaben.php?aktion=neu">' . icon('plus') . ' Neue Aufgabe</a>');
?>
<div class="filter"><div class="segment"><a href="aufgaben.php?f=meine" class="<?= $filter === 'meine' ? 'is-active' : '' ?>">Meine</a><a href="aufgaben.php?f=alle" class="<?= $filter === 'alle' ? 'is-active' : '' ?>">Alle</a></div></div>
<div class="grid grid--side">
  <section class="card">
    <form method="post" action="aufgaben.php?aktion=schnell" class="knopfreihe" style="margin-bottom:8px"><?= csrf_feld() ?><input name="titel" placeholder="Schnell notieren …" required style="flex:1"><button class="btn btn--klein" type="submit"><?= icon('plus') ?> Hinzufügen</button></form>
    <?php if (!$offen): ?><div style="margin-top:14px"><?= leer('Keine offenen Aufgaben.') ?></div><?php endif; ?>
    <?php foreach ($offen as $a): ?>
      <div class="aufgabe">
        <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($a['id']) ?>&zurueck=<?= rawurlencode('aufgaben.php?f=' . $filter) ?>"><?= csrf_feld() ?><button class="aufgabe__haken" title="Erledigt"></button></form>
        <div class="aufgabe__haupt">
          <a class="aufgabe__titel" href="aufgaben.php?id=<?= e($a['id']) ?>" style="text-decoration:none"><?= e($a['titel']) ?></a>
          <div class="aufgabe__meta">
            <?php if ($a['wichtig']): ?><span class="wichtig">Wichtig</span><?php endif; ?>
            <?php if ($a['faellig']): ?><span class="<?= $a['faellig'] < date('Y-m-d') ? 'faellig' : '' ?>">bis <?= datum($a['faellig']) ?></span><?php endif; ?>
            <?php if ($a['zugewiesen'] && $a['zugewiesen'] !== $ich['id']): ?><span><?= e(benutzer_name($a['zugewiesen'])) ?></span><?php endif; ?>
            <?php if ($b = bezug_text($a['bezug'])): ?><span><?= $b ?></span><?php endif; ?>
          </div>
          <?php if ($a['text']): ?><p class="aufgabe__text"><?= e(mb_strimwidth($a['text'], 0, 220, '…')) ?></p><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
  <section class="card">
    <h2 style="margin-bottom:10px">Erledigt</h2>
    <?php if (!$erledigt): ?><p class="muted klein">Noch nichts erledigt.</p><?php endif; ?>
    <?php foreach (array_slice($erledigt, 0, 15) as $a): ?>
      <div class="aufgabe ist-erledigt">
        <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($a['id']) ?>"><?= csrf_feld() ?><button class="aufgabe__haken" title="Wieder öffnen"></button></form>
        <div class="aufgabe__haupt"><span class="aufgabe__titel"><?= e($a['titel']) ?></span><div class="aufgabe__meta"><span><?= relativ($a['erledigt']) ?></span></div></div>
      </div>
    <?php endforeach; ?>
  </section>
</div>
<?php fuss();
