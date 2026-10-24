<?php
/* =========================================================
   DAVINCI intern – persönliche E-Mails mit Signatur
   Die Signatur entsteht aus dem Benutzerprofil (Name, Rolle, Telefon) und den Pflichtangaben.
   ========================================================= */
declare(strict_types=1);

/** Pflichtangaben für geschäftliche E-Mails (§ 35a GmbHG), änderbar unter Einstellungen. */
function pflichtangaben(): string {
  return (string) (einstellungen()['pflichtangaben'] ?? '')
    ?: 'Fivemeters UG (haftungsbeschränkt) · Sitz Düsseldorf · Amtsgericht Düsseldorf HRB 92800 · Geschäftsführer: Marco Lei Zimmermann';
}

function signatur_html(array $b): string {
  $web = website_url();
  $F = "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;";
  $zeilen = [];
  if (!empty($b['telefon'])) $zeilen[] = 'T&nbsp;<a href="tel:' . e(preg_replace('/[^\d+]/', '', $b['telefon'])) . '" style="color:#1d1d1f;text-decoration:none;">' . e($b['telefon']) . '</a>';
  if (!empty($b['mobil'])) $zeilen[] = 'M&nbsp;<a href="tel:' . e(preg_replace('/[^\d+]/', '', $b['mobil'])) . '" style="color:#1d1d1f;text-decoration:none;">' . e($b['mobil']) . '</a>';
  $mail = $b['email'] ?: 'bauen@davinci-homes.de';
  $zeilen[] = '<a href="mailto:' . e($mail) . '" style="color:#1d1d1f;text-decoration:none;">' . e($mail) . '</a>';
  return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:28px;border-collapse:collapse;">
  <tr><td style="padding:0 0 14px;' . $F . '">
    <div style="font-size:15px;line-height:1.4;color:#1d1d1f;font-weight:600;">' . e($b['name']) . '</div>'
    . (!empty($b['position']) ? '<div style="font-size:13px;line-height:1.4;color:#6e6e73;">' . e($b['position']) . '</div>' : '') . '
  </td></tr>
  <tr><td style="padding:14px 0 12px;border-top:1px solid #e6e5e1;">
    <a href="' . $web . '"><img src="' . $web . '/assets/img/email-signatur.png" width="150" alt="DAVINCI" style="display:block;border:0;width:150px;height:auto;"></a>
  </td></tr>
  <tr><td style="' . $F . 'font-size:13px;line-height:1.6;color:#1d1d1f;">' . implode('&nbsp;&nbsp;·&nbsp;&nbsp;', $zeilen) . '<br>
    <a href="' . $web . '" style="color:#406973;text-decoration:none;">davinci-homes.de</a>&nbsp;&nbsp;·&nbsp;&nbsp;Kaiser-Friedrich-Ring 43, 40545 Düsseldorf</td></tr>
  <tr><td style="padding-top:10px;' . $F . 'font-size:13px;color:#1d1d1f;font-style:italic;letter-spacing:.01em;">Designed to stay.</td></tr>
  <tr><td style="padding-top:12px;' . $F . 'font-size:10.5px;line-height:1.5;color:#9a9a9f;">' . e(pflichtangaben()) . '</td></tr>
</table>';
}

/** Schlichte persönliche E-Mail: Text wie geschrieben, darunter die Signatur. Kein Newsletter-Rahmen. */
function email_html(string $betreff, string $text, array $absender, ?array $kontakt = null): string {
  $F = "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;";
  $inhalt = str_replace(['font-size:16px;line-height:1.65;color:#3a3a3c;', 'margin:0 0 18px;'], ['font-size:15px;line-height:1.6;color:#1d1d1f;', 'margin:0 0 14px;'], nl_text($text, $kontakt));
  return '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>' . e($betreff) . '</title></head>
<body style="margin:0;padding:0;background:#ffffff;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:24px 20px;' . $F . '">
<div style="max-width:620px;">' . $inhalt . signatur_html($absender) . '</div>
</td></tr></table></body></html>';
}

/** Absenderadresse: eigene Adresse, wenn sie zur Domain gehört (Postfach bei SiteGround), sonst bauen@. */
function absender_fuer(array $b): string {
  $m = strtolower(trim((string) ($b['email'] ?? '')));
  return preg_match('/@davinci-homes\.de$/', $m) ? $m : (string) einstellungen()['absender'];
}
