<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => '',
  'title' => t('DAVINCI | Häuser aus Holz und Glas. Designed to stay.', 'DAVINCI | Homes in wood and glass. Designed to stay.'),
  'desc'  => t('DAVINCI baut Häuser aus Holz und Glas aus einem durchdachten System: zehn fertig gedachte Produkte oder individuell entworfen. Bewahren und besser machen.', 'DAVINCI builds homes in wood and glass from a considered system: ten ready-designed models, or bespoke. Preserve and make it better.'),
]);
?>

<!-- ============ HERO ============ -->
<section class="hero" id="hero">
  <div class="hero__media" data-hero-img>
    <div class="hero__slide is-active"><img src="/assets/img/dv-01-flachdach-weiss-abend.jpg" style="object-position:50% 55%" alt="<?= t('Weißes DAVINCI Haus mit Flachdach und raumhoher Verglasung in der Abenddämmerung', 'White DAVINCI house with flat roof and floor-to-ceiling glazing at dusk') ?>" fetchpriority="high"></div>
    <div class="hero__slide"><img src="/assets/img/echt-ansicht-strasse.jpg" style="object-position:55% 60%" alt="<?= t('Echtes DAVINCI Haus: Bungalow mit Flachdach, Pultdach-Aufsatz und Carport im Herbst', 'A real DAVINCI house: bungalow with flat roof, raised mono-pitch roof and carport in autumn') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/dv-12-hang-pool-nacht.jpg" style="object-position:45% 45%" alt="<?= t('DAVINCI Haus am Hang mit Pultdach, Terrasse und Pool bei Nacht', 'DAVINCI house on a slope with mono-pitch roof, terrace and pool at night') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/dv-03-giebel-winter-tag.jpg" style="object-position:50% 35%" alt="<?= t('DAVINCI Haus mit Glasgiebel im Schnee vor Bergkulisse', 'DAVINCI house with glazed gable in the snow against a mountain backdrop') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/dv-04-pultdach-bungalow-abend.jpg" style="object-position:50% 40%" alt="<?= t('DAVINCI Bungalow mit versetzten Pultdächern im Sonnenuntergang', 'DAVINCI bungalow with offset mono-pitch roofs at sunset') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/echt-luft-landschaft.jpg" style="object-position:50% 55%" alt="<?= t('Echtes DAVINCI Haus aus der Luft: Bungalow mit Pultdach-Aufsatz vor weiter Landschaft', 'A real DAVINCI house from above: bungalow with raised mono-pitch roof in an open landscape') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/dv-06-flachdach-schwarz-tag.jpg" style="object-position:50% 45%" alt="<?= t('DAVINCI Haus mit schwarzem Holzskelett, Flachdach und Glasfassade', 'DAVINCI house with black timber frame, flat roof and glass facade') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/dv-18-schwarz-giebel-hang-garage.jpg" style="object-position:50% 40%" alt="<?= t('DAVINCI Haus mit dunklem Tragwerk, Satteldach und Garage bei Nacht', 'DAVINCI house with dark timber structure, pitched roof and garage at night') ?>" loading="lazy"></div>
    <div class="hero__slide"><img src="/assets/img/dv-09-bungalow-flach-garten.jpg" style="object-position:50% 50%" alt="<?= t('Weißer DAVINCI Bungalow mit Flachdach und Garten', 'White DAVINCI bungalow with flat roof and garden') ?>" loading="lazy"></div>
  </div>
  <div class="hero__content">
    <div class="wrap">
      <p class="hero__kicker load-in"><?= t('DAVINCI · Häuser aus Holz und Glas', 'DAVINCI · Homes in wood and glass') ?></p>
      <h1 class="display hero__title load-in d1" data-hero-title><em>Designed</em><em>to stay.</em></h1>
      <div class="hero__bottom load-in d2">
        <p class="hero__sub"><?= t('Aus einem durchdachten System entworfen, präzise gefertigt und gebaut, um zu bleiben.', 'Designed from a considered system, precisely made and built to stay.') ?></p>
        <div class="hero__ctas">
          <a href="<?= u('haeuser') ?>" class="btn btn--light"><?= t('Häuser entdecken', 'Discover our homes') ?></a>
          <a href="#termin" class="btn btn--ghost" style="color:#fff" data-booking><span><?= t('Termin vereinbaren', 'Book a meeting') ?></span></a>
        </div>
      </div>
    </div>
  </div>
  <div class="hero__dots" role="tablist" aria-label="<?= t('Bilder', 'Images') ?>">
    <button role="tab" aria-selected="true" aria-label="<?= t('Flachdach', 'Flat roof') ?>" data-slide="0"><i></i><span><?= t('Flachdach', 'Flat roof') ?></span></button>
    <button role="tab" aria-selected="false" aria-label="<?= t('Am Hang', 'Hillside') ?>" data-slide="1"><i></i><span><?= t('Am Hang', 'Hillside') ?></span></button>
    <button role="tab" aria-selected="false" aria-label="<?= t('Winter', 'Winter') ?>" data-slide="2"><i></i><span><?= t('Winter', 'Winter') ?></span></button>
    <button role="tab" aria-selected="false" aria-label="<?= t('Pultdach', 'Mono-pitch') ?>" data-slide="3"><i></i><span><?= t('Pultdach', 'Mono-pitch') ?></span></button>
    <button role="tab" aria-selected="false" aria-label="<?= t('Holzskelett', 'Timber frame') ?>" data-slide="4"><i></i><span><?= t('Holzskelett', 'Timber frame') ?></span></button>
    <button role="tab" aria-selected="false" aria-label="<?= t('Bei Nacht', 'At night') ?>" data-slide="5"><i></i><span><?= t('Bei Nacht', 'At night') ?></span></button>
    <button role="tab" aria-selected="false" aria-label="<?= t('Bungalow', 'Bungalow') ?>" data-slide="6"><i></i><span><?= t('Bungalow', 'Bungalow') ?></span></button>
  </div>
