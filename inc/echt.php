<?php
/** Fotos eines echten DAVINCI Hauses. $hoch = Hochformat links, $quer = zwei Querformate rechts. */
const ECHT = [
  'traufe' => ['echt-detail-traufe-hoch.jpg', '40% 50%', 'Detail eines echten DAVINCI Hauses: Dachüberstand, Holzstützen und Regenkette', 'Detail of a real DAVINCI house: roof overhang, timber columns and rain chain'],
  'garten' => ['echt-fassade-garten.jpg', '50% 50%', 'Echtes DAVINCI Haus: Glasfassade mit dunklem Tragwerk zum Garten', 'A real DAVINCI house: glass facade with dark timber structure facing the garden'],
  'carport' => ['echt-carport-zufahrt.jpg', '50% 50%', 'Echtes DAVINCI Haus mit Carport und Zufahrt im Herbstlicht', 'A real DAVINCI house with carport and driveway in autumn light'],
  'herbst' => ['echt-fassade-herbst.jpg', '50% 50%', 'Echtes DAVINCI Haus: weiße Fassade, dunkles Holztragwerk und auskragende Balken im Herbst', 'A real DAVINCI house: white facade, dark timber structure and cantilevered beams in autumn'],
  'luft' => ['echt-luft-landschaft.jpg', '50% 55%', 'Echtes DAVINCI Haus aus der Luft: Bungalow mit Pultdach-Aufsatz vor weiter Landschaft', 'A real DAVINCI house from above: bungalow with raised mono-pitch roof in an open landscape'],
  'front' => ['echt-luft-front.jpg', '50% 50%', 'Echtes DAVINCI Haus: Bungalow mit Flachdach und Pultdach-Aufsatz von oben', 'A real DAVINCI house: bungalow with flat roof and raised mono-pitch roof from above'],
  'dachecke' => ['echt-detail-dachecke.jpg', '45% 50%', 'Detail eines echten DAVINCI Hauses: auskragende Holzbalken unter dem Flachdach', 'Detail of a real DAVINCI house: cantilevered timber beams under the flat roof'],
  'pultdach' => ['echt-detail-pultdach.jpg', '50% 50%', 'Echtes DAVINCI Haus: auskragendes Pultdach mit dunklen Holzbalken', 'A real DAVINCI house: cantilevered mono-pitch roof with dark timber beams'],
  'dachecke_h' => ['echt-detail-dachecke.jpg', '47% 50%', 'Detail eines echten DAVINCI Hauses: Dachecke mit auskragenden Balken und Regenrohr aus Glas', 'Detail of a real DAVINCI house: roof corner with cantilevered beams and glass downpipe'],
  'garten_h' => ['echt-fassade-garten.jpg', '56% 50%', 'Echtes DAVINCI Haus: raumhohe Fenster mit dunklem Holzrahmen, im Glas spiegelt sich der Garten', 'A real DAVINCI house: floor-to-ceiling windows in dark timber frames reflecting the garden'],
];
function echt_haus(string $hoch, array $quer, string $eyebrow, string $titel, string $text, string $klasse = ''): void {
  $bild = function (string $k, string $mod = '', int $d = 0) { [$f, $pos, $de, $en] = ECHT[$k]; ?>
      <figure class="echt__i<?= $mod ?> reveal"<?= $d ? ' data-delay="' . $d . '"' : '' ?>><img src="/assets/img/<?= $f ?>" style="object-position:<?= $pos ?>" alt="<?= e(t($de, $en)) ?>" loading="lazy"></figure>
  <?php }; ?>
<section class="section<?= $klasse ? ' ' . $klasse : '' ?>">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= $eyebrow ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= $titel ?></h2>
      <p class="lead reveal" data-delay="2" style="max-width:640px"><?= $text ?></p>
    </div>
    <div class="echt">
      <?php $bild($hoch, ' echt__i--hoch'); $bild($quer[0], '', 1); $bild($quer[1], '', 2); ?>
      <p class="echt__cap reveal"><?= t('Ein bestehendes DAVINCI Haus. Echte Fotos, keine Visualisierung.', 'An existing DAVINCI house. Real photos, no visualisation.') ?></p>
    </div>
  </div>
</section>
<?php }
