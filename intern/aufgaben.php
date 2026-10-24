<?php
/* DAVINCI intern – Aufgaben: Team-Prios für alle, persönliche Aufgaben nur für mich */
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
/** Eine Aufgabe als Zeile mit Haken. */
function aufgabe_zeile(array $a, string $zurueck, bool $zeigePrio = true): void {
  $ich = ich(); ?>
  <div class="aufgabe<?= $a['erledigt'] ? ' ist-erledigt' : '' ?><?= $a['art'] === 'team' && $zeigePrio ? ' aufgabe--p' . $a['prio'] : '' ?>">
    <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($a['id']) ?>&zurueck=<?= rawurlencode($zurueck) ?>"><?= csrf_feld() ?><button class="aufgabe__haken" title="<?= $a['erledigt'] ? 'Wieder öffnen' : 'Erledigt' ?>"></button></form>
    <div class="aufgabe__haupt">
      <a class="aufgabe__titel" href="aufgaben.php?id=<?= e($a['id']) ?>" style="text-decoration:none"><?= e($a['titel']) ?></a>
      <div class="aufgabe__meta">
        <?php if ($a['art'] === 'team' && $zeigePrio && $a['prio'] === 1): ?><span class="wichtig">Hohe Priorität</span><?php endif; ?>
        <?php if ($a['faellig']): ?><span class="<?= !$a['erledigt'] && $a['faellig'] < date('Y-m-d') ? 'faellig' : '' ?>">bis <?= datum($a['faellig']) ?></span><?php endif; ?>
        <?php if ($a['art'] === 'team'): ?><span><?= $a['zugewiesen'] ? ($a['zugewiesen'] === $ich['id'] ? 'Ich' : e(benutzer_name($a['zugewiesen']))) : 'Team' ?></span><?php endif; ?>
        <?php if ($b = bezug_text($a['bezug'])): ?><span><?= $b ?></span><?php endif; ?>
        <?php if ($a['erledigt']): ?><span>erledigt <?= relativ($a['erledigt']) ?></span><?php endif; ?>
      </div>
      <?php if ($a['text'] && !$a['erledigt']): ?><p class="aufgabe__text"><?= e(mb_strimwidth($a['text'], 0, 220, '…')) ?></p><?php endif; ?>
    </div>
  </div>
<?php }

$zurueck = (string) g('zurueck');
$ziel = $zurueck !== '' && preg_match('/^[a-z]+\.php(\?[\w=&%.:-]*)?$/', $zurueck) ? $zurueck : 'aufgaben.php';
$alle = aufgaben();
$eigene = fn(string $aid) => isset($alle[$aid]) && aufgabe_sichtbar(aufgabe_norm($alle[$aid]), $ich);

