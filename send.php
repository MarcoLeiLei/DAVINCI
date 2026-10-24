<?php
/* =========================================================
   DAVINCI – Formularversand
   Typen: Beratungsanfrage · Design-Book · Termin (HubSpot)
   Texte der E-Mails: lib/mail.php
   ========================================================= */
header('Content-Type: application/json; charset=utf-8');
// Diagnose: PHP-Fehler als kurzer Code zurückgeben statt leerer Seite
ini_set('display_errors', '0');
register_shutdown_function(function () {
  $e = error_get_last();
  if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
    if (!headers_sent()) { http_response_code(500); header('Content-Type: application/json; charset=utf-8'); }
    echo json_encode(['ok' => false, 'error' => 'php: ' . basename($e['file']) . ':' . $e['line'] . ' ' . mb_substr($e['message'], 0, 160)]);
  }
});
require __DIR__ . '/zugang.php';
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/store.php';
require __DIR__ . '/lib/hubspot.php';
require __DIR__ . '/lib/schutz.php';
if (SCHUTZ_AKTIV && (!isset($_COOKIE['dv_access']) || !hash_equals(ZUGANG, (string) $_COOKIE['dv_access']))) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }
$spam = spam_pruefen();
if ($spam) {
  dv_log("Spam abgewiesen ($spam) von " . substr(hash('sha256', $_SERVER['REMOTE_ADDR'] ?? ''), 0, 8));
  if (in_array($spam, ['honeypot', 'zu schnell'], true)) { echo json_encode(['ok' => true]); exit; }   // Bots nichts verraten
  http_response_code(429); echo json_encode(['ok' => false, 'error' => $spam]); exit;
}

function feld($k) { $v = $_POST[$k] ?? ''; if (is_array($v)) $v = implode(', ', array_map('strval', $v)); return trim(strip_tags($v)); }
function fehler($t) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>$t]); exit; }

$typ   = feld('anfrage_typ') ?: 'Beratungsanfrage';
$email = filter_var(feld('email'), FILTER_VALIDATE_EMAIL);
$name  = trim(feld('vorname') . ' ' . feld('nachname')) ?: feld('name');
if (!$email || !$name) fehler('Name und E-Mail fehlen');
$d = ['name' => $name, 'email' => $email];
$jetzt = date('d.m.Y, H:i') . ' Uhr';
$quelle = array_filter(['Quelle' => feld('utm_source'), 'Medium' => feld('utm_medium'), 'Kampagne' => feld('utm_campaign'),
                        'Suchbegriff' => feld('utm_term'), 'Einstieg' => feld('landing'), 'Verweis' => feld('referrer')]);
$sprache = feld('sprache') === 'en' ? 'Englisch' : '';
if ($sprache) $quelle = ['Sprache' => $sprache] + $quelle;
$hsBasis = ['email' => $email, 'vorname' => feld('vorname'), 'nachname' => feld('nachname')];

/* ---------- Termin (von der HubSpot-Buchung) ---------- */
if ($typ === 'Termin') {
  $start = (int) round(((float) feld('start')) / 1000);          // HubSpot liefert Millisekunden
  if ($start < time()) $start = 0;
  $d['start'] = $start ?: null;
  [$b, $html] = v_termin($d);
  $okK = m_senden($email, $b, $html, DV_NAME, DV_EMPFAENGER);
  [$bi, $hi] = v_intern('Termin gebucht', $d, ['Name' => $name, 'E-Mail' => $email, 'Termin' => $start ? datum_de($start) : 'siehe HubSpot', 'Gebucht am' => $jetzt]);
  $okI = m_senden(DV_EMPFAENGER, $bi, $hi, 'DAVINCI Website', $email);
  if ($start && $start - 86400 > time() + 3600) {
    st_mit_sperre(function (&$k, &$q) use ($d, $start) {
      $q[] = ['id' => st_token(), 'faellig' => $start - 86400, 'vorlage' => 'erinnerung', 'daten' => $d];
    });
  }
  hs_kontakt_uebertragen($hsBasis, hs_notiz('Termin gebucht über die Website', ['Termin' => $start ? datum_de($start) : 'siehe Kalender', 'Gebucht am' => $jetzt] + $quelle),
    ['phase' => 'Termin gebucht', 'name' => $name . ' · Termin', 'props' => ['davinci_quelle' => 'Terminbuchung']]);
  echo json_encode(['ok' => $okI, 'kunde' => $okK]); exit;
}

