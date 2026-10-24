<?php
/* =========================================================
   DAVINCI – HubSpot-Anbindung
   Legt jeden Kontakt in HubSpot an (oder aktualisiert ihn)
   und hängt eine Notiz mit allen Angaben der Anfrage an.
   ========================================================= */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/http.php';

function hs_api($methode, $pfad, $daten = null) {
  return http_anfrage($methode, HUBSPOT_API . $pfad,
    ['Authorization: Bearer ' . HUBSPOT_TOKEN, 'Content-Type: application/json'],
    $daten === null ? null : json_encode($daten));
}

/**
 * @param array  $kontakt  email, vorname, nachname, telefon, strasse, plz, ort, nachricht
 * @param string $notiz    HTML-Text für die Notiz am Kontakt
 * @return string|null     HubSpot-ID des Kontakts
 */
function hs_kontakt_uebertragen(array $kontakt, string $notiz, ?array $deal = null) {
  if (!HUBSPOT_TOKEN || empty($kontakt['email'])) return null;
  try {
    $props = array_filter([
      'email'     => $kontakt['email'],
      'firstname' => $kontakt['vorname'] ?? '',
      'lastname'  => $kontakt['nachname'] ?? '',
      'phone'     => $kontakt['telefon'] ?? '',
      'address'   => $kontakt['strasse'] ?? '',
      'zip'       => $kontakt['plz'] ?? '',
      'city'      => $kontakt['ort'] ?? '',
      'message'   => $kontakt['nachricht'] ?? '',
    ], fn($v) => $v !== '' && $v !== null);

    // 1. Gibt es den Kontakt schon?
    [$s, $r] = hs_api('POST', '/crm/v3/objects/contacts/search', [
      'filterGroups' => [['filters' => [['propertyName' => 'email', 'operator' => 'EQ', 'value' => $kontakt['email']]]]],
      'properties' => ['email'], 'limit' => 1,
    ]);
    $id = ($s === 200 && !empty($r['results'][0]['id'])) ? $r['results'][0]['id'] : null;

    if ($id) {
      // 2a. Vorhandenen Kontakt ergänzen (Phase und Status bleiben unangetastet)
      [$s, $r] = hs_api('PATCH', '/crm/v3/objects/contacts/' . $id, ['properties' => $props]);
      if ($s >= 300) dv_log("HubSpot-Update $s: " . json_encode($r));
    } else {
      // 2b. Neuen Kontakt anlegen
      $props['lifecyclestage'] = 'lead';
      $props['hs_lead_status'] = 'NEW';
      [$s, $r] = hs_api('POST', '/crm/v3/objects/contacts', ['properties' => $props]);
      if ($s >= 300 || empty($r['id'])) { dv_log("HubSpot-Anlage $s: " . json_encode($r)); return null; }
      $id = $r['id'];
    }

    // 3. Optional: Deal in der Verkaufs-Pipeline anlegen oder weiterschieben
    $dealId = $deal ? hs_deal($id, $deal) : null;

    // 4. Notiz mit allen Angaben an Kontakt (und Deal) hängen
    $assoc = [['to' => ['id' => $id], 'types' => [['associationCategory' => 'HUBSPOT_DEFINED', 'associationTypeId' => 202]]]];
    if ($dealId) $assoc[] = ['to' => ['id' => $dealId], 'types' => [['associationCategory' => 'HUBSPOT_DEFINED', 'associationTypeId' => 214]]];
    [$s, $r] = hs_api('POST', '/crm/v3/objects/notes', [
      'properties' => ['hs_timestamp' => gmdate('Y-m-d\TH:i:s\Z'), 'hs_note_body' => $notiz],
      'associations' => $assoc,
    ]);
    if ($s >= 300) dv_log("HubSpot-Notiz $s: " . json_encode($r));
    return $id;
  } catch (Throwable $e) {
    dv_log('HubSpot-Fehler: ' . $e->getMessage());
    return null;
  }
}

/* Notiztext aus Zeilen bauen */
function hs_notiz($titel, array $zeilen) {
  $o = '<p><strong>' . htmlspecialchars($titel) . '</strong></p><ul>';
  foreach ($zeilen as $k => $v) if ($v !== '' && $v !== null) $o .= '<li><strong>' . htmlspecialchars($k) . ':</strong> ' . nl2br(htmlspecialchars((string) $v)) . '</li>';
  return $o . '</ul><p>Quelle: Website davinci-homes.de</p>';
}

