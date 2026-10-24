<?php
/* =========================================================
   DAVINCI – E-Mail-Bausteine und alle Vorlagen
   Texte der E-Mails können hier angepasst werden.
   ========================================================= */
date_default_timezone_set('Europe/Berlin');

const DV_EMPFAENGER = 'bauen@davinci-homes.de';   // interne Benachrichtigungen
const DV_ABSENDER   = 'bauen@davinci-homes.de';   // Absender (Postfach muss in SiteGround existieren)
const DV_NAME       = 'DAVINCI';
const DV_SEITE      = 'https://davinci-homes.de';
const DV_PDF        = DV_SEITE . '/downloads/DAVINCI_Design-Book_Vol01.pdf';
const DV_TELEFON    = '+49 157 868 328 59';
const DV_ADRESSE    = 'Kaiser-Friedrich-Ring 43 · 40545 Düsseldorf';
const DV_FIRMA      = 'Fivemeters UG (haftungsbeschränkt)';
const DV_TERMIN     = DV_SEITE . '/#termin';

// Inspirationsstrecke nach dem Design-Book: Tage nach der Bestätigung
const DV_STRECKE = ['inspiration1' => 2, 'inspiration2' => 6, 'inspiration3' => 12, 'inspiration4' => 20];

function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
const F = "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;";

/* ---------- Bausteine ---------- */
function m_layout($preheader, $inhalt, $abmelden = null) {
  $ab = $abmelden ? '<br><br><span style="color:#6e6e73;">Sie erhalten diese E-Mail, weil Sie Inspiration von DAVINCI angefordert haben. <a href="' . h($abmelden) . '" style="color:#9a9a9f;">Abmelden</a></span>' : '';
  return '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>DAVINCI</title></head>
<body style="margin:0;padding:0;background:#f0f0ee;">
<div style="display:none;max-height:0;overflow:hidden;">' . h($preheader) . '</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f0ee;"><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;">
  <tr><td style="background:#0b0b0b;padding:28px 40px;"><a href="' . DV_SEITE . '"><img src="' . DV_SEITE . '/assets/img/email-logo.png" width="170" alt="DAVINCI" style="display:block;border:0;width:170px;height:auto;"></a></td></tr>
  ' . $inhalt . '
  <tr><td style="background:#0b0b0b;padding:34px 40px;' . F . 'color:#9a9a9f;font-size:13px;line-height:1.7;">
    <div style="color:#ffffff;font-size:20px;font-weight:300;letter-spacing:-.01em;margin-bottom:12px;">Designed to stay.</div>
    ' . h(DV_FIRMA) . ' · ' . h(DV_ADRESSE) . '<br>' . h(DV_TELEFON) . ' · <a href="mailto:' . DV_EMPFAENGER . '" style="color:#9dbcc4;text-decoration:none;">' . DV_EMPFAENGER . '</a><br>
    <a href="' . DV_SEITE . '" style="color:#9dbcc4;text-decoration:none;">davinci-homes.de</a> · <a href="' . DV_SEITE . '/impressum.php" style="color:#9a9a9f;">Impressum</a> · <a href="' . DV_SEITE . '/datenschutz.php" style="color:#9a9a9f;">Datenschutz</a>' . $ab . '
  </td></tr>
</table>
</td></tr></table></body></html>';
}
function m_p($t) { return '<p style="margin:0 0 16px;' . F . 'font-size:16px;line-height:1.6;color:#3a3a3c;">' . $t . '</p>'; }
function m_klein($t) { return '<p style="margin:0 0 24px;' . F . 'font-size:13px;line-height:1.5;color:#86868b;">' . $t . '</p>'; }
function m_knopf($url, $text, $dunkel = true) {
  $bg = $dunkel ? '#0b0b0b' : '#ffffff'; $fg = $dunkel ? '#ffffff' : '#0b0b0b';
  return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 24px;"><tr><td style="background:' . $bg . ';border:1px solid #0b0b0b;border-radius:999px;">
  <a href="' . h($url) . '" style="display:inline-block;padding:15px 30px;' . F . 'font-size:15px;color:' . $fg . ';text-decoration:none;">' . $text . '</a></td></tr></table>';
}
function m_zeilen($rows) {
  $o = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e6e5e1;margin:8px 0 24px;">';
  foreach ($rows as $k => $v) if ($v !== '' && $v !== null)
    $o .= '<tr><td style="padding:11px 0;border-bottom:1px solid #e6e5e1;' . F . 'font-size:13px;color:#86868b;width:140px;vertical-align:top;">' . h($k) . '</td><td style="padding:11px 0;border-bottom:1px solid #e6e5e1;' . F . 'font-size:15px;color:#1d1d1f;">' . nl2br(h($v)) . '</td></tr>';
  return $o . '</table>';
}
function m_liste($items) {
  $o = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:4px 0 24px;">';
  foreach ($items as $i) $o .= '<tr><td style="width:26px;vertical-align:top;padding:7px 0;"><div style="width:14px;height:1px;background:#406973;margin-top:11px;"></div></td><td style="padding:7px 0;' . F . 'font-size:15px;line-height:1.5;color:#3a3a3c;">' . $i . '</td></tr>';
  return $o . '</table>';
}
function m_block($html, $pad = '40px 40px 16px') { return '<tr><td style="padding:' . $pad . ';">' . $html . '</td></tr>'; }
function m_titel($eyebrow, $t) {
  return '<div style="' . F . 'font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#406973;margin-bottom:12px;">' . $eyebrow . '</div>
  <h1 style="margin:0 0 22px;' . F . 'font-size:32px;line-height:1.15;font-weight:300;letter-spacing:-.02em;color:#1d1d1f;">' . $t . '</h1>';
}
function m_bild($datei, $alt) {
  $ki = strpos($datei, 'dv-') === 0 ? '<tr><td style="padding:6px 40px 0;' . F . 'font-size:11px;color:#8a8a8f;text-align:right;">KI-Visualisierung</td></tr>' : '';
  return '<tr><td style="padding:0;"><img src="' . DV_SEITE . '/assets/img/' . $datei . '" width="600" alt="' . h($alt) . '" style="display:block;width:100%;height:auto;border:0;"></td></tr>' . $ki;
}
function m_kasten($titel, $html) {
  return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0f0ee;"><tr><td style="padding:30px 32px;">'
    . '<div style="' . F . 'font-size:21px;font-weight:300;color:#1d1d1f;margin-bottom:10px;">' . $titel . '</div>' . $html . '</td></tr></table>';
}
function m_gruss() { return m_p('Herzliche Grüße<br>Ihr DAVINCI Team'); }

