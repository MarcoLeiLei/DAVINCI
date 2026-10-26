<?php
/* =========================================================
   DAVINCI – gemeinsames Gerüst aller Seiten
   Sprache, Adressen, Preise, Kopf, Navigation, Fuß, SEO.
   Deutsche Seiten liegen im Hauptordner, englische unter /en/.
   ========================================================= */
require_once __DIR__ . '/../gate.php';
if (!defined('LANG')) define('LANG', 'de');
const SITE = 'https://davinci-homes.de';

function en(): bool { return LANG === 'en'; }
function t(string $de, string $en): string { return en() ? $en : $de; }
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
/** Adresse einer Seite in der aktuellen Sprache, z. B. u('haeuser') → /haeuser oder /en/haeuser */
function u(string $slug = ''): string { return (en() ? '/en/' : '/') . ($slug === '' ? '' : $slug . '/'); }
function asset(string $pfad): string { $f = __DIR__ . '/../assets/' . $pfad; return '/assets/' . $pfad . (is_file($f) ? '?v=' . filemtime($f) : ''); }

/* ---------- Preise (alle Beträge inkl. 19 % MwSt.) ← hier zentral pflegen ---------- */
const PREIS_HUELLE     = 3890;   // Hülle & Tragwerk, € je m² Wohnfläche
const PREIS_PRODUKT    = 5400;   // DAVINCI Produkte schlüsselfertig, € je m²
const PREIS_INDIVIDUELL = 5800;  // DAVINCI Individuell, ab € je m²
const PRODUKTE = [  // Reihenfolge = Reihenfolge auf der Seite. m2 = null: Größe nach Programm
  'tre' => ['name' => 'DAVINCI Tre', 'm2' => null, 'ebenen' => 2, 'img' => 'dv-24-giebel-3achser-tag.jpg', 'pos' => '50% 40%', 'tag' => ['Satteldach · 3 Achsen', 'Gable roof · 3 bays'], 'txt' => ['Der Klassiker in kompakter Form. Drei Achsen, ein Glasgiebel mit Balkon und ein Luftraum, der Wohnen und Obergeschoss verbindet.', 'The classic in compact form. Three bays, a glazed gable with balcony and an open void linking the living area with the upper floor.']],
  'quattro' => ['name' => 'DAVINCI Quattro', 'm2' => null, 'ebenen' => 2, 'img' => 'dv-20-giebel-4achser-carport.jpg', 'pos' => '50% 50%', 'tag' => ['Satteldach · 4 Achsen · Carport', 'Gable roof · 4 bays · carport'], 'txt' => ['Das klassische DAVINCI Haus mit vier Achsen. Mehr Raum für Familie und Gäste, ein großer Glasgiebel und der Carport unter einem Dach.', 'The classic DAVINCI house with four bays. More room for family and guests, a large glazed gable and the carport under one roof.']],
  'piano' => ['name' => 'DAVINCI Piano', 'm2' => 150, 'ebenen' => 1, 'img' => 'dv-09-bungalow-flach-garten.jpg', 'pos' => '50% 50%', 'tag' => ['Flachdach · Bungalow', 'Flat roof · bungalow'], 'txt' => ['Alles auf einer Ebene. Großzügige Raumhöhen, fließende Übergänge nach draußen und kein Schritt zu viel.', 'Everything on one level. Generous ceiling heights, seamless transitions to the outdoors and not a step too many.']],
  'alpina' => ['name' => 'DAVINCI Alpina', 'm2' => null, 'ebenen' => 2, 'img' => 'dv-03-giebel-winter-tag.jpg', 'pos' => '50% 40%', 'tag' => ['Satteldach · Glasgiebel', 'Pitched roof · glazed gable'], 'txt' => ['Der klassische DAVINCI Giebel, gemacht für Berge und Schnee. Der Luftraum hinter dem Glas holt das Winterlicht bis tief ins Haus.', 'The classic DAVINCI gable, made for mountains and snow. The open void behind the glass draws winter light deep into the house.']],
  'terrazza' => ['name' => 'DAVINCI Terrazza', 'm2' => 200, 'ebenen' => 2, 'img' => 'dv-11-flachdach-weiss-tag.jpg', 'pos' => '50% 50%', 'tag' => ['Flachdach · Dachterrasse', 'Flat roof · roof terrace'], 'txt' => ['Zwei Ebenen unter weiten Dachüberständen. Oben eine Terrasse mit Glasgeländer, unten raumhohes Glas zum Garten.', 'Two levels beneath wide roof overhangs. A terrace with glass balustrade above, floor-to-ceiling glass to the garden below.']],
  'pendio' => ['name' => 'DAVINCI Pendio', 'm2' => null, 'ebenen' => 3, 'img' => 'dv-22-giebel-hang-garage.jpg', 'pos' => '50% 50%', 'tag' => ['Satteldach · Hanglage · Garage', 'Gable roof · hillside · garage'], 'txt' => ['Der Giebel am Hang. Garage und Nebenräume liegen im Sockel, darüber zwei Ebenen mit Balkonen und Blick ins Tal.', 'The gable on the hillside. Garage and utility rooms sit in the base, above them two levels with balconies and a view of the valley.']],
  'corte' => ['name' => 'DAVINCI Corte', 'm2' => null, 'ebenen' => 1, 'img' => 'dv-23-bungalow-winkel-abend.jpg', 'pos' => '50% 50%', 'tag' => ['Flachdach · Bungalow im Winkel', 'Flat roof · L-shaped bungalow'], 'txt' => ['Ein Bungalow im Winkel. Zwei Flügel fassen eine geschützte Terrasse, die tiefen Dachüberstände spenden Schatten.', 'An L-shaped bungalow. Two wings frame a sheltered terrace, and the deep roof overhangs provide shade.']],
  'ala' => ['name' => 'DAVINCI Ala', 'm2' => null, 'ebenen' => 1, 'img' => 'dv-04-pultdach-bungalow-abend.jpg', 'pos' => '50% 45%', 'tag' => ['Pultdach · Bungalow', 'Mono-pitch · bungalow'], 'txt' => ['Ein Bungalow mit Flügeln. Die Dächer steigen zum Garten hin an und öffnen den Wohnraum mit einer hohen Glasfront zur Abendsonne.', 'A bungalow with wings. The roofs rise towards the garden and open the living space with a tall glass front to the evening sun.']],
  'vista' => ['name' => 'DAVINCI Vista', 'm2' => null, 'ebenen' => 2, 'img' => 'dv-12-hang-pool-nacht.jpg', 'pos' => '45% 50%', 'tag' => ['Pultdach · Hanglage · Pool', 'Mono-pitch · hillside · pool'], 'txt' => ['Ganz auf die Aussicht gebaut. Die Wohnebene liegt als gläserner Pavillon auf dem Sockel, Terrasse und Pool schweben über dem Hang.', 'Built entirely around the view. The living level sits as a glass pavilion on the base, with terrace and pool floating above the slope.']],
  'ombra' => ['name' => 'DAVINCI Ombra', 'm2' => null, 'ebenen' => 3, 'img' => 'dv-18-schwarz-giebel-hang-garage.jpg', 'pos' => '50% 45%', 'tag' => ['Satteldach · Tragwerk dunkel', 'Pitched roof · dark structure'], 'txt' => ['Dunkles Tragwerk, warmes Licht. Drei Ebenen am Hang, verbunden durch Balkone und Lufträume, mit Garage und Carport.', 'Dark structure, warm light. Three levels on a slope, linked by balconies and open voids, with garage and carport.']],
];
function zahl(float $v): string { return en() ? number_format($v, 0, '.', ',') : number_format($v, 0, ',', '.'); }
function eur_m2(int $v): string { return en() ? '€' . zahl($v) . '/m²' : zahl($v) . ' €/m²'; }
function eur(float $v): string {
  if ($v >= 1e6) { $m = rtrim(rtrim(number_format($v / 1e6, 2, en() ? '.' : ',', ''), '0'), en() ? '.' : ',');
    return en() ? '€' . $m . 'm' : $m . ' Mio. €'; }
  return en() ? '€' . zahl($v) : zahl($v) . ' €';
}

