<?php
/* DAVINCI – kleine HTTP-Hilfe für API-Aufrufe (HubSpot, reCAPTCHA) */
function http_anfrage($methode, $url, $kopf = [], $body = null, $timeout = 8) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST  => $methode,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => $timeout,
    CURLOPT_HTTPHEADER     => $kopf,
  ]);
  if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
  $antwort = curl_exec($ch);
  $status  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $fehler  = curl_error($ch);
  curl_close($ch);
  return [$status, $antwort === false ? null : json_decode($antwort, true), $fehler];
}
function dv_log($text) {
  $dir = __DIR__ . '/../data';
  if (!is_dir($dir)) @mkdir($dir, 0750, true);
  @file_put_contents($dir . '/protokoll.log', date('c') . ' ' . $text . "\n", FILE_APPEND);
}
