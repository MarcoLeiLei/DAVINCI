<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'kontakt',
  'title' => t('Kontakt und Termin | DAVINCI Düsseldorf', 'Contact and appointments | DAVINCI Düsseldorf'),
  'desc'  => t('Sprechen Sie mit uns über Ihr DAVINCI Haus. Kaiser-Friedrich-Ring 43, 40545 Düsseldorf, bauen@davinci-homes.de, +49 157 868 328 59. Oder buchen Sie direkt einen Termin.', 'Talk to us about your DAVINCI house. Kaiser-Friedrich-Ring 43, 40545 Düsseldorf, bauen@davinci-homes.de, +49 157 868 328 59. Or book a meeting directly.'),
]);
seitenkopf(t('Kontakt', 'Contact'), t('Lassen Sie uns sprechen.', "Let's talk."),
  t('Erzählen Sie uns von Ihrem Grundstück und Ihren Vorstellungen. Wir melden uns persönlich bei Ihnen. Oder Sie buchen direkt einen Termin in unserem Kalender.', 'Tell us about your plot and your ideas. We will get back to you personally. Or book a meeting directly in our calendar.'));
?>

<section class="section--tight" style="padding-top:0">
  <div class="wrap contact">
    <div class="contact__info">
      <p class="reveal"><a href="#termin" class="btn btn--dark" data-booking><?= t('Termin online buchen', 'Book online') ?></a></p>
      <dl class="reveal" data-delay="1">
        <div><dt><?= t('E-Mail', 'Email') ?></dt><dd><a href="mailto:bauen@davinci-homes.de">bauen@davinci-homes.de</a></dd></div>
        <div><dt><?= t('Telefon', 'Phone') ?></dt><dd><a href="tel:+4915786832859">+49 157 868 328 59</a></dd></div>
        <div><dt><?= t('Büro', 'Office') ?></dt><dd>Kaiser-Friedrich-Ring 43<br>40545 Düsseldorf<?= t('', ', Germany') ?></dd></div>
      </dl>
      <div class="mini-book reveal" data-delay="2">
        <p><strong>Design-Book</strong> <?= t('Häuser, Grundrisse und Materialien auf 48 Seiten. Als PDF zum Download.', 'Houses, floor plans and materials on 48 pages (in German). As a PDF download.') ?></p>
        <a href="<?= u('design-book') ?>" class="link-arrow"><?= t('Design-Book anfordern', 'Request the Design Book') ?> <svg><use href="#arrow"/></svg></a>
      </div>
    </div>

    <form class="form reveal" data-delay="1" action="/send.php" method="post" data-contact data-success-title="<?= t('Anfrage gesendet.', 'Request sent.') ?>" data-success="<?= t('Vielen Dank. Wir melden uns persönlich bei Ihnen.', 'Thank you. We will get back to you personally.') ?>" data-aktion="kontakt" novalidate>
      <input type="hidden" name="anfrage_typ" value="Beratungsanfrage"><input type="hidden" name="sprache" value="<?= en() ? 'en' : 'de' ?>">
      <div class="fld"><input id="f-vorname" name="vorname" type="text" placeholder=" " autocomplete="given-name" required><label for="f-vorname"><?= t('Vorname', 'First name') ?></label></div>
      <div class="fld"><input id="f-name" name="nachname" type="text" placeholder=" " autocomplete="family-name" required><label for="f-name"><?= t('Nachname', 'Last name') ?></label></div>
      <div class="fld"><input id="f-mail" name="email" type="email" placeholder=" " autocomplete="email" required><label for="f-mail"><?= t('E-Mail', 'Email') ?></label></div>
      <div class="fld"><input id="f-tel" name="telefon" type="tel" placeholder=" " autocomplete="tel"><label for="f-tel"><?= t('Telefon (optional)', 'Phone (optional)') ?></label></div>
      <div class="fld"><select id="f-typ" name="haustyp">
<?php foreach (PRODUKTE as $k => $pr): ?>        <option value="<?= e($pr['name']) ?>" data-key="<?= $k ?>"><?= e($pr['name']) ?></option>
<?php endforeach; ?>        <option value="DAVINCI Individuell" data-key="individuell"><?= t('DAVINCI Individuell', 'DAVINCI Bespoke') ?></option>
        <option value="Noch offen" data-key="offen" selected><?= t('Noch offen', 'Not decided yet') ?></option>
      </select><label for="f-typ"><?= t('Haus', 'Home') ?></label></div>
      <div class="fld"><select id="f-grund" name="grundstueck">
        <option value="Grundstück vorhanden"><?= t('Grundstück vorhanden', 'I have a plot') ?></option>
        <option value="Grundstück in Aussicht"><?= t('Grundstück in Aussicht', 'A plot is in view') ?></option>
        <option value="Noch auf der Suche"><?= t('Noch auf der Suche', 'Still looking') ?></option>
      </select><label for="f-grund"><?= t('Grundstück', 'Plot') ?></label></div>
      <div class="fld full"><textarea id="f-msg" name="nachricht" placeholder=" "></textarea><label for="f-msg"><?= t('Ihre Nachricht', 'Your message') ?></label></div>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <label class="consent full"><input type="checkbox" name="datenschutz" required><span><?= t('Ich habe die <a href="/datenschutz/">Datenschutzerklärung</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Beantwortung meiner Anfrage einverstanden.', 'I have read the <a href="/datenschutz/">privacy policy</a> (German) and agree to my details being processed to answer my enquiry.') ?></span></label>
      <div class="full" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
        <button type="submit" class="btn btn--dark" data-label="<?= t('Anfrage senden', 'Send enquiry') ?>"><?= t('Anfrage senden', 'Send enquiry') ?></button>
        <p class="form__msg" data-form-msg role="status"></p>
      </div>
    </form>
  </div>
</section>

<?php seite_ende();
