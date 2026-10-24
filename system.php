<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'system',
  'title' => t('Das DAVINCI System: Managed Diversity in Holz und Glas | DAVINCI', 'The DAVINCI system: Managed Diversity in wood and glass | DAVINCI'),
  'desc'  => t('Gute Architektur steckt bei DAVINCI im System: ein Raster, erprobte Details und viele Varianten in derselben Qualität. Luftraum, Galerien, Holz und Glas.', 'At DAVINCI, good architecture is built into the system: a grid, proven details and many variants in the same quality. Open voids, galleries, wood and glass.'),
  'img'   => 'echt-detail-pultdach.jpg',
]);
seitenkopf(t('Das System', 'The system'), t('Gute Architektur<br>ist kein Extra.', 'Good architecture<br>is not an extra.'),
  t('Bei DAVINCI steckt sie im System. Wir verstehen uns als Systemdesigner: Wir haben ein System entwickelt, das viele Häuser möglich macht und jedes davon gut.', 'At DAVINCI it is built into the system. We see ourselves as system designers: we have developed a system that makes many houses possible, and every one of them good.'),
  'echt-detail-pultdach.jpg', t('Echtes DAVINCI Haus: auskragendes Pultdach mit dunklen Holzbalken', 'A real DAVINCI house: cantilevered mono-pitch roof with dark timber beams'));
?>

<!-- ============ SYSTEMDESIGN ============ -->
<section class="section">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Systemdesign', 'System design') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Mehr als ein Architektenhaus.', 'Beyond the architect-designed house.') ?></h2>
      <p class="lead reveal" data-delay="2" style="max-width:760px"><?= t('Ein einzelner Entwurf ist so gut wie die Planung dahinter. Ein System ist so gut wie jedes Haus, das daraus entsteht. Deshalb haben wir die wichtigen Entscheidungen einmal gründlich getroffen, damit sie in jedem DAVINCI stimmen.', 'A single design is only as good as the planning behind it. A system is as good as every house it produces. That is why we made the important decisions once and thoroughly, so that they are right in every DAVINCI.') ?></p>
    </div>
    <ol class="layers">
      <li class="reveal"><span class="num">01</span><h3><?= t('Das Raster', 'The grid') ?></h3><p><?= t('Ein Raster mit frei stehenden Stützen trägt das Haus und lässt den Grundriss frei.', 'A grid with free-standing columns carries the house and leaves the floor plan free.') ?></p></li>
      <li class="reveal" data-delay="1"><span class="num">02</span><h3><?= t('Die Details', 'The details') ?></h3><p><?= t('Die Anschlüsse von Holz und Glas sind einmal durchdacht und werden in jedem Haus gleich gut gebaut.', 'The junctions of wood and glass are thought through once and built equally well in every house.') ?></p></li>
      <li class="reveal" data-delay="2"><span class="num">03</span><h3><?= t('Die Varianten', 'The variants') ?></h3><p><?= t('Größe, Ebenen, Luftraum, Galerie, Fassade und Ausbau lassen sich innerhalb des Systems kombinieren.', 'Size, levels, open void, gallery, facade and fit-out can be combined within the system.') ?></p></li>
    </ol>
  </div>
</section>