/* ---------- Seiten ---------- */
function seiten(): array {
  return [
    'haeuser'    => t('Häuser', 'Homes'),
    'system'     => t('System', 'System'),
    'manufaktur' => t('Manufaktur', 'Manufacture'),
    'geschichte' => t('Geschichte', 'Story'),
    'service'    => t('Service', 'Service'),
    'kontakt'    => t('Kontakt', 'Contact'),
  ];
}

/** Kopf, Navigation und Beginn von <main>. $p: slug, title, desc, img, nur_de, schema */
function seite_start(array $p): void {
  $slug = $p['slug'] ?? '';
  $sl = $slug === '' ? '' : $slug . '/';
  $de = SITE . '/' . $sl; $enUrl = SITE . '/en/' . $sl;
  $canon = !empty($p['nur_de']) ? $de : (en() ? $enUrl : $de);
  $title = $p['title']; $desc = $p['desc'];
  $img = SITE . '/assets/img/' . ($p['img'] ?? 'dv-01-flachdach-weiss-abend.jpg');
  $andere = !empty($p['nur_de']) ? '/en/' : (en() ? '/' . $sl : '/en/' . $sl);
  $GLOBALS['SEITE'] = $slug;

  $org = ['@context' => 'https://schema.org', '@type' => 'HomeAndConstructionBusiness', 'name' => 'DAVINCI', 'url' => SITE,
    'slogan' => 'Designed to stay.', 'logo' => SITE . '/assets/img/signet.svg', 'image' => SITE . '/assets/img/dv-01-flachdach-weiss-abend.jpg',
    'description' => t('Häuser aus Holz und Glas aus einem durchdachten System: zehn DAVINCI Produkte und individuelle Entwürfe.', 'Homes in wood and glass from a considered system: ten DAVINCI models and bespoke designs.'),
    'email' => 'bauen@davinci-homes.de', 'telephone' => '+4915786832859',
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Kaiser-Friedrich-Ring 43', 'postalCode' => '40545', 'addressLocality' => 'Düsseldorf', 'addressCountry' => 'DE'],
    'areaServed' => 'DE'];
  $schemas = [$org];
  if ($slug !== '') $schemas[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'DAVINCI', 'item' => SITE . u('')],
    ['@type' => 'ListItem', 'position' => 2, 'name' => $p['crumb'] ?? (seiten()[$slug] ?? $title), 'item' => $canon]]];
  foreach (($p['schema'] ?? []) as $s) $schemas[] = $s;
  ?><!DOCTYPE html>
