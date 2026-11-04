<?php
/* =========================================================
   DAVINCI – Einzelseite je Haus der Kollektion: /haeuser/<haus>/ und /en/haeuser/<haus>/
   Vorher HAUS definieren (in haeuser/<haus>/index.php), dann diese Datei einbinden.
   ========================================================= */
require_once __DIR__ . '/site.php';
require_once __DIR__ . '/haeuser_daten.php';

const HAUS_TEXTE = [
  'tre' => [
    'title' => ['DAVINCI Tre: Haus aus Holz und Glas mit Satteldach', 'DAVINCI Tre: wood and glass home with gable roof'],
    'desc'  => ['DAVINCI Tre: der Klassiker mit drei Achsen, Glasgiebel und Luftraum. Grundriss, Räume und Ebenen des Hauses aus Holz und Glas.', 'DAVINCI Tre: the classic with three bays, glazed gable and open void. Floor plan, rooms and levels of the wood and glass home.'],
    'text'  => ['Tre ist das kompakte Haus der Giebelfamilie. Im Erdgeschoss bilden Wohnen mit Kamin, Essen und Kochen einen offenen Raum, dazu kommen Windfang, Hauswirtschaft und ein Duschbad. Über dem Wohnen öffnet sich der Luftraum bis unter den Glasgiebel. Oben liegen Schlafen mit Ankleide und Bad, ein Studio und die Galerie mit Blick nach unten. Im Keller ist Platz für Gäste, Technik und zwei Werkstätten, die Doppelgarage steht mit Flachdach neben dem Haus.',
                'Tre is the compact member of the gable family. On the ground floor, living with fireplace, dining and kitchen form one open space, together with entrance, utility room and shower room. Above the living area the void opens up to the glazed gable. Upstairs are the bedroom with dressing room and bathroom, a studio and the gallery looking down. The lower floor has room for guests, building services and two workshops; the double garage with flat roof stands beside the house.'],
    'fuer'  => ['Für Paare und kleine Familien, die das klassische Haus aus Holz und Glas auf kompakter Grundfläche suchen.', 'For couples and small families looking for the classic wood and glass home on a compact footprint.'],
  ],
  'quattro' => [
    'title' => ['DAVINCI Quattro: Satteldach, vier Achsen und Carport', 'DAVINCI Quattro: gable roof, four bays and carport'],
    'desc'  => ['DAVINCI Quattro: vier Achsen, Glasfassaden an beiden Giebeln, Luftraum und Carport. Grundriss und Räume des Hauses aus Holz und Glas.', 'DAVINCI Quattro: four bays, glazed facades on both gables, open void and carport. Floor plan and rooms of the wood and glass home.'],
    'text'  => ['Quattro erweitert den Giebel auf vier Achsen. Das Erdgeschoss ist ein großer, offener Raum für Wohnen, Essen und Kochen, mit Glas an beiden Giebelseiten und Terrassen auf zwei Seiten. Windfang, WC und Treppe liegen kompakt an der Eingangsseite. Oben folgen Schlafen mit Ankleide und Bad sowie ein Studio mit Galerie über dem Luftraum, beide Giebel haben einen Balkon. Der Carport mit Flachdach schließt direkt an das Haus an.',
                'Quattro extends the gable to four bays. The ground floor is one large open space for living, dining and cooking, glazed on both gable ends with terraces on two sides. Entrance, WC and stairs sit compactly on the entrance side. Upstairs are the bedroom with dressing room and bathroom and a studio with gallery above the void; both gables have a balcony. The carport with flat roof adjoins the house directly.'],
    'fuer'  => ['Für Familien, die viel Licht und einen großen gemeinsamen Raum im Erdgeschoss wollen.', 'For families who want plenty of light and one large shared space on the ground floor.'],
  ],
  'piano' => [
    'title' => ['DAVINCI Piano: Bungalow mit Flachdach aus Holz und Glas', 'DAVINCI Piano: flat-roof bungalow in wood and glass'],
    'desc'  => ['DAVINCI Piano: Bungalow mit rund 150 m² auf einer Ebene, Flachdach, Doppelgarage und optionalem Sauna- und Gästehaus. Grundriss und Räume.', 'DAVINCI Piano: bungalow of around 150 m² on one level, flat roof, double garage and optional sauna and guest house. Floor plan and rooms.'],
    'text'  => ['Piano ist der Bungalow für das Leben auf einer Ebene. Wohnen, Essen und Kochen bilden einen durchgehenden Raum zur Terrasse. Der Schlafbereich mit Ankleide und Bad liegt getrennt in einem eigenen Flügel, dazu kommt ein Kinder- oder Gästezimmer mit Duschbad. Vorrat, Hauswirtschaft und die Doppelgarage sind direkt angebunden. Auf Wunsch ergänzt ein kleines Sauna- und Gästehaus im Garten das Haus.',
                'Piano is the bungalow for living on one level. Living, dining and kitchen form one continuous space opening onto the terrace. The bedroom with dressing room and bathroom sits separately in its own wing, plus a child or guest room with shower room. Pantry, utility room and the double garage are directly connected. On request, a small sauna and guest house in the garden completes the home.'],
    'fuer'  => ['Für alle, die ohne Treppen wohnen möchten, heute und in vielen Jahren.', 'For anyone who wants to live without stairs, today and for many years to come.'],
  ],
  'alpina' => [
    'title' => ['DAVINCI Alpina: Satteldach mit großem Glasgiebel', 'DAVINCI Alpina: pitched roof with large glazed gable'],
    'desc'  => ['DAVINCI Alpina: der klassische Giebel für Berge und Schnee, mit Glasgiebel, Luftraum, Galerie und Doppelcarport. Grundriss und Räume.', 'DAVINCI Alpina: the classic gable for mountains and snow, with glazed gable, open void, gallery and double carport. Floor plan and rooms.'],
    'text'  => ['Alpina ist der klassische DAVINCI Giebel. Hinter dem großen Glasgiebel öffnet sich der Luftraum über Wohnen und Essen, die offene Küche liegt daneben. Im Erdgeschoss gibt es außerdem ein Musik- oder Gästezimmer, ein Duschbad und den Technikraum. Oben liegen Schlafen mit Ankleide und Bad, ein Studio und die Galerie, Balkone öffnen das Dachgeschoss nach draußen. Ein Doppelcarport mit Abstellraum steht neben dem Haus.',
                'Alpina is the classic DAVINCI gable. Behind the large glazed gable the void opens up above living and dining, with the open kitchen alongside. The ground floor also has a music or guest room, a shower room and the plant room. Upstairs are the bedroom with dressing room and bathroom, a studio and the gallery, with balconies opening the upper floor to the outside. A double carport with storeroom stands beside the house.'],
    'fuer'  => ['Für Bauherren, die das typische Bild des Hauses aus Holz und Glas suchen, gern auch in den Bergen.', 'For clients looking for the archetypal wood and glass home, mountain plots included.'],
  ],
  'terrazza' => [
    'title' => ['DAVINCI Terrazza: Flachdach mit Staffelgeschoss', 'DAVINCI Terrazza: flat roof with stepped top floor'],
    'desc'  => ['DAVINCI Terrazza: rund 200 m², Flachdach mit Staffelgeschoss und großer Dachterrasse, Sauna im Keller. Grundriss und Räume des Hauses.', 'DAVINCI Terrazza: around 200 m², flat roof with stepped top floor and large roof terrace, sauna downstairs. Floor plan and rooms.'],
    'text'  => ['Terrazza hat zwei Ebenen unter weiten Dachüberständen. Im Erdgeschoss liegen Kochen, Essen und Wohnen mit Kamin, dazu ein Arbeitszimmer, Garderobe und WC, die Terrasse zieht sich über die ganze Gartenseite. Das Staffelgeschoss mit Schlafen, Ankleide und Bad, zwei Kinderzimmern und einem gemeinsamen Duschbad öffnet sich auf eine große Dachterrasse. Im Keller gibt es Sauna, Fitness, Technik, Hauswirtschaft und Vorrat.',
                'Terrazza has two levels beneath wide roof overhangs. The ground floor holds kitchen, dining and living with fireplace, plus a study, cloakroom and WC, with the terrace running along the whole garden side. The stepped top floor with bedroom, dressing room and bathroom, two children’s rooms and a shared shower room opens onto a large roof terrace. Downstairs there is a sauna, fitness room, building services, utility room and pantry.'],
    'fuer'  => ['Für Familien mit Kindern, die ein modernes Flachdachhaus mit viel Außenraum suchen.', 'For families with children looking for a modern flat-roof home with plenty of outdoor space.'],
  ],
  'pendio' => [
    'title' => ['DAVINCI Pendio: Haus am Hang mit Satteldach und Garage', 'DAVINCI Pendio: hillside home with gable roof and garage'],
    'desc'  => ['DAVINCI Pendio: drei Ebenen am Hang, Garage und Einliegerwohnung im Sockel, Galerie unter dem Satteldach. Grundriss und Räume.', 'DAVINCI Pendio: three levels on a slope, garage and annex flat in the base, gallery beneath the gable roof. Floor plan and rooms.'],
    'text'  => ['Pendio nutzt den Hang. Im Sockel liegen die Garage mit Schleuse und Hauswirtschaft sowie eine kleine eigene Wohnung mit Wohnen, Kochen, Schlafen und Duschbad. Darüber, auf Gartenebene, folgen Wohnen, Essen und Kochen mit Terrasse, ein Gästezimmer, Vorrat und WC. Unter dem Satteldach liegen Schlafen mit Ankleide und Bad sowie die Galerie über dem Luftraum, Balkone öffnen den Blick ins Tal.',
                'Pendio makes use of the slope. The base holds the garage with airlock and utility room, plus a small self-contained flat with living, kitchen, bedroom and shower room. Above, at garden level, are living, dining and kitchen with terrace, a guest room, pantry and WC. Beneath the gable roof are the bedroom with dressing room and bathroom and the gallery above the void, with balconies looking out over the valley.'],
    'fuer'  => ['Für Hanggrundstücke und für Familien, die Eltern oder erwachsene Kinder unter einem Dach wohnen lassen möchten.', 'For hillside plots and for families who want parents or grown-up children living under the same roof.'],
  ],
  'corte' => [
    'title' => ['DAVINCI Corte: Bungalow im Winkel mit Flachdach', 'DAVINCI Corte: L-shaped bungalow with flat roof'],
    'desc'  => ['DAVINCI Corte: Bungalow im Winkel, zwei Flügel um eine geschützte Terrasse, Schlafräume und Garage im Untergeschoss. Grundriss und Räume.', 'DAVINCI Corte: L-shaped bungalow, two wings around a sheltered terrace, bedrooms and garage on the lower floor. Floor plan and rooms.'],
    'text'  => ['Corte legt sich im Winkel um die Terrasse. Ein Flügel nimmt Wohnen, Essen und Kochen auf, der andere das Arbeitszimmer, Bad und Abstellraum, die Diele verbindet beide. Die tiefen Dachüberstände spenden Schatten und machen die Terrasse zum Zimmer im Freien. Im Untergeschoss liegen Schlafen, ein Kinder- oder Gästezimmer, ein Duschbad, Garderobe, Technik und die Doppelgarage.',
                'Corte wraps around the terrace in an L shape. One wing holds living, dining and kitchen, the other the study, bathroom and storeroom, with the hall linking both. Deep roof overhangs provide shade and turn the terrace into an outdoor room. The lower floor holds the bedroom, a child or guest room, a shower room, cloakroom, building services and the double garage.'],
    'fuer'  => ['Für Grundstücke mit Gefälle und für alle, die einen geschützten, privaten Außenraum suchen.', 'For sloping plots and for anyone looking for a sheltered, private outdoor space.'],
  ],
  'ala' => [
    'title' => ['DAVINCI Ala: Bungalow mit Doppel-Pultdach und Flachdach', 'DAVINCI Ala: bungalow with twin mono-pitch and flat roof'],
    'desc'  => ['DAVINCI Ala: Bungalow mit Flügeln, Doppel-Pultdach und Flachdach, Luftraum über dem Wohnen und eigener Sauna. Grundriss und Räume.', 'DAVINCI Ala: bungalow with wings, twin mono-pitch and flat roof, void above the living area and its own sauna. Floor plan and rooms.'],
    'text'  => ['Ala ist ein Bungalow mit Flügeln. Die Pultdächer steigen zum Garten an, darunter öffnet sich der Wohnraum mit einem Luftraum und einer hohen Glasfront zur Abendsonne. Auf der Wohnebene liegen außerdem Essen und Kochen mit zweiter Küche, Schlafen mit Ankleide, Bad und eigener Sauna, ein Gäste- oder Arbeitszimmer, Duschbad und Hauswirtschaft. Im Untergeschoss folgen zwei Kinderzimmer, ein Duschbad, Fitness, Keller und die Doppelgarage.',
                'Ala is a bungalow with wings. The mono-pitch roofs rise towards the garden, and beneath them the living space opens up with a void and a tall glass front facing the evening sun. The living level also holds dining and kitchen with a second kitchen, the bedroom with dressing room, bathroom and its own sauna, a guest room or study, shower room and utility room. The lower floor has two children’s rooms, a shower room, fitness room, storage and the double garage.'],
    'fuer'  => ['Für Familien, die großzügig auf einer Ebene leben und Wellness im eigenen Haus möchten.', 'For families who want to live generously on one level with wellness in their own home.'],
  ],
  'vista' => [
    'title' => ['DAVINCI Vista: Haus am Hang mit Pultdach und Pool', 'DAVINCI Vista: hillside home with mono-pitch roof and pool'],
    'desc'  => ['DAVINCI Vista: Wohnebene als gläserner Pavillon über dem Hang, Pool und Sauna, eigene Wohnung im Sockel. Grundriss und Räume.', 'DAVINCI Vista: living level as a glass pavilion above the slope, pool and sauna, self-contained flat in the base. Floor plan and rooms.'],
    'text'  => ['Vista ist ganz auf die Aussicht gebaut. Die Wohnebene liegt als gläserner Pavillon auf dem Sockel: Wohnen, Essen und Kochen, Schlafen mit Ankleide, ein Arbeitszimmer und ein Bad mit Sauna, rundum Terrassen und ein Pool über dem Hang. Im Sockel liegt eine eigene Wohnung mit Wohnküche, Schlafen, Bad und Ankleide, dazu Technik, Hauswirtschaft, ein Skiraum und die Doppelgarage.',
                'Vista is built entirely around the view. The living level sits as a glass pavilion on the base: living, dining and kitchen, the bedroom with dressing room, a study and a bathroom with sauna, surrounded by terraces and a pool above the slope. The base holds a self-contained flat with kitchen-living room, bedroom, bathroom and dressing room, plus building services, utility room, a ski room and the double garage.'],
    'fuer'  => ['Für Aussichtslagen am Hang und für alle, die eine zweite Wohnung für Familie, Gäste oder zur Vermietung möchten.', 'For hillside plots with a view and for anyone who wants a second flat for family, guests or letting.'],
  ],
  'ombra' => [
    'title' => ['DAVINCI Ombra: Satteldach mit dunklem Tragwerk am Hang', 'DAVINCI Ombra: gable roof with dark structure on a slope'],
    'desc'  => ['DAVINCI Ombra: dunkles Tragwerk, drei Ebenen am Hang, Bibliothek, Galerie und Wellness mit Sauna und Whirlpool. Grundriss und Räume.', 'DAVINCI Ombra: dark structure, three levels on a slope, library, gallery and wellness with sauna and whirlpool. Floor plan and rooms.'],
    'text'  => ['Ombra verbindet dunkles Tragwerk mit warmem Licht. Auf der Hauptebene liegen Wohnen, Essen und Kochen mit Speisekammer, eine Bibliothek, Schlafen mit Ankleide und Bad sowie die Hauswirtschaft. Unter dem Dach folgen Studio, Arbeiten und Hobby rund um die Galerie über dem Luftraum. Im Hanggeschoss gibt es einen Wellnessbereich mit Sauna, Whirlpool und Fitness, eine Bar, eine eigene kleine Wohnung, Werkstatt, Doppelgarage und Carport.',
                'Ombra combines a dark timber structure with warm light. The main level holds living, dining and kitchen with pantry, a library, the bedroom with dressing room and bathroom, and the utility room. Under the roof are a studio, study and hobby room around the gallery above the void. The hillside level has a wellness area with sauna, whirlpool and fitness, a bar, a small self-contained flat, workshop, double garage and carport.'],
    'fuer'  => ['Für ein großes Raumprogramm am Hang, mit Platz für Rückzug, Gäste und Wellness.', 'For a generous brief on a slope, with room for retreat, guests and wellness.'],
  ],
];

