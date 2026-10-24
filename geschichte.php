<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'geschichte',
  'title' => t('Geschichte: Bewahren und besser machen | DAVINCI', 'Story: preserve and make it better | DAVINCI'),
  'desc'  => t('Von 1936 bis heute: Wie aus dem Holz-Glas-Haus DAVINCI HAUS wurde, warum die Häuser geblieben sind und wie wir DAVINCI neu aufbauen.', 'From 1936 to today: how the wood and glass house became DAVINCI HAUS, why the houses remained and how we are rebuilding DAVINCI.'),
  'img'   => 'historie.jpg',
]);
seitenkopf(t('Geschichte', 'Story'), t('Bewahren.<br>Und besser machen.', 'Preserve.<br>And make it better.'),
  t('Über Jahrzehnte standen DAVINCI Häuser für eine eigene Idee vom Wohnen: wenige Wände, frei stehende Stützen, Glas vom Boden bis zur Decke. 2023 endete die Geschichte des alten Unternehmens. Die Häuser sind geblieben.', 'For decades, DAVINCI houses stood for a distinct idea of living: few walls, free-standing columns, glass from floor to ceiling. In 2023 the story of the original company came to an end. The houses remained.'),
  'historie.jpg', t('Ein klassisches DAVINCI HAUS aus Holz und Glas im Winter', 'A classic DAVINCI HAUS of wood and glass in winter'));
?>

