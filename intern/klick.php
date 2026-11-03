<?php
/* DAVINCI intern – Klick in einem Newsletter zählen und weiterleiten (öffentlich) */
require __DIR__ . '/lib/start.php';
header('X-Robots-Tag: noindex, nofollow');
$k = preg_replace('/[^a-f0-9]/', '', (string) g('k'));
$c = preg_replace('/[^a-f0-9]/', '', (string) g('c'));
$u = (string) g('u');
$ok = $k && $c && preg_match('#^https?://#i', $u) && hash_equals(nl_sig($k, $c, $u), (string) g('s'));
if ($ok) { try { nl_ereignis($k, $c, 'k'); } catch (Throwable $t) {} }
header('Location: ' . ($ok ? $u : website_url()), true, 302);
