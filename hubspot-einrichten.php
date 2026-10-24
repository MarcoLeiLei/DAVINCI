<?php
/* =========================================================
   DAVINCI – HubSpot einmalig einrichten
   Legt die Deal-Pipelines mit allen Phasen und die Deal-Eigenschaften an.
   Aufruf: https://davinci-homes.de/hubspot-einrichten.php (nur eingeloggt)
   Danach diese Datei wieder löschen.
   ========================================================= */
require __DIR__ . '/gate.php';
require __DIR__ . '/lib/hubspot.php';
header('Content-Type: text/html; charset=utf-8');

$PIPELINES = [
  ['label' => 'DAVINCI Verkauf', 'stages' => [
    ['Anfrage Kontaktformular', 0.10], ['Termin gebucht', 0.15], ['Design-Book heruntergeladen', 0.05],
    ['telefoniert', 0.10], ['Termin persönlich vereinbart', 0.25], ['hot LEAD', 0.45], ['verloren', 0.0],
  ]],
];
$AUSWAHL = fn(array $werte) => array_map(fn($w, $i) => ['label' => $w, 'value' => $w, 'displayOrder' => $i], $werte, array_keys($werte));
$EIGENSCHAFTEN = [
  ['davinci_haustyp', 'Haustyp', 'enumeration', 'select', ['Einfamilienhaus', 'Bungalow', 'Individuell']],
  ['davinci_wohnflaeche', 'Wohnfläche (m²)', 'number', 'number', null],
  ['davinci_ausbaustufe', 'Ausbaustufe', 'enumeration', 'select', ['Ausbauhaus', 'Schlüsselfertig']],
  ['davinci_grundstueck', 'Grundstück', 'enumeration', 'select', ['Vorhanden', 'In Aussicht', 'Auf der Suche']],
  ['davinci_extras', 'Gewünschte Extras', 'enumeration', 'checkbox', ['Galerie', 'Geschosserhöhung', 'Sauna', 'Garage', 'Carport', 'Keller', 'Pool']],
  ['davinci_baubeginn', 'Geplanter Baubeginn', 'date', 'date', null],
  ['davinci_ort', 'Ort des Grundstücks', 'string', 'text', null],
  ['davinci_quelle', 'Lead-Quelle', 'enumeration', 'select', ['Design-Book', 'Kontaktformular', 'Terminbuchung', 'Empfehlung', 'Partner', 'Messe', 'Telefon', 'Sonstiges']],
  ['davinci_verlustgrund', 'Verlustgrund', 'enumeration', 'select', ['Budget', 'Kein Grundstück', 'Wettbewerber', 'Zeitlich verschoben', 'Finanzierung abgelehnt', 'Kein Kontakt mehr', 'Sonstiges']],
];

$log = [];
$ok   = function ($t) use (&$log) { $log[] = ['ok', $t]; };
$warn = function ($t) use (&$log) { $log[] = ['warn', $t]; };
$err  = function ($t) use (&$log) { $log[] = ['err', $t]; };
$grund = fn($r) => is_array($r) ? ($r['message'] ?? json_encode($r)) : 'keine Antwort';

