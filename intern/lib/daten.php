<?php
/* =========================================================
   DAVINCI intern – Datenmodell: Kontakte, Leads, Aufgaben, Anfragen
   ========================================================= */
declare(strict_types=1);

const LEAD_STATUS = [
  'neu'        => 'Neu',
  'kontaktiert'=> 'Kontaktiert',
  'termin'     => 'Termin',
  'entwurf'    => 'Entwurf',
  'angebot'    => 'Angebot',
  'gewonnen'   => 'Gewonnen',
  'verloren'   => 'Verloren',
];
const ANFRAGE_STATUS = ['neu' => 'Neu', 'bearbeitung' => 'In Bearbeitung', 'erledigt' => 'Erledigt', 'spam' => 'Spam'];
const NL_STATUS = [
  'kein'          => 'Keine Einwilligung',
  'angefragt'     => 'Wartet auf Bestätigung',
  'bestaetigt'    => 'Eingewilligt',
  'bestandskunde' => 'Bestandskunde (§ 7 Abs. 3 UWG)',
  'abgemeldet'    => 'Abgemeldet',
];
const ANFRAGE_TYPEN = ['Beratungsanfrage', 'Terminanfrage', 'Termin', 'Design-Book', 'Grundriss'];

/** Darf dieser Kontakt einen Newsletter bekommen? */
function nl_erlaubt(array $k): bool {
  return !empty($k['email']) && in_array($k['newsletter']['status'] ?? 'kein', ['bestaetigt', 'bestandskunde'], true);
}

function kontakt_name(?array $k): string {
  if (!$k) return '–';
  $n = trim(($k['vorname'] ?? '') . ' ' . ($k['nachname'] ?? ''));
  if ($n !== '') return $n;
  return ($k['firma'] ?? '') ?: (($k['email'] ?? '') ?: 'Ohne Namen');
}
/** Vollen Namen in Vor- und Nachname zerlegen (letztes Wort = Nachname). */
function name_teilen(string $name): array {
  $name = trim(preg_replace('/\s+/', ' ', $name));
  if ($name === '') return ['', ''];
  $t = explode(' ', $name);
  $nach = array_pop($t);
  return [implode(' ', $t), $nach];
}
function leerer_kontakt(): array {
  return ['id' => neue_id(), 'vorname' => '', 'nachname' => '', 'firma' => '', 'email' => '', 'telefon' => '', 'strasse' => '', 'plz' => '', 'ort' => '',
          'gruppen' => [], 'notiz' => '', 'quelle' => '', 'erstellt' => date('c'), 'geaendert' => date('c'),
          'newsletter' => ['status' => 'kein', 'seit' => null, 'nachweis' => '', 'token' => token()], 'verlauf' => []];
}
function verlauf(array &$obj, string $text): void {
  array_unshift($obj['verlauf'], ['zeit' => date('c'), 'wer' => ich()['name'] ?? 'Website', 'text' => $text]);
  $obj['verlauf'] = array_slice($obj['verlauf'], 0, 200);
  $obj['geaendert'] = date('c');
}

/**
 * Kontakt anhand der E-Mail finden oder anlegen und leere Felder ergänzen.
 * Muss innerhalb von aendern('kontakte', …) aufgerufen werden. Gibt die ID zurück.
 */
function kontakt_zusammenfuehren(array &$alle, array $neu, string $verlaufText = '', array $gruppen = []): string {
  $mail = strtolower(trim($neu['email'] ?? ''));
  $id = null;
  if ($mail !== '') foreach ($alle as $k) if (strtolower($k['email']) === $mail) { $id = $k['id']; break; }
  if (!$id) { $k = leerer_kontakt(); $id = $k['id']; $k['quelle'] = $neu['quelle'] ?? ''; $alle[$id] = $k; $verlaufText = $verlaufText ?: 'Kontakt angelegt'; }
  foreach (['vorname', 'nachname', 'firma', 'email', 'telefon', 'strasse', 'plz', 'ort'] as $f)
    if (($alle[$id][$f] ?? '') === '' && !empty($neu[$f])) $alle[$id][$f] = trim((string) $neu[$f]);
  $alle[$id]['gruppen'] = array_values(array_unique(array_merge($alle[$id]['gruppen'], array_filter($gruppen))));
  if ($verlaufText) verlauf($alle[$id], $verlaufText);
  return $id;
}

/** Newsletter-Status eines Kontakts setzen (immer mit Nachweis). */
function nl_setzen(array &$k, string $status, string $nachweis, ?string $seit = null): void {
  $alt = $k['newsletter']['status'] ?? 'kein';
  $k['newsletter']['status'] = $status;
  $k['newsletter']['seit'] = $seit ?: date('c');
  $k['newsletter']['nachweis'] = $nachweis;
  if (empty($k['newsletter']['token'])) $k['newsletter']['token'] = token();
  if ($alt !== $status) verlauf($k, 'Newsletter: ' . NL_STATUS[$status] . ($nachweis ? ' · ' . $nachweis : ''));
}

function kontakte(): array { return lesen('kontakte'); }
function leads(): array { return lesen('leads'); }
function aufgaben(): array { return lesen('aufgaben'); }
function anfragen(): array { return lesen('anfragen'); }

/** Felder einer Website-Anfrage lesbar beschriften. */
function anfrage_felder(array $a): array {
  $namen = ['name' => 'Name', 'email' => 'E-Mail', 'telefon' => 'Telefon', 'adresse' => 'Adresse', 'haustyp' => 'Haustyp', 'haus' => 'Haus',
            'grundstueck' => 'Grundstück', 'nachricht' => 'Nachricht', 'format' => 'Format', 'termin' => 'Termin', 'inspiration' => 'Inspirations-Mails',
            'newsletter' => 'Newsletter', 'pdf' => 'Grundriss-PDF'];
  $o = [];
  foreach ($a['felder'] ?? [] as $k => $v) {
    if (in_array($k, ['vorname', 'nachname'], true)) continue;
    if ($k === 'termin' && ($a['typ'] ?? '') === 'Termin' && strtotime((string) $v)) $v = datum($v, true) . ' Uhr';
    $o[$namen[$k] ?? ucfirst($k)] = $v;
  }
  return $o;
}
function anfrage_name(array $a): string { return (string) ($a['felder']['name'] ?? $a['felder']['email'] ?? 'Ohne Namen'); }

function leerer_lead(): array {
  return ['id' => neue_id(), 'titel' => '', 'kontakt_id' => null, 'status' => 'neu', 'wert' => '', 'haus' => '', 'ort' => '', 'naechster_schritt' => '', 'naechster_termin' => '',
          'verantwortlich' => null, 'notizen' => '', 'anfrage_id' => null, 'erstellt' => date('c'), 'geaendert' => date('c'), 'abgeschlossen' => null, 'verlauf' => []];
}
function leere_aufgabe(): array {
  return ['id' => neue_id(), 'titel' => '', 'text' => '', 'faellig' => '', 'wichtig' => false, 'erledigt' => null, 'zugewiesen' => null,
          'bezug' => null, 'erstellt' => date('c'), 'von' => ich()['id'] ?? null];
}
/** Sperrliste: abgemeldete Adressen (als Prüfsumme), damit sie nach Löschen oder Import nicht erneut angeschrieben werden. */
function gesperrt(string $email): bool { return isset(lesen('sperrliste')[hash('sha256', strtolower(trim($email)))]); }
function sperren(string $email): void {
  if (trim($email) === '') return;
  aendern('sperrliste', function (&$s) use ($email) { $s[hash('sha256', strtolower(trim($email)))] = date('c'); });
}
