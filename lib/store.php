<?php
/* DAVINCI – einfacher Datenspeicher (JSON im geschützten Ordner /data) */
const DV_DATA = __DIR__ . '/../data';

/* Speichern darf das Absenden nie verhindern: Ist der Ordner nicht beschreibbar, läuft alles ohne Speicher weiter. */
function st_mit_sperre(callable $fn) {
  try {
    if (!is_dir(DV_DATA)) @mkdir(DV_DATA, 0755, true);
    $lock = @fopen(DV_DATA . '/.lock', 'c');
    if ($lock) flock($lock, LOCK_EX);
    $k = st_lesen('kontakte'); $q = st_lesen('warteschlange');
    $res = $fn($k, $q);
    if ($lock) { st_schreiben('kontakte', $k); st_schreiben('warteschlange', $q); flock($lock, LOCK_UN); fclose($lock); }
    else error_log('DAVINCI: Ordner data/ ist nicht beschreibbar');
    return $res;
  } catch (Throwable $e) { error_log('DAVINCI Speicher: ' . $e->getMessage()); return null; }
}
/* Gespeichert als .php mit Sperrzeile: selbst ohne .htaccess-Schutz liefert der Server nichts aus. */
const DV_SPERRE = "<?php exit; ?>\n";
function st_lesen($n) {
  $f = DV_DATA . "/$n.php";
  if (is_file($f)) { $t = file_get_contents($f); if (strpos($t, DV_SPERRE) === 0) $t = substr($t, strlen(DV_SPERRE)); return json_decode($t, true) ?: []; }
  $alt = DV_DATA . "/$n.json"; // ältere Version
  return is_file($alt) ? (json_decode(file_get_contents($alt), true) ?: []) : [];
}
function st_schreiben($n, $d) {
  @file_put_contents(DV_DATA . "/$n.php", DV_SPERRE . json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  $alt = DV_DATA . "/$n.json"; if (is_file($alt)) @unlink($alt);
}
function st_token() { return bin2hex(random_bytes(16)); }
