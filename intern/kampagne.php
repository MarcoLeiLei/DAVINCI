<?php
/* DAVINCI intern – Newsletter bearbeiten, testen und versenden */
require __DIR__ . '/lib/start.php';
$ich = seite_schuetzen();
$id = g('id');
$aktion = g('aktion');

const BLOCKTYPEN = ['titel', 'text', 'bild', 'knopf', 'trenner', 'kasten'];
function bloecke_aus_post(): array {
  $b = json_decode((string) p('bloecke'), true);
  if (!is_array($b)) return [];
  $o = [];
  foreach ($b as $x) {
    if (!is_array($x) || !in_array($x['typ'] ?? '', BLOCKTYPEN, true)) continue;
    $sauber = ['typ' => $x['typ']];
    foreach (['eyebrow', 'text', 'src', 'alt', 'link', 'url', 'stil', 'titel'] as $f) if (isset($x[$f])) $sauber[$f] = mb_substr(trim((string) $x[$f]), 0, 8000);
    foreach (['link', 'url'] as $f) if (!empty($sauber[$f]) && !preg_match('#^(https?://|mailto:|tel:)#i', $sauber[$f])) $sauber[$f] = 'https://' . ltrim($sauber[$f], '/');
    $o[] = $sauber;
  }
  return array_slice($o, 0, 60);
}
function kampagne_aus_post(array $k): array {
  $k['betreff'] = mb_substr(p('betreff'), 0, 200);
  $k['preheader'] = mb_substr(p('preheader'), 0, 200);
  $k['gruppen'] = array_values(array_filter((array) ($_POST['gruppen'] ?? []), 'is_string'));
  $k['bloecke'] = bloecke_aus_post();
  $k['geaendert'] = date('c');
  return $k;
}

if ($aktion === 'neu') {
  $k = leere_kampagne();
  aendern('kampagnen', function (&$a) use ($k) { $a[$k['id']] = $k; });
  weiter('kampagne.php?id=' . $k['id']);
}
$kamp = lesen('kampagnen')[$id] ?? null;
if (!$kamp) { meldung('Newsletter nicht gefunden.', 'fehler'); weiter('newsletter.php'); }