</section>

<!-- ============ HALTUNG ============ -->
<section class="statement">
  <div class="wrap">
    <p class="eyebrow reveal" style="margin-bottom:36px"><?= t('Unsere Haltung', 'Our philosophy') ?></p>
    <p class="statement__text" data-words><?= t('<span class="accent">Zeitlos.</span> Das ist unser Anspruch an jedes Haus. Wir bauen mit Holz und Glas, Materialien, die nie aus der Zeit fallen.', '<span class="accent">Timeless.</span> That is what we ask of every house we build. We build with wood and glass, materials that never go out of date.') ?></p>
  </div>
</section>

<!-- ============ ZWEI WEGE ============ -->
<section class="section grey">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Häuser', 'Homes') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Zwei Wege zu Ihrem DAVINCI.', 'Two paths to your DAVINCI.') ?></h2>
      <p class="lead reveal" data-delay="2" style="max-width:680px"><?= t('Ein fertig gedachtes Haus aus dem DAVINCI System oder ein Entwurf nur für Sie. Beide mit derselben Qualität.', 'A ready-designed house from the DAVINCI system or a design made only for you. Both with the same quality.') ?></p>
    </div>
    <div class="ways">
      <a href="<?= u('haeuser') ?>#produkte" class="way reveal">
        <div class="way__img"><img src="/assets/img/dv-11-flachdach-weiss-tag.jpg" alt="<?= t('DAVINCI Terrazza mit Flachdach, zwei Ebenen und Dachterrasse', 'DAVINCI Terrazza with flat roof, two levels and roof terrace') ?>" loading="lazy"></div>
        <div class="way__body">
          <span class="way__label"><?= t('DAVINCI Produkte', 'DAVINCI models') ?></span>
          <h3><?= t('Fertig gedacht.', 'Ready-designed.') ?></h3>
          <p><?= t('Zehn Häuser vom klassischen Giebel bis zum Bungalow. Klare Preise je m², Varianten im System.', 'Ten houses, from the classic gable to the bungalow. Clear prices per m², variants within the system.') ?></p>
          <div class="way__foot"><span class="way__price"><?= t('schlüsselfertig ab', 'turnkey from') ?> <b><?= eur_m2(PREIS_PRODUKT) ?></b></span><span class="house__plus"><svg><use href="#plus"/></svg></span></div>
        </div>
      </a>
      <a href="<?= u('haeuser') ?>#individuell" class="way reveal" data-delay="1">
        <div class="way__img"><img src="/assets/img/dv-07-hang-pultdach-garage.jpg" alt="<?= t('Individuell entworfenes DAVINCI Haus am Hang mit Pultdach und Garage', 'Bespoke DAVINCI house on a slope with mono-pitch roof and garage') ?>" loading="lazy"></div>
        <div class="way__body">
          <span class="way__label"><?= t('DAVINCI Individuell', 'DAVINCI Bespoke') ?></span>
          <h3><?= t('Ihr eigener DAVINCI.', 'Your own DAVINCI.') ?></h3>
          <p><?= t('Ihr Grundstück, Ihr Programm. Wir entwerfen aus dem System ein Haus, das es nur einmal gibt.', 'Your plot, your brief. We design a one-off house from the system.') ?></p>
          <div class="way__foot"><span class="way__price"><?= t('ab', 'from') ?> <b><?= eur_m2(PREIS_INDIVIDUELL) ?></b>, <?= t('Preis nach Entwurf', 'price after design') ?></span><span class="house__plus"><svg><use href="#plus"/></svg></span></div>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- ============ DAS SYSTEM ============ -->
