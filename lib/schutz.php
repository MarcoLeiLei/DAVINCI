<?php
/* =========================================================
   DAVINCI – Spamschutz für alle Formulare
   1. Honeypot (unsichtbares Feld)          2. Mindest-Ausfüllzeit
   3. Begrenzung je IP-Adresse und Stunde   4. Google reCAPTCHA v3
   ========================================================= */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/store.php';

/** Gibt null zurück, wenn alles in Ordnung ist, sonst den Grund. */
function spam_pruefen() {
  // 1. Honeypot
  if (!empty($_POST['website'])) return 'honeypot';

  // 2. Mindestzeit zwischen Seitenaufruf und Absenden
  $start = (int) ($_POST['ts'] ?? 0);
  if ($start && (time() - intdiv($start, 1000)) < SPAM_MIN_SEKUNDEN) return 'zu schnell';

  // 3. Begrenzung je IP (anonymisiert gespeichert, 1 Stunde)
  $ip = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ZUGANG_SALZ());
  $zuViel = st_mit_sperre(function (&$k, &$q) use ($ip) {
    $datei = DV_DATA . '/limit.json';
    $l = is_file($datei) ? (json_decode(file_get_contents($datei), true) ?: []) : [];
    $jetzt = time();
    foreach ($l as $h => $zeiten) { $l[$h] = array_values(array_filter($zeiten, fn($t) => $t > $jetzt - 3600)); if (!$l[$h]) unset($l[$h]); }
    $l[$ip] = $l[$ip] ?? [];
    $zuViel = count($l[$ip]) >= max(SPAM_MAX_PRO_STUNDE, 20);
    if (!$zuViel) $l[$ip][] = $jetzt;
    @file_put_contents($datei, json_encode($l));
    return $zuViel;
  });
  if ($zuViel) return 'limit';

  // 4. reCAPTCHA v3
  if (RECAPTCHA_SECRET) {
    $token = $_POST['g-recaptcha-response'] ?? '';
    if (!$token) { dv_log('reCAPTCHA-Token fehlt, Anfrage trotzdem angenommen'); return null; }  // ohne Website-Schlüssel kein Token
    [$s, $r] = http_anfrage('POST', 'https://www.google.com/recaptcha/api/siteverify',
      ['Content-Type: application/x-www-form-urlencoded'],
      http_build_query(['secret' => RECAPTCHA_SECRET, 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '']));
    if ($s !== 200 || empty($r['success'])) { dv_log('reCAPTCHA abgelehnt: ' . json_encode($r)); return 'captcha'; }
    if (($r['score'] ?? 0) < RECAPTCHA_MIN_SCORE) { dv_log('reCAPTCHA Score ' . ($r['score'] ?? '?')); return 'captcha score'; }
  }
  return null;
}
function ZUGANG_SALZ() { return defined('ZUGANG') ? ZUGANG : 'davinci'; }
