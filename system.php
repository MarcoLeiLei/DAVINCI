<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'system',
  'title' => t('Das DAVINCI System: Managed Diversity in Holz und Glas | DAVINCI', 'The DAVINCI system: Managed Diversity in wood and glass | DAVINCI'),
  'desc'  => t('Gute Architektur steckt bei DAVINCI im System: ein Raster, erprobte Details und viele Varianten in derselben Qualität. Luftraum, Galerien, Holz und Glas.', 'At DAVINCI, good architecture is built into the system: a grid, proven details and many variants in the same quality. Open voids, galleries, wood and glass.'),
  'img'   => 'dv-05-flachdach-schwarz-abend.jpg',
]);
seitenkopf(t('Das System', 'The system'), t('Gute Architektur<br>ist kein Extra.', 'Good architecture<br>is not an extra.'),
  t('Bei DAVINCI steckt sie im System. Wir verstehen uns als Systemdesigner: Wir haben ein System entwickelt, das viele Häuser möglich macht und jedes davon gut.', 'At DAVINCI it is built into the system. We see ourselves as system designers: we have developed a system that makes many houses possible, and every one of them good.'),
  'dv-05-flachdach-schwarz-abend.jpg', t('DAVINCI Haus mit schwarzem Holzskelett und raumhoher Verglasung in der Abenddämmerung', 'DAVINCI house with black timber frame and floor-to-ceiling glazing at dusk'));
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
        <li data-rstep="0" class="is-active"><span class="num">01</span><h3><?= t('Das Raster', 'The grid') ?></h3><p><?= t('Alle 1,50 m eine Linie, alle drei Meter eine Stütze. Darauf baut jedes DAVINCI Haus auf.', 'A line every 1.50 m, a column every three metres. Every DAVINCI house is built on this.') ?></p></li>
        <li data-rstep="1"><span class="num">02</span><h3><?= t('Eher offen', 'More open') ?></h3><p><?= t('Ein großer Raum für Wohnen, Kochen und Essen. Die Stützen tragen, die Wände sind frei.', 'One large space for living, cooking and dining. The columns carry the load, the walls are free.') ?></p></li>
        <li data-rstep="2"><span class="num">03</span><h3><?= t('Eher privat', 'More private') ?></h3><p><?= t('Auf demselben Raster: ein Flur, ein Arbeitszimmer, ein Gästezimmer. Dieselbe Qualität, ein anderes Leben.', 'On the same grid: a hall, a study, a guest room. The same quality, a different way of living.') ?></p></li>
      </ol>
    </div>
    <div class="raster__visual"><svg class="raster-svg" viewBox="0 0 600 600" role="img" aria-label="<?= t('Das DAVINCI Raster und zwei Grundrisse darauf', 'The DAVINCI grid and two floor plans on it') ?>"><rect class="r-frame" x="60" y="60" width="480" height="480"/><g class="r-rooms"><g class="r-room r-a r-b" data-room="eingang"><rect x="60" y="60" width="180.0" height="120" fill="#d2d1cd"/><text x="150.0" y="120.0"><?= t('Eingang', 'Entry') ?></text></g><g class="r-room r-a r-b" data-room="technik"><rect x="240.0" y="60" width="120" height="120" fill="#d2d1cd"/><text x="300.0" y="120.0"><?= t('Technik', 'Utility') ?></text></g><g class="r-room r-a r-b" data-room="bad"><rect x="360.0" y="60" width="180.0" height="120" fill="#d4dcdd"/><text x="450.0" y="120.0"><?= t('Bad', 'Bath') ?></text></g><g class="r-room r-a" data-room="wohnen-gross"><rect x="60" y="180" width="480" height="360" fill="#e8dcc8"/><text x="300.0" y="360.0"><?= t('Wohnen · Kochen · Essen', 'Living · Kitchen · Dining') ?></text></g><g class="r-room r-b" data-room="flur"><rect x="60" y="180" width="480" height="60.0" fill="#d2d1cd"/><text x="300.0" y="210.0"><?= t('Flur', 'Hall') ?></text></g><g class="r-room r-b" data-room="arbeiten"><rect x="60" y="240.0" width="180.0" height="150.0" fill="#ebe7dc"/><text x="150.0" y="315.0"><?= t('Arbeiten', 'Study') ?></text></g><g class="r-room r-b" data-room="gast"><rect x="60" y="390.0" width="180.0" height="150.0" fill="#ebe7dc"/><text x="150.0" y="465.0"><?= t('Gast', 'Guest') ?></text></g><g class="r-room r-b" data-room="kochen"><rect x="240.0" y="240.0" width="300.0" height="150.0" fill="#e8dcc8"/><text x="390.0" y="315.0"><?= t('Kochen · Essen', 'Kitchen · Dining') ?></text></g><g class="r-room r-b" data-room="wohnen"><rect x="240.0" y="390.0" width="300.0" height="150.0" fill="#e8dcc8"/><text x="390.0" y="465.0"><?= t('Wohnen', 'Living') ?></text></g></g><g class="r-lines"><line class="r-grid" x1="120.0" y1="60" x2="120.0" y2="540"/><line class="r-grid" x1="180.0" y1="60" x2="180.0" y2="540"/><line class="r-grid" x1="240.0" y1="60" x2="240.0" y2="540"/><line class="r-grid" x1="300.0" y1="60" x2="300.0" y2="540"/><line class="r-grid" x1="360.0" y1="60" x2="360.0" y2="540"/><line class="r-grid" x1="420.0" y1="60" x2="420.0" y2="540"/><line class="r-grid" x1="480.0" y1="60" x2="480.0" y2="540"/><line class="r-grid" x1="60" y1="120.0" x2="540" y2="120.0"/><line class="r-grid" x1="60" y1="180.0" x2="540" y2="180.0"/><line class="r-grid" x1="60" y1="240.0" x2="540" y2="240.0"/><line class="r-grid" x1="60" y1="300.0" x2="540" y2="300.0"/><line class="r-grid" x1="60" y1="360.0" x2="540" y2="360.0"/><line class="r-grid" x1="60" y1="420.0" x2="540" y2="420.0"/><line class="r-grid" x1="60" y1="480.0" x2="540" y2="480.0"/></g><rect class="r-border" x="60" y="60" width="480" height="480"/><g class="r-posts"><rect class="r-post" x="55" y="55" width="10" height="10"/><rect class="r-post" x="55" y="175" width="10" height="10"/><rect class="r-post" x="55" y="295" width="10" height="10"/><rect class="r-post" x="55" y="415" width="10" height="10"/><rect class="r-post" x="55" y="535" width="10" height="10"/><rect class="r-post" x="175" y="55" width="10" height="10"/><rect class="r-post" x="175" y="175" width="10" height="10"/><rect class="r-post" x="175" y="295" width="10" height="10"/><rect class="r-post" x="175" y="415" width="10" height="10"/><rect class="r-post" x="175" y="535" width="10" height="10"/><rect class="r-post" x="295" y="55" width="10" height="10"/><rect class="r-post" x="295" y="175" width="10" height="10"/><rect class="r-post" x="295" y="295" width="10" height="10"/><rect class="r-post" x="295" y="415" width="10" height="10"/><rect class="r-post" x="295" y="535" width="10" height="10"/><rect class="r-post" x="415" y="55" width="10" height="10"/><rect class="r-post" x="415" y="175" width="10" height="10"/><rect class="r-post" x="415" y="295" width="10" height="10"/><rect class="r-post" x="415" y="415" width="10" height="10"/><rect class="r-post" x="415" y="535" width="10" height="10"/><rect class="r-post" x="535" y="55" width="10" height="10"/><rect class="r-post" x="535" y="175" width="10" height="10"/><rect class="r-post" x="535" y="295" width="10" height="10"/><rect class="r-post" x="535" y="415" width="10" height="10"/><rect class="r-post" x="535" y="535" width="10" height="10"/></g><text class="r-dim" x="90.0" y="44">1,50 m</text><line class="r-dimline" x1="60" y1="50" x2="120.0" y2="50"/></svg></div>
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