/* ---------- Aktionen ---------- */
if (ist_post()) {
  switch ($aktion) {
    case 'vorschau':   // Live-Vorschau im Editor, ohne zu speichern
      header('Content-Type: text/html; charset=utf-8');
      $k = kampagne_aus_post($kamp);
      $muster = ['vorname' => 'Max', 'nachname' => 'Mustermann', 'newsletter' => ['token' => 'vorschau']];
      echo nl_html($k, $muster, '#');
      exit;
    case 'speichern':
      if ($kamp['status'] !== 'entwurf') weiter('kampagne.php?id=' . $id);
      aendern('kampagnen', function (&$a) use ($id) { $a[$id] = kampagne_aus_post($a[$id]); });
      if (!empty($_SERVER['HTTP_X_CSRF'])) json_aus(['ok' => true, 'zeit' => date('H:i')]);
      meldung('Entwurf gespeichert.');
      weiter('kampagne.php?id=' . $id);
    case 'bild':   // Bild hochladen
      $f = $_FILES['bild'] ?? null;
      if (!$f || $f['error'] !== UPLOAD_ERR_OK) json_aus(['ok' => false, 'fehler' => 'Kein Bild empfangen.'], 422);
      if ($f['size'] > 12 * 1024 * 1024) json_aus(['ok' => false, 'fehler' => 'Das Bild ist größer als 12 MB.'], 422);
      $info = @getimagesize($f['tmp_name']);
      $typen = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
      if (!$info || !isset($typen[$info[2]])) json_aus(['ok' => false, 'fehler' => 'Bitte ein JPG, PNG, GIF oder WebP hochladen.'], 422);
      $name = date('Ymd') . '-' . bin2hex(random_bytes(4)) . '.' . ($info[2] === IMAGETYPE_GIF ? 'gif' : ($info[2] === IMAGETYPE_PNG ? 'png' : 'jpg'));
      $ziel = APP . '/medien/' . $name;
      if (!is_dir(APP . '/medien')) mkdir(APP . '/medien', 0755, true);
      // Große Fotos auf 1200 px Breite verkleinern (spart Ladezeit im Postfach)
      if (function_exists('imagecreatefromstring') && $info[2] !== IMAGETYPE_GIF && $info[0] > 1200) {
        $src = imagecreatefromstring((string) file_get_contents($f['tmp_name']));
        $h = (int) round($info[1] * 1200 / $info[0]);
        $dst = imagecreatetruecolor(1200, $h);
        if ($info[2] === IMAGETYPE_PNG) { imagealphablending($dst, false); imagesavealpha($dst, true); }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, 1200, $h, $info[0], $info[1]);
        $info[2] === IMAGETYPE_PNG ? imagepng($dst, $ziel, 8) : imagejpeg($dst, $ziel, 84);
      } elseif ($info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
        imagejpeg(imagecreatefromwebp($f['tmp_name']), $ziel, 86);
      } else move_uploaded_file($f['tmp_name'], $ziel);
      json_aus(['ok' => true, 'src' => 'medien:' . $name, 'url' => 'medien/' . $name]);
    case 'test':
      $k = kampagne_aus_post($kamp);
      if ($kamp['status'] === 'entwurf') aendern('kampagnen', function (&$a) use ($id, $k) { $a[$id] = $k; });
      $an = array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', p('an'))), fn($m) => filter_var($m, FILTER_VALIDATE_EMAIL)));
      if (!$an) json_aus(['ok' => false, 'fehler' => 'Bitte eine gültige E-Mail-Adresse eingeben.'], 422);
      if (count($an) > 5) json_aus(['ok' => false, 'fehler' => 'Höchstens fünf Testadressen.'], 422);
      if ($k['betreff'] === '') json_aus(['ok' => false, 'fehler' => 'Bitte zuerst einen Betreff eintragen.'], 422);
      $muster = ['vorname' => explode(' ', $ich['name'])[0], 'nachname' => explode(' ', $ich['name'])[1] ?? '', 'newsletter' => ['token' => 'test']];
      $ok = 0;
      foreach ($an as $m) $ok += nl_mail($m, '[Test] ' . nl_ersetzen($k['betreff'], $muster), nl_html($k, $muster, basis_url() . '/newsletter-abmelden.php?t=test')) ? 1 : 0;
      aendern('kampagnen', function (&$a) use ($id, $an) { $a[$id]['tests'][] = ['zeit' => date('c'), 'an' => $an, 'wer' => ich()['name']]; });
      json_aus(['ok' => $ok > 0, 'meldung' => $ok ? "Test an $ok Adresse(n) verschickt." : 'Versand fehlgeschlagen. Ist das Postfach bei SiteGround eingerichtet?']);
    case 'starten':
      if ($kamp['status'] !== 'entwurf') weiter('kampagne.php?id=' . $id);
      $k = kampagne_aus_post($kamp);
      $empf = empfaenger_waehlen(kontakte(), $k['gruppen']);
      $fehler = $k['betreff'] === '' ? 'Bitte einen Betreff eintragen.' : (!$k['bloecke'] ? 'Der Newsletter hat noch keinen Inhalt.' : (!$empf ? 'Für diese Auswahl gibt es keine Empfänger mit Einwilligung.' : (!p('bestaetigt') ? 'Bitte den Versand bestätigen.' : '')));
      if ($fehler) { aendern('kampagnen', function (&$a) use ($id, $k) { $a[$id] = $k; }); meldung($fehler, 'fehler'); weiter('kampagne.php?id=' . $id); }
      // Später versenden: Zeitpunkt prüfen und Newsletter einplanen
      if (p('wann') === 'spaeter') {
        $ts = strtotime(p('zeitpunkt'));
        if (!$ts || $ts < time() + 120) { aendern('kampagnen', function (&$a) use ($id, $k) { $a[$id] = $k; }); meldung('Bitte einen Zeitpunkt mindestens zwei Minuten in der Zukunft wählen.', 'fehler'); weiter('kampagne.php?id=' . $id); }
        aendern('kampagnen', function (&$a) use ($id, $k, $ts) { $k['status'] = 'geplant'; $k['geplant_fuer'] = date('c', $ts); $k['von_versand'] = ich()['id']; $a[$id] = $k; });
        protokoll('Newsletter geplant für ' . datum($ts, true) . ' Uhr: ' . $k['betreff']);
        meldung('Newsletter geplant für ' . datum($ts, true) . ' Uhr.');
        weiter('kampagne.php?id=' . $id);
      }
      aendern('kampagnen', function (&$a) use ($id, $k) { $k['von_versand'] = ich()['id']; $a[$id] = $k; });
      $n = kampagne_starten($id);
      protokoll('Newsletter-Versand gestartet: ' . $k['betreff'] . " ($n Empfänger)");
      weiter('kampagne.php?id=' . $id);
    case 'entplanen':
      aendern('kampagnen', function (&$a) use ($id) { if ($a[$id]['status'] === 'geplant') { $a[$id]['status'] = 'entwurf'; $a[$id]['geplant_fuer'] = null; } });
      meldung('Planung aufgehoben. Der Newsletter ist wieder ein Entwurf.');
      weiter('kampagne.php?id=' . $id);
    case 'pausieren':
    case 'fortsetzen':
      aendern('kampagnen', function (&$a) use ($id, $aktion) {
        if ($aktion === 'pausieren' && $a[$id]['status'] === 'laeuft') $a[$id]['status'] = 'pausiert';
        if ($aktion === 'fortsetzen' && $a[$id]['status'] === 'pausiert') $a[$id]['status'] = 'laeuft';
      });
      weiter('kampagne.php?id=' . $id);
    case 'kopieren':
      $neu = leere_kampagne();
      foreach (['betreff', 'preheader', 'gruppen', 'bloecke'] as $f) $neu[$f] = $kamp[$f];
      $neu['betreff'] = $kamp['betreff'] . ' (Kopie)';
      aendern('kampagnen', function (&$a) use ($neu) { $a[$neu['id']] = $neu; });
      meldung('Kopie angelegt.');
      weiter('kampagne.php?id=' . $neu['id']);
    case 'loeschen':
      if ($kamp['status'] === 'laeuft') { meldung('Bitte den Versand zuerst pausieren.', 'fehler'); weiter('kampagne.php?id=' . $id); }
      aendern('kampagnen', function (&$a) use ($id) { unset($a[$id]); });
      meldung('Newsletter gelöscht.');
      weiter('newsletter.php');
  }
  weiter('kampagne.php?id=' . $id);
}

