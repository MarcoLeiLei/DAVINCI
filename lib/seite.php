<?php
/* Schlichte Hinweisseite im DAVINCI-Design (Bestätigen, Abmelden) */
function dv_seite($titel, $text, $knopf = null, $url = '/') {
  header('X-Robots-Tag: noindex, nofollow');
  ?><!DOCTYPE html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>DAVINCI</title><link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
<style>
@font-face { font-family: "Outfit"; src: url("/assets/fonts/outfit.woff2") format("woff2"); font-weight: 100 900; font-display: swap; }
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body { font-family: "Outfit", -apple-system, "Helvetica Neue", Arial, sans-serif; font-weight: 300; color: #fff; background: #0b0b0b; display: grid; place-items: center; padding: 24px 16px; -webkit-font-smoothing: antialiased; }
.bg { position: fixed; inset: 0; z-index: -1; } .bg img { width: 100%; height: 100%; object-fit: cover; }
.bg::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.75)); }
.card { width: min(460px, 100%); padding: 40px 34px 34px; border-radius: 24px; text-align: center; background: rgba(20,20,20,.5); -webkit-backdrop-filter: blur(24px); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,.14); display: grid; gap: 22px; }
.brand { display: inline-flex; align-items: center; justify-content: center; gap: 12px; }
.brand svg { width: 22px; height: 29px; fill: #fff; } .brand span { font-weight: 250; font-size: 24px; letter-spacing: .32em; margin-right: -.32em; }
h1 { font-weight: 250; font-size: 32px; letter-spacing: -.02em; line-height: 1.1; }
p { font-size: 16px; line-height: 1.55; color: rgba(255,255,255,.75); }
a.btn { display: inline-block; justify-self: center; padding: 14px 28px; border-radius: 999px; background: #fff; color: #0b0b0b; text-decoration: none; font-size: 16px; }
</style></head>
<body><div class="bg"><img src="/assets/img/dv-12-hang-pool-nacht.jpg" alt=""></div>
<main class="card">
  <div class="brand"><svg viewBox="0 0 822 1073" aria-hidden="true"><path d="M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"/></svg><span>DAVINCI</span></div>
  <h1><?= $titel ?></h1><p><?= $text ?></p>
  <?php if ($knopf): ?><a class="btn" href="<?= htmlspecialchars($url) ?>"><?= $knopf ?></a><?php endif; ?>
</main></body></html><?php
}