if (ist_post()) {
  if ($aktion === 'schnell') {
    $art = p('art') === 'persoenlich' ? 'persoenlich' : 'team';
    aendern('aufgaben', function (&$a) use ($ich, $art) {
      $x = leere_aufgabe(); $x['titel'] = p('titel'); $x['art'] = $art;
      $x['prio'] = $art === 'team' ? max(1, min(3, (int) p('prio', '2'))) : 2;
      $x['zugewiesen'] = $art === 'persoenlich' ? $ich['id'] : null;
      $a[$x['id']] = $x;
    });
    meldung($art === 'team' ? 'Team-Aufgabe angelegt.' : 'Persönliche Aufgabe angelegt.');
    weiter($ziel);
  }
  if ($aktion === 'haken' && $id && $eigene($id)) {
    aendern('aufgaben', function (&$a) use ($id) { if (isset($a[$id])) $a[$id]['erledigt'] = $a[$id]['erledigt'] ? null : date('c'); });
    weiter($ziel);
  }
  if ($aktion === 'speichern' && (!$id || $eigene($id))) {
    $id = aendern('aufgaben', function (&$a) use ($id, $ich) {
      $x = $id && isset($a[$id]) ? aufgabe_norm($a[$id]) : leere_aufgabe();
      $x['titel'] = p('titel') ?: 'Aufgabe';
      $x['text'] = p('text');
      $x['faellig'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', p('faellig')) ? p('faellig') : '';
      $x['art'] = p('art') === 'persoenlich' ? 'persoenlich' : 'team';
      $x['prio'] = max(1, min(3, (int) p('prio', '2')));
      $x['wichtig'] = $x['art'] === 'team' && $x['prio'] === 1;
      $x['zugewiesen'] = $x['art'] === 'persoenlich' ? $ich['id'] : (p('zugewiesen') ?: null);
      $x['bezug'] = bezug_lesen(p('bezug'));
      $a[$x['id']] = $x;
      return $x['id'];
    });
    meldung('Aufgabe gespeichert.');
    $b = bezug_lesen(p('bezug'));
    weiter($b ? ($b['typ'] === 'lead' ? 'leads.php?id=' : 'kontakte.php?id=') . $b['id'] : 'aufgaben.php');
  }
  if ($aktion === 'loeschen' && $id && $eigene($id)) {
    aendern('aufgaben', function (&$a) use ($id) { unset($a[$id]); });
    meldung('Aufgabe gelöscht.');
    weiter('aufgaben.php');
  }
  weiter('aufgaben.php');
}

/* ---------- Formular ---------- */
if ($aktion === 'neu' || $id) {
  $a = $id ? ($eigene($id) ? aufgabe_norm($alle[$id]) : null) : leere_aufgabe();
  if (!$a) weiter('aufgaben.php');
  if (!$id) { $a['bezug'] = bezug_lesen((string) g('bezug')); $a['art'] = g('art') === 'persoenlich' ? 'persoenlich' : 'team'; }
  $bez = ['' => '– kein Bezug –'];
  foreach (leads() as $l) if (!in_array($l['status'], ['gewonnen', 'verloren'], true) || ($a['bezug']['id'] ?? '') === $l['id']) $bez['lead:' . $l['id']] = 'Projekt: ' . $l['titel'];
  $ks = kontakte(); uasort($ks, fn($x, $y) => strcasecmp(kontakt_name($x), kontakt_name($y)));
  foreach ($ks as $k) $bez['kontakt:' . $k['id']] = 'Kontakt: ' . kontakt_name($k);
  kopf($id ? 'Aufgabe' : 'Neue Aufgabe', 'aufgaben');
  seitenkopf('Aufgaben', $id ? e($a['titel']) : 'Neue Aufgabe');
  ?>
  <section class="card" style="max-width:820px">
    <form method="post" action="aufgaben.php?aktion=speichern<?= $id ? '&id=' . e($id) : '' ?>" class="form form--2" data-aufgabe><?= csrf_feld() ?>
      <div class="feld voll">Art
        <div class="checks">
          <label class="chip"><input type="radio" name="art" value="team"<?= $a['art'] === 'team' ? ' checked' : '' ?>><span>Team-Prio · sieht das ganze Team</span></label>
          <label class="chip"><input type="radio" name="art" value="persoenlich"<?= $a['art'] === 'persoenlich' ? ' checked' : '' ?>><span>Persönlich · nur für mich</span></label>
        </div>
      </div>
      <label class="feld voll">Aufgabe<input name="titel" value="<?= e($a['titel']) ?>" required autofocus placeholder="z. B. Grundstücksunterlagen anfordern"></label>
      <label class="feld voll">Details<textarea name="text"><?= e($a['text']) ?></textarea></label>
      <label class="feld" data-nur-team>Priorität<?= auswahl('prio', PRIOS, $a['prio']) ?></label>
      <label class="feld">Fällig am<input type="date" name="faellig" value="<?= e($a['faellig']) ?>"></label>
      <label class="feld" data-nur-team>Zuständig<?= auswahl('zugewiesen', ['' => '– ganzes Team –'] + benutzer_optionen(false), $a['zugewiesen']) ?></label>
      <label class="feld">Bezug<?= auswahl('bezug', $bez, $a['bezug'] ? $a['bezug']['typ'] . ':' . $a['bezug']['id'] : '') ?></label>
      <div class="voll knopfreihe"><button class="btn" type="submit">Speichern</button><a class="btn btn--hell" href="aufgaben.php">Abbrechen</a></div>
    </form>
    <?php if ($id): ?>
      <div class="knopfreihe" style="margin-top:18px;padding-top:18px;border-top:1px solid var(--line)">
        <form method="post" action="aufgaben.php?aktion=haken&id=<?= e($id) ?>&zurueck=<?= rawurlencode('aufgaben.php?id=' . $id) ?>"><?= csrf_feld() ?><button class="btn btn--hell btn--klein"><?= $a['erledigt'] ? 'Wieder öffnen' : 'Als erledigt markieren' ?></button></form>
        <form method="post" action="aufgaben.php?aktion=loeschen&id=<?= e($id) ?>" data-bestaetigen="Aufgabe löschen?"><?= csrf_feld() ?><button class="btn btn--gefahr btn--klein"><?= icon('trash') ?> Löschen</button></form>
        <span class="muted klein">Angelegt <?= datum($a['erstellt'], true) ?> von <?= e(benutzer_name($a['von'])) ?></span>
      </div>
    <?php endif; ?>
  </section>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const f = document.querySelector('[data-aufgabe]');
    const set = () => { const team = f.querySelector('[name=art]:checked').value === 'team'; f.querySelectorAll('[data-nur-team]').forEach(el => { el.style.display = team ? '' : 'none'; }); };
    f.querySelectorAll('[name=art]').forEach(r => r.addEventListener('change', set)); set();
  });
  </script>
  <?php fuss(); exit;
}

