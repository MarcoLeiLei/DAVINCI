<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'service',
  'title' => t('DAVINCI Service: Pflege, Reparatur und Erweiterung | DAVINCI', 'DAVINCI Service: care, repair and extension | DAVINCI'),
  'desc'  => t('Wir bleiben auch nach der Übergabe. Mit Fivemeters Bau pflegen und reparieren wir seit 2024 bestehende DAVINCI Häuser: Dach, Holz, Glas, Jalousien, Anbau.', 'We stay after handover too. Through Fivemeters Bau we have been maintaining and repairing existing DAVINCI houses since 2024: roof, timber, glass, blinds, extensions.'),
  'img'   => 'bestand.jpg',
]);
seitenkopf('Service', t('Wir bleiben.<br>Auch nach der Übergabe.', 'We stay.<br>Even after handover.'),
  t('Viele sind nach der Schlüsselübergabe weg. Wir nicht. Mit Fivemeters Bau pflegen und reparieren wir seit 2024 bestehende DAVINCI Häuser. Dieselben Menschen sind auch für Ihr neues Haus da.', 'Many are gone once the keys are handed over. Not us. Through Fivemeters Bau we have been maintaining and repairing existing DAVINCI houses since 2024. The same people are there for your new house too.'),
  'bestand.jpg', t('Ein bestehendes DAVINCI Haus mit Glasgiebel, Terrasse und Teich', 'An existing DAVINCI house with glazed gable, terrace and pond'));
?>

<section class="section">
  <div class="wrap">
    <div class="pillars3">
      <div class="reveal"><span class="num">01</span><h3><?= t('Ein Ansprechpartner', 'One contact') ?></h3><p><?= t('Auch nach der Übergabe wissen Sie, wen Sie anrufen. Ohne Hotline und ohne Weiterleitung.', 'After handover too, you know whom to call. No hotline, no forwarding.') ?></p></div>
      <div class="reveal" data-delay="1"><span class="num">02</span><h3><?= t('Pflege und Reparatur', 'Care and repair') ?></h3><p><?= t('Dach und Rinnen, Holzkonstruktion, Glas und Jalousien. Wir kennen jedes Detail dieser Häuser.', 'Roof and gutters, timber structure, glass and blinds. We know every detail of these houses.') ?></p></div>
      <div class="reveal" data-delay="2"><span class="num">03</span><h3><?= t('Erweiterung', 'Extension') ?></h3><p><?= t('Anbau oder Carport, im Stil Ihres DAVINCI Hauses geplant und gebaut.', 'Extension or carport, planned and built in the style of your DAVINCI house.') ?></p></div>
    </div>
  </div>
</section>

<section class="section grey">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow reveal"><?= t('Stimmen', 'Voices') ?></span>
      <h2 class="h2 reveal" data-delay="1"><?= t('Was Eigentümer sagen.', 'What owners say.') ?></h2>
    </div>
    <div class="quotes">
      <figure class="quote reveal">
        <blockquote>„<?= t('Wir hatten bereits mehrfach die Möglichkeit, unsere DAVINCI Haus Projekte mit Fivemetersbau abzuwickeln. Von der Kommunikation über die Organisation und den Bauablauf bis zur Abnahme verlief alles bestens.', 'We have already had several opportunities to carry out our DAVINCI house projects with Fivemetersbau. From communication and organisation to the construction process and final acceptance, everything went perfectly.') ?>“</blockquote>
        <figcaption><?= t('P. V., Eigentümer eines DAVINCI Hauses', 'P. V., owner of a DAVINCI house (translated)') ?></figcaption>
      </figure>
      <figure class="quote reveal" data-delay="1">
        <blockquote>„<?= t('Ich bin sehr zufrieden mit den Reparaturen an meinem Haus. Wir haben nach einigen Jahren die gesamte Holzkonstruktion aufbereitet bekommen. Die Kommunikation lief reibungslos und das junge Team war sehr nett.', 'I am very happy with the repairs to my house. After several years, the entire timber structure was refurbished. Communication was smooth and the young team was very friendly.') ?>“</blockquote>
        <figcaption><?= t('Yü Z., Eigentümerin eines DAVINCI Hauses', 'Yü Z., owner of a DAVINCI house (translated)') ?></figcaption>
      </figure>
    </div>
    <div class="svc-cta reveal">
      <p><?= t('Sie besitzen bereits ein DAVINCI Haus?', 'Do you already own a DAVINCI house?') ?></p>
      <a href="https://fivemetersbau.de/#kontakt" class="btn btn--dark" target="_blank" rel="noopener"><?= t('Service anfragen', 'Request service') ?></a>
      <a href="https://fivemetersbau.de/" class="link-arrow" target="_blank" rel="noopener"><?= t('Zu Fivemeters Bau', 'Visit Fivemeters Bau') ?> <svg><use href="#arrow"/></svg></a>
    </div>
  </div>
</section>

<?php claim_band(); seite_ende();
