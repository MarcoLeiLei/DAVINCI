<?php
/* =========================================================
   DAVINCI intern – Newsletter: Aufbau, Darstellung, Versand
   Versand über das Postfach bei SiteGround (PHP mail), in Paketen mit Stundenlimit.
   ========================================================= */
declare(strict_types=1);

const KAMPAGNE_STATUS = ['entwurf' => 'Entwurf', 'geplant' => 'Geplant', 'laeuft' => 'Versand läuft', 'pausiert' => 'Pausiert', 'fertig' => 'Versendet'];
const NL_F = "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;";
const FIRMA = 'Fivemeters UG (haftungsbeschränkt)';
const ADRESSE = 'Kaiser-Friedrich-Ring 43 · 40545 Düsseldorf';
const TELEFON = '+49 157 868 328 59';

function website_url(): string { return rtrim((string) cfg('website_url', 'https://davinci-homes.de'), '/'); }

function leere_kampagne(): array {
  return ['id' => neue_id(), 'betreff' => '', 'preheader' => '', 'gruppen' => [], 'status' => 'entwurf',
    'bloecke' => [
      ['typ' => 'bild', 'src' => 'web:echt-luft-landschaft.jpg', 'alt' => 'DAVINCI Haus aus Holz und Glas', 'link' => ''],
      ['typ' => 'titel', 'eyebrow' => 'Neuigkeiten', 'text' => 'Designed to stay.'],
      ['typ' => 'text', 'text' => "Guten Tag {name},\n\nhier steht Ihr Text. Absätze trennen Sie mit einer leeren Zeile, **fett** mit zwei Sternchen und Links so: [davinci-homes.de](https://davinci-homes.de)."],
      ['typ' => 'knopf', 'text' => 'Termin vereinbaren', 'url' => 'https://davinci-homes.de/#termin', 'stil' => 'dunkel'],
      ['typ' => 'text', 'text' => "Herzliche Grüße\nIhr DAVINCI Team"],
    ],
    'empfaenger' => [], 'abmeldungen' => 0, 'erstellt' => date('c'), 'geaendert' => date('c'), 'von' => ich()['id'] ?? null,
    'gestartet' => null, 'fertig' => null, 'tests' => []];
}

function kampagne_zahlen(array $k): array {
  $z = ['gesamt' => count($k['empfaenger'] ?? []), 'ok' => 0, 'fehler' => 0, 'offen' => 0, 'uebersprungen' => 0, 'abgemeldet' => (int) ($k['abmeldungen'] ?? 0)];
  foreach ($k['empfaenger'] ?? [] as $e) $z[$e['status']] = ($z[$e['status']] ?? 0) + 1;
  return $z;
}

/** Kontakte, die einen Newsletter an diese Gruppen bekommen dürfen (keine Gruppe = alle). */
function empfaenger_waehlen(array $kontakte, array $gruppen): array {
  $o = []; $mails = [];
  foreach ($kontakte as $k) {
    if (!nl_erlaubt($k)) continue;
    if ($gruppen && !array_intersect($gruppen, $k['gruppen'])) continue;
    $m = strtolower($k['email']); if (isset($mails[$m]) || gesperrt($m)) continue;
    $mails[$m] = 1; $o[$k['id']] = $k;
  }
  return $o;
}

