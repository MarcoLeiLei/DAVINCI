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

/* Eine beliebige Datei unter Sperre lesen, ändern und schreiben. Fehler verhindern nie das Absenden. */
function st_aendern(string $n, callable $fn) {
  try {
    if (!is_dir(DV_DATA)) @mkdir(DV_DATA, 0755, true);
    $lock = @fopen(DV_DATA . "/.lock-$n", 'c');
    if ($lock) flock($lock, LOCK_EX);
    $d = st_lesen($n);
    $res = $fn($d);
    st_schreiben($n, $d);
    if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
    return $res;
  } catch (Throwable $e) { error_log("DAVINCI Speicher $n: " . $e->getMessage()); return null; }
}

/* Jede Anfrage für den internen Bereich (intern.davinci-homes.de) ablegen. Der interne Bereich übernimmt sie
   in sein eigenes Archiv; hier werden sie nach 90 Tagen automatisch gelöscht. */
function anfrage_ablegen(string $typ, array $felder, array $quelle = []): void {
  st_aendern('anfragen', function (&$a) use ($typ, $felder, $quelle) {
    $grenze = time() - 90 * 86400;
    $a = array_values(array_filter($a, fn($e) => strtotime($e['zeit'] ?? 'now') >= $grenze));
    $a[] = ['id' => st_token(), 'zeit' => date('c'), 'typ' => $typ, 'felder' => array_filter($felder, fn($v) => $v !== '' && $v !== null && $v !== []), 'quelle' => $quelle];
  });
}

/* Newsletter-Anmeldung aus einem Formular (Double-Opt-in). Gibt den Bestätigungslink zurück oder null. */
function newsletter_anmelden(string $name, string $email, string $quelle, string $sprache = 'de'): ?string {
  $token = st_token();
  $ok = st_aendern('newsletter', function (&$n) use ($token, $name, $email, $quelle, $sprache) {
    foreach ($n as $t => $e) if (strcasecmp($e['email'], $email) === 0 && $e['bestaetigt'] && !$e['abgemeldet']) return false;   // schon dabei
    $n[$token] = ['name' => $name, 'email' => $email, 'quelle' => $quelle, 'sprache' => $sprache, 'angefordert' => date('c'),
                  'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'bestaetigt' => null, 'bestaetigt_ip' => null, 'abgemeldet' => null,
                  'text' => 'Ja, ich möchte den DAVINCI Newsletter mit Neuigkeiten zu Häusern, Projekten und Veranstaltungen per E-Mail erhalten. Abmeldung jederzeit möglich.'];
    return true;
  });
  return $ok ? DV_SEITE . '/newsletter-bestaetigen.php?t=' . $token : null;
}
