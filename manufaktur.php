<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'manufaktur',
  'title' => t('Manufaktur: Präzision im Werk, Montage in 5 bis 7 Tagen | DAVINCI', 'Manufacture: precision in the works, assembly in 5 to 7 days | DAVINCI'),
  'desc'  => t('Die Bauteile Ihres DAVINCI Hauses entstehen bei einem ausgewählten Manufakturpartner in Deutschland. Der Weg zu Ihrem Haus in fünf Schritten.', 'The components of your DAVINCI house are made by a selected manufacturing partner in Germany. The path to your home in five steps.'),
  'img'   => 'skizze-dach.jpg',
]);
seitenkopf(t('Manufaktur', 'Manufacture'), t('Präzision im Werk.', 'Precision in the works.'),
  t('Die Bauteile Ihres Hauses entstehen bei einem sorgfältig ausgewählten Manufakturpartner in Deutschland. Präzise nach unseren Plänen und geschützt vor Wind und Wetter.', 'The components of your house are made by a carefully selected manufacturing partner in Germany. Precisely to our plans and protected from wind and weather.'));
?>

<section class="section--tight" style="padding-top:0">
  <div class="wrap">
    <div class="prefab-hero">
      <div class="section-head" style="margin-bottom:0">
        <h2 class="h2 reveal"><?= t('Gefertigt im Werk.<br>Montiert in Tagen.', 'Made in the works.<br>Assembled in days.') ?></h2>
        <p class="lead reveal" data-delay="1"><?= t('Tragwerk und Hülle entstehen nach den Plänen aus dem DAVINCI System. Auf Ihrem Grundstück steht die Hülle dann in fünf bis sieben Tagen.*', 'Structure and shell are made to plans from the DAVINCI system. On your plot, the shell then stands within five to seven days.*') ?></p>
        <p class="reveal" data-delay="2"><a href="#termin" class="link-arrow" data-booking><?= t('Termin für ein Erstgespräch', 'Book an initial consultation') ?> <svg><use href="#arrow"/></svg></a></p>
      </div>
      <div class="prefab-hero__sketch reveal" data-delay="1">
        <img src="/assets/img/skizze-dach.jpg" alt="<?= t('Konzeptzeichnung: Gebäudehülle und Tragwerk eines DAVINCI Hauses', 'Concept drawing: shell and structure of a DAVINCI house') ?>" loading="lazy">
      </div>
    </div>
    <div class="compare">
      <div class="compare__item reveal"><strong class="num" data-count="5-7">5–7<small><?= t('Tage', 'days') ?></small></strong><span><?= t('vom leeren Grundstück bis zur stehenden Hülle*', 'from an empty plot to a standing shell*') ?></span></div>
      <div class="compare__item reveal" data-delay="1"><strong class="num">DE<small><?= t('Fertigung', 'made') ?></small></strong><span><?= t('bei einem ausgewählten Manufakturpartner in Deutschland', 'by a selected manufacturing partner in Germany') ?></span></div>
      <div class="compare__item reveal" data-delay="2"><strong class="num">1<small><?= t('Ansprechpartner', 'contact') ?></small></strong><span><?= t('von der ersten Skizze bis nach der Übergabe', 'from the first sketch until after handover') ?></span></div>
      <div class="compare__item reveal" data-delay="3"><strong class="num">1<small>System</small></strong><span><?= t('für Produkte und individuelle Häuser', 'for models and bespoke houses') ?></span></div>
    </div>
    <p class="footnote">* <?= t('Richtwert für die Montage, abhängig von Entwurf, Haustyp und Baustelle. Ohne Innenausbau.', 'Guide value for assembly, depending on design, house type and site. Excludes interior fit-out.') ?></p>
  </div>
</section>

<?php require __DIR__ . '/inc/prozess.php'; ?>

<?php require_once __DIR__ . '/inc/echt.php';
echt_haus('dachecke_h', ['pultdach', 'carport'], t('Das Ergebnis', 'The result'), t('Präzision,<br>die bleibt.', 'Precision<br>that lasts.'),
  t('Was im Werk vorgefertigt wird, steht Jahrzehnte später noch genauso klar da. Saubere Anschlüsse, gerade Kanten, ruhige Flächen.', 'What is prefabricated in the works still stands just as clean decades later. Neat junctions, straight edges, calm surfaces.')); ?>

<section class="section--tight">
  <div class="wrap after">
    <div class="reveal">
      <p class="eyebrow"><?= t('Und danach?', 'And afterwards?') ?></p>
      <h2 class="h2" style="margin-top:14px"><?= t('Wir bleiben.', 'We stay.') ?></h2>
    </div>
    <div class="reveal" data-delay="1">
      <p class="lead"><?= t('Nach der Übergabe sind wir weiter für Ihr Haus da. Wartung, Reparatur und Erweiterung kommen aus derselben Hand.', 'After handover we are still there for your house. Maintenance, repair and extension come from the same hands.') ?></p>
      <p style="margin-top:18px"><a href="<?= u('service') ?>" class="link-arrow"><?= t('Zum DAVINCI Service', 'DAVINCI Service') ?> <svg><use href="#arrow"/></svg></a></p>
    </div>
  </div>
</section>

<?php claim_band(); seite_ende();
