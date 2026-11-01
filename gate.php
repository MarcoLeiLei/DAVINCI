<?php
/* Passwortschutz – wird oben in jede Seite eingebunden */
require_once __DIR__ . '/zugang.php';
require_once __DIR__ . '/lib/live.php';
// Schutz greift, solange die Website nicht live ist; Werkzeuge setzen $immer_schuetzen = true
$dv_schutz = SCHUTZ_AKTIV && (!WEBSITE_LIVE || !empty($immer_schuetzen));
// Solange der Passwortschutz aktiv ist: nicht zwischenspeichern und nicht bei Google listen
if ($dv_schutz) {
  header('Cache-Control: private, no-cache, no-store, must-revalidate');
  header('Pragma: no-cache');
  header('X-Robots-Tag: noindex, nofollow');
}
if ($dv_schutz && (!isset($_COOKIE['dv_access']) || !hash_equals(ZUGANG, (string) $_COOKIE['dv_access']))) {
  require __DIR__ . '/login.php';
  exit;
}