/* ---------- Versand ---------- */
function m_senden($an, $betreff, $html, $absName = DV_NAME, $replyTo = null) {
  $grenze = 'dv' . bin2hex(random_bytes(8));
  $h2 = preg_replace('#<div style="display:none[^>]*>.*?</div>#s', '', $html);
  $h2 = preg_replace('#<a href="(?!mailto)([^"]+)"[^>]*>(.*?)</a>#s', '$2 ($1)', $h2);
  $plain = trim(html_entity_decode(strip_tags(preg_replace(['#<br\s*/?>#i', '#</(p|tr|h1|div)>#i', '#</td>#i'], ["\n", "\n", '  '], $h2)), ENT_QUOTES, 'UTF-8'));
  $plain = preg_replace(["/[ \t]+\n/", "/\n{3,}/"], ["\n", "\n\n"], $plain);
  $kopf  = 'From: =?UTF-8?B?' . base64_encode($absName) . '?= <' . DV_ABSENDER . ">\r\n";
  if ($replyTo) $kopf .= "Reply-To: $replyTo\r\n";
  $kopf .= "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; boundary=\"$grenze\"\r\n";
  $body  = "--$grenze\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . quoted_printable_encode($plain) . "\r\n";
  $body .= "--$grenze\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . quoted_printable_encode($html) . "\r\n--$grenze--";
  return @mail($an, '=?UTF-8?B?' . base64_encode($betreff) . '?=', $body, $kopf, '-f' . DV_ABSENDER);
}

