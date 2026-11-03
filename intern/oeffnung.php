<?php
/* DAVINCI intern – Newsletter geöffnet (unsichtbares Pixel, öffentlich) */
require __DIR__ . '/lib/start.php';
$k = preg_replace('/[^a-f0-9]/', '', (string) g('k'));
$c = preg_replace('/[^a-f0-9]/', '', (string) g('c'));
if ($k && $c && hash_equals(nl_sig($k, $c), (string) g('s'))) { try { nl_ereignis($k, $c, 'o'); } catch (Throwable $t) {} }
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