/** Bilder der Website zur Auswahl. */
function website_bilder(): array {
  $o = website_ordner();
  $dir = $o ? dirname($o) . '/assets/img' : null;
  $liste = [];
  if ($dir && is_dir($dir)) foreach (glob($dir . '/*.jpg') ?: [] as $f) $liste[] = basename($f);
  if (!$liste) $liste = ['echt-luft-landschaft.jpg', 'echt-ansicht-strasse.jpg', 'echt-fassade-herbst.jpg', 'dv-01-flachdach-weiss-abend.jpg', 'dv-03-giebel-winter-tag.jpg'];
  $liste = array_values(array_filter($liste, fn($b) => !preg_match('/^(email-|dv-drohne-poster)/', $b)));
  usort($liste, fn($a, $b) => [str_starts_with($a, 'dv-'), $a] <=> [str_starts_with($b, 'dv-'), $b]);   // echte Fotos zuerst
  return $liste;
}
function medien_liste(): array {
  $l = [];
  foreach (glob(APP . '/medien/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [] as $f) $l[basename($f)] = filemtime($f);
  arsort($l);
  return array_keys($l);
}
function bild_url(string $src): string {
  if (str_starts_with($src, 'web:')) return website_url() . '/assets/img/' . rawurlencode(substr($src, 4));
  if (str_starts_with($src, 'medien:')) return basis_url() . '/medien/' . rawurlencode(substr($src, 7));
  return preg_match('#^https://#', $src) ? $src : '';
}
function ist_ki_bild(string $src): bool { return (bool) preg_match('#(^web:|/)dv-#', $src); }

/* ---------- Darstellung ---------- */
function nl_ersetzen(string $t, ?array $k): string {
  $vn = $k ? trim($k['vorname']) : 'Max'; $nn = $k ? trim($k['nachname']) : 'Mustermann';
  $name = trim("$vn $nn") ?: 'Damen und Herren';
  return strtr($t, ['{vorname}' => $vn ?: $name, '{nachname}' => $nn, '{name}' => $name]);
}
/** Einfache Auszeichnung: **fett**, [Text](https://…), Absätze, Listen mit „- “. */
function nl_text(string $t, ?array $k): string {
  $t = nl_ersetzen(str_replace("\r", '', $t), $k);
  $o = '';
  foreach (preg_split("/\n\s*\n/", trim($t)) as $abs) {
    $zeilen = explode("\n", $abs);
    $inline = function (string $s): string {
      $s = e($s);
      $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong style="font-weight:600;color:#1d1d1f;">$1</strong>', $s);
      return preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+|mailto:[^\s)]+)\)/', fn($m) => '<a href="' . $m[2] . '" style="color:#406973;text-decoration:underline;">' . $m[1] . '</a>', $s);
    };
    if (count(array_filter($zeilen, fn($z) => preg_match('/^\s*[-•]\s+/', $z))) === count($zeilen)) {
      $o .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">';
      foreach ($zeilen as $z) $o .= '<tr><td style="width:26px;vertical-align:top;padding:6px 0;"><div style="width:14px;height:1px;background:#406973;margin-top:12px;"></div></td><td style="padding:6px 0;' . NL_F . 'font-size:16px;line-height:1.55;color:#3a3a3c;">' . $inline(preg_replace('/^\s*[-•]\s+/', '', $z)) . '</td></tr>';
      $o .= '</table>';
    } else {
      $o .= '<p style="margin:0 0 18px;' . NL_F . 'font-size:16px;line-height:1.65;color:#3a3a3c;">' . implode('<br>', array_map($inline, $zeilen)) . '</p>';
    }
  }
  return $o;
}
function nl_block(array $b, ?array $k): string {
  $pad = fn($html, $p = '0 40px') => '<tr><td style="padding:' . $p . ';">' . $html . '</td></tr>';
  switch ($b['typ'] ?? '') {
    case 'titel':
      return $pad(($b['eyebrow'] ?? '') !== '' ? '<div style="' . NL_F . 'font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#406973;margin:0 0 12px;">' . e(nl_ersetzen($b['eyebrow'], $k)) . '</div>' : '', '36px 40px 0')
        . $pad('<h1 style="margin:0 0 20px;' . NL_F . 'font-size:32px;line-height:1.15;font-weight:300;letter-spacing:-.02em;color:#1d1d1f;">' . nl2br(e(nl_ersetzen($b['text'] ?? '', $k))) . '</h1>');
    case 'text':
      return $pad(nl_text($b['text'] ?? '', $k), '8px 40px 0');
    case 'bild':
      $u = bild_url($b['src'] ?? ''); if (!$u) return '';
      $img = '<img src="' . e($u) . '" width="600" alt="' . e($b['alt'] ?? '') . '" style="display:block;width:100%;height:auto;border:0;">';
      if (!empty($b['link'])) $img = '<a href="' . e($b['link']) . '">' . $img . '</a>';
      $ki = ist_ki_bild($b['src']) ? '<tr><td style="padding:6px 40px 0;' . NL_F . 'font-size:11px;color:#8a8a8f;text-align:right;">KI-Visualisierung</td></tr>' : '';
      return '<tr><td style="padding:8px 0 0;">' . $img . '</td></tr>' . $ki . '<tr><td style="height:16px;"></td></tr>';
    case 'knopf':
      $d = ($b['stil'] ?? 'dunkel') !== 'hell';
      return $pad('<table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 26px;"><tr><td style="background:' . ($d ? '#0b0b0b' : '#ffffff') . ';border:1px solid #0b0b0b;border-radius:999px;">'
        . '<a href="' . e($b['url'] ?? '#') . '" style="display:inline-block;padding:15px 30px;' . NL_F . 'font-size:15px;color:' . ($d ? '#ffffff' : '#0b0b0b') . ';text-decoration:none;">' . e($b['text'] ?? '') . '</a></td></tr></table>');
    case 'trenner':
      return $pad('<div style="height:1px;background:#e6e5e1;margin:10px 0 28px;"></div>');
    case 'kasten':
      return $pad('<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f0ee;margin:8px 0 26px;"><tr><td style="padding:28px 30px;">'
        . (($b['titel'] ?? '') !== '' ? '<div style="' . NL_F . 'font-size:21px;font-weight:300;color:#1d1d1f;margin-bottom:10px;">' . e(nl_ersetzen($b['titel'], $k)) . '</div>' : '')
        . str_replace('margin:0 0 18px;', 'margin:0 0 12px;', nl_text($b['text'] ?? '', $k)) . '</td></tr></table>');
  }
  return '';
}
function nl_html(array $kamp, ?array $k, string $abmelden): string {
  $inhalt = '';
  foreach ($kamp['bloecke'] as $b) $inhalt .= nl_block($b, $k);
  $web = website_url();
  return '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>' . e($kamp['betreff']) . '</title></head>
<body style="margin:0;padding:0;background:#f0f0ee;">
<div style="display:none;max-height:0;overflow:hidden;">' . e(nl_ersetzen($kamp['preheader'] ?? '', $k)) . '</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f0ee;"><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;">
  <tr><td style="background:#0b0b0b;padding:28px 40px;"><a href="' . $web . '"><img src="' . $web . '/assets/img/email-logo.png" width="170" alt="DAVINCI" style="display:block;border:0;width:170px;height:auto;"></a></td></tr>
  ' . $inhalt . '
  <tr><td style="height:24px;"></td></tr>
  <tr><td style="background:#0b0b0b;padding:34px 40px;' . NL_F . 'color:#9a9a9f;font-size:13px;line-height:1.7;">
    <div style="color:#ffffff;font-size:20px;font-weight:300;letter-spacing:-.01em;margin-bottom:12px;">Designed to stay.</div>
    ' . e(FIRMA) . ' · ' . e(ADRESSE) . '<br>' . e(TELEFON) . ' · <a href="mailto:bauen@davinci-homes.de" style="color:#9dbcc4;text-decoration:none;">bauen@davinci-homes.de</a><br>
    <a href="' . $web . '" style="color:#9dbcc4;text-decoration:none;">davinci-homes.de</a> · <a href="' . $web . '/impressum/" style="color:#9a9a9f;">Impressum</a> · <a href="' . $web . '/datenschutz/" style="color:#9a9a9f;">Datenschutz</a><br><br>
    <span style="color:#6e6e73;">Sie erhalten diesen Newsletter, weil Sie ihn bei DAVINCI bestellt haben oder Kunde von DAVINCI sind. <a href="' . e($abmelden) . '" style="color:#9a9a9f;">Newsletter abbestellen</a></span>
  </td></tr>
</table>
</td></tr></table></body></html>';
}
function abmelde_url(array $k, string $kampagne = ''): string {
  return basis_url() . '/newsletter-abmelden.php?t=' . ($k['newsletter']['token'] ?? '') . ($kampagne ? '&k=' . $kampagne : '');
}