const RAUM_EN = ['Wohnen' => 'Living', 'Essen' => 'Dining', 'Kochen' => 'Kitchen', 'Kamin' => 'Fireplace', 'Schlafen' => 'Bedroom', 'Ankleide' => 'Dressing room',
  'Bad' => 'Bathroom', 'Du / WC' => 'Shower room', 'Du/WC' => 'Shower room', 'WC' => 'WC', 'Windfang' => 'Entrance', 'Windfang/Garderobe' => 'Entrance and cloakroom',
  'Diele' => 'Hall', 'Diele/Garderobe' => 'Hall and cloakroom', 'Flur' => 'Hallway', 'Garderobe' => 'Cloakroom', 'HWR' => 'Utility room', 'Technik / HWR' => 'Services and utility',
  'Technik/HWR' => 'Services and utility', 'Technik' => 'Building services', 'Technik/Keller' => 'Services and storage', 'Technik/Keller II' => 'Services and storage II',
  'HWR/Schleuse' => 'Utility and airlock', 'Abstell' => 'Storeroom', 'Abstell / Ankleide' => 'Storeroom and dressing room', 'Abstell / Skiraum' => 'Storeroom and ski room',
  'Skiraum / Fahrräder' => 'Ski and bike room', 'Vorrat' => 'Pantry', 'Speisekammer' => 'Pantry', 'Terrasse' => 'Terrace', 'Balkon' => 'Balcony', 'Galerie' => 'Gallery',
  'Studio' => 'Studio', 'Studio / Galerie' => 'Studio and gallery', 'Galerie/Studio' => 'Gallery and studio', 'Gast' => 'Guest room', 'Gast/Arbeiten' => 'Guest room or study',
  'Kind' => 'Child’s room', 'Kind 1' => 'Child’s room 1', 'Kind 2' => 'Child’s room 2', 'Kind I' => 'Child’s room 1', 'Kind II' => 'Child’s room 2', 'Kind/Gast' => 'Child or guest room',
  'Arbeiten' => 'Study', 'Musik/Gast' => 'Music or guest room', 'Bibliothek' => 'Library', 'Hobby' => 'Hobby room', 'Fitness' => 'Fitness', 'Fitness / Hobby' => 'Fitness and hobby',
  'Fitness/Wellness' => 'Fitness and wellness', 'Sauna' => 'Sauna', 'Whirlpool' => 'Whirlpool', 'Relaxen' => 'Relaxation', 'Dusche' => 'Shower', 'Bar' => 'Bar',
  'Keller' => 'Storage', 'Keller I' => 'Storage 1', 'Keller III' => 'Storage 3', 'Keller 2' => 'Storage 2', 'Keller / Abstell' => 'Storage', 'Geräte/Abstell' => 'Equipment store',
  'Werkstatt' => 'Workshop', 'Werkstatt / Elektro' => 'Workshop (electrics)', 'Werkstatt / Holz' => 'Workshop (wood)', 'Garage' => 'Garage', 'Doppelgarage' => 'Double garage',
  'Carport' => 'Carport', 'Doppelcarport' => 'Double carport', 'Holz / Müll / Fahrräder' => 'Firewood, bins and bikes', 'Küchen II' => 'Second kitchen',
  'Kochen / Essen / Wohnen' => 'Kitchen, dining and living'];
