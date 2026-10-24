<?php
/* =========================================================
   DAVINCI intern – Excel (.xlsx) und CSV lesen, ohne Zusatzbibliotheken
   ========================================================= */
declare(strict_types=1);

/** Gibt die Zeilen als Liste von Listen (Strings) zurück. */
function tabelle_lesen(string $pfad, string $name): array {
  $endung = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if ($endung === 'xlsx') return xlsx_lesen($pfad);
  if (in_array($endung, ['csv', 'txt', 'tsv'], true)) return csv_lesen($pfad);
  throw new RuntimeException('Bitte eine Excel-Datei (.xlsx) oder CSV-Datei hochladen. Alte .xls-Dateien bitte in Excel als .xlsx speichern.');
}

function csv_lesen(string $pfad): array {
  $t = (string) file_get_contents($pfad);
  if (strncmp($t, "\xEF\xBB\xBF", 3) === 0) $t = substr($t, 3);
  if (!mb_check_encoding($t, 'UTF-8')) $t = mb_convert_encoding($t, 'UTF-8', 'Windows-1252');
  $erste = strtok($t, "\n") ?: '';
  $trenner = ';'; $max = -1;
  foreach ([';', ',', "\t", '|'] as $z) { $n = substr_count($erste, $z); if ($n > $max) { $max = $n; $trenner = $z; } }
  $f = fopen('php://memory', 'r+'); fwrite($f, $t); rewind($f);
  $zeilen = [];
  while (($z = fgetcsv($f, 0, $trenner, '"', '')) !== false) {
    if ($z === [null]) continue;
    $zeilen[] = array_map(fn($v) => trim((string) $v), $z);
  }
  fclose($f);
  return $zeilen;
}

function xlsx_lesen(string $pfad): array {
  if (!class_exists('ZipArchive')) throw new RuntimeException('Auf dem Server fehlt die PHP-Erweiterung „zip“. Bitte die Liste als CSV speichern.');
  $zip = new ZipArchive();
  if ($zip->open($pfad) !== true) throw new RuntimeException('Die Excel-Datei lässt sich nicht öffnen.');
  $strings = [];
  if (($x = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
    $xml = simplexml_load_string($x, 'SimpleXMLElement', LIBXML_NONET);
    foreach ($xml->si as $si) {
      if (isset($si->t)) $strings[] = (string) $si->t;
      else { $s = ''; foreach ($si->r as $r) $s .= (string) $r->t; $strings[] = $s; }
    }
  }
  // erstes Tabellenblatt laut workbook
  $blatt = 'xl/worksheets/sheet1.xml';
  if (($wb = $zip->getFromName('xl/workbook.xml')) !== false && ($rels = $zip->getFromName('xl/_rels/workbook.xml.rels')) !== false) {
    $w = simplexml_load_string($wb, 'SimpleXMLElement', LIBXML_NONET);
    $w->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
    $sheet = $w->sheets->sheet[0] ?? null;
    if ($sheet) {
      $rid = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
      $r = simplexml_load_string($rels, 'SimpleXMLElement', LIBXML_NONET);
      foreach ($r->Relationship as $rel) if ((string) $rel['Id'] === $rid) { $ziel = ltrim((string) $rel['Target'], '/'); $blatt = str_starts_with($ziel, 'xl/') ? $ziel : 'xl/' . $ziel; }
    }
  }
  $x = $zip->getFromName($blatt);
  $zip->close();
  if ($x === false) throw new RuntimeException('In der Excel-Datei wurde kein Tabellenblatt gefunden.');
  $xml = simplexml_load_string($x, 'SimpleXMLElement', LIBXML_NONET);
  $zeilen = [];
  foreach ($xml->sheetData->row as $row) {
    $z = [];
    foreach ($row->c as $c) {
      $ref = (string) $c['r'];
      $spalte = 0;
      if (preg_match('/^([A-Z]+)/', $ref, $m)) foreach (str_split($m[1]) as $ch) $spalte = $spalte * 26 + (ord($ch) - 64);
      $typ = (string) $c['t'];
      $v = $typ === 'inlineStr' ? (string) $c->is->t : (string) $c->v;
      if ($typ === 's') $v = $strings[(int) $v] ?? '';
      elseif ($typ === 'b') $v = $v === '1' ? 'ja' : 'nein';
      elseif ($typ === '' && is_numeric($v) && str_contains($v, 'E')) $v = number_format((float) $v, 0, '', '');   // lange Telefonnummern
      $z[$spalte - 1] = trim($v);
    }
    if (!$z) continue;
    $voll = [];
    for ($i = 0; $i <= max(array_keys($z)); $i++) $voll[] = $z[$i] ?? '';
    $zeilen[] = $voll;
  }
  return $zeilen;
}

/** Spaltenüberschrift einem Feld zuordnen. */
function spalte_erkennen(string $kopf): string {
  $k = mb_strtolower(trim($kopf));
  $k = str_replace(['ä', 'ö', 'ü', 'ß', '-', '_', '.'], ['ae', 'oe', 'ue', 'ss', ' ', ' ', ''], $k);
  $regeln = [
    'email'    => ['e mail', 'email', 'mail', 'e mail adresse', 'emailadresse'],
    'vorname'  => ['vorname', 'first name', 'firstname', 'given name'],
    'nachname' => ['nachname', 'last name', 'lastname', 'familienname', 'surname'],
    'name'     => ['name', 'vollstaendiger name', 'kontakt', 'kunde', 'full name', 'ansprechpartner', 'vor und nachname'],
    'firma'    => ['firma', 'unternehmen', 'company', 'organisation'],
    'telefon'  => ['telefon', 'tel', 'phone', 'mobil', 'handy', 'telefonnummer', 'mobile'],
    'strasse'  => ['strasse', 'adresse', 'street', 'anschrift'],
    'plz'      => ['plz', 'postleitzahl', 'zip', 'postal code'],
    'ort'      => ['ort', 'stadt', 'city', 'wohnort'],
    'gruppe'   => ['gruppe', 'gruppen', 'kategorie', 'tag', 'tags', 'segment'],
  ];
  foreach ($regeln as $feld => $woerter) if (in_array($k, $woerter, true)) return $feld;
  foreach ($regeln as $feld => $woerter) foreach ($woerter as $w) if (strlen($w) > 3 && str_contains($k, $w)) return $feld;
  return '';
}