/* ---------- Versand ---------- */
function nl_mail(string $an, string $betreff, string $html, string $abmelden = ''): bool {
  $e = einstellungen();
  return mail_roh($an, $betreff, $html, ['von' => $e['absender'], 'von_name' => $e['absender_name'], 'antwort' => $e['antwort'], 'abmelden' => $abmelden]);
}

/** HTML in lesbaren Klartext umwandeln (zweiter Teil jeder E-Mail). */
function klartext(string $html): string {
  $h2 = preg_replace('#<div style="display:none[^>]*>.*?</div>#s', '', $html);
  $h2 = preg_replace('#<a href="(?!mailto)([^"]+)"[^>]*>(.*?)</a>#s', '$2 ($1)', $h2);
  $plain = trim(html_entity_decode(strip_tags(preg_replace(['#<br\s*/?>#i', '#</(p|tr|h1|div)>#i', '#</td>#i', '#<title>.*?</title>#s', '#<style.*?</style>#s'], ["\n", "\n", '  ', '', ''], $h2)), ENT_QUOTES, 'UTF-8'));
  return preg_replace(["/[ \t]+\n/", "/\n[ \t]+/", "/\n{3,}/"], ["\n", "\n", "\n\n"], $plain);
}
function kopf_name(string $name, string $mail): string { return '=?UTF-8?B?' . base64_encode($name) . '?= <' . $mail . '>'; }