/** Reihenfolge im Raumprogramm: erst Wohnen, dann Schlafen, Bäder, Arbeiten, Nebenräume, außen. */
const RAUM_FOLGE = ['Wohnen', 'Kamin', 'Essen', 'Kochen', 'Kochen / Essen / Wohnen', 'Küchen II', 'Speisekammer', 'Vorrat', 'Bibliothek', 'Studio', 'Studio / Galerie', 'Galerie/Studio', 'Galerie',
  'Schlafen', 'Ankleide', 'Abstell / Ankleide', 'Bad', 'Kind', 'Kind 1', 'Kind 2', 'Kind I', 'Kind II', 'Kind/Gast', 'Gast', 'Gast/Arbeiten', 'Musik/Gast', 'Arbeiten', 'Hobby',
  'Du / WC', 'Du/WC', 'WC', 'Sauna', 'Whirlpool', 'Dusche', 'Relaxen', 'Fitness', 'Fitness / Hobby', 'Fitness/Wellness', 'Bar',
  'Windfang', 'Windfang/Garderobe', 'Diele', 'Diele/Garderobe', 'Flur', 'Garderobe', 'HWR', 'HWR/Schleuse', 'Technik', 'Technik / HWR', 'Technik/HWR', 'Technik/Keller', 'Technik/Keller II',
  'Keller', 'Keller / Abstell', 'Keller I', 'Keller 2', 'Keller III', 'Abstell', 'Abstell / Skiraum', 'Skiraum / Fahrräder', 'Geräte/Abstell', 'Werkstatt', 'Werkstatt / Elektro', 'Werkstatt / Holz',
  'Garage', 'Doppelgarage', 'Carport', 'Doppelcarport', 'Holz / Müll / Fahrräder', 'Balkon', 'Terrasse'];
