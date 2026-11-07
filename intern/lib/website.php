<?php
/* =========================================================
   DAVINCI intern – Abgleich mit der Website davinci-homes.de
   Die Website legt Anfragen und Newsletter-Anmeldungen in ihrem Ordner data/ ab.
   Hier werden sie übernommen: Anfragen ins Archiv, Absender als Kontakte,
   bestätigte Newsletter-Anmeldungen als Einwilligung.
   ========================================================= */
declare(strict_types=1);

/** Ordner data/ der Website suchen (einstellbar in config.php: website_daten). */
function website_ordner(): ?string {
  $kandidaten = array_filter([
    cfg('website_daten'),
    APP . '/../data',                                         // intern liegt im Website-Ordner (lokal / Test)
    APP . '/../../davinci-homes.de/public_html/data',         // SiteGround: www/intern.davinci-homes.de/public_html
    APP . '/../../../davinci-homes.de/public_html/data',
  ]);
  foreach ($kandidaten as $k) if (is_dir($k)) return realpath($k) ?: $k;
  return null;
}
function website_lesen(string $n): array {
  $o = website_ordner(); if (!$o) return [];
  $f = "$o/$n.php"; if (!is_file($f)) return [];
  $t = (string) file_get_contents($f);
  if (strpos($t, SPERRZEILE) === 0) $t = substr($t, strlen(SPERRZEILE));
  return json_decode($t, true) ?: [];
}

/** Abgleich, höchstens einmal pro Minute (außer $sofort). Gibt die Zahl neuer Anfragen zurück. */
function website_abgleich(bool $sofort = false): int {
  $stand = lesen('abgleich');
  if (!$sofort && ($stand['zeit'] ?? 0) > time() - 60) return 0;
  if (!website_ordner()) { schreiben('abgleich', ['zeit' => time(), 'fehler' => 'Ordner der Website nicht gefunden']); return 0; }

  $webAnfragen = website_lesen('anfragen');
  // Ältere Grundriss-Anfragen (vor der Anfragen-Ablage) mit übernehmen
  foreach (website_lesen('leads') as $l) {
    $webAnfragen[] = ['id' => 'gr-' . substr(sha1(($l['zeit'] ?? '') . ($l['email'] ?? '')), 0, 16), 'zeit' => $l['zeit'] ?? date('c'), 'typ' => 'Grundriss',
                      'felder' => array_filter(['name' => $l['name'] ?? '', 'email' => $l['email'] ?? '', 'haus' => $l['haus'] ?? '', 'pdf' => !empty($l['pdf']) ? 'verschickt' : 'fehlt']), 'quelle' => []];
  }
  $nl = website_lesen('newsletter');
  $mailStat = website_lesen('mail_statistik');

  $neu = 0;
  aendern('kontakte', function (&$kontakte) use ($webAnfragen, $nl, $mailStat, &$neu) {
    aendern('anfragen', function (&$archiv) use (&$kontakte, $webAnfragen, $mailStat, &$neu) {
      // Grundriss-Anfragen stehen auf der Website zweimal (Anfragen-Ablage und alte Lead-Liste): Doppelte entfernen
      $doppelt = function (array $w, array $liste): bool {
        if (($w['typ'] ?? '') !== 'Grundriss') return false;
        $k = strtolower((string) ($w['felder']['email'] ?? '')) . '|' . ($w['felder']['haus'] ?? '');
        $t = strtotime((string) ($w['zeit'] ?? '')) ?: 0;
        foreach ($liste as $x) {
          if (($x['id'] ?? '') === ($w['id'] ?? '') || strpos((string) ($x['id'] ?? ''), 'gr-') === 0 || ($x['typ'] ?? '') !== 'Grundriss') continue;
          if (strtolower((string) ($x['felder']['email'] ?? '')) . '|' . ($x['felder']['haus'] ?? '') === $k
              && abs((strtotime((string) ($x['zeit'] ?? '')) ?: 0) - $t) <= 600) return true;
        }
        return false;
      };
      $unberuehrt = fn($e) => ($e['status'] ?? 'neu') === 'neu' && empty($e['notiz']) && empty($e['lead_id']) && empty($e['bearbeiter']);
      foreach ($archiv as $id => $e)
        if (strpos((string) $id, 'gr-') === 0 && $unberuehrt($e) && $doppelt($e, array_merge($archiv, $webAnfragen))) unset($archiv[$id]);

      $weg = lesen('anfragen_geloescht');   // in intern gelöschte Anfragen nicht wieder übernehmen
      foreach ($webAnfragen as $w) {
        if (!isset($w['id']) || isset($archiv[$w['id']]) || isset($weg[$w['id']]) || $doppelt($w, $weg)) continue;
        if (strpos((string) $w['id'], 'gr-') === 0 && $doppelt($w, array_merge($archiv, $webAnfragen))) continue;
        $f = $w['felder'] ?? [];
        [$vn, $nn] = isset($f['vorname']) && $f['vorname'] !== '' ? [$f['vorname'], $f['nachname'] ?? ''] : name_teilen((string) ($f['name'] ?? ''));
        $kid = null;
        if (!empty($f['email'])) {
          $adr = [];
          if (!empty($f['adresse']) && preg_match('/^(.*?),\s*(\d{4,5})\s+(.+)$/', $f['adresse'], $m)) $adr = ['strasse' => $m[1], 'plz' => $m[2], 'ort' => $m[3]];
          $kid = kontakt_zusammenfuehren($kontakte, ['vorname' => $vn, 'nachname' => $nn, 'email' => $f['email'], 'telefon' => $f['telefon'] ?? '', 'quelle' => 'Website · ' . $w['typ']] + $adr,
            'Website: ' . $w['typ'] . (!empty($f['haus']) ? ' · ' . $f['haus'] : ''), ['Interessent']);
        }
        $archiv[$w['id']] = $w + ['status' => 'neu', 'kontakt_id' => $kid, 'lead_id' => null, 'notiz' => '', 'bearbeiter' => null, 'importiert' => date('c')];
        $neu++;
      }
      // Öffnungen und Klicks der Grundriss-E-Mail übernehmen
      foreach ($mailStat as $mid => $m) if (isset($archiv[$mid]) && is_array($m)) $archiv[$mid]['mail'] = $m;
    });
    // Newsletter-Anmeldungen der Website
    foreach ($nl as $tok => $a) {
      if (empty($a['email'])) continue;
      [$vn, $nn] = name_teilen((string) ($a['name'] ?? ''));
      $kid = kontakt_zusammenfuehren($kontakte, ['vorname' => $vn, 'nachname' => $nn, 'email' => $a['email'], 'quelle' => 'Website · ' . ($a['quelle'] ?? 'Newsletter')], '', ['Interessent']);
      $k = &$kontakte[$kid];
      $st = $k['newsletter']['status'] ?? 'kein';
      $seit = strtotime((string) ($k['newsletter']['seit'] ?? '')) ?: 0;
      if (!empty($a['abgemeldet'])) {
        if ($st !== 'abgemeldet' && strtotime($a['abgemeldet']) > $seit) nl_setzen($k, 'abgemeldet', 'Abmeldung auf der Website', $a['abgemeldet']);
      } elseif (!empty($a['bestaetigt'])) {
        // Bestätigung übernehmen, außer der Kontakt hat sich danach hier abgemeldet
        if (!in_array($st, ['bestaetigt', 'bestandskunde'], true) && !($st === 'abgemeldet' && $seit > strtotime($a['bestaetigt'])))
          nl_setzen($k, 'bestaetigt', 'Double-Opt-in über die Website (' . ($a['quelle'] ?? '') . '): angemeldet ' . datum($a['angefordert'], true) . ', bestätigt ' . datum($a['bestaetigt'], true) . ', IP ' . ($a['bestaetigt_ip'] ?? '–') . '. Text: „' . ($a['text'] ?? '') . '“', $a['bestaetigt']);
      } elseif ($st === 'kein') {
        nl_setzen($k, 'angefragt', 'Anmeldung über die Website (' . ($a['quelle'] ?? '') . '), Bestätigung steht aus', $a['angefordert'] ?? null);
      }
      unset($k);
    }
  });
  website_mails_uebernehmen();
  schreiben('abgleich', ['zeit' => time(), 'neu' => $neu, 'ordner' => website_ordner()]);
  return $neu;
}