$kontakte = kontakte();
$einst = einstellungen();

/* ---------- Versandstatus (nach dem Start) ---------- */
if ($kamp['status'] === 'geplant') {
  geplante_pruefen();
  $kamp = lesen('kampagnen')[$id];
}
if ($kamp['status'] === 'geplant') {
  kopf($kamp['betreff'], 'newsletter');
  seitenkopf('Newsletter · Geplant', e($kamp['betreff']), '<form method="post" action="kampagne.php?id=' . e($id) . '&aktion=entplanen">' . csrf_feld() . '<button class="btn btn--hell">Planung aufheben und bearbeiten</button></form>');
  $ts = strtotime($kamp['geplant_fuer']);
  ?>
  <div class="grid grid--side">
    <section class="card" data-geplant="<?= $ts ?>">
      <div class="card__head"><h2>Geplanter Versand</h2><?= status_pill('geplant', KAMPAGNE_STATUS) ?></div>
      <p style="font-size:28px;font-weight:250;letter-spacing:-.02em"><?= datum($ts, true) ?> Uhr</p>
      <p class="muted" style="margin-top:6px" data-countdown></p>
      <dl class="dl" style="margin-top:18px">
        <dt>Gruppen</dt><dd><?= $kamp['gruppen'] ? e(implode(', ', $kamp['gruppen'])) : 'Alle Empfänger mit Einwilligung' ?></dd>
        <dt>Empfänger heute</dt><dd><?= count(empfaenger_waehlen($kontakte, $kamp['gruppen'])) ?> (wird zum Versandzeitpunkt neu bestimmt)</dd>
        <dt>Geplant von</dt><dd><?= e(benutzer_name($kamp['von_versand'] ?? null)) ?></dd>
      </dl>
    </section>
    <section class="vorschau"><div class="vorschau__kopf"><span>Vorschau</span></div>
      <iframe title="Vorschau" srcdoc="<?= e(nl_html($kamp, ['vorname' => 'Max', 'nachname' => 'Mustermann', 'newsletter' => ['token' => 'x']], '#')) ?>" sandbox></iframe></section>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const box = document.querySelector('[data-geplant]'), out = box.querySelector('[data-countdown]'), ziel = +box.dataset.geplant * 1000;
    const tick = () => { const s = Math.round((ziel - Date.now()) / 1000);
      if (s <= 0) { out.textContent = 'Der Versand startet jetzt …'; setTimeout(() => location.reload(), 4000); return; }
      const t = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60);
      out.textContent = 'Noch ' + (t ? t + ' Tag' + (t > 1 ? 'e ' : ' ') : '') + (h ? h + ' Std. ' : '') + m + ' Min.'; setTimeout(tick, 20000); };
    tick();
  });
  </script>
  <?php fuss(); exit;
}
if ($kamp['status'] !== 'entwurf') {
  $z = kampagne_zahlen($kamp);
  $prozent = $z['gesamt'] ? round(($z['gesamt'] - $z['offen']) / $z['gesamt'] * 100) : 0;
  kopf($kamp['betreff'], 'newsletter');
  seitenkopf('Newsletter · ' . (KAMPAGNE_STATUS[$kamp['status']] ?? ''), e($kamp['betreff']),
    '<form method="post" action="kampagne.php?id=' . e($id) . '&aktion=kopieren">' . csrf_feld() . '<button class="btn btn--hell">Als Vorlage kopieren</button></form>'
    . ($kamp['status'] === 'laeuft' ? '<form method="post" action="kampagne.php?id=' . e($id) . '&aktion=pausieren">' . csrf_feld() . '<button class="btn btn--hell">Pausieren</button></form>' : '')
    . ($kamp['status'] === 'pausiert' ? '<form method="post" action="kampagne.php?id=' . e($id) . '&aktion=fortsetzen">' . csrf_feld() . '<button class="btn">Fortsetzen</button></form>' : ''));
  ?>
  <div class="grid grid--side">
    <div class="stack">
      <section class="card" data-versand="<?= e($id) ?>" data-status="<?= e($kamp['status']) ?>">
        <div class="card__head"><h2>Versand</h2><span data-v-status><?= status_pill($kamp['status'], KAMPAGNE_STATUS) ?></span></div>
        <div class="fortschritt"><i data-v-balken style="width:<?= $prozent ?>%"></i></div>
        <p style="margin-top:12px" data-v-text><?= $z['ok'] ?> von <?= $z['gesamt'] ?> zugestellt<?= $z['fehler'] ? ' · ' . $z['fehler'] . ' Fehler' : '' ?><?= $z['uebersprungen'] ? ' · ' . $z['uebersprungen'] . ' übersprungen' : '' ?></p>
        <?php if ($kamp['status'] === 'laeuft'): ?><p class="muted klein" style="margin-top:8px" data-v-hinweis>Der Versand läuft in Paketen von <?= (int) $einst['paket'] ?> E-Mails, höchstens <?= (int) $einst['stundenlimit'] ?> pro Stunde. Solange diese Seite offen ist, geht es hier weiter. Ist der Cron-Job eingerichtet, läuft er auch ohne geöffnete Seite.</p><?php endif; ?>
        <dl class="dl" style="margin-top:18px">
          <dt>Gestartet</dt><dd><?= datum($kamp['gestartet'], true) ?> · <?= e(benutzer_name($kamp['von_versand'] ?? null)) ?></dd>
          <?php if ($kamp['fertig']): ?><dt>Fertig</dt><dd><?= datum($kamp['fertig'], true) ?></dd><?php endif; ?>
          <dt>Gruppen</dt><dd><?= $kamp['gruppen'] ? e(implode(', ', $kamp['gruppen'])) : 'Alle Empfänger mit Einwilligung' ?></dd>
          <dt>Abmeldungen</dt><dd><?= $z['abgemeldet'] ?></dd>
        </dl>
      </section>
      <section class="card">
        <h2 style="margin-bottom:12px">Empfänger</h2>
        <div class="tabelle-wrap"><table class="tabelle"><thead><tr><th>Name</th><th>E-Mail</th><th>Status</th><th>Zeit</th></tr></thead><tbody>
          <?php foreach (array_slice($kamp['empfaenger'], 0, 500, true) as $cid => $e): $c = $kontakte[$cid] ?? null; ?>
            <tr><td><?= $c ? '<a href="kontakte.php?id=' . e($cid) . '">' . e(kontakt_name($c)) . '</a>' : '<span class="muted">gelöscht</span>' ?></td><td><?= e($e['email']) ?></td>
              <td><?= status_pill($e['status'], ['offen' => 'wartet', 'ok' => 'zugestellt', 'fehler' => 'Fehler', 'uebersprungen' => 'übersprungen']) ?></td><td class="muted klein nowrap"><?= $e['zeit'] ? datum($e['zeit'], true) : '' ?></td></tr>
          <?php endforeach; ?>
        </tbody></table></div>
        <?php if (count($kamp['empfaenger']) > 500): ?><p class="muted klein" style="margin-top:10px">… und <?= count($kamp['empfaenger']) - 500 ?> weitere</p><?php endif; ?>
      </section>
    </div>
    <section class="vorschau"><div class="vorschau__kopf"><span>So sah der Newsletter aus</span></div>
      <iframe title="Vorschau" srcdoc="<?= e(nl_html($kamp, ['vorname' => 'Max', 'nachname' => 'Mustermann', 'newsletter' => ['token' => 'x']], '#')) ?>" sandbox></iframe></section>
  </div>
  <?php fuss(['versand.js']); exit;
}

