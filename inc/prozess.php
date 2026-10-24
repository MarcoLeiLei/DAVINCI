<?php
/* Der DAVINCI Prozess: fünf Schritte, überall gleich (Manufaktur-Seite) */
$schritte = [
  [t('Gespräch', 'Conversation'), t('Wir lernen Sie, Ihr Grundstück und Ihre Wünsche kennen.', 'We get to know you, your plot and your wishes.'), '<g class="a-pop a-o-bl"><path d="M6 9h22a4 4 0 0 1 4 4v9a4 4 0 0 1-4 4H16l-6 5v-5H6a4 4 0 0 1-4-4v-9a4 4 0 0 1 4-4z"/><circle class="a-dot" cx="11" cy="17.5" r="1.3"/><circle class="a-dot a-t2" cx="17" cy="17.5" r="1.3"/><circle class="a-dot a-t3" cx="23" cy="17.5" r="1.3"/></g><g class="a-pop a-o-br a-t4"><path d="M36 20h6a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4h-1v5l-6-5h-8a4 4 0 0 1-4-4v-4"/></g>'],
  [t('Entwurf', 'Design'), t('Aus dem DAVINCI System entsteht Ihr Haus, als fertiges Produkt oder individuell.', 'Your house takes shape from the DAVINCI system, as a ready-designed model or bespoke.'), '<path class="a-draw" pathLength="1" d="M10 36V20L24 8l14 12v16z"/><path class="a-draw a-t3" pathLength="1" d="M19 36v-9h10v9"/><g class="a-growx a-t4"><path d="M8 43h32M8 40v6M40 40v6"/></g>'],
  [t('Planung', 'Planning'), t('Bemusterung, Genehmigung und ein verbindliches Angebot.', 'Material selection, permit and a binding offer.'), '<g class="a-slide"><rect x="4" y="36" width="26" height="6" rx="1"/><path d="M9 39h9"/></g><g class="a-slide a-t2"><rect x="4" y="29" width="26" height="6" rx="1"/><path d="M14 32h10"/></g><g class="a-slide a-t3"><rect x="4" y="22" width="26" height="6" rx="1"/><path d="M8 25h7"/></g><g class="a-drop a-t4"><rect class="a-glass" x="34" y="8" width="10" height="34" rx="1"/><path class="a-draw a-t6" pathLength="1" d="M37 17l4-4M37 24l4-4"/></g>'],
  [t('Manufaktur', 'Manufacture'), t('Die Bauteile entstehen präzise im Werk unseres Partners in Deutschland.', 'The components are made precisely at our partner\'s works in Germany.'), '<rect x="6" y="18" width="36" height="24" rx="1"/><path class="a-draw a-t1" pathLength="1" d="M6 18l18 24"/><path class="a-draw a-t3" pathLength="1" d="M24 18v24"/><path class="a-draw a-t5" pathLength="1" d="M42 18L24 42"/><g class="a-head"><rect x="20" y="4" width="8" height="6" rx="1"/><path d="M24 10v5"/><circle class="a-spark" cx="24" cy="16.5" r="1.2"/></g>'],
  [t('Montage', 'Assembly'), t('Auf Ihrem Grundstück steht die Hülle in 5–7 Tagen. Danach Ausbau und Übergabe.', 'On your plot, the shell stands within 5–7 days. Then fit-out and handover.'), '<path d="M8 44V5h30M8 11l6-6"/><path class="a-draw" pathLength="1" d="M3 44h42"/><path class="a-growy a-t1" d="M21 44V31"/><path class="a-growy a-t2" d="M41 44V31"/><path class="a-rope" d="M31 5v17"/><g class="a-roof"><path d="M18 32l13-10 13 10"/></g><rect class="a-glass a-fade a-t6" x="27" y="35" width="8" height="9"/>'],
];
?>
<section class="hsteps" aria-label="<?= t('So entsteht Ihr Haus', 'How your home comes together') ?>" data-hsteps>
  <div class="hsteps__pin">
    <div class="wrap hsteps__head">
      <p class="eyebrow"><?= t('So entsteht Ihr DAVINCI', 'How your DAVINCI comes together') ?></p>
      <h2 class="h2"><?= t('Fünf Schritte, ein Ansprechpartner.', 'Five steps, one point of contact.') ?></h2>
    </div>
    <div class="hsteps__viewport">
      <ol class="hsteps__track">
<?php foreach ($schritte as $i => [$titel, $text, $icon]): ?>
        <li class="hstep">
          <span class="hstep__num">0<?= $i + 1 ?></span>
          <span class="flow__icon hstep__icon"><svg viewBox="0 0 48 48" aria-hidden="true"><?= $icon ?></svg></span>
          <h3><?= e($titel) ?></h3>
          <p><?= e($text) ?></p>
        </li>
<?php endforeach; ?>
      </ol>
    </div>
    <div class="wrap"><div class="hsteps__progress" aria-hidden="true"><i></i></div></div>
  </div>
</section>
