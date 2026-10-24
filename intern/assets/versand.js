/* DAVINCI intern – laufenden Newsletter-Versand Paket für Paket weiterschieben und Fortschritt zeigen */
(() => {
  'use strict';
  const box = document.querySelector('[data-versand]');
  if (!box || box.dataset.status !== 'laeuft') return;
  const id = box.dataset.versand;
  const balken = box.querySelector('[data-v-balken]');
  const text = box.querySelector('[data-v-text]');
  const hinweis = box.querySelector('[data-v-hinweis]');
  const schritt = () => window.dvPost('versand.php', { id }).then(r => {
    if (r.gesamt) {
      balken.style.width = Math.round((r.gesamt - r.offen) / r.gesamt * 100) + '%';
      text.textContent = `${r.ok} von ${r.gesamt} zugestellt` + (r.fehler ? ` · ${r.fehler} Fehler` : '') + (r.uebersprungen ? ` · ${r.uebersprungen} übersprungen` : '');
    }
    if (r.status === 'fertig' || r.status === 'pausiert') { setTimeout(() => location.reload(), 800); return; }
    if (r.warten && hinweis) hinweis.textContent = 'Stundenlimit erreicht. Der Versand geht automatisch weiter, sobald wieder E-Mails frei sind (diese Seite offen lassen oder Cron-Job nutzen).';
    setTimeout(schritt, r.warten ? 60000 : 2500);
  }).catch(() => setTimeout(schritt, 10000));
  setTimeout(schritt, 600);
})();