<html lang="<?= en() ? 'en' : 'de' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#0b0b0b">
<link rel="canonical" href="<?= e($canon) ?>">
<?php if (empty($p['nur_de'])): ?>
<link rel="alternate" hreflang="de" href="<?= e($de) ?>">
<link rel="alternate" hreflang="en" href="<?= e($enUrl) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e($de) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="DAVINCI">
<meta property="og:locale" content="<?= en() ? 'en_GB' : 'de_DE' ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($img) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
<link rel="preload" href="/assets/fonts/outfit.woff2" as="font" type="font/woff2" crossorigin>
<?php if ($slug === ''): ?><link rel="preload" href="/assets/img/dv-drohne-poster.jpg" as="image" fetchpriority="high"><?php endif; ?>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script>if('scrollRestoration' in history)history.scrollRestoration='manual';</script>
<?php if ($slug === ''): ?><script>try{if(!sessionStorage.getItem('dv-intro')&&!matchMedia('(prefers-reduced-motion: reduce)').matches)document.documentElement.classList.add('intro')}catch(e){}</script><?php endif; ?>
<?php foreach ($schemas as $s): ?>
<script type="application/ld+json"><?= json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endforeach; ?>
</head>
<body class="seite-<?= e($slug === '' ? 'start' : $slug) ?>">
<?php if ($slug === ''): ?>
<div class="intro-overlay" aria-hidden="true">
  <div class="intro__bg"></div>
  <div class="intro__logo brand"><svg class="brand__signet intro__signet" viewBox="0 0 822 1073"><defs><clipPath id="intro-c1"><rect class="intro__mask" data-von="unten" x="0" y="1072" width="252" height="0" data-y="0" data-h="1072"/></clipPath><clipPath id="intro-c2"><rect class="intro__mask" data-von="unten" x="284" y="1072" width="252" height="0" data-y="631" data-h="441"/></clipPath><clipPath id="intro-c3"><rect class="intro__mask" data-von="oben" x="284" y="0" width="252" height="0" data-y="0" data-h="441"/></clipPath><clipPath id="intro-c4"><rect class="intro__mask" data-von="unten" x="569" y="1072" width="251" height="0" data-y="0" data-h="1072"/></clipPath></defs><path class="intro__bar" clip-path="url(#intro-c1)" d="M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"/><path class="intro__bar" clip-path="url(#intro-c2)" d="M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z"/><path class="intro__bar" clip-path="url(#intro-c3)" d="M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z"/><path class="intro__bar" clip-path="url(#intro-c4)" d="M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z"/></svg><span class="brand__word intro__word">DAVINCI</span></div>