<!-- ============ RASTER (Animation) ============ -->
<section class="section raster" id="raster" data-raster>
  <div class="wrap raster__grid">
    <div class="raster__text">
      <span class="eyebrow reveal"><?= t('Ein Raster, viele Häuser', 'One grid, many houses') ?></span>
      <ol class="raster__steps">
        <li data-rstep="0" class="is-active"><span class="num">01</span><h3><?= t('Das Raster', 'The grid') ?></h3><p><?= t('Alle 1,50 m eine Achse für Wände und Fenster, alle drei Meter eine Stütze. Räume nutzen ganze und halbe Rastermaße. Darauf baut jedes DAVINCI Haus auf.', 'An axis every 1.50 m for walls and windows, a column every three metres. Rooms use full and half grid dimensions. Every DAVINCI house is built on this.') ?></p></li>
        <li data-rstep="1"><span class="num">02</span><h3><?= t('Eher offen', 'More open') ?></h3><p><?= t('Diele, Bad, WC, Technik und Schlafen bilden den festen Kern. Der Rest ist ein einziger Raum mit 90 m² zum Garten. Zwei Stützen stehen frei im Raum, die Wände sind frei.', 'Entry, bath, WC, utility and bedroom form a fixed core. The rest is one single space of 90 m² facing the garden. Two columns stand freely in the room, the walls are free.') ?></p></li>
        <li data-rstep="2"><span class="num">03</span><h3><?= t('Eher privat', 'More private') ?></h3><p><?= t('Derselbe Kern, dieselben Stützen. Ein Flur erschließt Arbeiten und Kinderzimmer, Kochen und Essen bleiben offen zum Wohnen. Dieselbe Qualität, ein anderes Leben.', 'The same core, the same columns. A hall leads to the study and the child’s room, kitchen and dining stay open to the living room. The same quality, a different way of living.') ?></p></li>
      </ol>
    </div>
    <div class="raster__visual"><svg class="raster-svg" viewBox="0 0 600 620" role="img" aria-label="<?= t('Das DAVINCI Raster mit zwei Grundrissen: offen und privat', 'The DAVINCI grid with two floor plans: open and private') ?>"><rect class="r-frame" x="60" y="60" width="480" height="480"/><g class="r-lines"><line class="r-grid" x1="120" y1="60" x2="120" y2="540"/><line class="r-grid" x1="180" y1="60" x2="180" y2="540"/><line class="r-grid" x1="240" y1="60" x2="240" y2="540"/><line class="r-grid" x1="300" y1="60" x2="300" y2="540"/><line class="r-grid" x1="360" y1="60" x2="360" y2="540"/><line class="r-grid" x1="420" y1="60" x2="420" y2="540"/><line class="r-grid" x1="480" y1="60" x2="480" y2="540"/><line class="r-grid" x1="60" y1="120" x2="540" y2="120"/><line class="r-grid" x1="60" y1="180" x2="540" y2="180"/><line class="r-grid" x1="60" y1="240" x2="540" y2="240"/><line class="r-grid" x1="60" y1="300" x2="540" y2="300"/><line class="r-grid" x1="60" y1="360" x2="540" y2="360"/><line class="r-grid" x1="60" y1="420" x2="540" y2="420"/><line class="r-grid" x1="60" y1="480" x2="540" y2="480"/></g><g class="r-rooms"><g class="r-room r-a r-b" data-room="technik"><polygon points="60,60 180,60 180,120 60,120" fill="#d2d1cd"/><text x="120" y="90"><tspan x="120" dy="-6"><?= t('Technik', 'Utility') ?></tspan><tspan class="r-qm" x="120" dy="17">4,5 m²</tspan></text></g><g class="r-room r-a r-b" data-room="wc"><polygon points="60,120 180,120 180,180 60,180" fill="#d4dcdd"/><text x="120" y="150"><tspan x="120" dy="-6"><?= t('WC', 'WC') ?></tspan><tspan class="r-qm" x="120" dy="17">4,5 m²</tspan></text></g><g class="r-room r-a r-b" data-room="eingang"><polygon points="180,60 360,60 360,180 180,180" fill="#d2d1cd"/><text x="270" y="120"><tspan x="270" dy="-6"><?= t('Diele', 'Entry') ?></tspan><tspan class="r-qm" x="270" dy="17">13,5 m²</tspan></text></g><g class="r-room r-a r-b" data-room="bad"><polygon points="360,60 540,60 540,180 360,180" fill="#d4dcdd"/><text x="450" y="120"><tspan x="450" dy="-6"><?= t('Bad', 'Bath') ?></tspan><tspan class="r-qm" x="450" dy="17">13,5 m²</tspan></text></g><g class="r-room r-a r-b" data-room="schlafen"><polygon points="420,180 540,180 540,420 420,420" fill="#ebe7dc"/><text x="480" y="300"><tspan x="480" dy="-6"><?= t('Schlafen', 'Bedroom') ?></tspan><tspan class="r-qm" x="480" dy="17">18 m²</tspan></text></g><g class="r-room r-a" data-room="offen"><polygon points="60,180 420,180 420,420 540,420 540,540 60,540" fill="#e8dcc8"/><text x="240" y="404"><tspan x="240" dy="-6"><?= t('Wohnen · Kochen · Essen', 'Living · Kitchen · Dining') ?></tspan><tspan class="r-qm" x="240" dy="17">90 m²</tspan></text></g><g class="r-room r-b" data-room="flur"><polygon points="180,180 420,180 420,240 180,240" fill="#d2d1cd"/><text x="300" y="210"><tspan x="300"><?= t('Flur', 'Hall') ?></tspan></text></g><g class="r-room r-b" data-room="arbeiten"><polygon points="60,180 180,180 180,300 60,300" fill="#ebe7dc"/><text x="120" y="240"><tspan x="120" dy="-6"><?= t('Arbeiten', 'Study') ?></tspan><tspan class="r-qm" x="120" dy="17">9 m²</tspan></text></g><g class="r-room r-b" data-room="kind"><polygon points="60,300 180,300 180,540 60,540" fill="#ebe7dc"/><text x="120" y="420"><tspan x="120" dy="-6"><?= t('Kind', 'Child') ?></tspan><tspan class="r-qm" x="120" dy="17">18 m²</tspan></text></g><g class="r-room r-b" data-room="kochen"><polygon points="180,240 420,240 420,360 180,360" fill="#e8dcc8" class="r-open"/><text x="300" y="272"><tspan x="300" dy="-6"><?= t('Kochen · Essen', 'Kitchen · Dining') ?></tspan><tspan class="r-qm" x="300" dy="17">18 m²</tspan></text></g><g class="r-room r-b" data-room="wohnen"><polygon points="180,360 420,360 420,420 540,420 540,540 180,540" fill="#e8dcc8" class="r-open"/><text x="360" y="480"><tspan x="360" dy="-6"><?= t('Wohnen', 'Living') ?></tspan><tspan class="r-qm" x="360" dy="17">36 m²</tspan></text></g></g><rect class="r-border" x="60" y="60" width="480" height="480"/><g class="r-posts"><rect class="r-post" x="55" y="55" width="10" height="10"/><rect class="r-post" x="175" y="55" width="10" height="10"/><rect class="r-post" x="295" y="55" width="10" height="10"/><rect class="r-post" x="415" y="55" width="10" height="10"/><rect class="r-post" x="535" y="55" width="10" height="10"/><rect class="r-post" x="55" y="175" width="10" height="10"/><rect class="r-post" x="535" y="175" width="10" height="10"/><rect class="r-post" x="55" y="295" width="10" height="10"/><rect class="r-post" x="175" y="295" width="10" height="10"/><rect class="r-post" x="295" y="295" width="10" height="10"/><rect class="r-post" x="415" y="295" width="10" height="10"/><rect class="r-post" x="535" y="295" width="10" height="10"/><rect class="r-post" x="55" y="415" width="10" height="10"/><rect class="r-post" x="535" y="415" width="10" height="10"/><rect class="r-post" x="55" y="535" width="10" height="10"/><rect class="r-post" x="175" y="535" width="10" height="10"/><rect class="r-post" x="295" y="535" width="10" height="10"/><rect class="r-post" x="415" y="535" width="10" height="10"/><rect class="r-post" x="535" y="535" width="10" height="10"/></g><path class="r-entry" d="M270 22 L270 50 M264 43 L270 51 L276 43"/><text class="r-dim" x="90" y="44">1,50 m</text><line class="r-dimline" x1="60" y1="50" x2="120" y2="50"/><text class="r-dim" x="360" y="44">3,00 m</text><line class="r-dimline" x1="300" y1="50" x2="420" y2="50"/><line class="r-dimline" x1="60" y1="566" x2="540" y2="566"/><line class="r-dimline" x1="60" y1="560" x2="60" y2="572"/><line class="r-dimline" x1="540" y1="560" x2="540" y2="572"/><text class="r-dim" x="300" y="590"><?= t('12,00 m · Garten im Süden', '12.00 m · garden to the south') ?></text></svg></div>
  </div>
