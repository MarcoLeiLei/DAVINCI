<?php
/* DAVINCI intern – persönliche E-Mail mit Signatur schreiben */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
$aktion = g('aktion');
$kontakte = kontakte();

function adressen(string $s): array {
  return array_values(array_unique(array_filter(array_map(fn($m) => strtolower(trim($m)), preg_split('/[,;\s]+/', $s)), fn($m) => filter_var($m, FILTER_VALIDATE_EMAIL))));
}

if (ist_post() && $aktion === 'vorschau') {
  header('Content-Type: text/html; charset=utf-8');
  $k = p('kontakt') ? ($kontakte[p('kontakt')] ?? null) : null;
  echo email_html(p('betreff'), p('text') ?: ' ', $ich, $k);
  exit;
}

if (ist_post() && $aktion === 'senden') {
  $an = adressen(p('an')); $cc = adressen(p('cc'));
  $zurueck = 'email.php?' . http_build_query(array_filter(['kontakt' => p('kontakt'), 'lead' => p('lead')]));
  $_SESSION['email_entwurf'] = ['an' => p('an'), 'cc' => p('cc'), 'betreff' => p('betreff'), 'text' => p('text')];
  if (!$an) { meldung('Bitte mindestens eine gültige Empfängeradresse eintragen.', 'fehler'); weiter($zurueck); }
  if (count($an) + count($cc) > 20) { meldung('Höchstens 20 Empfänger. Für mehr bitte den Newsletter nutzen.', 'fehler'); weiter($zurueck); }
  if (p('betreff') === '' || p('text') === '') { meldung('Bitte Betreff und Text ausfüllen.', 'fehler'); weiter($zurueck); }
  // Anhänge
  $anhaenge = []; $summe = 0;
  foreach ($_FILES['anhang']['name'] ?? [] as $i => $name) {
    if (($_FILES['anhang']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
    if ($_FILES['anhang']['error'][$i] !== UPLOAD_ERR_OK) { meldung('Ein Anhang konnte nicht hochgeladen werden.', 'fehler'); weiter($zurueck); }
    $summe += $_FILES['anhang']['size'][$i];
    $typ = function_exists('mime_content_type') ? (mime_content_type($_FILES['anhang']['tmp_name'][$i]) ?: '') : '';
    $anhaenge[] = [basename($name), $_FILES['anhang']['tmp_name'][$i], $typ];
  }
  if ($summe > 15 * 1024 * 1024) { meldung('Die Anhänge sind zusammen größer als 15 MB.', 'fehler'); weiter($zurueck); }
  $k = p('kontakt') ? ($kontakte[p('kontakt')] ?? null) : null;
  $html = email_html(p('betreff'), p('text'), $ich, $k);
  $bcc = p('kopie') && filter_var($ich['email'] ?? '', FILTER_VALIDATE_EMAIL) ? [$ich['email']] : [];
  $ok = mail_roh(implode(', ', $an), p('betreff'), $html, ['von' => absender_fuer($ich), 'von_name' => $ich['name'] . ' · DAVINCI', 'antwort' => $ich['email'] ?: einstellungen()['antwort'], 'cc' => $cc, 'bcc' => $bcc, 'anhaenge' => $anhaenge]);
  if (!$ok) { meldung('Die E-Mail konnte nicht gesendet werden. Bitte später erneut versuchen.', 'fehler'); weiter($zurueck); }
  unset($_SESSION['email_entwurf']);
  $mid = neue_id();
  aendern('mails', function (&$m) use ($mid, $an, $cc, $html, $anhaenge, $ich) {
    $m[$mid] = ['id' => $mid, 'zeit' => date('c'), 'von' => $ich['id'], 'an' => $an, 'cc' => $cc, 'betreff' => p('betreff'), 'html' => $html,
                'anhaenge' => array_map(fn($x) => $x[0], $anhaenge), 'kontakt' => p('kontakt') ?: null, 'lead' => p('lead') ?: null];
    if (count($m) > 2000) $m = array_slice($m, -2000, null, true);
  });
  // Im Verlauf der Kontakte und des Projekts vermerken
  $alleMails = array_merge($an, $cc);
  aendern('kontakte', function (&$ks) use ($alleMails, $mid) { foreach ($ks as &$x) if ($x['email'] && in_array(strtolower($x['email']), $alleMails, true)) verlauf($x, 'E-Mail gesendet: ' . p('betreff')); });
  if (p('lead')) aendern('leads', function (&$l) { if (isset($l[p('lead')])) verlauf($l[p('lead')], 'E-Mail gesendet: ' . p('betreff')); });
  meldung('E-Mail gesendet.');
  weiter(p('lead') ? 'leads.php?id=' . p('lead') : (p('kontakt') ? 'kontakte.php?id=' . p('kontakt') : 'email.php'));
}

/* ---------- Gesendete E-Mail ansehen ---------- */
if (g('id')) {
  $m = lesen('mails')[g('id')] ?? null;
  if (!$m) weiter('email.php');
  kopf($m['betreff'], 'email');
  seitenkopf('Gesendet · ' . datum($m['zeit'], true) . ' Uhr', e($m['betreff']), '<a class="btn btn--hell" href="email.php">Neue E-Mail</a>');
  ?>
  <div class="grid grid--side">
    <section class="vorschau" style="position:static"><iframe title="E-Mail" sandbox srcdoc="<?= e($m['html']) ?>" style="height:640px;background:#fff"></iframe></section>
    <section class="card"><dl class="dl">
      <dt>Von</dt><dd><?= e(benutzer_name($m['von'])) ?></dd><dt>An</dt><dd><?= e(implode(', ', $m['an'])) ?></dd>
      <?php if ($m['cc']): ?><dt>Kopie</dt><dd><?= e(implode(', ', $m['cc'])) ?></dd><?php endif; ?>
      <?php if ($m['anhaenge']): ?><dt>Anhänge</dt><dd><?= e(implode("\n", $m['anhaenge'])) ?></dd><?php endif; ?>
    </dl></section>
  </div>
  <?php fuss(); exit;
}

/* ---------- Schreiben ---------- */
$entwurf = $_SESSION['email_entwurf'] ?? null;
$k = g('kontakt') ? ($kontakte[g('kontakt')] ?? null) : null;
$lead = g('lead') ? (leads()[g('lead')] ?? null) : null;
if (!$k && $lead && $lead['kontakt_id']) $k = $kontakte[$lead['kontakt_id']] ?? null;
$an = $entwurf['an'] ?? ($k['email'] ?? '');
$text = $entwurf['text'] ?? ('Guten Tag ' . ($k ? kontakt_name($k) : '') . ",\n\n\n\nHerzliche Grüße\n" . explode(' ', $ich['name'])[0]);
$betreff = $entwurf['betreff'] ?? ($lead ? 'Ihr DAVINCI Haus' . ($lead['haus'] ? ' · ' . $lead['haus'] : '') : '');
$gesendet = lesen('mails'); uasort($gesendet, fn($a, $b) => strcmp($b['zeit'], $a['zeit']));
kopf('E-Mail', 'email');
seitenkopf('Persönliche E-Mail mit Signatur', 'E-Mail schreiben');
?>
<?php if (!($ich['position'] ?? '') || !($ich['email'] ?? '')): ?><p class="hinweis hinweis--warn" style="margin-bottom:20px">Ihre Signatur ist noch unvollständig. Tragen Sie unter <a href="konto.php">Mein Zugang</a> Ihre Rolle, Telefon und E-Mail ein.</p><?php endif; ?>
<form method="post" action="email.php?aktion=senden" enctype="multipart/form-data" class="editor" data-mail>
  <?= csrf_feld() ?><input type="hidden" name="kontakt" value="<?= e($k['id'] ?? '') ?>"><input type="hidden" name="lead" value="<?= e($lead['id'] ?? '') ?>">
  <div class="stack">
    <section class="card form">
      <label class="feld">An<input name="an" list="kontaktmails" value="<?= e($an) ?>" placeholder="name@beispiel.de, mehrere mit Komma" required autocomplete="off"></label>
      <datalist id="kontaktmails"><?php foreach ($kontakte as $x) if ($x['email']): ?><option value="<?= e($x['email']) ?>"><?= e(kontakt_name($x)) ?></option><?php endif; ?></datalist>
      <label class="feld">Kopie (CC, optional)<input name="cc" value="<?= e($entwurf['cc'] ?? '') ?>" list="kontaktmails" autocomplete="off"></label>
      <label class="feld">Betreff<input name="betreff" value="<?= e($betreff) ?>" required></label>
      <label class="feld">Text<textarea name="text" rows="14" required style="min-height:300px"><?= e($text) ?></textarea></label>
      <p class="muted klein">Leerzeile = neuer Absatz · <code>**fett**</code> · <code>[Linktext](https://…)</code> · „- “ am Zeilenanfang = Liste. Die Signatur mit Logo wird automatisch angehängt.</p>
      <label class="feld"><?= icon('clip') ?> Anhänge (zusammen bis 15 MB)<input type="file" name="anhang[]" multiple></label>
      <label class="check"><input type="checkbox" name="kopie" value="1" checked><span>Kopie an mich (<?= e($ich['email'] ?: 'keine E-Mail im Profil') ?>)</span></label>
      <div class="knopfreihe"><button class="btn" type="submit"><?= icon('send') ?> Senden</button><?php if ($k): ?><a class="btn btn--hell" href="kontakte.php?id=<?= e($k['id']) ?>">Zurück zum Kontakt</a><?php endif; ?></div>
      <p class="muted klein">Absender: <?= e($ich['name']) ?> · DAVINCI &lt;<?= e(absender_fuer($ich)) ?>&gt;. Antworten gehen an <?= e($ich['email'] ?: einstellungen()['antwort']) ?>.</p>
    </section>
    <section class="card">
      <h2 style="margin-bottom:12px">Zuletzt gesendet</h2>
      <?php if (!$gesendet): ?><p class="muted klein">Noch keine E-Mails aus dem internen Bereich.</p><?php else: ?>
      <ul class="liste"><?php foreach (array_slice($gesendet, 0, 12) as $m): ?>
        <li><div class="liste__haupt"><a class="liste__titel" href="email.php?id=<?= e($m['id']) ?>"><?= e($m['betreff']) ?></a><span class="liste__sub">an <?= e(implode(', ', $m['an'])) ?> · <?= e(benutzer_name($m['von'])) ?></span></div><span class="muted klein nowrap"><?= relativ($m['zeit']) ?></span></li>
      <?php endforeach; ?></ul><?php endif; ?>
    </section>
  </div>
  <section class="vorschau" data-vorschau>
    <div class="vorschau__kopf"><span>Vorschau mit Signatur</span></div>
    <iframe title="Vorschau" sandbox="allow-same-origin" data-frame style="background:#fff"></iframe>
  </section>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const f = document.querySelector('[data-mail]'), frame = f.querySelector('[data-frame]'); let t;
  const zeigen = () => { const fd = new FormData(f); fd.delete('anhang[]'); fetch('email.php?aktion=vorschau', { method: 'POST', body: fd, credentials: 'same-origin' }).then(r => r.text()).then(h => { frame.srcdoc = h; }); };
  f.addEventListener('input', e => { if (e.target.type === 'file') return; clearTimeout(t); t = setTimeout(zeigen, 400); });
  zeigen();
});
</script>
<?php unset($_SESSION['email_entwurf']); fuss();