if (!HUBSPOT_TOKEN) {
  $err('In lib/config.php ist noch kein HUBSPOT_TOKEN (Service Key) eingetragen.');
} elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  /* ---------- Pipelines ---------- */
  [$s, $r] = hs_api('GET', '/crm/v3/pipelines/deals');
  $vorhanden = [];
  foreach (($r['results'] ?? []) as $p) $vorhanden[$p['label']] = $p;
  $standard = $r['results'][0] ?? null;

  foreach ($PIPELINES as $n => $pl) {
    if (isset($vorhanden[$pl['label']])) { $warn("Pipeline „{$pl['label']}“ gibt es bereits, nichts geändert."); continue; }
    $body = ['label' => $pl['label'], 'displayOrder' => $n, 'stages' => array_map(
      fn($st, $i) => ['label' => $st[0], 'displayOrder' => $i, 'metadata' => ['probability' => number_format($st[1], 2, '.', '')]],
      $pl['stages'], array_keys($pl['stages']))];
    [$s, $r] = hs_api('POST', '/crm/v3/pipelines/deals', $body);
    if ($s >= 200 && $s < 300) { $ok("Pipeline „{$pl['label']}“ mit " . count($pl['stages']) . ' Phasen angelegt.'); continue; }

    // Kein Platz für eine weitere Pipeline (z. B. kostenloses Konto): Standard-Pipeline umbauen
    if ($n === 0 && $standard && !isset($_POST['nicht_umbauen'])) {
      $body['displayOrder'] = $standard['displayOrder'] ?? 0;
      [$s2, $r2] = hs_api('PUT', '/crm/v3/pipelines/deals/' . $standard['id'], $body);
      if ($s2 >= 200 && $s2 < 300) { $ok("Neue Pipeline war nicht möglich (" . $grund($r) . "). Stattdessen wurde die Standard-Pipeline zu „{$pl['label']}“ umgebaut."); continue; }
      $err("Pipeline „{$pl['label']}“ konnte weder angelegt noch umgebaut werden: " . $grund($r2));
    } else {
      $err("Pipeline „{$pl['label']}“ nicht angelegt (Status $s): " . $grund($r));
    }
  }

  /* ---------- Eigenschaften ---------- */
  [$s, $r] = hs_api('POST', '/crm/v3/properties/deals/groups', ['name' => 'davinci', 'label' => 'DAVINCI', 'displayOrder' => 0]);
  if ($s >= 300 && $s !== 409) $warn('Eigenschaftsgruppe „DAVINCI“: ' . $grund($r) . ' – die Felder landen dann in der Standardgruppe.');
  $gruppe = ($s < 300 || $s === 409) ? 'davinci' : 'dealinformation';
  foreach ($EIGENSCHAFTEN as [$name, $label, $typ, $feld, $werte]) {
    $body = ['name' => $name, 'label' => $label, 'type' => $typ, 'fieldType' => $feld, 'groupName' => $gruppe];
    if ($werte) $body['options'] = $AUSWAHL($werte);
    [$s, $r] = hs_api('POST', '/crm/v3/properties/deals', $body);
    if ($s >= 200 && $s < 300) $ok("Eigenschaft „{$label}“ angelegt.");
    elseif ($s === 409) $warn("Eigenschaft „{$label}“ gibt es bereits.");
    else $err("Eigenschaft „{$label}“ nicht angelegt (Status $s): " . $grund($r));
  }
}
?><!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HubSpot einrichten – DAVINCI</title>
<style>@font-face{font-family:Outfit;src:url(/assets/fonts/outfit.woff2)}body{font-family:Outfit,Arial,sans-serif;font-weight:300;background:#f0f0ee;color:#1d1d1f;margin:0;padding:48px 16px}main{max-width:720px;margin:0 auto;background:#fff;padding:40px;border-radius:20px}h1{font-weight:300;font-size:34px;margin:0 0 12px}p{color:#6e6e73;line-height:1.55}ul{list-style:none;padding:0;margin:24px 0}li{padding:12px 14px;border-radius:10px;margin-bottom:8px;line-height:1.45}.ok{background:#e8f3ec}.warn{background:#fff4dc}.err{background:#fde8e6}button{font:inherit;background:#0b0b0b;color:#fff;border:0;border-radius:999px;padding:14px 28px;cursor:pointer}label{display:block;margin:16px 0;color:#6e6e73}</style></head>
<body><main>
<h1>HubSpot einrichten</h1>
<p>Legt die Pipeline „DAVINCI Verkauf“ mit 7 Phasen sowie 9 Deal-Eigenschaften an. Bereits Vorhandenes wird nicht verändert.</p>
<?php if ($log): ?><ul><?php foreach ($log as [$k, $t]): ?><li class="<?= $k ?>"><?= htmlspecialchars($t) ?></li><?php endforeach; ?></ul>
<p><strong>Fertig.</strong> Bitte diese Datei (hubspot-einrichten.php) jetzt vom Server löschen.</p>
<?php else: ?>
<form method="post">
  <label><input type="checkbox" name="nicht_umbauen" value="1"> Standard-Pipeline nicht umbauen, falls keine neue Pipeline angelegt werden kann</label>
  <button type="submit">Jetzt in HubSpot anlegen</button>
</form>
<?php endif; ?>
</main></body></html>
