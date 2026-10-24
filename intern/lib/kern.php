<?php
/* =========================================================
   DAVINCI intern – Grundlagen: Konfiguration, Speicher, Sitzung, Schutz, Hilfsfunktionen
   ========================================================= */
declare(strict_types=1);

const APP   = __DIR__ . '/..';
const DATEN = APP . '/daten';
const SPERRZEILE = "<?php exit; ?>\n";

date_default_timezone_set('Europe/Berlin');
mb_internal_encoding('UTF-8');
ini_set('display_errors', '0');

$GLOBALS['CFG'] = is_file(APP . '/config.php') ? (require APP . '/config.php') : [];

function cfg(string $k, $std = null) {
  $v = $GLOBALS['CFG'];
  foreach (explode('.', $k) as $p) { if (!is_array($v) || !array_key_exists($p, $v)) return $std; $v = $v[$p]; }
  return $v;
}

/* ---------- Speicher: JSON in daten/*.php mit Sperrzeile ---------- */
function lesen(string $n): array {
  $f = DATEN . "/$n.php";
  if (!is_file($f)) return [];
  $t = (string) file_get_contents($f);
  if (strpos($t, SPERRZEILE) === 0) $t = substr($t, strlen(SPERRZEILE));
  return json_decode($t, true) ?: [];
}
function schreiben(string $n, array $d): void {
  if (!is_dir(DATEN)) @mkdir(DATEN, 0750, true);
  $tmp = DATEN . "/.$n." . bin2hex(random_bytes(4));
  file_put_contents($tmp, SPERRZEILE . json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
  rename($tmp, DATEN . "/$n.php");
}
/** Datei unter Sperre lesen, mit $fn ändern und speichern. Rückgabe von $fn wird durchgereicht. */
function aendern(string $n, callable $fn) {
  if (!is_dir(DATEN)) @mkdir(DATEN, 0750, true);
  $lock = fopen(DATEN . "/.lock-$n", 'c');
  flock($lock, LOCK_EX);
  try { $d = lesen($n); $r = $fn($d); schreiben($n, $d); return $r; }
  finally { flock($lock, LOCK_UN); fclose($lock); }
}
function neue_id(): string { return bin2hex(random_bytes(6)); }
function token(): string { return bin2hex(random_bytes(16)); }

/* ---------- Ausgabe ---------- */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function weiter(string $url): void { header('Location: ' . $url); exit; }
function json_aus($d, int $code = 200): void { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
function eur($v): string { return number_format((float) $v, 0, ',', '.') . ' €'; }
function datum($iso, bool $zeit = false): string {
  if (!$iso) return '–';
  $t = is_numeric($iso) ? (int) $iso : strtotime((string) $iso);
  return $t ? date($zeit ? 'd.m.Y, H:i' : 'd.m.Y', $t) : '–';
}
function relativ($iso): string {
  $t = strtotime((string) $iso); if (!$t) return '–';
  $d = time() - $t;
  if ($d < 60) return 'gerade eben';
  if ($d < 3600) return 'vor ' . floor($d / 60) . ' Min.';
  if ($d < 86400) return 'vor ' . floor($d / 3600) . ' Std.';
  if ($d < 172800) return 'gestern';
  if ($d < 7 * 86400) return 'vor ' . floor($d / 86400) . ' Tagen';
  return date('d.m.Y', $t);
}
function p(string $k, $std = '') { $v = $_POST[$k] ?? $std; return is_string($v) ? trim($v) : $v; }
function g(string $k, $std = '') { $v = $_GET[$k] ?? $std; return is_string($v) ? trim($v) : $v; }
function ist_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function basis_url(): string { return rtrim((string) cfg('url', ''), '/') ?: ((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost')); }

/* ---------- Sitzung ---------- */
function sitzung_start(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;
  $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
  session_name('dvintern');
  session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
  session_start();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = token();
}
function csrf(): string { return $_SESSION['csrf'] ?? ''; }
function csrf_feld(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf()) . '">'; }
function csrf_pruefen(): void {
  $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
  if (!is_string($t) || !hash_equals(csrf(), $t)) { http_response_code(400); exit('Sitzung abgelaufen. Bitte die Seite neu laden.'); }
}
function meldung(string $text, string $art = 'ok'): void { $_SESSION['meldungen'][] = [$art, $text]; }
function meldungen(): array { $m = $_SESSION['meldungen'] ?? []; unset($_SESSION['meldungen']); return $m; }

/* ---------- Benutzer ---------- */
function benutzer_alle(): array { return lesen('benutzer'); }
function ich(): ?array {
  static $ich = false;
  if ($ich !== false) return $ich;
  $id = $_SESSION['uid'] ?? null;
  $b = $id ? (benutzer_alle()[$id] ?? null) : null;
  if ($b && ($_SESSION['seit'] ?? 0) < time() - 12 * 3600) $b = null;   // nach 12 Stunden neu anmelden
  return $ich = $b;
}
function ist_admin(): bool { return (ich()['rolle'] ?? '') === 'admin'; }
function benutzer_name(?string $id): string { return $id ? (benutzer_alle()[$id]['name'] ?? '–') : '–'; }

/** Jede geschützte Seite beginnt hiermit. */
function seite_schuetzen(bool $nurAdmin = false): array {
  sitzung_start();
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: same-origin');
  header('X-Robots-Tag: noindex, nofollow');
  header('Cache-Control: private, no-store');
  $b = ich();
  if (!$b) {
    if (!empty($_SERVER['HTTP_X_CSRF'])) json_aus(['ok' => false, 'fehler' => 'abgemeldet'], 401);
    weiter('anmelden.php' . (($_SERVER['REQUEST_URI'] ?? '') ? '?zurueck=' . rawurlencode($_SERVER['REQUEST_URI']) : ''));
  }
  if ($nurAdmin && $b['rolle'] !== 'admin') { http_response_code(403); exit('Nur für Administratoren.'); }
  if (ist_post()) csrf_pruefen();
  return $b;
}

/* ---------- Anmeldeversuche begrenzen ---------- */
function versuche_ok(): bool {
  $ip = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . 'dv');
  $v = lesen('versuche'); $grenze = time() - 900;
  $n = count(array_filter($v[$ip] ?? [], fn($t) => $t > $grenze));
  return $n < 8;
}
function versuch_merken(): void {
  $ip = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . 'dv');
  aendern('versuche', function (&$v) use ($ip) {
    $grenze = time() - 900;
    foreach ($v as $k => $l) { $v[$k] = array_values(array_filter($l, fn($t) => $t > $grenze)); if (!$v[$k]) unset($v[$k]); }
    $v[$ip][] = time();
  });
}

/* ---------- Einstellungen ---------- */
function einstellungen(): array {
  return lesen('einstellungen') + [
    'absender_name' => 'DAVINCI',
    'absender'      => cfg('absender', 'bauen@davinci-homes.de'),
    'antwort'       => cfg('absender', 'bauen@davinci-homes.de'),
    'stundenlimit'  => 300,
    'paket'         => 15,
    'gruppen'       => ['Interessent', 'Bauherr', 'Bestandskunde DAVINCI', 'Partner', 'Presse'],
  ];
}

/* ---------- Protokoll ---------- */
function protokoll(string $text): void {
  aendern('protokoll', function (&$p) use ($text) {
    array_unshift($p, ['zeit' => date('c'), 'wer' => ich()['name'] ?? 'System', 'text' => $text]);
    $p = array_slice($p, 0, 500);
  });
}