const GESCHOSS_EN = ['Erdgeschoss' => 'Ground floor', 'Dachgeschoss' => 'Upper floor', 'Kellergeschoss' => 'Lower floor', 'Sauna- und Gästehaus' => 'Sauna and guest house'];

$key = HAUS;
$pr = PRODUKTE[$key];
$tx = HAUS_TEXTE[$key];
$gd = HAUS_GESCHOSSE[$key];
$L = en() ? 1 : 0;
$geschossName = fn($g) => en() ? (GESCHOSS_EN[$g] ?? $g) : $g;
$raumName = fn($r) => en() ? (RAUM_EN[$r] ?? $r) : $r;
$url = SITE . u('haeuser') . $key . '/';

seite_start([
  'slug'  => 'haeuser/' . $key,
  'nav'   => 'haeuser',
  'crumb' => $pr['name'],
  'eltern' => ['haeuser', t('Häuser', 'Homes')],
  'title' => $tx['title'][$L] . ' | DAVINCI',
  'desc'  => $tx['desc'][$L],
  'img'   => $pr['img'],
  'schema' => [[
    '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $pr['name'], 'url' => $url,
    'image' => [SITE . '/assets/img/' . $pr['img'], SITE . '/assets/img/grundriss/' . $key . '-eg.webp'],
    'description' => $tx['desc'][$L], 'category' => t('Haus aus Holz und Glas', 'Wood and glass home'),
    'brand' => ['@type' => 'Brand', 'name' => 'DAVINCI'],
  ]],
]);
seitenkopf(t('DAVINCI Produkte', 'DAVINCI models') . ' · ' . t($pr['tag'][0], $pr['tag'][1]), e($pr['name']) . '.', t($pr['txt'][0], $pr['txt'][1]), $pr['img'],
  t('Visualisierung: ', 'Visualisation: ') . $pr['name'] . ', ' . t($pr['tag'][0], $pr['tag'][1]));