</section>

<!-- ============ MANAGED DIVERSITY ============ -->
<section class="section grey" id="managed-diversity">
  <div class="wrap split split--media-right">
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal">Managed Diversity</span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Viele Möglichkeiten.<br>Eine Qualität.', 'Many possibilities.<br>One quality.') ?></h2>
      <p class="lead reveal" data-delay="2"><?= t('Ob Glasgiebel am Hang, Pavillon mit Flachdach oder klarer Kubus: Jedes DAVINCI Haus entsteht aus demselben System. Form, Fassade und Grundriss passen sich Ihrem Grundstück an. Sie haben Auswahl, und die Qualität bleibt gesichert.', 'Whether a glazed gable on a slope, a pavilion with a flat roof or a clear cube: every DAVINCI house comes from the same system. Form, facade and floor plan adapt to your plot. You have choice, and the quality stays assured.') ?></p>
    </div>
    <figure class="split__img reveal" data-delay="1"><img src="/assets/img/dv-16-pultdach-hang-carport-nacht.jpg" style="object-position:45% 50%" alt="<?= t('DAVINCI Haus am Hang mit Pultdach, Terrasse und Carport bei Nacht', 'DAVINCI house on a slope with mono-pitch roof, terrace and carport at night') ?>" loading="lazy"></figure>
  </div>