<section class="section">
  <div class="wrap split split--media-right">
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal"><?= t('Das System', 'The system') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Gute Architektur ist bei uns kein Extra.', 'Good architecture is not an extra for us.') ?></h2>
      <p class="lead reveal" data-delay="2"><?= t('Sie steckt im System. Raster, Details und Materialien sind durchdacht und erprobt. Innerhalb dieses Systems ist vieles möglich, und jede Variante hat dieselbe Qualität. Wir nennen das Managed Diversity.', 'It is built into the system. Grid, details and materials are considered and proven. Much is possible within this system, and every variant has the same quality. We call it Managed Diversity.') ?></p>
      <p class="reveal" data-delay="3"><a href="<?= u('system') ?>" class="link-arrow"><?= t('Das System entdecken', 'Discover the system') ?> <svg><use href="#arrow"/></svg></a></p>
    </div>
    <figure class="split__img reveal" data-delay="1"><img src="/assets/img/echt-detail-dachecke.jpg" style="object-position:45% 50%" alt="<?= t('Detail eines echten DAVINCI Hauses: auskragende Holzbalken unter dem Flachdach', 'Detail of a real DAVINCI house: cantilevered timber beams under the flat roof') ?>" loading="lazy"></figure>
  </div>
</section>

<!-- ============ HOLZ UND GLAS ============ -->
<section class="section dark">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Material', 'Material') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Holz und Glas.', 'Wood and glass.') ?></h2>
      <p class="lead reveal" data-delay="2" style="max-width:640px"><?= t('Warm und klar. Zwei Materialien, die zusammen mehr sind als die Summe ihrer Teile.', 'Warm and clear. Two materials that together are more than the sum of their parts.') ?></p>
    </div>
    <?php require __DIR__ . '/inc/moodboard.php'; ?>
    <p class="reveal" style="margin-top:40px"><a href="<?= u('system') ?>#material" class="link-arrow" style="color:var(--wood-light)"><?= t('Mehr über Material und Raum', 'More about material and space') ?> <svg><use href="#arrow"/></svg></a></p>
  </div>
</section>

