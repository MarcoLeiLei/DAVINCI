<?php
// Ersatz-Router: greift nur, wenn der Server eine saubere Adresse (z. B. /haeuser) nicht selbst auflöst.
$pfad = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
$seiten = ['haeuser', 'system', 'manufaktur', 'geschichte', 'service', 'kontakt', 'design-book', 'impressum', 'datenschutz'];
$en = false;
if ($pfad === 'en') { $en = true; $pfad = ''; }
elseif (strpos($pfad, 'en/') === 0) { $en = true; $pfad = substr($pfad, 3); }
if ($pfad === '' || in_array($pfad, $seiten, true)) {
  http_response_code(200);
  header("Cache-Control: private, no-cache");
  $datei = $en ? __DIR__ . '/en/' . ($pfad === '' ? 'index' : $pfad) . '.php' : __DIR__ . '/' . ($pfad === '' ? 'index' : $pfad) . '.php';
  if (is_file($datei)) { chdir(dirname($datei)); require $datei; exit; }
}
http_response_code(404);
header("Cache-Control: no-store");
?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Seite nicht gefunden | DAVINCI</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b0b0b;color:#fff;font:300 18px/1.5 -apple-system,"Helvetica Neue",Arial,sans-serif;text-align:center;padding:24px}h1{font-weight:200;font-size:44px;letter-spacing:-.02em;margin:0 0 12px}a{color:#9dbcc4}</style></head>
<body><div><h1>Seite nicht gefunden.</h1><p>Diese Adresse gibt es nicht. <a href="/">Zur Startseite</a></p></div></body></html>
