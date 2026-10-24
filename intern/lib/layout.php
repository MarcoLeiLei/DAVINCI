<?php
/* =========================================================
   DAVINCI intern – Seitenrahmen im Look von davinci-homes.de
   ========================================================= */
declare(strict_types=1);

const SIGNET = '<svg viewBox="0 0 822 1073" aria-hidden="true"><path d="M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"/></svg>';

function icon(string $n): string {
  $p = [
    'home'    => 'M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z',
    'inbox'   => 'M3 13l3-8h12l3 8v6H3zM3 13h5l1 3h6l1-3h5',
    'users'   => 'M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M21 19v-1a4 4 0 0 0-3-3.9M16 3.1a3.5 3.5 0 0 1 0 6.8',
    'board'   => 'M4 4h5v16H4zM10 4h5v10h-5zM16 4h4v13h-4z',
    'check'   => 'M9 11l3 3 8-8M20 12v7a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h11',
    'mail'    => 'M3 6h18v12H3zM3 7l9 6 9-6',
    'plus'    => 'M12 5v14M5 12h14',
    'out'     => 'M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 16l-4-4 4-4M6 12h10',
    'cog'     => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z',
    'arrow'   => 'M5 12h14M13 6l6 6-6 6',
    'search'  => 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-4.3-4.3',
    'up'      => 'M12 19V5M6 11l6-6 6 6',
    'down'    => 'M12 5v14M6 13l6 6 6-6',
    'trash'   => 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
    'upload'  => 'M12 16V4M7 9l5-5 5 5M4 20h16',
    'menu'    => 'M4 7h16M4 12h16M4 17h16',
    'send'    => 'M22 2L11 13M22 2l-7 20-4-9-9-4z',
    'clip'    => 'M21 12l-8.5 8.5a5 5 0 0 1-7-7L14 5a3.3 3.3 0 0 1 4.7 4.7l-8.5 8.5a1.7 1.7 0 0 1-2.4-2.4L15.5 8',
    'clock'   => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2',
  ][$n] ?? '';
  return '<svg class="i" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $p . '"/></svg>';
}

function kopf(string $titel, string $aktiv = '', array $opt = []): void {
  $ich = ich();
  $offen = 0;
  foreach (anfragen() as $a) if (($a['status'] ?? 'neu') === 'neu') $offen++;
  $nav = [
    'index'      => ['Übersicht', 'home', 'index.php'],
    'anfragen'   => ['Anfragen', 'inbox', 'anfragen.php'],
    'kontakte'   => ['Kontakte', 'users', 'kontakte.php'],
    'leads'      => ['Projekte', 'board', 'leads.php'],
    'aufgaben'   => ['Aufgaben', 'check', 'aufgaben.php'],
    'email'      => ['E-Mail', 'send', 'email.php'],
    'newsletter' => ['Newsletter', 'mail', 'newsletter.php'],
  ];
  ?><!DOCTYPE html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title><?= e($titel) ?> · DAVINCI intern</title>
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
<link rel="stylesheet" href="assets/app.css?v=<?= @filemtime(APP . '/assets/app.css') ?>">
<meta name="csrf" content="<?= e(csrf()) ?>">
</head>
<body class="<?= e($opt['body'] ?? '') ?>">
<header class="top">
  <a class="brand" href="index.php"><?= SIGNET ?><span>DAVINCI</span><em>intern</em></a>
  <button class="top__menu" type="button" aria-label="Menü" data-menu><?= icon('menu') ?></button>
  <nav class="top__nav" data-nav>
    <?php foreach ($nav as $k => [$t, $i, $u]): ?>
      <a href="<?= $u ?>" class="<?= $aktiv === $k ? 'is-active' : '' ?>"><?= icon($i) ?><span><?= $t ?></span><?php if ($k === 'anfragen' && $offen): ?><b class="badge"><?= $offen ?></b><?php endif; ?></a>
    <?php endforeach; ?>
  </nav>
  <details class="top__user">
    <summary><span class="avatar"><?= e(mb_strtoupper(mb_substr($ich['name'] ?? '?', 0, 1))) ?></span><span class="top__username"><?= e($ich['name'] ?? '') ?></span></summary>
    <div class="menu">
      <a href="konto.php">Mein Zugang</a>
      <?php if (ist_admin()): ?><a href="benutzer.php">Benutzer</a><a href="einstellungen.php">Einstellungen</a><?php endif; ?>
      <a href="https://davinci-homes.de" target="_blank" rel="noopener">Zur Website</a>
      <form method="post" action="anmelden.php?aktion=abmelden"><?= csrf_feld() ?><button type="submit"><?= icon('out') ?> Abmelden</button></form>
    </div>
  </details>
</header>
<main class="main">
<?php foreach (meldungen() as [$art, $text]): ?><div class="toast toast--<?= e($art) ?>" role="status"><?= e($text) ?></div><?php endforeach;
}

function fuss(array $skripte = []): void { ?>
</main>
<script src="assets/app.js?v=<?= @filemtime(APP . '/assets/app.js') ?>" defer></script>
<?php foreach ($skripte as $s): ?><script src="assets/<?= e($s) ?>?v=<?= @filemtime(APP . '/assets/' . $s) ?>" defer></script><?php endforeach; ?>
</body></html><?php
}

function seitenkopf(string $eyebrow, string $titel, string $aktionen = ''): void { ?>
  <div class="pagehead">
    <div><p class="eyebrow"><?= e($eyebrow) ?></p><h1><?= $titel ?></h1></div>
    <?php if ($aktionen): ?><div class="pagehead__actions"><?= $aktionen ?></div><?php endif; ?>
  </div>
<?php }

function status_pill(string $status, array $namen): string { return '<span class="pill pill--' . e($status) . '">' . e($namen[$status] ?? $status) . '</span>'; }
function leer(string $text): string { return '<div class="empty">' . e($text) . '</div>'; }

/** Auswahlliste */
function auswahl(string $name, array $optionen, $wert, string $extra = ''): string {
  $o = '<select name="' . e($name) . '" ' . $extra . '>';
  foreach ($optionen as $k => $v) $o .= '<option value="' . e($k) . '"' . ((string) $k === (string) $wert ? ' selected' : '') . '>' . e($v) . '</option>';
  return $o . '</select>';
}
function benutzer_optionen(bool $leer = true): array {
  $o = $leer ? ['' => '– niemand –'] : [];
  foreach (benutzer_alle() as $b) $o[$b['id']] = $b['name'];
  return $o;
}