</div>
<?php endif; ?>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="signet" viewBox="0 0 822 1073"><path d="M536,631 L474,631 L285,860 L284,1072 L354,1072 L536,849Z M820,0 L754,0 L569,229 L569,1072 L647,1072 L820,856Z M536,0 L473,0 L284,230 L284,441 L353,441 L535,219Z M252,0 L186,0 L0,230 L0,1072 L79,1072 L252,855Z"/></symbol>
  <symbol id="arrow" viewBox="0 0 14 14"><path d="M1 7h12M8 2l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.4"/></symbol>
  <symbol id="check" viewBox="0 0 16 16"><path d="M2.5 8.5l3.5 3.5 7.5-8" fill="none" stroke-width="1.5"/></symbol>
  <symbol id="plus" viewBox="0 0 14 14"><path d="M7 1v12M1 7h12" fill="none" stroke-width="1.4"/></symbol>
</svg>

<header class="nav" id="nav">
  <div class="wrap nav__inner">
    <a href="<?= u('') ?>" class="brand" aria-label="<?= t('DAVINCI, zur Startseite', 'DAVINCI, home') ?>">
      <svg class="brand__signet"><use href="#signet"/></svg>
      <span class="brand__word">DAVINCI</span>
    </a>
    <nav aria-label="<?= t('Hauptnavigation', 'Main navigation') ?>">
      <ul class="nav__links">
<?php foreach (seiten() as $s => $name): ?>
        <li><a href="<?= u($s) ?>"<?= $s === $slug ? ' class="is-active" aria-current="page"' : '' ?>><?= e($name) ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>
    <div class="nav__end">
      <div class="lang" aria-label="<?= t('Sprache', 'Language') ?>"><?php if (en()): ?><a href="<?= e($andere) ?>" hreflang="de" lang="de">DE</a><span aria-current="true">EN</span><?php else: ?><span aria-current="true">DE</span><a href="<?= e($andere) ?>" hreflang="en" lang="en">EN</a><?php endif; ?></div>
      <a href="#termin" class="btn nav__cta" data-booking><?= t('Termin vereinbaren', 'Book a meeting') ?></a>
    </div>
    <button class="burger" id="burger" aria-label="<?= t('Menü öffnen', 'Open menu') ?>" aria-expanded="false" aria-controls="mobile-menu"><span></span><span></span></button>
  </div>
</header>

<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
  <nav class="mobile-menu__nav" aria-label="<?= t('Menü', 'Menu') ?>">
    <ul class="mm-primary">
      <li><a href="<?= u('haeuser') ?>"><?= t('Häuser', 'Homes') ?></a></li>
      <li><a href="<?= u('system') ?>"><?= t('Das System', 'The system') ?></a></li>
      <li><a href="<?= u('geschichte') ?>"><?= t('Geschichte', 'Story') ?></a></li>
      <li><a href="<?= u('service') ?>">Service</a></li>
    </ul>
    <ul class="mm-secondary">
      <li><a href="<?= u('manufaktur') ?>"><?= t('Manufaktur', 'Manufacture') ?></a></li>
      <li><a href="<?= u('haeuser') ?>#produkte"><?= t('Produkte', 'Models') ?></a></li>
      <li><a href="<?= u('design-book') ?>">Design-Book</a></li>
      <li><a href="<?= u('kontakt') ?>"><?= t('Kontakt', 'Contact') ?></a></li>
      <li><a href="<?= u('') ?>"><?= t('Start', 'Home') ?></a></li>
    </ul>
  </nav>
  <div class="mobile-menu__foot">
    <a href="#termin" class="btn btn--light mm-cta" data-booking><?= t('Termin vereinbaren', 'Book a meeting') ?></a>
    <p class="mm-contact"><a href="tel:+4915786832859">+49 157 868 328 59</a><a href="mailto:bauen@davinci-homes.de">bauen@davinci-homes.de</a></p>
  </div>