</section>

<!-- ============ RAUM ============ -->
<section class="section" id="raum">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Raum', 'Space') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Raum, der mehr kann.', 'Space that does more.') ?></h2>
    </div>
    <div class="features" data-features>
      <div class="features__media" aria-hidden="true">
        <img src="/assets/img/galerie.jpg" alt="" class="is-active" data-feature-img="0">
        <img src="/assets/img/tragwerk.jpg" alt="" data-feature-img="1" loading="lazy">
        <img src="/assets/img/dv-03-giebel-winter-tag.jpg" alt="" data-feature-img="2" loading="lazy">
        <span class="tag" data-feature-tag><?= t('Luftraum', 'Open void') ?></span>
      </div>
      <div class="features__steps">
        <article class="feature is-active" data-feature="0" data-label="<?= t('Luftraum', 'Open void') ?>">
          <div class="feature__img-m"><img src="/assets/img/galerie.jpg" alt="<?= t('Offener Luftraum über dem Essbereich mit Galerie', 'Open void above the dining area with gallery') ?>" loading="lazy"></div>
          <span class="idx">01</span>
          <h3 class="h2"><?= t('Luftraum.<br>Licht bis tief ins Haus.', 'Open void.<br>Light deep into the house.') ?></h3>
          <p class="lead"><?= t('Offene Lufträume reichen bis unter das Dach. Das Licht fällt von oben und von der Seite bis in die Mitte des Hauses.', 'Open voids reach up to the roof. Light falls from above and from the side into the heart of the house.') ?></p>
        </article>
        <article class="feature" data-feature="1" data-label="<?= t('Raumhöhe', 'Ceiling height') ?>">
          <div class="feature__img-m"><img src="/assets/img/tragwerk.jpg" alt="<?= t('Hoher Wohnraum mit sichtbarem Tragwerk und bodentiefen Fenstern', 'Tall living space with exposed structure and floor-to-ceiling windows') ?>" loading="lazy"></div>
          <span class="idx">02</span>
          <h3 class="h2"><?= t('Großzügige<br>Raumhöhen.', 'Generous<br>ceiling heights.') ?></h3>
          <p class="lead"><?= t('Mehr Höhe heißt größere Fenster, ruhigere Proportionen und mehr Licht in jedem Raum.', 'More height means larger windows, calmer proportions and more light in every room.') ?></p>
          <ul>
            <li><?= t('Lichte Raumhöhen über dem Standardmaß', 'Clear ceiling heights above the standard') ?></li>
            <li><?= t('Raumhohe Verglasung für viel Tageslicht', 'Floor-to-ceiling glazing for plenty of daylight') ?></li>
          </ul>
        </article>
        <article class="feature" data-feature="2" data-label="<?= t('Galerie', 'Gallery') ?>">
          <div class="feature__img-m"><img src="/assets/img/dv-03-giebel-winter-tag.jpg" alt="<?= t('Glasgiebel mit Galerie hinter dem Glasgeländer', 'Glazed gable with gallery behind the glass balustrade') ?>" loading="lazy"></div>
          <span class="idx">03</span>
          <h3 class="h2"><?= t('Galerien.<br>Räume, die sich begegnen.', 'Galleries.<br>Spaces that meet.') ?></h3>
          <p class="lead"><?= t('Galerien verbinden die Ebenen. Man sieht sich, und aus zwei Geschossen wird ein gemeinsamer Raum.', 'Galleries connect the levels. You see each other, and two storeys become one shared space.') ?></p>
          <ul>
            <li><?= t('Brüstungen aus Glas für freie Durchblicke', 'Glass balustrades for open views') ?></li>
            <li><?= t('Durchblicke über zwei Ebenen', 'Views across two levels') ?></li>
          </ul>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- ============ MATERIAL ============ -->
