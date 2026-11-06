<?php
/* DAVINCI – Öffnungen und Klicks automatischer E-Mails (Grundriss) messen.
   Die E-Mail enthält ein unsichtbares Bild und Links über /mail.php. Gespeichert wird je Anfrage nur,
   ob und wann geöffnet bzw. geklickt wurde (data/mail_statistik). intern.davinci-homes.de liest das beim Abgleich. */
require_once __DIR__ . '/store.php';

const SPUR_ZIELE = ['pdf', 'termin'];

function spur_geheim(): string {
  static $g = null;
  if ($g === null) $g = (string) st_aendern('geheim', function (&$d) { if (empty($d['spur'])) $d['spur'] = st_token(); return $d['spur']; });
  return $g;
}
function spur_sig(string $aid, string $e, string $z = ''): string { return substr(hash_hmac('sha256', "$aid|$e|$z", spur_geheim()), 0, 20); }

/** Adressen für eine Anfrage: unsichtbares Bild und weitergeleitete Links. $haus = Schlüssel des Grundrisses. */
function spur_links(string $aid, string $haus = ''): array {
  $u = fn(string $e, string $z = '') => DV_SEITE . '/mail.php?' . http_build_query(array_filter(['a' => $aid, 'e' => $e, 'z' => $z, 'h' => $haus, 's' => spur_sig($aid, $e, $z . $haus)]));
  return ['bild' => $u('o'), 'pdf' => $u('k', 'pdf'), 'termin' => $u('k', 'termin')];
}
function spur_bild(string $url): string { return '<img src="' . htmlspecialchars($url) . '" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;opacity:0;">'; }

/** Ereignis speichern: 'o' (geöffnet) oder 'k' (geklickt, mit Ziel). */
function spur_ereignis(string $aid, string $e, string $z = ''): void {
  st_aendern('mail_statistik', function (&$s) use ($aid, $e, $z) {
    $grenze = time() - 400 * 86400;
    foreach ($s as $k => $v) if (strtotime($v['zuletzt'] ?? 'now') < $grenze) unset($s[$k]);
    $x = $s[$aid] ?? [];
    $jetzt = date('c');
    if ($e === 'o') { $x['geoeffnet'] = $x['geoeffnet'] ?? $jetzt; $x['oeffnungen'] = ($x['oeffnungen'] ?? 0) + 1; }
    else {
      $x['geklickt'] = $x['geklickt'] ?? $jetzt; $x['klicks'] = ($x['klicks'] ?? 0) + 1;
      $x['ziele'][$z] = $x['ziele'][$z] ?? $jetzt;
      $x['geoeffnet'] = $x['geoeffnet'] ?? $jetzt;   // wer klickt, hat geöffnet (auch bei blockierten Bildern)
    }
    $x['zuletzt'] = $jetzt;
    $s[$aid] = $x;
  });
}
