<?php
require __DIR__ . '/inc/site.php';
seite_start([
  'slug'  => 'design-book',
  'title' => t('Design-Book anfordern: 48 Seiten Holz und Glas | DAVINCI', 'Request the Design Book: 48 pages of wood and glass | DAVINCI'),
  'desc'  => t('Häuser, Grundrisse, Materialien und die Geschichte hinter DAVINCI auf 48 Seiten. Als PDF sofort in Ihrem Postfach.', 'Houses, floor plans, materials and the story behind DAVINCI on 48 pages (in German). As an instant PDF.'),
  'crumb' => 'Design-Book',
]);
?>
<section class="section" style="padding-top:calc(var(--nav-h) + clamp(48px,7vw,110px))">
  <div class="wrap dbook">
    <?php buch_visual(); ?>
    <div class="dbook__content">
      <div class="section-head" style="margin-bottom:0">
        <span class="eyebrow load-in">Design-Book</span>
        <h1 class="h1 load-in d1"><?= t('Inspiration für<br>zu Hause.', 'Inspiration<br>for your home.') ?></h1>
        <p class="lead load-in d2"><?= t('Häuser, Grundrisse, Materialien und die Geschichte hinter DAVINCI auf 48 Seiten. Als PDF sofort in Ihrem Postfach.', 'Houses, floor plans, materials and the story behind DAVINCI on 48 pages (in German). As a PDF straight to your inbox.') ?></p>
      </div>
      <form class="form dbook__form reveal" data-delay="2" action="/send.php" method="post" data-contact data-success-title="<?= t('Ihr Design-Book ist unterwegs.', 'Your Design Book is on its way.') ?>" data-success="<?= t('Vielen Dank. Bitte schauen Sie in Ihr Postfach, auch im Spam-Ordner.', 'Thank you. Please check your inbox, including the spam folder.') ?>" data-aktion="designbook" novalidate>
        <input type="hidden" name="anfrage_typ" value="Design-Book"><input type="hidden" name="sprache" value="<?= en() ? 'en' : 'de' ?>">
        <input type="hidden" name="format" value="PDF">
        <div class="fld"><input id="db-vorname" name="vorname" type="text" placeholder=" " autocomplete="given-name" required><label for="db-vorname"><?= t('Vorname', 'First name') ?></label></div>
        <div class="fld"><input id="db-name" name="nachname" type="text" placeholder=" " autocomplete="family-name" required><label for="db-name"><?= t('Nachname', 'Last name') ?></label></div>
        <div class="fld full"><input id="db-mail" name="email" type="email" placeholder=" " autocomplete="email" required><label for="db-mail"><?= t('E-Mail', 'Email') ?></label></div>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <label class="consent full"><input type="checkbox" name="datenschutz" required><span><?= t('Ich bin einverstanden, dass DAVINCI mir das Design-Book zusendet und meine Angaben dafür verarbeitet. <a href="/datenschutz/">Datenschutz</a>', 'I agree that DAVINCI may send me the Design Book and process my details for this purpose. <a href="/datenschutz/">Privacy policy</a>') ?></span></label>
        <label class="consent full"><input type="checkbox" name="inspiration" value="1"><span><?= t('Optional: Ja, ich möchte in den nächsten Wochen vier E-Mails mit Inspiration zu Grundstück, Grundrissen und Ausstattung erhalten. Abmeldung jederzeit möglich.', 'Optional: Yes, I would like to receive four emails over the coming weeks with inspiration on plots, floor plans and finishes (in German). You can unsubscribe at any time.') ?></span></label>
        <div class="full" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
          <button type="submit" class="btn btn--dark" data-label="<?= t('Design-Book anfordern', 'Request the Design Book') ?>"><?= t('Design-Book anfordern', 'Request the Design Book') ?></button>
          <p class="form__msg" data-form-msg role="status"></p>
        </div>
      </form>
    </div>
  </div>
</section>
<?php seite_ende();
