<?php
/* Passwortschutz – wird oben in jede Seite eingebunden */
require __DIR__ . '/zugang.php';
// Solange der Passwortschutz aktiv ist: nicht zwischenspeichern und nicht bei Google listen
if (SCHUTZ_AKTIV) {
  header('Cache-Control: private, no-cache, no-store, must-revalidate');
  header('Pragma: no-cache');
  header('X-Robots-Tag: noindex, nofollow');
}
if (SCHUTZ_AKTIV && (!isset($_COOKIE['dv_access']) || !hash_equals(ZUGANG, (string) $_COOKIE['dv_access']))) {
  require __DIR__ . '/login.php';
  exit;
}
