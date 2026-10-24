<?php
/* DAVINCI intern – nächstes Paket eines laufenden Newsletters senden (wird von der Versandseite aufgerufen) */
require __DIR__ . '/lib/start.php';
seite_schuetzen();
if (!ist_post()) json_aus(['ok' => false], 405);
json_aus(['ok' => true] + versand_schritt((string) p('id')));