/* =========================================================
   Deals: Pipeline und Phase werden über ihre Namen gefunden
   ========================================================= */
if (!defined('HS_PIPELINE')) define('HS_PIPELINE', 'DAVINCI Verkauf');
// Wie weit ein Interessent ist. Ein Deal wird nur nach vorn geschoben, nie zurück.
const HS_RANG = ['design-book heruntergeladen' => 1, 'anfrage kontaktformular' => 2, 'telefoniert' => 3,
                 'termin gebucht' => 4, 'termin persönlich vereinbart' => 4, 'hot lead' => 5];
function hs_norm($t) { return mb_strtolower(trim(preg_replace('/[\s\x{00A0}]+/u', ' ', (string) $t))); }

/** Pipeline-ID und Phasen (Name → [ID, Reihenfolge]) holen, 1 Stunde zwischenspeichern */
function hs_pipeline() {
  $cache = __DIR__ . '/../data/hs_pipeline.json';
  // Ausweg: IDs fest in config.php eingetragen → Pipelines müssen nicht gelesen werden
  if (defined('HS_PIPELINE_ID') && HS_PIPELINE_ID && defined('HS_STAGE_IDS') && HS_STAGE_IDS) {
    $stufen = []; $i = 0;
    foreach (HS_STAGE_IDS as $label => $id) $stufen[hs_norm($label)] = [(string) $id, $i++, hs_norm($label) === 'verloren'];
    return ['id' => (string) HS_PIPELINE_ID, 'name' => HS_PIPELINE, 'stufen' => $stufen];
  }
  if (is_file($cache) && filemtime($cache) > time() - 3600) return json_decode(file_get_contents($cache), true);
  [$s, $r] = hs_api('GET', '/crm/v3/pipelines/deals');
  $alle = $r['results'] ?? [];
  // 1. Pipeline mit dem eingestellten Namen, 2. sonst die erste Pipeline mit der Phase „Anfrage Kontaktformular“
  $treffer = null;
  foreach ($alle as $p) if (hs_norm($p['label']) === hs_norm(HS_PIPELINE)) { $treffer = $p; break; }
  if (!$treffer) foreach ($alle as $p) foreach (($p['stages'] ?? []) as $st) if (str_contains(hs_norm($st['label']), 'kontaktformular')) { $treffer = $p; break 2; }
  if (!$treffer && count($alle) === 1) $treffer = $alle[0];   // nur eine Pipeline vorhanden: diese nehmen
  if ($treffer) {
    $stufen = [];
    foreach ($treffer['stages'] as $st) $stufen[hs_norm($st['label'])] = [$st['id'], (int) $st['displayOrder'], ($st['metadata']['isClosed'] ?? 'false') === 'true'];
    $daten = ['id' => $treffer['id'], 'name' => $treffer['label'], 'stufen' => $stufen];
    @file_put_contents($cache, json_encode($daten));
    return $daten;
  }
  $liste = [];
  foreach ($alle as $p) $liste[] = ($p['label'] ?? '?') . ' [' . implode(' | ', array_map(fn($st) => $st['label'] ?? '?', $p['stages'] ?? [])) . ']';
  dv_log("HubSpot: Pipeline „" . HS_PIPELINE . "“ nicht gefunden (Status $s). HubSpot liefert: " . ($liste ? implode(' ; ', $liste) : 'keine Pipelines – Antwort: ' . substr(json_encode($r), 0, 300)));
  return null;
}

/**
 * Legt für den Kontakt einen Deal an. Gibt es schon einen offenen Deal in der Pipeline,
 * wird kein zweiter angelegt; liegt er in einer früheren Phase, wird er weitergeschoben.
 * @param array $deal ['phase' => 'Anfrage eingegangen', 'name' => '…', 'props' => [...]]
 */
