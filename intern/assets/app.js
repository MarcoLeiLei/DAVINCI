/* DAVINCI intern – kleine Helfer */
(() => {
  'use strict';
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const csrf = $('meta[name="csrf"]')?.content || '';
  window.dvPost = (url, daten) => {
    const fd = daten instanceof FormData ? daten : new FormData();
    if (!(daten instanceof FormData)) Object.entries(daten || {}).forEach(([k, v]) => fd.append(k, v));
    return fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF': csrf }, credentials: 'same-origin' }).then(r => r.json());
  };

  // Menü auf dem Handy
  $('[data-menu]')?.addEventListener('click', () => $('[data-nav]').classList.toggle('is-open'));
  // Benutzermenü schließt bei Klick daneben
  document.addEventListener('click', e => { const d = $('.top__user[open]'); if (d && !d.contains(e.target)) d.removeAttribute('open'); });

  // Ganze Tabellenzeile klickbar
  $$('tr[data-href]').forEach(tr => {
    tr.classList.add('klickbar');
    tr.addEventListener('click', e => { if (!e.target.closest('a, button, input, select, label')) location.href = tr.dataset.href; });
  });

  // Sicherheitsabfrage
  $$('[data-bestaetigen]').forEach(el => el.addEventListener(el.tagName === 'FORM' ? 'submit' : 'click', e => {
    if (!confirm(el.dataset.bestaetigen)) { e.preventDefault(); e.stopImmediatePropagation(); }
  }));
  // Auswahl sendet Formular sofort
  $$('[data-auto]').forEach(el => el.addEventListener('change', () => el.form.submit()));

  // Kanban: Karten zwischen Spalten ziehen
  const board = $('[data-kanban]');
  if (board) {
    let karte = null;
    $$('.karte', board).forEach(k => {
      k.draggable = true;
      k.addEventListener('dragstart', e => { karte = k; k.classList.add('ist-ziehen'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', k.dataset.id); });
      k.addEventListener('dragend', () => { k.classList.remove('ist-ziehen'); $$('.spalte', board).forEach(s => s.classList.remove('ist-ziel')); });
    });
    $$('.spalte', board).forEach(sp => {
      sp.addEventListener('dragover', e => { e.preventDefault(); sp.classList.add('ist-ziel'); });
      sp.addEventListener('dragleave', e => { if (!sp.contains(e.relatedTarget)) sp.classList.remove('ist-ziel'); });
      sp.addEventListener('drop', e => {
        e.preventDefault(); sp.classList.remove('ist-ziel');
        if (!karte) return;
        const liste = $('.spalte__karten', sp);
        const vor = [...liste.children].find(c => c !== karte && e.clientY < c.getBoundingClientRect().top + c.offsetHeight / 2);
        liste.insertBefore(karte, vor || null);
        dvPost('leads.php?aktion=status', { id: karte.dataset.id, status: sp.dataset.status }).then(r => { if (!r.ok) location.reload(); zaehlen(); });
      });
    });
    const zaehlen = () => $$('.spalte', board).forEach(sp => {
      const ks = $$('.karte', sp);
      const sum = ks.reduce((a, k) => a + (+k.dataset.wert || 0), 0);
      $('.spalte__kopf span', sp).textContent = ks.length + (sum ? ' · ' + sum.toLocaleString('de-DE') + ' €' : '');
    });
  }
})();