?>

<section class="section">
  <div class="wrap split haus">
    <div class="section-head" style="margin-bottom:0">
      <span class="eyebrow reveal"><?= t('Das Haus', 'The home') ?></span>
      <h2 class="h2 reveal" data-delay="1"><?= e(t('Räume und Ebenen', 'Rooms and levels')) ?></h2>
      <p class="lead reveal" data-delay="2"><?= e($tx['text'][$L]) ?></p>
      <ul class="haus__fakten reveal" data-delay="2">
        <li><span><?= t('Geschosse', 'Floors') ?></span><b><?= e(implode(' · ', array_map($geschossName, array_column($gd['geschosse'], 0)))) ?></b></li>
        <li><span><?= t('Wohnfläche', 'Living space') ?></span><b><?= $pr['m2'] ? 'ca. ' . $pr['m2'] . ' m²' : t('nach Ihrem Programm', 'to your brief') ?></b></li>
        <li><span><?= t('Bauweise', 'Design') ?></span><b><?= t($pr['tag'][0], $pr['tag'][1]) ?></b></li>
      </ul>
      <p class="reveal" data-delay="3" style="margin-top:22px"><b><?= t('Für wen?', 'Who is it for?') ?></b> <?= e($tx['fuer'][$L]) ?></p>
    </div>
    <figure class="haus__plan reveal" data-delay="1">
      <img src="/assets/img/grundriss/<?= $key ?>-eg.webp" width="<?= $gd['plan'][0] ?>" height="<?= $gd['plan'][1] ?>" alt="<?= e(t('Grundriss Erdgeschoss', 'Ground floor plan') . ' ' . $pr['name']) ?>" loading="lazy">
      <figcaption><?= t('Erdgeschoss · Beispielgrundriss', 'Ground floor · example plan') ?></figcaption>
      <a class="btn btn--dark" href="/downloads/grundrisse/<?= $key ?>.pdf" download="DAVINCI <?= e(ucfirst($key)) ?> - Grundrisse.pdf" data-dl="grundriss"><?= t('Alle Geschosse als PDF herunterladen', 'Download all floors as PDF') ?></a>
    </figure>
  </div>