<!-- ============ GESCHICHTE ============ -->
<section class="section">
  <div class="wrap split">
    <figure class="split__img split__img--quer reveal"><img src="/assets/img/historie-baustelle.jpg" style="object-position:100% 50%" alt="<?= t('Baustelle der ersten DAVINCI Häuser in den 1980er-Jahren', 'Construction site of the first DAVINCI houses in the 1980s') ?>" loading="lazy"><figcaption><?= t('Die Anfänge · DAVINCI HAUS', 'The beginnings · DAVINCI HAUS') ?></figcaption></figure>
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal"><?= t('Geschichte', 'Story') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Bewahren.<br>Und besser machen.', 'Preserve.<br>And make it better.') ?></h2>
      <p class="lead reveal" data-delay="2"><?= t('Über Jahrzehnte standen DAVINCI Häuser für eine eigene Idee vom Wohnen. 2023 endete die Geschichte des alten Unternehmens, die Häuser sind geblieben. Heute bauen wir DAVINCI neu auf.', 'For decades, DAVINCI houses stood for a distinct idea of living. In 2023 the story of the original company ended, but the houses remained. Today we are rebuilding DAVINCI.') ?></p>
      <p class="reveal" data-delay="3"><a href="<?= u('geschichte') ?>" class="link-arrow"><?= t('Die ganze Geschichte', 'The whole story') ?> <svg><use href="#arrow"/></svg></a></p>
    </div>
  </div>
</section>

<!-- ============ SERVICE ============ -->
<section class="section">
  <div class="wrap split split--media-right">
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal">Service</span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Wir bleiben.<br>Auch nach der Übergabe.', 'We stay.<br>Even after handover.') ?></h2>
      <p class="lead reveal" data-delay="2"><?= t('Viele sind nach der Schlüsselübergabe weg. Wir nicht. Mit Fivemeters Bau pflegen und reparieren wir seit 2024 bestehende DAVINCI Häuser. Dieselben Menschen sind auch für Ihr neues Haus da.', 'Many are gone once the keys are handed over. Not us. Through Fivemeters Bau we have been maintaining and repairing existing DAVINCI houses since 2024. The same people are there for your new house too.') ?></p>
      <p class="reveal" data-delay="3"><a href="<?= u('service') ?>" class="link-arrow"><?= t('Zum DAVINCI Service', 'DAVINCI Service') ?> <svg><use href="#arrow"/></svg></a></p>
    </div>
    <figure class="split__img reveal" data-delay="1"><img src="/assets/img/bestand.jpg" alt="<?= t('Ein bestehendes DAVINCI Haus mit Glasgiebel, Terrasse und Teich', 'An existing DAVINCI house with glazed gable, terrace and pond') ?>" loading="lazy"></figure>
  </div>
</section>

<!-- ============ DESIGN-BOOK ============ -->
<section class="section grey dbook-teaser">
  <div class="wrap dbook">
    <?php buch_visual(); ?>
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal">Design-Book</span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Inspiration für<br>zu Hause.', 'Inspiration<br>for home.') ?></h2>
      <p class="lead reveal" data-delay="2"><?= t('Häuser, Grundrisse, Materialien und die Geschichte hinter DAVINCI auf 48 Seiten. In Ruhe durchblättern, bevor wir uns kennenlernen.', 'Homes, floor plans, materials and the story behind DAVINCI across 48 pages. Browse at your leisure before we meet.') ?></p>
      <ul class="ticks reveal" data-delay="2">
        <li><?= t('Als PDF sofort in Ihrem Postfach', 'As a PDF, instantly in your inbox') ?></li>
        <li><?= t('Kostenfrei und unverbindlich', 'Free and without obligation') ?></li>
      </ul>
      <p class="reveal" data-delay="3" style="margin-top:32px"><a href="<?= u('design-book') ?>" class="btn btn--dark"><?= t('Design-Book anfordern', 'Request the Design Book') ?></a></p>
    </div>
  </div>
</section>

<?php claim_band(); seite_ende();
