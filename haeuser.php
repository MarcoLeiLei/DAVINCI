<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'haeuser',
  'title' => t('Häuser aus Holz und Glas: DAVINCI Produkte und Individuell | DAVINCI', 'Homes in wood and glass: DAVINCI models and bespoke | DAVINCI'),
  'desc'  => t('Zwei Wege zu Ihrem DAVINCI: zehn fertig gedachte Häuser ab ' . eur_m2(PREIS_PRODUKT) . ' schlüsselfertig oder ein individueller Entwurf ab ' . eur_m2(PREIS_INDIVIDUELL) . '.', 'Two paths to your DAVINCI: ten ready-designed homes from ' . eur_m2(PREIS_PRODUKT) . ' turnkey, or a bespoke design from ' . eur_m2(PREIS_INDIVIDUELL) . '.'),
  'img'   => 'dv-15-doppelpult-bungalow-abend.jpg',
]);
seitenkopf(t('Häuser', 'Homes'), t('Zwei Wege zu<br>Ihrem DAVINCI.', 'Two paths to<br>your DAVINCI.'),
  t('Ein fertig gedachtes Haus aus dem DAVINCI System oder ein Entwurf nur für Sie. Beide entstehen aus demselben System und haben dieselbe Qualität.', 'A ready-designed house from the DAVINCI system or a design made only for you. Both come from the same system and share the same quality.'),
  'dv-15-doppelpult-bungalow-abend.jpg', t('DAVINCI Haus mit zwei versetzten Pultdächern und Terrassen in der Abenddämmerung', 'DAVINCI house with two offset mono-pitch roofs and terraces at dusk'));

?>

<!-- ============ WEG 1: PRODUKTE ============ -->
<section class="section" id="produkte">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Weg 1 · DAVINCI Produkte', 'Path 1 · DAVINCI models') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Fertig gedacht.', 'Ready-designed.') ?></h2>
      <p class="lead reveal" data-delay="2" style="max-width:700px"><?= t('Zehn Häuser aus dem DAVINCI System, mit einem Preis je Quadratmeter, den Sie vorher kennen. Innerhalb des Systems wählen Sie Größe, Fassade, Ausbau und Details.', 'Ten houses from the DAVINCI system, with a price per square metre you know in advance. Within the system you choose size, facade, fit-out and details.') ?></p>
      <p class="price-note reveal" data-delay="3"><span><?= t('Alle fertig gedachten Häuser', 'All ready-designed houses') ?></span><b><small><?= t('schlüsselfertig ab', 'turnkey from') ?></small> <?= eur_m2(PREIS_PRODUKT) ?></b></p>
    </div>
    <?php
    $kategorien = ['sattel' => t('Satteldach', 'Gable roof'), 'flach' => t('Flachdach', 'Flat roof'), 'pult' => t('Pultdach', 'Mono-pitch'), 'bungalow' => 'Bungalow', 'hang' => t('Am Hang', 'Hillside')];
    $katVon = function ($pr) { $tag = $pr['tag'][0]; $k = [];
      foreach (['Satteldach' => 'sattel', 'Flachdach' => 'flach', 'Pultdach' => 'pult', 'Bungalow' => 'bungalow', 'Hang' => 'hang'] as $w => $c) if (stripos($tag, $w) !== false) $k[] = $c;
      if ($pr['ebenen'] == 1 && !in_array('bungalow', $k)) $k[] = 'bungalow';
      return implode(' ', $k); };
    ?>
    <div class="filter" data-filter role="group" aria-label="<?= t('Häuser filtern', 'Filter homes') ?>">
      <button type="button" class="filter__btn is-active" data-f="alle" aria-pressed="true"><?= t('Alle', 'All') ?> <small><?= count(PRODUKTE) ?></small></button>
      <?php foreach ($kategorien as $c => $label): $n = count(array_filter(PRODUKTE, fn($pr) => in_array($c, explode(' ', $katVon($pr))))); if (!$n) continue; ?>
      <button type="button" class="filter__btn" data-f="<?= $c ?>" aria-pressed="false"><?= $label ?> <small><?= $n ?></small></button>
      <?php endforeach; ?>
    </div>
    <div class="products products--2" data-filter-grid>
<?php $i = 0; foreach (PRODUKTE as $k => $pr): ?>
      <article class="product reveal"<?= $i % 2 ? ' data-delay="1"' : '' ?> id="<?= $k ?>" data-kat="<?= $katVon($pr) ?>">
        <div class="product__img"><img data-speed src="/assets/img/<?= $pr['img'] ?>" style="object-position:<?= $pr['pos'] ?>" alt="<?= e($pr['name']) ?>" loading="lazy"></div>
        <div class="product__body">
          <h3><?= e($pr['name']) ?></h3>
          <ul class="product__facts"><?php if ($pr['m2']): ?><li><b><?= $pr['m2'] ?> m²</b> <?= t('Wohnfläche', 'living space') ?></li><?php endif; ?><li><b><?= $pr['ebenen'] ?></b> <?= $pr['ebenen'] > 1 ? t('Ebenen', 'levels') : t('Ebene', 'level') ?></li><li><?= t($pr['tag'][0], $pr['tag'][1]) ?></li></ul>
          <p><?= t($pr['txt'][0], $pr['txt'][1]) ?></p>
          <div class="product__foot product__foot--link">
            <button type="button" class="gr-btn" data-grundriss="<?= $k ?>" data-name="<?= e($pr['name']) ?>"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 2v8m0 0l-3.2-3.2M8 10l3.2-3.2M2.5 13.5h11" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg><?= t('Grundriss herunterladen', 'Download floor plan') ?></button>
            <a href="<?= u('kontakt') ?>?haus=<?= $k ?>" class="link-arrow"><?= t('Anfragen', 'Enquire') ?> <svg><use href="#arrow"/></svg></a>
          </div>
        </div>
      </article>