/* ---------- Editor ---------- */
$gruppenZahl = [];
$erlaubt = array_filter($kontakte, 'nl_erlaubt');
foreach ($erlaubt as $k) foreach ($k['gruppen'] as $g) $gruppenZahl[$g] = ($gruppenZahl[$g] ?? 0) + 1;
$alleGruppen = array_values(array_unique(array_merge($einst['gruppen'], array_keys($gruppenZahl))));
$gruppenListe = array_values(array_map(fn($k) => $k['gruppen'], $erlaubt));   // für die Live-Zählung, ohne persönliche Daten
kopf($kamp['betreff'] ?: 'Neuer Newsletter', 'newsletter', ['body' => 'editor-seite']);
seitenkopf('Newsletter · Entwurf', e($kamp['betreff'] ?: 'Neuer Newsletter'),
  '<form method="post" action="kampagne.php?id=' . e($id) . '&aktion=loeschen" data-bestaetigen="Entwurf löschen?">' . csrf_feld() . '<button class="btn btn--hell">' . icon('trash') . ' Löschen</button></form>');
?>
<form method="post" action="kampagne.php?id=<?= e($id) ?>&aktion=speichern" class="editor" data-editor data-id="<?= e($id) ?>"
  data-web="<?= e(website_url()) ?>" data-bilder="<?= e(json_encode(website_bilder())) ?>" data-medien="<?= e(json_encode(medien_liste())) ?>" data-gruppenliste="<?= e(json_encode($gruppenListe, JSON_UNESCAPED_UNICODE)) ?>">
  <?= csrf_feld() ?>
  <input type="hidden" name="bloecke" value="<?= e(json_encode($kamp['bloecke'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>" data-bloecke>
  <div class="stack">
    <section class="card form">
      <label class="feld">Betreff<input name="betreff" value="<?= e($kamp['betreff']) ?>" placeholder="z. B. Herbst bei DAVINCI: zwei neue Häuser" maxlength="200" required></label>
      <label class="feld">Vorschautext (erscheint im Postfach neben dem Betreff)<input name="preheader" value="<?= e($kamp['preheader']) ?>" maxlength="200" placeholder="z. B. Und eine Einladung zum Tag der offenen Tür"></label>
      <p class="muted klein">Platzhalter in Betreff und Text: <code>{name}</code> (Vor- und Nachname), <code>{vorname}</code>, <code>{nachname}</code>.</p>
    </section>
    <section class="card">
      <div class="card__head"><h2>Inhalt</h2><span class="muted klein" data-gespeichert></span></div>
      <div data-blockliste></div>
      <div class="bloecke-neu">
        <button type="button" class="btn btn--hell btn--klein" data-neu="titel"><?= icon('plus') ?> Überschrift</button>
        <button type="button" class="btn btn--hell btn--klein" data-neu="text"><?= icon('plus') ?> Text</button>
        <button type="button" class="btn btn--hell btn--klein" data-neu="bild"><?= icon('plus') ?> Bild</button>
        <button type="button" class="btn btn--hell btn--klein" data-neu="knopf"><?= icon('plus') ?> Knopf</button>
        <button type="button" class="btn btn--hell btn--klein" data-neu="kasten"><?= icon('plus') ?> Kasten</button>
        <button type="button" class="btn btn--hell btn--klein" data-neu="trenner"><?= icon('plus') ?> Linie</button>
      </div>
    </section>
    <section class="card form">
      <h2>Empfänger</h2>
      <div class="checks"><?php foreach ($alleGruppen as $g): ?><label class="chip"><input type="checkbox" name="gruppen[]" value="<?= e($g) ?>"<?= in_array($g, $kamp['gruppen'], true) ? ' checked' : '' ?>><span><?= e($g) ?> · <?= $gruppenZahl[$g] ?? 0 ?></span></label><?php endforeach; ?></div>
      <p class="hinweis"><b data-anzahl><?= count(empfaenger_waehlen($kontakte, $kamp['gruppen'])) ?></b> Empfänger. <span data-anzahl-text><?= $kamp['gruppen'] ? 'Kontakte in mindestens einer der gewählten Gruppen' : 'Keine Gruppe gewählt: alle Kontakte' ?></span> mit Einwilligung oder als Bestandskunde. Abgemeldete werden nie angeschrieben.</p>
    </section>
    <section class="card form">
      <h2>Testen</h2>
      <div class="knopfreihe"><input type="email" name="an" value="<?= e($ich['email']) ?>" placeholder="ihre@adresse.de" style="flex:1;min-width:220px" data-test-an><button type="button" class="btn btn--hell" data-test>Test senden</button></div>
      <p class="muted klein" data-test-meldung><?= $kamp['tests'] ? 'Letzter Test ' . relativ(end($kamp['tests'])['zeit']) . ' an ' . e(implode(', ', end($kamp['tests'])['an'])) : 'Schicken Sie sich den Newsletter vor dem Versand selbst, um ihn im echten Postfach zu prüfen.' ?></p>
    </section>
    <section class="card form">
      <h2>Versenden</h2>
      <div class="checks">
        <label class="chip"><input type="radio" name="wann" value="jetzt" checked data-wann><span>Jetzt senden</span></label>
        <label class="chip"><input type="radio" name="wann" value="spaeter" data-wann><span><?= icon('clock') ?> Später senden</span></label>
      </div>
      <label class="feld" data-zeitpunkt hidden>Versenden am<input type="datetime-local" name="zeitpunkt" value="<?= e(date('Y-m-d\TH:i', strtotime('tomorrow 09:00'))) ?>" min="<?= e(date('Y-m-d\TH:i')) ?>"></label>
      <label class="check"><input type="checkbox" name="bestaetigt" value="1"><span>Ich habe den Test geprüft. Der Newsletter geht an <b data-anzahl><?= count(empfaenger_waehlen($kontakte, $kamp['gruppen'])) ?></b> Empfänger.</span></label>
      <p class="muted klein" data-zeitpunkt hidden>Die Empfänger werden zum Versandzeitpunkt aus den gewählten Gruppen zusammengestellt. Wer sich bis dahin abmeldet, bekommt nichts. Für den pünktlichen Start muss der Cron-Job eingerichtet sein.</p>
      <div class="knopfreihe"><button class="btn" type="submit">Entwurf speichern</button><button class="btn btn--akzent" type="submit" formaction="kampagne.php?id=<?= e($id) ?>&aktion=starten" data-starten><?= icon('mail') ?> <span data-starten-text>Jetzt versenden</span></button></div>
    </section>
  </div>
  <section class="vorschau" data-vorschau>
    <div class="vorschau__kopf"><span>Vorschau mit Beispielname</span><div class="segment"><a href="#" class="is-active" data-breite="desktop">Desktop</a><a href="#" data-breite="mobil">Handy</a></div></div>
    <iframe title="Vorschau" sandbox="allow-same-origin" data-frame></iframe>
  </section>
</form>
<input type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden data-upload>
<?php fuss(['editor.js']);