/* ---------- E-Mails der Website an Kunden (Kopien aus data/mails) ---------- */
const WEB_MAILS = DATEN . '/mails_web';

function website_mails_uebernehmen(): void {
  $o = website_ordner(); if (!$o || !is_dir("$o/mails")) return;
  $neu = [];
  foreach (glob("$o/mails/*.php") ?: [] as $f) {
    $id = basename($f, '.php');
    if (!preg_match('/^[0-9a-f-]+$/', $id) || is_file(WEB_MAILS . "/$id.php")) continue;
    $t = (string) file_get_contents($f);
    $m = json_decode(strpos($t, SPERRZEILE) === 0 ? substr($t, strlen(SPERRZEILE)) : $t, true);
    if (!is_array($m) || empty($m['an'])) continue;
    if (!is_dir(WEB_MAILS)) @mkdir(WEB_MAILS, 0750, true);
    @file_put_contents(WEB_MAILS . "/$id.php", SPERRZEILE . json_encode($m, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $neu[$id] = ['id' => $id, 'zeit' => $m['zeit'], 'an' => strtolower($m['an']), 'betreff' => $m['betreff'], 'anfrage' => $m['anfrage'] ?? null];
  }
  if ($neu) aendern('mail_index', function (&$ix) use ($neu) { $ix = $neu + $ix; });
}
function web_mail(string $id): ?array {
  if (!preg_match('/^[0-9a-f-]+$/', $id)) return null;
  $f = WEB_MAILS . "/$id.php"; if (!is_file($f)) return null;
  $t = (string) file_get_contents($f);
  return json_decode(strpos($t, SPERRZEILE) === 0 ? substr($t, strlen(SPERRZEILE)) : $t, true) ?: null;
}
/** Alle E-Mails der Website an diese Adresse (neueste zuerst). */
function web_mails_fuer(string $email): array {
  $email = strtolower(trim($email)); if ($email === '') return [];
  $l = array_filter(lesen('mail_index'), fn($m) => ($m['an'] ?? '') === $email);
  uasort($l, fn($a, $b) => strcmp($b['zeit'], $a['zeit']));
  return $l;
}
/** Beim Löschen eines Kontakts (DSGVO) auch die Kopien entfernen. */
function web_mails_loeschen(string $email): void {
  $email = strtolower(trim($email)); if ($email === '') return;
  aendern('mail_index', function (&$ix) use ($email) {
    foreach ($ix as $id => $m) if (($m['an'] ?? '') === $email) { @unlink(WEB_MAILS . "/$id.php"); unset($ix[$id]); }
  });
  $o = website_ordner();
  if ($o) foreach (glob("$o/mails/*.php") ?: [] as $f) { $t = (string) @file_get_contents($f); if (stripos($t, '"an":"' . $email . '"') !== false) @unlink($f); }
}

/* ---------- Anfragen endgültig löschen ---------- */
/** Datei der Website ändern (mit Sperre). Gelingt das nicht (Rechte), bleibt es bei der Löschliste in intern. */
function website_aendern(string $n, callable $f): void {
  $o = website_ordner(); if (!$o || !is_file("$o/$n.php") || !is_writable("$o/$n.php")) return;
  $h = @fopen("$o/$n.php", 'c+'); if (!$h) return;
  if (flock($h, LOCK_EX)) {
    $t = (string) stream_get_contents($h);
    $d = json_decode(strpos($t, SPERRZEILE) === 0 ? substr($t, strlen(SPERRZEILE)) : $t, true) ?: [];
    $f($d);
    ftruncate($h, 0); rewind($h);
    fwrite($h, SPERRZEILE . json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($h); flock($h, LOCK_UN);
  }
  fclose($h);
}
/** Löscht Anfragen in intern und auf der Website (Ablage, alte Grundriss-Liste, E-Mail-Kopien, Messung)
    und merkt sie sich, damit der Abgleich sie nicht zurückholt. Gibt die Zahl gelöschter Anfragen zurück. */
function anfragen_loeschen(array $ids): int {
  $alle = anfragen();
  $weg = [];
  foreach ($ids as $id) if (isset($alle[$id])) {
    $a = $alle[$id];
    $weg[$id] = ['id' => $id, 'zeit' => $a['zeit'], 'typ' => $a['typ'], 'felder' => array_intersect_key($a['felder'] ?? [], ['email' => 1, 'haus' => 1]), 'geloescht' => date('c')];
  }
  if (!$weg) return 0;
  aendern('anfragen_geloescht', function (&$g) use ($weg) { $g = $weg + $g; });
  aendern('anfragen', function (&$a) use ($weg) { foreach ($weg as $id => $_) unset($a[$id]); });

  // Website: Ablage und alte Grundriss-Liste
  website_aendern('anfragen', function (&$l) use ($weg) { $l = array_values(array_filter($l, fn($x) => !isset($weg[$x['id'] ?? '']))); });
  website_aendern('leads', function (&$l) use ($weg) {
    $l = array_values(array_filter($l, function ($x) use ($weg) {
      if (isset($weg['gr-' . substr(sha1(($x['zeit'] ?? '') . ($x['email'] ?? '')), 0, 16)])) return false;
      foreach ($weg as $w) if (($w['typ'] ?? '') === 'Grundriss' && strtolower((string) ($w['felder']['email'] ?? '')) === strtolower((string) ($x['email'] ?? ''))
        && abs((strtotime((string) $w['zeit']) ?: 0) - (strtotime((string) ($x['zeit'] ?? '')) ?: 0)) <= 600) return false;
      return true;
    }));
  });
  website_aendern('mail_statistik', function (&$m) use ($weg) { foreach ($weg as $id => $_) unset($m[$id]); });

  // Kopien der E-Mails zu diesen Anfragen
  aendern('mail_index', function (&$ix) use ($weg) {
    foreach ($ix as $mid => $m) if (isset($weg[$m['anfrage'] ?? ''])) { @unlink(WEB_MAILS . "/$mid.php"); unset($ix[$mid]); }
  });
  $o = website_ordner();
  if ($o) foreach (glob("$o/mails/*.php") ?: [] as $f) {
    $t = (string) @file_get_contents($f);
    if (preg_match('/"anfrage":"([0-9a-f]+)"/', $t, $m) && isset($weg[$m[1]])) @unlink($f);
  }
  return count($weg);
}