/**
 * E-Mail mit HTML- und Textteil senden, optional mit Anhängen.
 * $o: von, von_name, antwort, cc[], bcc[], anhaenge[[name, pfad, typ]], abmelden
 */
function mail_roh(string $an, string $betreff, string $html, array $o = []): bool {
  $von = $o['von'] ?? cfg('absender', 'bauen@davinci-homes.de');
  $kopf = 'From: ' . kopf_name($o['von_name'] ?? 'DAVINCI', $von) . "\r\n";
  if (!empty($o['antwort'])) $kopf .= 'Reply-To: ' . $o['antwort'] . "\r\n";
  if (!empty($o['cc'])) $kopf .= 'Cc: ' . implode(', ', $o['cc']) . "\r\n";
  if (!empty($o['bcc'])) $kopf .= 'Bcc: ' . implode(', ', $o['bcc']) . "\r\n";
  if (!empty($o['abmelden'])) $kopf .= 'List-Unsubscribe: <' . $o['abmelden'] . '>, <mailto:' . ($o['antwort'] ?? $von) . "?subject=Newsletter%20abbestellen>\r\nList-Unsubscribe-Post: List-Unsubscribe=One-Click\r\n";
  $alt = 'alt' . bin2hex(random_bytes(8));
  $teil = "--$alt\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . quoted_printable_encode(klartext($html)) . "\r\n"
        . "--$alt\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . quoted_printable_encode($html) . "\r\n--$alt--";
  $kopf .= "MIME-Version: 1.0\r\n";
  if (!empty($o['anhaenge'])) {
    $mix = 'mix' . bin2hex(random_bytes(8));
    $kopf .= "Content-Type: multipart/mixed; boundary=\"$mix\"\r\n";
    $body = "--$mix\r\nContent-Type: multipart/alternative; boundary=\"$alt\"\r\n\r\n$teil\r\n";
    foreach ($o['anhaenge'] as [$name, $pfad, $typ]) {
      $n = '=?UTF-8?B?' . base64_encode($name) . '?=';
      $body .= "--$mix\r\nContent-Type: " . ($typ ?: 'application/octet-stream') . "; name=\"$n\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"$n\"\r\n\r\n"
             . chunk_split(base64_encode((string) file_get_contents($pfad))) . "\r\n";
    }
    $body .= "--$mix--";
  } else {
    $kopf .= "Content-Type: multipart/alternative; boundary=\"$alt\"\r\n";
    $body = $teil;
  }
  // Testbetrieb: E-Mails in eine Datei schreiben statt zu senden
  if ($datei = getenv('DV_MAIL_DATEI')) { file_put_contents($datei, json_encode(['an' => $an, 'betreff' => $betreff, 'kopf' => $kopf, 'laenge' => strlen($body)]) . "\n", FILE_APPEND); return !str_contains($an, 'fehler@'); }
  return @mail($an, '=?UTF-8?B?' . base64_encode($betreff) . '?=', $body, $kopf, '-f' . $von);
}

/** Empfänger festhalten und Versand starten (sofort oder zum geplanten Zeitpunkt). */
function kampagne_starten(string $id): int {
  $empf = empfaenger_waehlen(kontakte(), lesen('kampagnen')[$id]['gruppen'] ?? []);
  aendern('kampagnen', function (&$a) use ($id, $empf) {
    $a[$id]['empfaenger'] = [];
    foreach ($empf as $cid => $c) $a[$id]['empfaenger'][$cid] = ['email' => $c['email'], 'status' => 'offen', 'zeit' => null];
    $a[$id]['status'] = $empf ? 'laeuft' : 'fertig';
    $a[$id]['gestartet'] = date('c');
    if (!$empf) $a[$id]['fertig'] = date('c');
  });
  return count($empf);
}
/** Fällige geplante Newsletter starten (bei jedem Seitenaufruf und im Cron-Job). */
function geplante_pruefen(): void {
  foreach (lesen('kampagnen') as $id => $k) {
    if (($k['status'] ?? '') !== 'geplant' || strtotime((string) ($k['geplant_fuer'] ?? '')) > time()) continue;
    $n = kampagne_starten($id);
    protokoll('Geplanter Newsletter gestartet: ' . $k['betreff'] . " ($n Empfänger)");
  }
}