function datum_de($ts) {
  $tage = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
  $mon  = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
  return $tage[(int) date('w', $ts)] . ', ' . date('j', $ts) . '. ' . $mon[(int) date('n', $ts)] . ' ' . date('Y', $ts) . ', ' . date('H:i', $ts) . ' Uhr';
}

/* =========================================================
   VORLAGEN
   Jede Funktion liefert [Betreff, HTML]
   $d = Daten (name, email, …), $links = [bestaetigen, abmelden]
   ========================================================= */

/* 1 · Kontaktformular – Bestätigung an den Kunden */
function v_anfrage($d) {
  $istTermin = !empty($d['termin']);
  $t = m_titel($istTermin ? 'Ihre Terminanfrage' : 'Ihre Anfrage', 'Vielen Dank für Ihre Nachricht.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p($istTermin ? 'wir haben Ihre Terminanfrage erhalten und melden uns innerhalb eines Werktags mit einem konkreten Terminvorschlag. Hier noch einmal Ihre Angaben:' : 'wir haben Ihre Anfrage erhalten und melden uns persönlich bei Ihnen. Hier noch einmal Ihre Angaben:')
    . m_zeilen(($d['termin'] ?? []) + ['Haustyp' => $d['haustyp'] ?? '', 'Grundstück' => $d['grund'] ?? '', 'Telefon' => $d['telefon'] ?? '', 'Nachricht' => $d['nachricht'] ?? ''])
    . ($istTermin ? '' : m_p('Sie möchten direkt einen Termin vereinbaren? Sagen Sie uns, wann es Ihnen passt.') . m_knopf(DV_TERMIN, 'Termin vereinbaren'));
  $k = m_kasten('Schon einmal stöbern?', m_p('In unserem Design-Book finden Sie Grundrisse, Ideen zu Grundstück und Ausrichtung sowie Inspiration für die gesamte DAVINCI Welt.') . m_knopf(DV_PDF, 'Design-Book herunterladen &nbsp;→', false));
  return ['Ihre Anfrage bei DAVINCI', m_layout('Vielen Dank, wir melden uns persönlich bei Ihnen.', m_bild('dv-01-flachdach-weiss-abend.jpg', 'DAVINCI Haus aus Holz und Glas') . m_block($t) . m_block($k . '<div style="height:16px"></div>' . m_gruss(), '0 40px 40px'))];
}

/* 2 · Design-Book – Auslieferung (PDF oder Print) */
function v_designbook($d, $links = []) {
  $print = !empty($d['print']);
  $t = m_titel('Design-Book · Vol. 01', 'Willkommen in der Welt von DAVINCI.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('vielen Dank für Ihr Interesse. ' . ($print
        ? 'Ihr gedrucktes Design-Book geht in den nächsten Tagen an folgende Adresse auf die Reise: <strong style="font-weight:600;color:#1d1d1f;">' . h($d['adresse']) . '</strong>. Damit Sie nicht warten müssen, finden Sie hier schon die digitale Ausgabe.'
        : 'Ihr persönliches Design-Book steht ab sofort für Sie bereit.'))
    . m_p('Auf 48 Seiten zeigen wir Ihnen, wie wir Grundstücke lesen und Räume nach der Sonne ausrichten, welche Grundrisse zu welcher Lebenssituation passen und wie Sauna, Garage und Carport Teil der Architektur werden.')
    . m_knopf(DV_PDF, 'Design-Book herunterladen &nbsp;→')
    . m_klein('PDF · 48 Seiten · ca. 10 MB');
  $k = '';
  if (!empty($links['bestaetigen'])) {
    $k = m_kasten('Noch ein Klick für mehr Inspiration', m_p('Sie möchten in den nächsten Wochen Ideen zu Grundstück, Grundrissen und Ausstattung von uns erhalten? Bitte bestätigen Sie das kurz. Ohne Bestätigung schreiben wir Ihnen nicht weiter.') . m_knopf($links['bestaetigen'], 'Ja, Inspiration erhalten', false));
  } else {
    $k = m_kasten('Der nächste Schritt', m_p('Markieren Sie beim Lesen, was Ihnen gefällt, und bringen Sie Ihre Notizen zum Erstgespräch mit. Wir freuen uns darauf, Sie kennenzulernen.') . m_knopf(DV_TERMIN, 'Termin vereinbaren', false));
  }
  return ['Ihr DAVINCI Design-Book', m_layout('Ihr Design-Book steht zum Download bereit.', m_bild('dv-01-flachdach-weiss-abend.jpg', 'DAVINCI Haus aus Holz und Glas') . m_block($t) . m_block($k . '<div style="height:16px"></div>' . m_gruss(), '0 40px 40px'))];
}