<section class="section dark" id="chronik">
  <div class="wrap history">
    <figure class="history__media history__media--quer history__media--stack reveal" data-history-media>
      <?php foreach ([
        ['historie-1936-saegewerk.jpg', '50% 60%', t('1936 · Das Sägewerk im Westerwald', '1936 · The sawmill in the Westerwald'), t('Historisches Foto des Sägewerks im Westerwald mit Holzstämmen im Vordergrund', 'Historic photo of the sawmill in the Westerwald with logs in the foreground')],
        ['historie-baustelle.jpg', '100% 50%', t('Die Anfänge · Baustelle in den 1980er-Jahren', 'The beginnings · construction site in the 1980s'), t('Baustelle der ersten DAVINCI Häuser in den 1980er-Jahren', 'Construction site of the first DAVINCI houses in the 1980s')],
        ['historie-1993-musterhaus.jpg', '50% 45%', t('1993 · Musterhaus aus Holz und Glas', '1993 · Show house of wood and glass'), t('Musterhaus mit dunklem Holztragwerk, Glasgiebel und Pergola in der Dämmerung', 'Show house with dark timber structure, glazed gable and pergola at dusk')],
        ['historie-2015-glasgiebel.jpg', '50% 55%', t('Ein DAVINCI HAUS mit Glasgiebel', 'A DAVINCI HAUS with glazed gable'), t('Helles DAVINCI Haus mit Glasgiebel und Balkon unter blauem Himmel', 'Light DAVINCI house with glazed gable and balcony under a blue sky')],
        ['bestand.jpg', '50% 50%', t('Ein bestehendes DAVINCI Haus · heute', 'An existing DAVINCI house · today'), t('Ein bestehendes DAVINCI Haus mit Glasgiebel und Teich', 'An existing DAVINCI house with glazed gable and pond')],
        ['dv-03-giebel-winter-tag.jpg', '50% 40%', t('DAVINCI heute · Alpina', 'DAVINCI today · Alpina'), t('Ein neues DAVINCI Haus mit Glasgiebel im Schnee', 'A new DAVINCI house with glazed gable in the snow')],
      ] as $i => [$img, $pos, $cap, $alt]): ?>
      <img src="/assets/img/<?= $img ?>" style="object-position:<?= $pos ?>" alt="<?= e($alt) ?>" data-cap="<?= e($cap) ?>"<?= $i ? ' loading="lazy"' : ' class="is-active"' ?>>
      <?php endforeach; ?>
      <figcaption data-history-cap><?= t('1936 · Das Sägewerk im Westerwald', '1936 · The sawmill in the Westerwald') ?></figcaption>
    </figure>
    <div>
      <div class="section-head" style="margin-bottom:0">
        <span class="eyebrow reveal"><?= t('Chronik', 'Timeline') ?></span>
        <p class="lead reveal" data-delay="1"><?= t('Wir kennen diese Häuser von innen, weil wir sie pflegen und reparieren. Heute bauen wir DAVINCI neu auf. Wir bewahren, was diese Architektur zeitlos macht, und verbessern, was sich verbessern lässt.', 'We know these houses from the inside, because we maintain and repair them. Today we are rebuilding DAVINCI. We preserve what makes this architecture timeless and improve what can be improved.') ?></p>
      </div>
      <ol class="timeline timeline--phases" data-timeline><li class="timeline__progress" aria-hidden="true"><i></i></li>
        <li class="timeline__phase reveal"><?= t('Das Original', 'The original') ?></li>
        <li class="reveal is-past" data-bild="0"><span class="num">1936</span><div><h4><?= t('Die Wurzeln', 'The roots') ?></h4><p><?= t('Gestartet als Sägewerk im Westerwald. Holz ist von Anfang an das Material, um das sich alles dreht.', 'It all started as a sawmill in the Westerwald. From the very beginning, wood is the material everything revolves around.') ?></p></div></li>
        <li class="reveal is-past" data-bild="1"><span class="num">1974</span><div><h4><?= t('Das Modulhaus', 'The modular house') ?></h4><p><?= t('Bauen in Holz wird zum Architekturprinzip.', 'Building in wood becomes an architectural principle.') ?></p></div></li>
        <li class="reveal is-past" data-bild="1"><span class="num">1981</span><div><h4><?= t('Die Idee', 'The idea') ?></h4><p><?= t('Gemeinsam mit Architekten entsteht das Prinzip: freier Grundriss, wenige Wände, Glas vom Boden bis zur Decke.', 'Together with architects, the principle takes shape: open plan, few walls, glass from floor to ceiling.') ?></p></div></li>
        <li class="reveal is-past" data-bild="2"><span class="num">1995</span><div><h4><?= t('Der Name', 'The name') ?></h4><p><?= t('Aus dem Holz-Glas-Haus wird DAVINCI HAUS.', 'The wood and glass house becomes DAVINCI HAUS.') ?></p></div></li>
        <li class="reveal is-past" data-bild="3"><span class="num">2015</span><div><h4><?= t('Weit über Deutschland hinaus', 'Far beyond Germany') ?></h4><p><?= t('Über 1.000 DAVINCI Häuser stehen in Mitteleuropa, England, Irland, der Ukraine und sogar in Japan.', 'More than 1,000 DAVINCI houses stand in Central Europe, England, Ireland, Ukraine and even Japan.') ?></p></div></li>
        <li class="reveal is-past" data-bild="3"><span class="num">2023</span><div><h4><?= t('Das Ende', 'The end') ?></h4><p><?= t('Das Unternehmen stellt den Betrieb ein. Die Häuser bleiben.', 'The company ceases trading. The houses remain.') ?></p></div></li>
        <li class="timeline__phase timeline__phase--new reveal"><?= t('Der neue Anfang', 'A new beginning') ?></li>
        <li class="reveal" data-bild="4"><span class="num">2024</span><div><h4><?= t('Der Service', 'The service') ?></h4><p><?= t('Mit Fivemeters Bau beginnen wir, bestehende DAVINCI Häuser zu betreuen und zu erhalten.', 'Through Fivemeters Bau, we begin looking after and preserving existing DAVINCI houses.') ?> <a href="<?= u('service') ?>" class="link-arrow"><?= t('Zum DAVINCI Service', 'DAVINCI Service') ?> <svg><use href="#arrow"/></svg></a></p></div></li>
        <li class="reveal" data-bild="5"><span class="num"><?= t('Heute', 'Today') ?></span><div><h4><?= t('DAVINCI baut wieder', 'DAVINCI builds again') ?></h4><p><?= t('Mit neuen Gründern, der zeitlosen Architektur von damals, einem durchdachten System und allem, was wir im Service an diesen Häusern gelernt haben.', 'With new founders, the timeless architecture of the original, a considered system and everything we have learned from servicing these houses.') ?></p></div></li>
      </ol>
      <div class="firsts reveal">
        <p class="firsts__label"><?= t('Die ersten Häuser', 'The first houses') ?></p>
        <h3><?= t('Wir stehen am Anfang. Und sagen das offen.', 'We are at the beginning. And we say so openly.') ?></h3>
        <p><?= t('Wer jetzt mit uns baut, bekommt eines der ersten neuen DAVINCI Häuser. Sie sprechen direkt mit den Gründern und bauen mit Menschen, die diese Häuser von innen kennen und genau wissen, was sie besser machen wollen.', 'If you build with us now, you get one of the first new DAVINCI houses. You talk directly to the founders and build with people who know these houses from the inside and know exactly what they want to improve.') ?></p>
      </div>
    </div>
  </div>
</section>
<?php claim_band(t('Das gilt für jedes Haus, das wir bauen. Und für die Marke DAVINCI, die wir bewahren und besser machen.', 'That is true of every house we build. And of the DAVINCI brand, which we preserve and make better.')); seite_ende();
