<?php
/* DAVINCI – Öffnungs-Bild und Link-Weiterleitung der Grundriss-E-Mail (siehe lib/spur.php) */
require __DIR__ . '/lib/mail.php';
require __DIR__ . '/lib/spur.php';
$aid = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['a'] ?? ''));
$e = ($_GET['e'] ?? '') === 'k' ? 'k' : 'o';
$z = in_array($_GET['z'] ?? '', SPUR_ZIELE, true) ? $_GET['z'] : '';
$h = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['h'] ?? '')));
$gueltig = $aid !== '' && hash_equals(spur_sig($aid, $e, $z . $h), (string) ($_GET['s'] ?? ''));
// Vorab-Abrufe von Linkprüfern (HEAD) nicht zählen
if ($gueltig && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') spur_ereignis($aid, $e, $z);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex');
if ($e === 'o') {
  header('Content-Type: image/gif');
  echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'); exit;
}
$ziel = DV_SEITE . '/';
if ($z === 'pdf' && $h !== '' && is_file(__DIR__ . "/downloads/grundrisse/$h.pdf")) $ziel = DV_SEITE . "/downloads/grundrisse/$h.pdf";
elseif ($z === 'termin') $ziel = DV_TERMIN;
header('Location: ' . $ziel, true, 302); exit;