/** Wie viele E-Mails dürfen in dieser Stunde noch raus? */
function stundenbudget(): int {
  $e = einstellungen();
  $grenze = time() - 3600;
  $n = count(array_filter(lesen('versandzeit'), fn($t) => $t > $grenze));
  return max(0, (int) $e['stundenlimit'] - $n);
}

/** Ein Paket einer laufenden Kampagne versenden. */
function versand_schritt(string $kid): array {
  $lock = fopen(DATEN . '/.lock-versandlauf', 'c');
  if (!flock($lock, LOCK_EX | LOCK_NB)) { fclose($lock); return ['laeuft_schon' => true]; }
  try {
    $kamp = lesen('kampagnen')[$kid] ?? null;
    if (!$kamp || $kamp['status'] !== 'laeuft') return ['status' => $kamp['status'] ?? 'fehlt'] + kampagne_zahlen($kamp ?? []);
    $budget = stundenbudget();
    if ($budget <= 0) return ['status' => 'laeuft', 'warten' => true] + kampagne_zahlen($kamp);
    $n = min((int) einstellungen()['paket'], $budget);
    $kontakte = kontakte();
    $ergebnis = []; $zeiten = []; $erhalten = [];
    foreach ($kamp['empfaenger'] as $cid => $emp) {
      if ($emp['status'] !== 'offen') continue;
      if ($n-- <= 0) break;
      $k = $kontakte[$cid] ?? null;
      if (!$k || !nl_erlaubt($k) || gesperrt($k['email'])) { $ergebnis[$cid] = 'uebersprungen'; continue; }
      $ab = abmelde_url($k, $kid);
      $ok = nl_mail($k['email'], nl_ersetzen($kamp['betreff'], $k), nl_html($kamp, $k, $ab), $ab);
      $ergebnis[$cid] = $ok ? 'ok' : 'fehler';
      $zeiten[] = time();
      if ($ok) $erhalten[] = $cid;
    }
    aendern('versandzeit', function (&$v) use ($zeiten) { $g = time() - 3600; $v = array_values(array_filter(array_merge($v, $zeiten), fn($t) => $t > $g)); });
    $kamp = aendern('kampagnen', function (&$alle) use ($kid, $ergebnis) {
      foreach ($ergebnis as $cid => $st) { $alle[$kid]['empfaenger'][$cid]['status'] = $st; $alle[$kid]['empfaenger'][$cid]['zeit'] = date('c'); }
      $offen = count(array_filter($alle[$kid]['empfaenger'], fn($e) => $e['status'] === 'offen'));
      if (!$offen && $alle[$kid]['status'] === 'laeuft') { $alle[$kid]['status'] = 'fertig'; $alle[$kid]['fertig'] = date('c'); }
      return $alle[$kid];
    });
    if ($erhalten) aendern('kontakte', function (&$k) use ($erhalten, $kamp) { foreach ($erhalten as $cid) if (isset($k[$cid])) verlauf($k[$cid], 'Newsletter erhalten: ' . $kamp['betreff']); });
    if ($kamp['status'] === 'fertig') protokoll('Newsletter versendet: ' . $kamp['betreff']);
    return ['status' => $kamp['status'], 'gesendet' => count($ergebnis)] + kampagne_zahlen($kamp);
  } finally { flock($lock, LOCK_UN); fclose($lock); }
}

/** Abmeldung auch in der Website eintragen, damit eine alte Anmeldung dort nicht wieder auflebt. */
function website_nl_abmelden(string $email): void {
  $o = website_ordner(); if (!$o || !is_file("$o/newsletter.php")) return;
  $lock = @fopen("$o/.lock-newsletter", 'c'); if ($lock) flock($lock, LOCK_EX);
  $t = (string) file_get_contents("$o/newsletter.php");
  $d = json_decode(strpos($t, SPERRZEILE) === 0 ? substr($t, strlen(SPERRZEILE)) : $t, true) ?: [];
  $geaendert = false;
  foreach ($d as &$x) if (strcasecmp($x['email'] ?? '', $email) === 0 && empty($x['abgemeldet'])) { $x['abgemeldet'] = date('c'); $geaendert = true; }
  unset($x);
  if ($geaendert) @file_put_contents("$o/newsletter.php", SPERRZEILE . json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
}