<?php $i++; endforeach; ?>
    </div>
    <p class="footnote reveal"><?= t('Schlüsselfertige Richtpreise je m² Wohnfläche inkl. 19 % MwSt. Der endgültige Preis hängt von Größe, Ausstattung, Fassade und Grundstück ab und steht nach der Bemusterung fest. Ohne Grundstück, Bodenplatte oder Keller, Erschließung und Baunebenkosten.', 'Turnkey guide prices per m² of living space incl. 19% German VAT. The final price depends on size, fit-out, facade and plot and is fixed once the specification is agreed. Excluding plot, foundation slab or basement, site services and ancillary costs.') ?></p>
  </div>
</section>

<!-- ============ WEG 2: INDIVIDUELL ============ -->
<section class="section grey" id="individuell">
  <div class="wrap split">
    <figure class="split__img reveal"><img src="/assets/img/dv-10-hang-pultdach-terrasse.jpg" style="object-position:35% 50%" alt="<?= t('Individuell entworfenes DAVINCI Haus am Hang mit großer Terrasse', 'Bespoke DAVINCI house on a slope with large terrace') ?>" loading="lazy"></figure>
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal"><?= t('Weg 2 · DAVINCI Individuell', 'Path 2 · DAVINCI Bespoke') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Ihr eigener DAVINCI.', 'Your own DAVINCI.') ?></h2>
      <p class="lead reveal" data-delay="2"><?= t('Ihr Grundstück, Ihr Programm, Ihre Vorstellung. Wir entwerfen aus dem DAVINCI System ein Haus, das es nur einmal gibt. Raster, Details und Materialien bleiben erprobt, alles andere entsteht für Sie.', 'Your plot, your brief, your vision. We design a house from the DAVINCI system that exists only once. Grid, details and materials stay proven, everything else is made for you.') ?></p>
      <ul class="ticks reveal" data-delay="2">
        <li><?= t('Freier Entwurf auf dem DAVINCI Raster', 'Free design on the DAVINCI grid') ?></li>
        <li><?= t('Luftraum, Galerien und Raumhöhen nach Wunsch', 'Open voids, galleries and ceiling heights as you wish') ?></li>
        <li><?= t('Innenarchitektur und Möbel auf Wunsch', 'Interior design and furniture on request') ?></li>
      </ul>
      <div class="indiv__foot reveal" data-delay="3">
        <span class="product__price"><small><?= t('ab', 'from') ?></small><?= eur_m2(PREIS_INDIVIDUELL) ?></span>
        <span class="muted" style="font-size:15px"><?= t('Preis nach Entwurf', 'Price after design') ?></span>
        <a href="#termin" class="btn btn--dark" data-booking><?= t('Termin vereinbaren', 'Book a meeting') ?></a>
      </div>
    </div>
  </div>
</section>

<!-- ============ VERGLEICH ============ -->
<section class="section--tight">
  <div class="wrap">
    <div class="section-head section-head--center" style="margin-bottom:48px">
      <h2 class="h2 reveal"><?= t('Was die beiden Wege unterscheidet.', 'What sets the two paths apart.') ?></h2>
    </div>
    <div class="vs reveal">
      <div class="vs__row vs__head"><span></span><b><?= t('DAVINCI Produkte', 'DAVINCI models') ?></b><b><?= t('DAVINCI Individuell', 'DAVINCI Bespoke') ?></b></div>
      <div class="vs__row"><span><?= t('Grundriss', 'Floor plan') ?></span><p><?= t('Fertig gedacht, mit Varianten im System', 'Ready-designed, with variants within the system') ?></p><p><?= t('Frei entworfen für Ihr Grundstück', 'Freely designed for your plot') ?></p></div>
      <div class="vs__row"><span><?= t('Preis', 'Price') ?></span><p><?= t('Vorab klar, schlüsselfertig ab', 'Clear in advance, turnkey from') ?> <?= eur_m2(PREIS_PRODUKT) ?></p><p><?= t('Nach Entwurf, ab', 'After design, from') ?> <?= eur_m2(PREIS_INDIVIDUELL) ?></p></div>
      <div class="vs__row"><span><?= t('Planung', 'Planning') ?></span><p><?= t('Startet mit einem fertigen Entwurf', 'Starts with a finished design') ?></p><p><?= t('Startet mit dem ersten Strich', 'Starts with the first line') ?></p></div>
      <div class="vs__row"><span><?= t('Qualität', 'Quality') ?></span><p><?= t('Dasselbe System, dieselben Details', 'Same system, same details') ?></p><p><?= t('Dasselbe System, dieselben Details, dazu Zusatzdetails nach Ihren Wünschen', 'Same system, same details, plus additional details to your wishes') ?></p></div>
    </div>
  </div>
</section>

<?php claim_band(); seite_ende();