/* 3 · Double-Opt-in bestätigt – Willkommen in der Inspirationsstrecke */
function v_willkommen($d, $links) {
  $t = m_titel('Inspiration', 'Schön, dass Sie dabei sind.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('vielen Dank für Ihre Bestätigung. In den nächsten Wochen schicken wir Ihnen vier kurze E-Mails, jeweils zu einem Thema, das beim Planen eines Hauses den Unterschied macht:')
    . m_liste(['<strong style="font-weight:600;color:#1d1d1f;">Grundstück und Sonne:</strong> wie wir einen Ort lesen', '<strong style="font-weight:600;color:#1d1d1f;">Grundrisse:</strong> welches Haus zu Ihrem Leben passt', '<strong style="font-weight:600;color:#1d1d1f;">Wohlfühlorte:</strong> Sauna, Galerie, Garage und Carport', '<strong style="font-weight:600;color:#1d1d1f;">Ihr Erstgespräch:</strong> wie es weitergeht'])
    . m_p('Sie können sich jederzeit mit einem Klick abmelden.') . m_gruss();
  return ['Willkommen bei DAVINCI', m_layout('Vier E-Mails, vier Themen für Ihr Haus.', m_bild('dv-09-bungalow-flach-garten.jpg', 'DAVINCI Haus aus Holz und Glas') . m_block($t, '40px 40px 24px'), $links['abmelden'])];
}

/* 4 · Inspirationsstrecke (nur nach Double-Opt-in) */
function v_inspiration1($d, $links) {
  $t = m_titel('Inspiration 1 von 4 · Grundstück', 'Wo geht bei Ihnen die Sonne auf?')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('bevor wir die erste Linie zeichnen, sehen wir uns Ihr Grundstück genau an. Die Himmelsrichtung entscheidet darüber, wie sich ein Haus im Alltag anfühlt:')
    . m_liste(['<strong style="font-weight:600;color:#1d1d1f;">Osten:</strong> Schlafen und Frühstück im Morgenlicht', '<strong style="font-weight:600;color:#1d1d1f;">Süden:</strong> Wohnen und Terrasse hinter großen Glasflächen', '<strong style="font-weight:600;color:#1d1d1f;">Westen:</strong> Abendterrasse und Sauna', '<strong style="font-weight:600;color:#1d1d1f;">Norden:</strong> Eingang, Technik und Bäder als ruhige Schicht zur Straße'])
    . m_p('Unser Tipp: Stellen Sie sich zu verschiedenen Tageszeiten auf Ihr Grundstück und machen Sie Fotos in alle Richtungen. Für das Erstgespräch ist das Gold wert.')
    . m_knopf(DV_PDF, 'Kapitel „Grundstück“ im Design-Book') . m_gruss();
  return ['Wo geht bei Ihnen die Sonne auf?', m_layout('Wie wir ein Grundstück lesen.', m_bild('dv-10-hang-pultdach-terrasse.jpg', 'DAVINCI Haus am Hang') . m_block($t, '40px 40px 24px'), $links['abmelden'])];
}
function v_inspiration2($d, $links) {
  $t = m_titel('Inspiration 2 von 4 · Grundrisse', 'Welches Haus passt zu Ihrem Leben?')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('ein Grundriss ist mehr als eine Anzahl von Zimmern. Er beschreibt, wie Sie leben möchten, heute und in zwanzig Jahren. Drei Fragen helfen bei der Orientierung:')
    . m_liste(['Wer wird im Haus leben, heute und in zehn Jahren?', 'Eine Ebene oder zwei?', 'Wo verbringen Sie die meiste Zeit des Tages?'])
    . m_p('Im DAVINCI Holzskelett tragen die Stützen, nicht die Innenwände. Deshalb lassen sich Räume später verändern: Aus zwei Kinderzimmern werden Arbeitszimmer und Gästezimmer, später vielleicht ein großer Raum.')
    . m_knopf(DV_PDF, 'Grundrisse und Wohnsituationen ansehen') . m_gruss();
  return ['Welches Haus passt zu Ihrem Leben?', m_layout('Grundrisse, die mitwachsen.', m_bild('galerie.jpg', 'Essbereich unter einer Galerie') . m_block($t, '40px 40px 24px'), $links['abmelden'])];
}
function v_inspiration3($d, $links) {
  $t = m_titel('Inspiration 3 von 4 · Wohlfühlorte', 'Sauna, Galerie, Garage.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('die schönsten Orte in einem Haus sind oft die, die man anfangs für ein Extra hält. Wir planen sie von Anfang an mit, damit sie selbstverständlich wirken:')
    . m_liste(['<strong style="font-weight:600;color:#1d1d1f;">Die Sauna</strong> als privates Spa am Schlafbereich oder mit Glasfront zum Garten', '<strong style="font-weight:600;color:#1d1d1f;">Die Galerie</strong> mit Luftraum, die zwei Ebenen zu einem Raum verbindet', '<strong style="font-weight:600;color:#1d1d1f;">Garage und Carport</strong> in derselben Holz-Glas-Sprache, als Schutz für den Garten'])
    . m_p('Schreiben Sie sich schon einmal eine Wunschliste. Was gehört für Sie unbedingt dazu?')
    . m_knopf(DV_PDF, 'Ausstattung im Design-Book') . m_gruss();
  return ['Sauna, Galerie, Garage: Ihre Wunschliste', m_layout('Die Orte, an denen man ankommt.', m_bild('galerie.jpg', 'Luftraum mit Galerie') . m_block($t, '40px 40px 24px'), $links['abmelden'])];
}
function v_inspiration4($d, $links) {
  $t = m_titel('Inspiration 4 von 4 · Ihr Haus', 'Lassen Sie uns sprechen.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('Sie kennen jetzt die DAVINCI Welt. Der nächste Schritt ist ein persönliches Gespräch über Ihr Grundstück, Ihre Wünsche und Ihr Budget. Unverbindlich, in Düsseldorf, bei Ihnen vor Ort oder per Video.')
    . m_p('Bringen Sie mit, was Sie haben:')
    . m_liste(['Lageplan und Bebauungsplan, falls vorhanden', 'Fotos vom Grundstück', 'Ihre markierten Seiten aus dem Design-Book', 'Ihre Wunschliste'])
    . m_knopf(DV_TERMIN, 'Termin vereinbaren')
    . m_p('Oder rufen Sie uns an: ' . h(DV_TELEFON)) . m_gruss();
  return ['Lassen Sie uns über Ihr Haus sprechen', m_layout('Ihr Erstgespräch bei DAVINCI.', m_bild('dv-03-giebel-winter-tag.jpg', 'DAVINCI Einfamilienhaus') . m_block($t, '40px 40px 24px'), $links['abmelden'])];
}

/* 5 · Termin gebucht */
function v_termin($d) {
  $wann = !empty($d['start']) ? datum_de($d['start']) : 'wie im Kalender gebucht';
  $t = m_titel('Ihr Termin', 'Wir freuen uns auf Sie.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('vielen Dank für Ihre Terminbuchung. Hier die Eckdaten:')
    . m_zeilen(['Termin' => $wann, 'Art' => $d['art'] ?? 'Persönliches Erstgespräch', 'Ort' => $d['ort'] ?? 'Wie in der Kalendereinladung angegeben'])
    . m_p('Die Kalendereinladung erhalten Sie in einer separaten E-Mail. Damit wir das Gespräch optimal nutzen können, bringen Sie gern mit:')
    . m_liste(['Lageplan oder Flurkarte Ihres Grundstücks', 'Bebauungsplan, falls vorhanden', 'Fotos vom Grundstück und der Umgebung', 'Ihre Raumwünsche und Ihren Budgetrahmen', 'Markierte Seiten aus dem Design-Book'])
    . m_knopf(DV_PDF, 'Design-Book herunterladen', false)
    . m_p('Sie müssen den Termin verschieben? Antworten Sie einfach auf diese E-Mail oder rufen Sie uns an: ' . h(DV_TELEFON)) . m_gruss();
  return ['Ihr Termin bei DAVINCI', m_layout('Ihr Termin ist bestätigt.', m_bild('galerie.jpg', 'DAVINCI Innenraum') . m_block($t, '40px 40px 24px'))];
}
/* 6 · Terminerinnerung (24 Stunden vorher) */
function v_erinnerung($d) {
  $t = m_titel('Erinnerung', 'Morgen sehen wir uns.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('wir freuen uns auf unser Gespräch am <strong style="font-weight:600;color:#1d1d1f;">' . h(datum_de($d['start'])) . '</strong>.')
    . m_p('Eine kurze Checkliste für morgen:')
    . m_liste(['Lageplan, Bebauungsplan und Fotos vom Grundstück', 'Ihre Wunschliste: Räume, Sauna, Garage, Galerie', 'Ihr Budgetrahmen und Zeitplan', 'Alle Fragen, die Ihnen auf dem Herzen liegen'])
    . m_p('Falls etwas dazwischenkommt, antworten Sie einfach auf diese E-Mail oder rufen Sie uns an: ' . h(DV_TELEFON)) . m_gruss();
  return ['Erinnerung: Ihr Termin bei DAVINCI morgen', m_layout('Morgen sehen wir uns.', m_bild('dv-03-giebel-winter-tag.jpg', 'DAVINCI Einfamilienhaus') . m_block($t, '40px 40px 24px'))];
}

/* 7 · Interne Benachrichtigung */
function v_intern($typ, $d, $zeilen, $hinweis = '') {
  $t = m_titel('Neu · ' . h($typ), h($d['name']))
    . m_zeilen($zeilen)
    . ($hinweis ? m_p('<strong style="color:#b3261e;">' . h($hinweis) . '</strong>') : '')
    . m_knopf('mailto:' . $d['email'], 'Antworten');
  return [$typ . ': ' . $d['name'], m_layout($typ . ' von ' . $d['name'], m_block($t))];
}

/* Grundriss · Versand nach Anfrage auf der Häuser-Seite */
function v_grundriss($d, $pdfUrl) {
  $haus = h($d['haustyp'] ?: 'Ihr DAVINCI Haus');
  $t = m_titel('Grundriss · ' . $haus, 'Ihr Grundriss ist da.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . ($pdfUrl
        ? m_p('vielen Dank für Ihr Interesse am ' . $haus . '. Hier ist der Grundriss als PDF, mit Maßen und Raumaufteilung.') . m_knopf($pdfUrl, 'Grundriss herunterladen')
        : m_p('vielen Dank für Ihr Interesse am ' . $haus . '. Den Grundriss bereiten wir gerade für Sie auf und schicken ihn Ihnen in Kürze persönlich zu.'))
    . m_p('Jeder Grundriss ist ein Ausgangspunkt. Innerhalb des DAVINCI Systems passen wir Größe, Räume und Ausstattung an Ihr Grundstück und Ihr Leben an.');
  $k = m_kasten('Der nächste Schritt', m_p('Sprechen Sie mit uns über Ihr Grundstück und Ihre Wünsche. Persönlich, per Video oder bei Ihnen vor Ort.') . m_knopf(DV_TERMIN, 'Termin vereinbaren &nbsp;→', false));
  return ['Ihr Grundriss: ' . ($d['haustyp'] ?: 'DAVINCI'), m_layout('Ihr Grundriss für ' . ($d['haustyp'] ?: 'Ihr DAVINCI Haus') . '.', m_bild('dv-01-flachdach-weiss-abend.jpg', 'DAVINCI Haus aus Holz und Glas') . m_block($t) . m_block($k . '<div style="height:16px"></div>' . m_gruss(), '0 40px 40px'))];
}

/* 8 · Vorlagen zum manuellen Versand (z. B. aus Outlook oder HubSpot) */
function v_danke_termin($d) {
  $t = m_titel('Nach unserem Gespräch', 'Vielen Dank für Ihre Zeit.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('vielen Dank für das offene und inspirierende Gespräch. Wir haben viele Ideen für Ihr Haus mitgenommen und machen uns jetzt an die Arbeit.')
    . m_p('So geht es weiter:')
    . m_liste(['Wir fassen Ihre Wünsche und die Vorgaben Ihres Grundstücks zusammen.', 'Wir entwickeln aus dem DAVINCI System eine erste Skizze.', 'Wir melden uns mit einem Terminvorschlag für die Präsentation.'])
    . m_p('Wenn Ihnen in der Zwischenzeit noch etwas einfällt, antworten Sie einfach auf diese E-Mail.') . m_gruss();
  return ['Vielen Dank für das Gespräch', m_layout('Wie es jetzt weitergeht.', m_bild('skizze.jpg', 'Skizze eines DAVINCI Hauses') . m_block($t, '40px 40px 24px'))];
}
function v_termin_verpasst($d) {
  $t = m_titel('Ihr Termin', 'Schade, dass es nicht geklappt hat.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('heute haben wir uns leider verpasst. Das passiert, kein Problem. Suchen Sie sich einfach einen neuen Termin aus, der besser passt.')
    . m_knopf(DV_TERMIN, 'Neuen Termin wählen')
    . m_p('Oder rufen Sie uns an: ' . h(DV_TELEFON)) . m_gruss();
  return ['Neuer Termin bei DAVINCI?', m_layout('Suchen Sie sich einen neuen Termin aus.', m_bild('dv-19-schwarz-giebel-garten-nacht.jpg', 'DAVINCI Haus am Abend') . m_block($t, '40px 40px 24px'))];
}
function v_buch_versendet($d) {
  $t = m_titel('Design-Book', 'Ihr Buch ist unterwegs.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('Ihr gedrucktes DAVINCI Design-Book ist heute auf den Weg zu Ihnen gegangen und kommt in den nächsten Tagen an.')
    . m_p('Nehmen Sie sich Zeit, markieren Sie, was Ihnen gefällt, und bringen Sie das Buch gern zum Erstgespräch mit.')
    . m_knopf(DV_TERMIN, 'Termin vereinbaren') . m_gruss();
  return ['Ihr Design-Book ist unterwegs', m_layout('Ihr gedrucktes Design-Book ist auf dem Weg.', m_bild('dv-01-flachdach-weiss-abend.jpg', 'DAVINCI Haus aus Holz und Glas') . m_block($t, '40px 40px 24px'))];
}
function v_entwurf_bereit($d) {
  $t = m_titel('Ihr Entwurf', 'Ihr Haus nimmt Form an.')
    . m_p('Guten Tag ' . h($d['name']) . ',')
    . m_p('unser Design-Team hat den ersten Entwurf für Ihr Haus fertiggestellt. Wir möchten ihn Ihnen gern persönlich vorstellen, mit Grundrissen, Ansichten und einer ersten Kostenschätzung.')
    . m_knopf(DV_TERMIN, 'Präsentationstermin wählen')
    . m_p('Wir freuen uns sehr auf diesen Moment.') . m_gruss();
  return ['Ihr Entwurf ist fertig', m_layout('Wir möchten Ihnen Ihren Entwurf vorstellen.', m_bild('dv-07-hang-pultdach-garage.jpg', 'DAVINCI Haus aus Holz und Glas') . m_block($t, '40px 40px 24px'))];
}
