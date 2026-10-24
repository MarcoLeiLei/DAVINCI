<?php
require_once __DIR__ . '/zugang.php';

if (!headers_sent()) { header('X-Robots-Tag: noindex, nofollow'); header('Cache-Control: private, no-cache, no-store'); }

// Ziel nach dem Login: die ursprünglich aufgerufene Seite
$ziel = $_POST['ziel'] ?? ($_SERVER['REQUEST_URI'] ?? '/');
if (!is_string($ziel) || $ziel === '' || $ziel[0] !== '/' || str_starts_with($ziel, '//') || str_starts_with($ziel, '/login.php') || str_starts_with($ziel, '/send.php')) $ziel = '/';

$fehler = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passwort'])) {
  if (hash_equals(PASSWORT, (string) $_POST['passwort'])) {
    setcookie('dv_access', ZUGANG, [
      'expires'  => time() + TAGE * 86400,
      'path'     => '/',
      'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
      'httponly' => true,
      'samesite' => 'Lax',
    ]);
    header('Location: ' . $ziel, true, 303);
    exit;
  }
  $fehler = true;
  usleep(600000); // bremst automatisches Durchprobieren
}
http_response_code($fehler ? 401 : 200);
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>DAVINCI – Zugang</title>
<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
<style>
@font-face { font-family: "Outfit"; src: url("/assets/fonts/outfit.woff2") format("woff2"); font-weight: 100 900; font-display: swap; }
:root { --ease: cubic-bezier(.16,1,.3,1); }
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body {
  font-family: "Outfit", -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif; font-weight: 300;
  color: #fff; background: #0b0b0b; -webkit-font-smoothing: antialiased;
  display: grid; place-items: center; padding: 24px 16px; overflow: hidden;
}
.bg { position: fixed; inset: 0; z-index: -1; }
.bg img { width: 100%; height: 100%; object-fit: cover; animation: zoom 14s var(--ease) both; }
.bg::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.72)); }
@keyframes zoom { from { transform: scale(1.12); } to { transform: scale(1); } }
.card {
  width: min(400px, 100%); padding: 40px 32px 32px; border-radius: 24px; text-align: center;
  background: rgba(20,20,20,.5); -webkit-backdrop-filter: blur(24px) saturate(160%); backdrop-filter: blur(24px) saturate(160%);
  border: 1px solid rgba(255,255,255,.14); display: grid; gap: 22px;
  animation: in 1.2s var(--ease) both .2s;
}
@keyframes in { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: none; } }
.brand { display: inline-flex; align-items: center; justify-content: center; gap: 12px; }
.brand svg { width: 22px; height: 29px; fill: #fff; }
.brand span { font-weight: 250; font-size: 24px; letter-spacing: .32em; margin-right: -.32em; }
h1 { font-weight: 250; font-size: 30px; letter-spacing: -.02em; line-height: 1.1; }
p { font-size: 15px; color: rgba(255,255,255,.7); line-height: 1.5; }
form { display: grid; gap: 12px; margin-top: 4px; }
input {
  width: 100%; height: 52px; padding: 0 18px; border-radius: 14px; font: inherit; font-size: 17px; color: #fff;
  background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.2); outline: none; text-align: center; letter-spacing: .08em;
  transition: border-color .3s, background .3s;
}
input::placeholder { color: rgba(255,255,255,.5); letter-spacing: 0; }
input:focus { border-color: rgba(255,255,255,.7); background: rgba(255,255,255,.12); }
button {
  height: 50px; border: 0; border-radius: 999px; background: #fff; color: #0b0b0b; font: inherit; font-size: 16px; font-weight: 400;
  cursor: pointer; transition: background .3s, transform .3s;
}
button:hover { background: #e6e5e1; }
button:active { transform: scale(.98); }
.err { color: #ffb4a9; font-size: 14px; }
.shake { animation: shake .45s; }
@keyframes shake { 20%, 60% { transform: translateX(-8px); } 40%, 80% { transform: translateX(8px); } }
.foot { position: fixed; bottom: 18px; left: 0; right: 0; text-align: center; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.5); }
</style>
</head>
<body>
<div class="bg"><img src="/assets/img/dv-12-hang-pool-nacht.jpg" alt=""></div>
<main class="card<?= $fehler ? ' shake' : '' ?>">
  <div class="brand" aria-label="DAVINCI">
    <svg viewBox="0 0 822 1073" aria-hidden="true"><path d="M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"/></svg>
    <span>DAVINCI</span>
  </div>
  <div>
    <h1>Bald für Sie da.</h1>
    <p style="margin-top:10px">Diese Website ist noch nicht öffentlich. Bitte geben Sie das Passwort ein.</p>
  </div>
  <form method="post" action="/login.php">
    <input type="password" name="passwort" placeholder="Passwort" autocomplete="current-password" autofocus required aria-label="Passwort">
    <input type="hidden" name="ziel" value="<?= htmlspecialchars($ziel, ENT_QUOTES) ?>">
    <button type="submit">Eintreten</button>
    <?php if ($fehler): ?><p class="err" role="alert">Das Passwort ist nicht korrekt. Bitte versuchen Sie es erneut.</p><?php endif; ?>
  </form>
</main>
<p class="foot">Designed to stay.</p>
</body>
</html>