</div>

<main id="top">
<?php
}

/** Großer Seitenkopf. $bild = Dateiname in assets/img oder leer für hellen Kopf */
function seitenkopf(string $eyebrow, string $titel, string $lead = '', string $bild = '', string $alt = ''): void { ?>
<section class="phero<?= $bild ? '' : ' phero--light' ?>">
  <?php if ($bild): ?><div class="phero__media"><img src="/assets/img/<?= e($bild) ?>" alt="<?= e($alt) ?>" data-parallax="0.12"></div><?php endif; ?>
  <div class="wrap phero__content">
    <p class="eyebrow load-in"><?= $eyebrow ?></p>
    <h1 class="h1 load-in d1"><?= $titel ?></h1>
    <?php if ($lead): ?><p class="lead load-in d2"><?= $lead ?></p><?php endif; ?>
  </div>
</section>
<?php }

/** Abschluss-Band mit Claim und zwei Wegen weiter */
function claim_band(string $text = ''): void { ?>
<section class="claim claim--short" aria-label="Designed to stay">
  <div class="claim__img"><img src="/assets/img/dv-17-schwarz-giebel-hang-carport.jpg" alt="" loading="lazy" data-parallax="0.15"></div>
  <div class="claim__inner">
    <svg class="claim__signet reveal"><use href="#signet"/></svg>
    <h2 class="display reveal" data-delay="1">Designed to stay.</h2>
    <p class="lead reveal" data-delay="2" style="color:rgba(255,255,255,.78);max-width:600px"><?= $text ?: t('Für jedes Haus, das wir bauen. Und für die Marke, die wir neu aufbauen.', 'For every house we build. And for the brand we are rebuilding.') ?></p>
    <div class="claim__ctas reveal" data-delay="3">
      <a href="#termin" class="btn btn--light" data-booking><?= t('Termin vereinbaren', 'Book a meeting') ?></a>
      <a href="<?= u('design-book') ?>" class="btn btn--ghost" style="color:#fff"><span><?= t('Design-Book anfordern', 'Request the Design Book') ?></span></a>
    </div>
  </div>
</section>
<?php }