/* ---------- Übersicht ---------- */
$sicht = aufgaben_sichtbar($ich);
$team = array_values(array_filter($sicht, fn($a) => $a['art'] === 'team' && !$a['erledigt']));
$pers = array_values(array_filter($sicht, fn($a) => $a['art'] === 'persoenlich' && !$a['erledigt'] && ($a['zugewiesen'] ?? $a['von']) === $ich['id']));
$mirZugewiesen = count(array_filter($team, fn($a) => $a['zugewiesen'] === $ich['id']));
aufgaben_sortieren($team); aufgaben_sortieren($pers);
$erledigt = array_values(array_filter($sicht, fn($a) => $a['erledigt']));
usort($erledigt, fn($a, $b) => strcmp($b['erledigt'], $a['erledigt']));
kopf('Aufgaben', 'aufgaben');
seitenkopf('Team und ich', 'Aufgaben', '<a class="btn" href="aufgaben.php?aktion=neu">' . icon('plus') . ' Neue Aufgabe</a>');
?>
<div class="grid grid--2">
  <section class="card">
    <div class="card__head"><h2>Team-Prios</h2><span class="muted klein"><?= count($team) ?> offen<?= $mirZugewiesen ? ' · ' . $mirZugewiesen . ' bei mir' : '' ?></span></div>
    <p class="muted klein" style="margin:-8px 0 14px">Für alle sichtbar, nach Priorität sortiert.</p>
    <form method="post" action="aufgaben.php?aktion=schnell" class="knopfreihe" style="margin-bottom:8px"><?= csrf_feld() ?><input type="hidden" name="art" value="team">
      <input name="titel" placeholder="Neue Team-Aufgabe …" required style="flex:1;min-width:180px"><?= auswahl('prio', PRIOS, 2, 'style="width:auto"') ?><button class="btn btn--klein" type="submit"><?= icon('plus') ?></button></form>
    <?php if (!$team): ?><div style="margin-top:14px"><?= leer('Keine offenen Team-Aufgaben.') ?></div><?php endif; ?>
    <?php $vorher = 0; foreach ($team as $a): if ($a['prio'] !== $vorher): $vorher = $a['prio']; ?><p class="prio-kopf prio-kopf--<?= $a['prio'] ?>"><?= PRIOS[$a['prio']] ?></p><?php endif; aufgabe_zeile($a, 'aufgaben.php', false); endforeach; ?>
  </section>
  <section class="card">
    <div class="card__head"><h2>Persönlich</h2><span class="muted klein"><?= count($pers) ?> offen</span></div>
    <p class="muted klein" style="margin:-8px 0 14px">Nur Sie sehen diese Aufgaben.</p>
    <form method="post" action="aufgaben.php?aktion=schnell" class="knopfreihe" style="margin-bottom:8px"><?= csrf_feld() ?><input type="hidden" name="art" value="persoenlich">
      <input name="titel" placeholder="Für mich notieren …" required style="flex:1"><button class="btn btn--klein" type="submit"><?= icon('plus') ?></button></form>
    <?php if (!$pers): ?><div style="margin-top:14px"><?= leer('Keine persönlichen Aufgaben.') ?></div><?php endif; ?>
    <?php foreach ($pers as $a) aufgabe_zeile($a, 'aufgaben.php'); ?>
  </section>
</div>
<?php if ($erledigt): ?>
<details class="card" style="margin-top:20px"><summary style="cursor:pointer"><h2 style="display:inline">Erledigt</h2> <span class="muted klein"><?= count($erledigt) ?></span></summary>
  <div style="margin-top:12px"><?php foreach (array_slice($erledigt, 0, 30) as $a) aufgabe_zeile($a, 'aufgaben.php'); ?></div>
</details>
<?php endif; fuss();