</section>

<section class="section--tight grey">
  <div class="wrap">
    <h2 class="h3 reveal" style="margin-bottom:28px"><?= t('Raumprogramm', 'Room schedule') ?></h2>
    <div class="haus__geschosse">
      <?php foreach ($gd['geschosse'] as $i => [$g, $raeume]): ?>
      <div class="reveal"<?= $i ? ' data-delay="' . min($i, 3) . '"' : '' ?>>
        <h3><?= e($geschossName($g)) ?></h3>
        <?php usort($raeume, fn($x, $y) => (array_search($x, RAUM_FOLGE, true) === false ? 99 : array_search($x, RAUM_FOLGE, true)) <=> (array_search($y, RAUM_FOLGE, true) === false ? 99 : array_search($y, RAUM_FOLGE, true))); ?>
        <ul class="ticks"><?php foreach ($raeume as $r): ?><li><?= e($raumName($r)) ?></li><?php endforeach; ?></ul>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="footnote reveal"><?= t('Beispielgrundriss. Jedes Haus von DAVINCI wird für Grundstück und Bauherren individuell geplant; Größe, Räume, Fassade und Ausstattung passen wir im Entwurf an.', 'Example floor plan. Every house by DAVINCI is planned individually for plot and clients; we adapt size, rooms, facade and fit-out in the design.') ?></p>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Weitere Häuser', 'More homes') ?></span>
      <h2 class="h2 reveal" data-delay="1"><?= t('Aus derselben Kollektion.', 'From the same collection.') ?></h2>
    </div>
    <div class="products haus__weitere">
      <?php $keys = array_keys(PRODUKTE); $pos = array_search($key, $keys);
      foreach ([1, 2, 3] as $n): $k = $keys[($pos + $n) % count($keys)]; $p2 = PRODUKTE[$k]; ?>
      <a class="product reveal" href="<?= u('haeuser') . $k ?>/"<?= $n > 1 ? ' data-delay="' . ($n - 1) . '"' : '' ?>>
        <div class="product__img"><img src="/assets/img/<?= $p2['img'] ?>" style="object-position:<?= $p2['pos'] ?>" alt="<?= e($p2['name']) ?>" loading="lazy"></div>
        <div class="product__body"><h3><?= e($p2['name']) ?></h3><p><?= t($p2['tag'][0], $p2['tag'][1]) ?></p></div>
      </a>
      <?php endforeach; ?>
    </div>
    <p class="reveal" style="margin-top:32px"><a href="<?= u('haeuser') ?>" class="link-arrow"><?= t('Alle Häuser und DAVINCI Individuell', 'All homes and DAVINCI Bespoke') ?> <svg><use href="#arrow"/></svg></a></p>
  </div>
</section>

<?php claim_band(); seite_ende();