/** Ende von <main>, Fuß, Terminbuchung, Skript */
function seite_ende(): void { ?>
</main>

<footer class="footer">
  <div class="wrap">
    <div class="footer__top">
      <div>
        <a href="<?= u('') ?>" class="brand"><svg class="brand__signet"><use href="#signet"/></svg><span class="brand__word">DAVINCI</span></a>
        <p style="max-width:300px"><?= t('Häuser aus Holz und Glas. Aus einem durchdachten System, gebaut, um zu bleiben.', 'Homes in wood and glass. From a considered system, built to stay.') ?></p>
      </div>
      <div>
        <h5><?= t('Häuser', 'Homes') ?></h5>
        <ul>
          <li><a href="<?= u('haeuser') ?>#produkte"><?= t('DAVINCI Produkte', 'DAVINCI models') ?></a></li>
          <li><a href="<?= u('haeuser') ?>#individuell"><?= t('Individuell', 'Bespoke') ?></a></li>
          <li><a href="<?= u('design-book') ?>">Design-Book</a></li>
        </ul>
      </div>
      <div>
        <h5>DAVINCI</h5>
        <ul>
          <li><a href="<?= u('system') ?>"><?= t('Das System', 'The system') ?></a></li>
          <li><a href="<?= u('manufaktur') ?>"><?= t('Manufaktur', 'Manufacture') ?></a></li>
          <li><a href="<?= u('geschichte') ?>"><?= t('Geschichte', 'Story') ?></a></li>
          <li><a href="<?= u('service') ?>">Service</a></li>
        </ul>
      </div>
      <div>
        <h5><?= t('Kontakt', 'Contact') ?></h5>
        <ul>
          <li><a href="mailto:bauen@davinci-homes.de">bauen@davinci-homes.de</a></li>
          <li><a href="tel:+4915786832859">+49 157 868 328 59</a></li>
          <li>Kaiser-Friedrich-Ring 43<br>40545 Düsseldorf<?= t('', ', Germany') ?></li>
          <li><a href="#termin" data-booking><?= t('Termin vereinbaren', 'Book a meeting') ?></a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span>© <span data-year>2026</span> DAVINCI</span>
      <nav aria-label="<?= t('Rechtliches', 'Legal') ?>"><a href="/impressum/"><?= t('Impressum', 'Legal notice') ?></a><a href="/datenschutz/"><?= t('Datenschutz', 'Privacy') ?></a><a href="#" data-cookie-settings><?= t('Cookie-Einstellungen', 'Cookie settings') ?></a></nav>
    </div>
    <p class="footer__ki"><?= t('Viele Abbildungen auf dieser Website zeigen Entwürfe, die mit Unterstützung von KI visualisiert wurden. Sie sind als KI-Visualisierung gekennzeichnet, zeigen Beispiele und können Sonderausstattungen enthalten. Daneben zeigen wir Fotos echter DAVINCI Häuser.', 'Many images on this website show designs visualised with the help of AI. They are labelled as AI visualisations, show examples and may include optional extras. We also show photos of real DAVINCI houses.') ?></p>
  </div>
</footer>

<div class="booking" data-booking-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="booking-title">
  <div class="booking__panel">
    <div class="booking__head">
      <div>
        <p class="eyebrow"><?= t('Persönliche Beratung', 'Personal consultation') ?></p>
        <h2 class="h3" id="booking-title"><?= t('Ihr Termin bei DAVINCI.', 'Your meeting with DAVINCI.') ?></h2>
      </div>
      <button class="booking__close" data-booking-close aria-label="<?= t('Schließen', 'Close') ?>"><svg viewBox="0 0 14 14"><path d="M2 2l10 10M12 2L2 12" fill="none" stroke="currentColor" stroke-width="1.4"/></svg></button>
    </div>
    <div class="booking__body" data-booking-body>
      <form class="form booking__form" action="/send.php" method="post" data-contact data-success-title="<?= t('Terminanfrage gesendet.', 'Appointment request sent.') ?>" data-success="<?= t('Vielen Dank. Wir melden uns innerhalb eines Werktags mit einem Terminvorschlag.', 'Thank you. We will get back to you within one working day with a suggested time.') ?>" data-aktion="terminanfrage" novalidate>
        <input type="hidden" name="anfrage_typ" value="Terminanfrage"><input type="hidden" name="sprache" value="<?= en() ? 'en' : 'de' ?>">
        <p class="lead full"><?= t('Sagen Sie uns, wie und wann es Ihnen passt. Wir melden uns innerhalb eines Werktags mit einem konkreten Terminvorschlag.', 'Tell us how and when suits you. We will get back to you within one working day with a specific time.') ?></p>
        <fieldset class="full bk-group"><legend><?= t('Wo möchten Sie sprechen?', 'Where would you like to meet?') ?></legend>
          <div class="seg">
            <input type="radio" name="gespraechsart" id="t-ort" value="Bei mir vor Ort" checked><label for="t-ort"><?= t('Bei mir vor Ort', 'At my place') ?></label>
            <input type="radio" name="gespraechsart" id="t-ddf" value="In Düsseldorf"><label for="t-ddf"><?= t('Bei Ihnen in Düsseldorf', 'In Düsseldorf') ?></label>
            <input type="radio" name="gespraechsart" id="t-video" value="Per Video"><label for="t-video"><?= t('Per Video', 'By video') ?></label>
            <input type="radio" name="gespraechsart" id="t-tel" value="Telefonisch"><label for="t-tel"><?= t('Telefonisch', 'By phone') ?></label>
          </div>
        </fieldset>
        <fieldset class="full bk-group"><legend><?= t('Welche Tage passen grundsätzlich?', 'Which days generally suit you?') ?></legend>
          <div class="seg seg--small">
<?php foreach ([['Mo', 'Mon'], ['Di', 'Tue'], ['Mi', 'Wed'], ['Do', 'Thu'], ['Fr', 'Fri'], ['Sa', 'Sat']] as $i => [$tde, $ten]): ?>            <input type="checkbox" name="tage[]" id="t-tag<?= $i ?>" value="<?= $tde ?>"><label for="t-tag<?= $i ?>"><?= t($tde, $ten) ?></label>
<?php endforeach; ?>          </div>
        </fieldset>
        <fieldset class="full bk-group"><legend><?= t('Welche Uhrzeit passt Ihnen?', 'What time suits you?') ?></legend>
          <div class="seg seg--small">
            <input type="checkbox" name="zeit[]" id="t-z1" value="Vormittags (9–12 Uhr)"><label for="t-z1"><?= t('Vormittags · 9–12 Uhr', 'Morning · 9–12') ?></label>
            <input type="checkbox" name="zeit[]" id="t-z2" value="Mittags (12–14 Uhr)"><label for="t-z2"><?= t('Mittags · 12–14 Uhr', 'Midday · 12–2 pm') ?></label>
            <input type="checkbox" name="zeit[]" id="t-z3" value="Nachmittags (14–17 Uhr)"><label for="t-z3"><?= t('Nachmittags · 14–17 Uhr', 'Afternoon · 2–5 pm') ?></label>
            <input type="checkbox" name="zeit[]" id="t-z4" value="Abends (17–20 Uhr)"><label for="t-z4"><?= t('Abends · 17–20 Uhr', 'Evening · 5–8 pm') ?></label>
          </div>
        </fieldset>
        <div class="fld"><input id="t-vorname" name="vorname" type="text" placeholder=" " autocomplete="given-name" required><label for="t-vorname"><?= t('Vorname', 'First name') ?></label></div>
        <div class="fld"><input id="t-name" name="nachname" type="text" placeholder=" " autocomplete="family-name" required><label for="t-name"><?= t('Nachname', 'Last name') ?></label></div>
        <div class="fld"><input id="t-mail" name="email" type="email" placeholder=" " autocomplete="email" required><label for="t-mail"><?= t('E-Mail', 'Email') ?></label></div>
        <div class="fld"><input id="t-telnr" name="telefon" type="tel" placeholder=" " autocomplete="tel"><label for="t-telnr"><?= t('Telefon (optional)', 'Phone (optional)') ?></label></div>
        <div class="fld full"><input id="t-ortsangabe" name="ort" type="text" placeholder=" " autocomplete="address-level2"><label for="t-ortsangabe"><?= t('Ort oder Grundstückslage (optional)', 'Town or plot location (optional)') ?></label></div>
        <div class="fld full"><textarea id="t-msg" name="nachricht" placeholder=" " rows="2"></textarea><label for="t-msg"><?= t('Worum geht es? (optional)', 'What is it about? (optional)') ?></label></div>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <label class="consent full"><input type="checkbox" name="datenschutz" required><span><?= t('Ich habe die <a href="/datenschutz/">Datenschutzerklärung</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Terminabstimmung einverstanden.', 'I have read the <a href="/datenschutz/">privacy policy</a> (German) and agree to my details being processed to arrange a meeting.') ?></span></label>
        <label class="consent full"><input type="checkbox" name="newsletter" value="1"><span><?= t('Optional: Ja, ich möchte den DAVINCI Newsletter mit Neuigkeiten zu Häusern, Projekten und Veranstaltungen per E-Mail erhalten. Abmeldung jederzeit möglich.', 'Optional: Yes, I would like to receive the DAVINCI newsletter with news on houses, projects and events by email (in German). You can unsubscribe at any time.') ?></span></label>
        <div class="full" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
          <button type="submit" class="btn btn--dark" data-label="<?= t('Termin anfragen', 'Request a meeting') ?>"><?= t('Termin anfragen', 'Request a meeting') ?></button>
          <p class="form__msg" data-form-msg role="status"></p>
        </div>
        <p class="booking__alt full"><?= t('Lieber direkt?', 'Prefer to talk directly?') ?> <a href="tel:+4915786832859">+49 157 868 328 59</a> · <a href="mailto:bauen@davinci-homes.de">bauen@davinci-homes.de</a></p>
      </form>
    </div>
  </div>
</div>

<div class="booking" data-gr-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="gr-title">
  <div class="booking__panel booking__panel--sm">
    <div class="booking__head">
      <div>
        <p class="eyebrow"><?= t('Grundriss', 'Floor plan') ?></p>
        <h2 class="h3" id="gr-title" data-gr-title><?= t('Grundriss herunterladen', 'Download the floor plan') ?></h2>
      </div>
      <button class="booking__close" data-gr-close aria-label="<?= t('Schließen', 'Close') ?>"><svg viewBox="0 0 14 14"><path d="M2 2l10 10M12 2L2 12" fill="none" stroke="currentColor" stroke-width="1.4"/></svg></button>
    </div>
    <div class="booking__body">
      <form class="form booking__form" action="/send.php" method="post" data-contact data-success-title="<?= t('Grundriss angefordert.', 'Floor plan requested.') ?>" data-success="<?= t('Vielen Dank. Wir senden Ihnen den Grundriss per E-Mail, bitte schauen Sie in Ihr Postfach.', 'Thank you. We will email you the floor plan, please check your inbox.') ?>" data-aktion="grundriss" novalidate>
        <input type="hidden" name="anfrage_typ" value="Grundriss"><input type="hidden" name="sprache" value="<?= en() ? 'en' : 'de' ?>">
        <input type="hidden" name="haus" value="" data-gr-haus><input type="hidden" name="haustyp" value="" data-gr-name>
        <p class="lead full"><?= t('Wir schicken Ihnen den Grundriss als PDF per E-Mail, mit Maßen und Raumaufteilung.', 'We will email you the floor plan as a PDF, with dimensions and room layout.') ?></p>
        <div class="fld"><input id="g-vorname" name="vorname" type="text" placeholder=" " autocomplete="given-name" required><label for="g-vorname"><?= t('Vorname', 'First name') ?></label></div>
        <div class="fld"><input id="g-name" name="nachname" type="text" placeholder=" " autocomplete="family-name" required><label for="g-name"><?= t('Nachname', 'Last name') ?></label></div>
        <div class="fld full"><input id="g-mail" name="email" type="email" placeholder=" " autocomplete="email" required><label for="g-mail"><?= t('E-Mail', 'Email') ?></label></div>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <label class="consent full"><input type="checkbox" name="datenschutz" required><span><?= t('Ich bin einverstanden, dass DAVINCI mir den Grundriss zusendet und meine Angaben dafür verarbeitet. <a href="/datenschutz/">Datenschutz</a>', 'I agree that DAVINCI may send me the floor plan and process my details for this purpose. <a href="/datenschutz/">Privacy policy</a> (German)') ?></span></label>
        <label class="consent full"><input type="checkbox" name="newsletter" value="1"><span><?= t('Optional: Ja, ich möchte den DAVINCI Newsletter mit Neuigkeiten zu Häusern, Projekten und Veranstaltungen per E-Mail erhalten. Abmeldung jederzeit möglich.', 'Optional: Yes, I would like to receive the DAVINCI newsletter with news on houses, projects and events by email (in German). You can unsubscribe at any time.') ?></span></label>
        <div class="full" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
          <button type="submit" class="btn btn--dark" data-label="<?= t('Grundriss anfordern', 'Request floor plan') ?>"><?= t('Grundriss anfordern', 'Request floor plan') ?></button>
          <p class="form__msg" data-form-msg role="status"></p>
        </div>
      </form>
    </div>
  </div>
</div>

<?php foreach (['gsap', 'ScrollTrigger', 'SplitText', 'Flip', 'DrawSVGPlugin'] as $g): ?><script src="<?= asset('js/gsap/' . $g . '.min.js') ?>" defer></script>
<?php endforeach; ?><script src="<?= asset('js/main.js') ?>" defer></script>
<script src="<?= asset('js/animationen.js') ?>" defer></script>
<script src="<?= asset('js/viewer.js') ?>" defer></script>
</body>
</html>
<?php }

/** 3D-Buch des Design-Books (Startseite und Design-Book-Seite). */
function buch_visual(): void { ?>
    <div class="dbook__visual reveal" aria-hidden="true">
      <div class="book">
        <div class="book__pages"></div>
        <div class="book__cover">
          <div class="book__img"><img src="/assets/img/dv-01-flachdach-weiss-abend.jpg" alt="" loading="lazy"></div>
          <div class="book__top"><svg class="book__signet"><use href="#signet"/></svg><span>DAVINCI</span></div>
          <div class="book__title"><small>Design-Book · Vol. 01</small>Wohnen in<br>Holz und Glas.</div>
        </div>
        <div class="book__spine"></div>
      </div>
      <div class="book__shadow"></div>
    </div>
<?php }