/* ---------- Grundriss-Download (Lead) ---------- */
if ($typ === 'Grundriss') {
  if (empty($_POST['datenschutz'])) fehler('Einwilligung fehlt');
  $key = preg_replace('/[^a-z0-9-]/', '', strtolower(feld('haus')));
  $d['haustyp'] = feld('haustyp');
  $pdf = ($key && is_file(__DIR__ . "/downloads/grundrisse/$key.pdf")) ? DV_SEITE . "/downloads/grundrisse/$key.pdf" : null;
  [$bK, $hK] = v_grundriss($d, $pdf);
  $okK = m_senden($email, $bK, $hK, DV_NAME, DV_EMPFAENGER);
  $zeilen = ['Typ' => 'Grundriss angefordert', 'Haus' => $d['haustyp'], 'Name' => $name, 'E-Mail' => $email,
             'Grundriss-PDF' => $pdf ? 'automatisch verschickt' : 'fehlt noch: downloads/grundrisse/' . ($key ?: '?') . '.pdf',
             'Einwilligung' => 'Datenschutz akzeptiert am ' . $jetzt] + $quelle;
  [$bI, $hI] = v_intern('Grundriss', $d, $zeilen, $pdf ? '' : 'PDF liegt noch nicht auf dem Server. Bitte Grundriss persönlich nachsenden.');
  $okI = m_senden(DV_EMPFAENGER, $bI, $hI, 'DAVINCI Website', $email);
  try { $l = st_lesen('leads'); $l[] = ['zeit' => date('c'), 'name' => $d['name'], 'email' => $d['email'], 'haus' => $d['haustyp'], 'pdf' => (bool) $pdf, 'sprache' => $sprache]; st_schreiben('leads', $l); }
  catch (Throwable $e) { error_log('DAVINCI Leads: ' . $e->getMessage()); }
  $ok = $okK || $okI;
  if (!$ok) http_response_code(500);
  echo json_encode(['ok' => $ok, 'error' => $ok ? null : 'zustellung (mail fehlt)', 'kunde' => $okK, 'pdf' => (bool) $pdf]); exit;
}

/* ---------- Kontaktformular & Design-Book ---------- */
if (empty($_POST['datenschutz'])) fehler('Einwilligung fehlt');
$format   = feld('format');
$adresse  = trim(feld('strasse') . ', ' . trim(feld('plz') . ' ' . feld('ort')), ', ');
$istBook  = ($typ === 'Design-Book');
$istPrint = $istBook && $format !== 'PDF';
if ($istPrint && !feld('strasse')) fehler('Adresse fehlt');

$istTermin = ($typ === 'Terminanfrage');
if ($istTermin) $adresse = '';
$termin = $istTermin ? array_filter(['Gespräch' => feld('gespraechsart'), 'Tage' => feld('tage'), 'Uhrzeit' => feld('zeit'), 'Ort' => feld('ort')]) : [];
$d += ['telefon' => feld('telefon'), 'haustyp' => feld('haustyp'), 'grund' => feld('grundstueck'),
       'nachricht' => feld('nachricht'), 'adresse' => $adresse, 'print' => $istPrint, 'termin' => $termin];

$links = [];
$inspiration = $istBook && !empty($_POST['inspiration']);
if ($inspiration) {
  $token = st_token();
  st_mit_sperre(function (&$k, &$q) use ($token, $d) {
    $k[$token] = ['name' => $d['name'], 'email' => $d['email'], 'angefordert' => date('c'), 'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                  'bestaetigt' => null, 'abgemeldet' => null];
  });
  $links['bestaetigen'] = DV_SEITE . '/bestaetigen.php?t=' . $token;
}

[$bK, $hK] = $istBook ? v_designbook($d, $links) : v_anfrage($d);
$okK = m_senden($email, $bK, $hK, DV_NAME, DV_EMPFAENGER);

$zeilen = ['Typ' => $typ . ($istBook ? " · $format" : ''), 'Name' => $name, 'E-Mail' => $email, 'Telefon' => $d['telefon']] + $termin + [
           'Adresse' => $adresse, 'Haustyp' => $d['haustyp'], 'Grundstück' => $d['grund'], 'Nachricht' => $d['nachricht'],
           'Inspiration' => $istBook ? ($inspiration ? 'angefragt (wartet auf Bestätigung)' : 'nein') : '',
           'Einwilligung' => 'Datenschutz akzeptiert am ' . $jetzt] + $quelle;
[$bI, $hI] = v_intern($istBook ? 'Design-Book' : ($istTermin ? 'Terminanfrage' : 'Beratungsanfrage'), $d, $zeilen, $istPrint ? 'Bitte gedrucktes Design-Book versenden.' : '');
$okI = m_senden(DV_EMPFAENGER, $bI, $hI, 'DAVINCI Website', $email);

$hsId = hs_kontakt_uebertragen($hsBasis + ['telefon' => $d['telefon'], 'strasse' => feld('strasse'), 'plz' => feld('plz'), 'ort' => feld('ort'), 'nachricht' => $d['nachricht']],
  hs_notiz(($istBook ? 'Design-Book angefordert' : 'Beratungsanfrage') . ' über die Website', array_diff_key($zeilen, ['Name' => 1, 'E-Mail' => 1]) + $quelle),
  $istBook
    ? ['phase' => 'Design-Book heruntergeladen', 'name' => $name . ' · Design-Book', 'props' => ['davinci_quelle' => 'Design-Book']]
    : ['phase' => 'Anfrage Kontaktformular', 'name' => $name . ($d['haustyp'] && $d['haustyp'] !== 'Noch offen' ? ' · ' . $d['haustyp'] : ' · Anfrage'),
       'props' => hs_deal_felder($d['haustyp'], $d['grund'], 'Kontaktformular')]);

// Erfolg, sobald die Anfrage irgendwo angekommen ist: als E-Mail an uns oder in HubSpot
$ok = $okI || (bool) $hsId;
if (!$ok) { dv_log('Anfrage nicht zustellbar: ' . $email); http_response_code(500); }
echo json_encode(['ok' => $ok, 'error' => $ok ? null : 'zustellung (mail ' . ($okI ? 'ok' : 'fehlt') . ', hubspot ' . ($hsId ? 'ok' : (HUBSPOT_TOKEN ? 'fehlt' : 'kein token')) . ')', 'mail' => $okI, 'hubspot' => (bool) $hsId, 'kunde' => $okK]);