function hs_deal($kontaktId, array $deal) {
  $pl = hs_pipeline();
  $phase = hs_norm($deal['phase']);
  if ($pl && empty($pl['stufen'][$phase])) {
    // tolerant: Phase finden, deren Name das Schlüsselwort enthält (z. B. „Kontaktformular“, „Design-Book“)
    $wort = ['anfrage kontaktformular' => 'kontaktformular', 'termin gebucht' => 'gebucht', 'design-book heruntergeladen' => 'design'][$phase] ?? $phase;
    foreach ($pl['stufen'] as $label => $st) if (str_contains($label, $wort)) { $pl['stufen'][$phase] = $st; break; }
  }
  if (!$pl || empty($pl['stufen'][$phase])) {
    dv_log('HubSpot: Phase „' . $deal['phase'] . '“ fehlt. Gefundene Phasen: ' . ($pl ? implode(', ', array_keys($pl['stufen'])) : 'keine Pipeline'));
    return null;
  }
  $zielId = $pl['stufen'][$phase][0];
  $zielRang = HS_RANG[$phase] ?? 0;
  $rangVon = []; $zu = [];
  foreach ($pl['stufen'] as $label => $st) { $rangVon[$st[0]] = HS_RANG[$label] ?? 99; if (!empty($st[2])) $zu[$st[0]] = true; }

  // Offene Deals dieses Kontakts in der Pipeline suchen
  [$s, $r] = hs_api('POST', '/crm/v3/objects/deals/search', [
    'filterGroups' => [['filters' => [
      ['propertyName' => 'associations.contact', 'operator' => 'EQ', 'value' => (string) $kontaktId],
      ['propertyName' => 'pipeline', 'operator' => 'EQ', 'value' => $pl['id']],
    ]]],
    'properties' => ['dealstage'], 'limit' => 10,
  ]);
  foreach (($r['results'] ?? []) as $d) {
    $stufe = $d['properties']['dealstage'] ?? '';
    if (isset($zu[$stufe])) continue;   // gewonnen oder verloren: neuer Deal
    // unbekannte, später ergänzte Phasen (Rang 99) werden nie zurückgeschoben
    if ($rangVon[$stufe] < $zielRang) {
      [$s2, $r2] = hs_api('PATCH', '/crm/v3/objects/deals/' . $d['id'], ['properties' => ['dealstage' => $zielId]]);
      if ($s2 >= 300) dv_log("HubSpot-Deal-Update $s2: " . json_encode($r2));
    }
    return $d['id'];
  }

  // Neuen Deal anlegen und mit dem Kontakt verknüpfen
  $props = array_filter(($deal['props'] ?? []) + ['dealname' => $deal['name'], 'pipeline' => $pl['id'], 'dealstage' => $zielId],
                        fn($v) => $v !== '' && $v !== null);
  [$s, $r] = hs_api('POST', '/crm/v3/objects/deals', [
    'properties' => $props,
    'associations' => [['to' => ['id' => $kontaktId], 'types' => [['associationCategory' => 'HUBSPOT_DEFINED', 'associationTypeId' => 3]]]],
  ]);
  if ($s >= 300 && !empty($deal['props'])) {
    // Falls die DAVINCI-Felder noch nicht angelegt sind: ohne sie erneut versuchen
    dv_log("HubSpot-Deal mit Feldern $s: " . json_encode($r));
    [$s, $r] = hs_api('POST', '/crm/v3/objects/deals', [
      'properties' => ['dealname' => $deal['name'], 'pipeline' => $pl['id'], 'dealstage' => $zielId],
      'associations' => [['to' => ['id' => $kontaktId], 'types' => [['associationCategory' => 'HUBSPOT_DEFINED', 'associationTypeId' => 3]]]],
    ]);
  }
  if ($s >= 300 || empty($r['id'])) { dv_log("HubSpot-Deal $s: " . json_encode($r)); return null; }
  return $r['id'];
}

/* Formularwerte in die Auswahlwerte der Deal-Felder übersetzen */
function hs_deal_felder($haustyp, $grund, $quelle) {
  $ht = ['Einfamilienhaus' => 'Einfamilienhaus', 'Bungalow' => 'Bungalow', 'Individuelles Projekt' => 'Individuell',
         'DAVINCI Terrazza' => 'Einfamilienhaus', 'DAVINCI Piano' => 'Bungalow', 'DAVINCI Ala' => 'Bungalow', 'DAVINCI Individuell' => 'Individuell'][$haustyp] ?? '';
  $gr = ['Grundstück vorhanden' => 'Vorhanden', 'Grundstück in Aussicht' => 'In Aussicht', 'Noch auf der Suche' => 'Auf der Suche'][$grund] ?? '';
  return ['davinci_haustyp' => $ht, 'davinci_grundstueck' => $gr, 'davinci_quelle' => $quelle];
}