<section class="section dark" id="material">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Material', 'Material') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Holz und Glas.', 'Wood and glass.') ?></h2>
    </div>
    <div class="mats">
      <div class="reveal"><h3><?= t('Holz', 'Wood') ?></h3><p><?= t('Warm, lebendig, nachwachsend. Das Tragwerk trägt das Haus und prägt seinen Charakter.', 'Warm, alive, renewable. The structure carries the house and shapes its character.') ?></p></div>
      <div class="reveal" data-delay="1"><h3><?= t('Glas', 'Glass') ?></h3><p><?= t('Wo andere Wände setzen, öffnen wir. Innen und außen gehen ineinander über.', 'Where others build walls, we open up. Inside and outside flow into each other.') ?></p></div>
      <div class="reveal" data-delay="2"><h3><?= t('Detail', 'Detail') ?></h3><p><?= t('Schmale Profile und saubere Anschlüsse. Sie schützen das Holz und halten die Linien klar.', 'Slim profiles and clean junctions. They protect the wood and keep the lines clean.') ?></p></div>
      <div class="reveal" data-delay="3"><h3><?= t('Licht', 'Light') ?></h3><p><?= t('Das eigentliche Material. Alles andere ist dafür gebaut.', 'The real material. Everything else is built for it.') ?></p></div>
    </div>
    <?php require __DIR__ . '/inc/moodboard.php'; ?>
  </div>
</section>

<!-- ============ DETAIL AM ECHTEN HAUS ============ -->
<?php require_once __DIR__ . '/inc/echt.php';
echt_haus('traufe', ['dachecke', 'herbst'], t('Detail', 'Detail'), t('Das System<br>im Detail.', 'The system<br>in detail.'),
  t('Auskragende Balken, schlanke Stützen, ein Dach, das die Fassade schützt. Die Details sind nicht dekorativ, sie folgen dem Raster.', 'Cantilevered beams, slim columns, a roof that protects the facade. The details are not decorative, they follow the grid.')); ?>

<!-- ============ NACHHALTIGKEIT ============ -->
<section class="section grey" id="nachhaltigkeit">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Nachhaltigkeit', 'Sustainability') ?></span>
      <h2 class="h1 reveal" data-delay="1"><?= t('Nachhaltig<br>in jeder Dimension.', 'Sustainable<br>in every dimension.') ?></h2>
      <p class="lead reveal" data-delay="2" style="max-width:680px"><?= t('Nachhaltigkeit ist bei uns kein Siegel, sondern Teil des Systems. Auch gestalterisch: Was nicht der Mode folgt, muss nicht ersetzt werden.', 'For us, sustainability is not a label but part of the system. In design too: what does not follow fashion does not need replacing.') ?></p>
    </div>
    <div class="dims">
      <div class="dim reveal"><span class="num">01</span><h3>Material</h3><p><?= t('Holz wächst nach und speichert CO₂, solange das Haus steht.', 'Wood grows back and stores CO₂ for as long as the house stands.') ?></p></div>
      <div class="dim reveal" data-delay="1"><span class="num">02</span><h3><?= t('Fertigung', 'Production') ?></h3><p><?= t('Die Bauteile entstehen präzise im Werk, geschützt vor Wind und Wetter.', 'Components are made precisely in the works, protected from wind and weather.') ?></p></div>
      <div class="dim reveal" data-delay="2"><span class="num">03</span><h3><?= t('Gestaltung', 'Design') ?></h3><p><?= t('Zeitlos statt modisch. Ein Haus, das man nicht nach zehn Jahren erneuern möchte.', 'Timeless rather than fashionable. A house you will not want to renew after ten years.') ?></p></div>
      <div class="dim reveal" data-delay="3"><span class="num">04</span><h3><?= t('Nutzung', 'Use') ?></h3><p><?= t('Frei stehende Stützen lassen den Grundriss offen. Räume können sich mit dem Leben verändern.', 'Free-standing columns keep the floor plan open. Rooms can change with life.') ?></p></div>
      <div class="dim reveal" data-delay="4"><span class="num">05</span><h3><?= t('Pflege', 'Care') ?></h3><p><?= t('Wir betreuen Häuser auch nach der Übergabe. Was gepflegt wird, bleibt.', 'We look after houses after handover too. What is cared for, stays.') ?></p></div>
    </div>
  </div>
</section>

<?php claim_band(); seite_ende();
