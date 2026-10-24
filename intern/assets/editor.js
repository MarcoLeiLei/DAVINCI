/* DAVINCI intern – Newsletter-Editor: Blöcke bearbeiten, Live-Vorschau, Bilder, Test */
(() => {
  'use strict';
  const form = document.querySelector('[data-editor]');
  if (!form) return;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const id = form.dataset.id;
  const web = form.dataset.web;
  const webBilder = JSON.parse(form.dataset.bilder || '[]');
  let medien = JSON.parse(form.dataset.medien || '[]');
  const gruppenListe = JSON.parse(form.dataset.gruppenliste || '[]');
  const feld = $('[data-bloecke]');
  const liste = $('[data-blockliste]');
  const frame = $('[data-frame]');
  let bloecke = JSON.parse(feld.value || '[]');

  const NAMEN = { titel: 'Überschrift', text: 'Text', bild: 'Bild', knopf: 'Knopf', trenner: 'Linie', kasten: 'Kasten' };
  const NEU = {
    titel: { eyebrow: '', text: 'Neue Überschrift' },
    text: { text: '' },
    bild: { src: '', alt: '', link: '' },
    knopf: { text: 'Mehr erfahren', url: 'https://davinci-homes.de', stil: 'dunkel' },
    trenner: {},
    kasten: { titel: 'Gut zu wissen', text: '' },
  };
  const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const icon = d => `<svg class="i" viewBox="0 0 24 24"><path d="${d}"/></svg>`;
  const bildUrl = src => src.startsWith('web:') ? web + '/assets/img/' + src.slice(4) : src.startsWith('medien:') ? 'medien/' + src.slice(7) : src;

  const eingabe = (b, k, label, art = 'input', extra = '') => {
    const v = esc(b[k]);
    if (art === 'textarea') return `<label class="feld">${label}<textarea data-k="${k}" rows="5" ${extra}>${v}</textarea></label>`;
    return `<label class="feld">${label}<input data-k="${k}" value="${v}" ${extra}></label>`;
  };
  const inhalt = (b, i) => {
    switch (b.typ) {
      case 'titel': return eingabe(b, 'eyebrow', 'Kleine Zeile darüber (optional)') + eingabe(b, 'text', 'Überschrift', 'textarea', 'rows="2" style="min-height:60px"');
      case 'text': return eingabe(b, 'text', 'Text · Leerzeile = neuer Absatz · **fett** · [Linktext](https://…) · „- “ am Zeilenanfang = Liste', 'textarea');
      case 'kasten': return eingabe(b, 'titel', 'Titel') + eingabe(b, 'text', 'Text', 'textarea', 'rows="3"');
      case 'knopf': return `<div class="form form--2">${eingabe(b, 'text', 'Beschriftung')}${eingabe(b, 'url', 'Link', 'input', 'placeholder="https://davinci-homes.de/…"')}
        <label class="feld">Stil<select data-k="stil"><option value="dunkel"${b.stil !== 'hell' ? ' selected' : ''}>Schwarz</option><option value="hell"${b.stil === 'hell' ? ' selected' : ''}>Weiß mit Rahmen</option></select></label></div>`;
      case 'bild': return `<div class="bildwahl" data-bildwahl="${i}">
          <button type="button" data-hochladen title="Eigenes Bild hochladen" style="display:grid;place-items:center;color:var(--grey-4);font-size:12px">${icon('M12 16V4M7 9l5-5 5 5M4 20h16')}<span>Hochladen</span></button>
          ${medien.map(m => `<button type="button" data-src="medien:${esc(m)}" class="${b.src === 'medien:' + m ? 'is-active' : ''}"><img src="medien/${esc(m)}" alt="" loading="lazy"></button>`).join('')}
          ${webBilder.map(m => `<button type="button" data-src="web:${esc(m)}" class="${b.src === 'web:' + m ? 'is-active' : ''}" title="${esc(m)}${m.startsWith('dv-') ? ' (KI-Visualisierung)' : ''}"><img src="${web}/assets/img/${esc(m)}" alt="" loading="lazy"></button>`).join('')}
        </div><div class="form form--2">${eingabe(b, 'alt', 'Bildbeschreibung (für Screenreader und blockierte Bilder)')}${eingabe(b, 'link', 'Link beim Klick (optional)')}</div>
        ${b.src && b.src.includes('dv-') ? '<p class="muted klein">KI-Visualisierung: wird im Newsletter automatisch gekennzeichnet.</p>' : ''}`;
      default: return '<p class="muted klein">Trennlinie zwischen Abschnitten.</p>';
    }
  };
  const zeichnen = () => {
    liste.innerHTML = bloecke.map((b, i) => `<div class="block" data-i="${i}">
      <div class="block__kopf"><span>${NAMEN[b.typ] || b.typ}</span><span class="block__knoepfe">
        <button type="button" data-hoch title="Nach oben"${i === 0 ? ' disabled' : ''}>${icon('M12 19V5M6 11l6-6 6 6')}</button>
        <button type="button" data-runter title="Nach unten"${i === bloecke.length - 1 ? ' disabled' : ''}>${icon('M12 5v14M6 13l6 6 6-6')}</button>
        <button type="button" data-weg title="Entfernen">${icon('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3')}</button></span></div>
      ${inhalt(b, i)}</div>`).join('') || '<div class="empty">Noch kein Inhalt. Fügen Sie unten Blöcke hinzu.</div>';
    sync();
  };
  let vorschauTimer = null, speicherTimer = null;
  const sync = () => {
    feld.value = JSON.stringify(bloecke);
    clearTimeout(vorschauTimer); vorschauTimer = setTimeout(vorschau, 350);
    clearTimeout(speicherTimer); speicherTimer = setTimeout(autosave, 4000);
  };
  const vorschau = () => {
    const fd = new FormData(form);
    fetch(`kampagne.php?id=${id}&aktion=vorschau`, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.text()).then(html => {
        const y = frame.contentWindow ? frame.contentWindow.scrollY : 0;
        frame.srcdoc = html;
        frame.onload = () => { try { frame.contentWindow.scrollTo(0, y); } catch (e) {} };
      });
  };
  const autosave = () => {
    if (!$('[name="betreff"]', form).value.trim()) return;
    window.dvPost(`kampagne.php?id=${id}&aktion=speichern`, new FormData(form)).then(r => { if (r.ok) $('[data-gespeichert]').textContent = 'Automatisch gespeichert ' + r.zeit; }).catch(() => {});
  };

  liste.addEventListener('input', e => {
    const el = e.target.closest('[data-k]'); if (!el) return;
    const i = +el.closest('.block').dataset.i;
    bloecke[i][el.dataset.k] = el.value; sync();
  });
  liste.addEventListener('change', e => { if (e.target.matches('select[data-k]')) { const i = +e.target.closest('.block').dataset.i; bloecke[i][e.target.dataset.k] = e.target.value; sync(); } });
  liste.addEventListener('click', e => {
    const blk = e.target.closest('.block'); if (!blk) return;
    const i = +blk.dataset.i;
    if (e.target.closest('[data-hoch]') && i > 0) { [bloecke[i - 1], bloecke[i]] = [bloecke[i], bloecke[i - 1]]; zeichnen(); }
    else if (e.target.closest('[data-runter]') && i < bloecke.length - 1) { [bloecke[i + 1], bloecke[i]] = [bloecke[i], bloecke[i + 1]]; zeichnen(); }
    else if (e.target.closest('[data-weg]')) { bloecke.splice(i, 1); zeichnen(); }
    else if (e.target.closest('[data-src]')) {
      const src = e.target.closest('[data-src]').dataset.src;
      bloecke[i].src = src;
      if (!bloecke[i].alt) bloecke[i].alt = 'DAVINCI Haus aus Holz und Glas';
      zeichnen();
    } else if (e.target.closest('[data-hochladen]')) { upload.dataset.i = i; upload.click(); }
  });
  $$('[data-neu]').forEach(b => b.addEventListener('click', () => {
    bloecke.push({ typ: b.dataset.neu, ...JSON.parse(JSON.stringify(NEU[b.dataset.neu])) });
    zeichnen();
    const letzter = liste.lastElementChild; letzter?.scrollIntoView({ behavior: 'smooth', block: 'center' }); $('input, textarea', letzter)?.focus({ preventScroll: true });
  }));

  // Bild hochladen
  const upload = $('[data-upload]');
  upload.addEventListener('change', () => {
    const f = upload.files[0]; if (!f) return;
    const fd = new FormData(); fd.append('bild', f);
    const i = +upload.dataset.i;
    const knopf = $(`[data-bildwahl="${i}"] [data-hochladen] span`); if (knopf) knopf.textContent = 'Lädt …';
    window.dvPost(`kampagne.php?id=${id}&aktion=bild`, fd).then(r => {
      if (!r.ok) { alert(r.fehler || 'Hochladen fehlgeschlagen.'); zeichnen(); return; }
      medien.unshift(r.src.slice(7));
      bloecke[i].src = r.src; zeichnen();
    }).finally(() => { upload.value = ''; });
  });

  // Empfänger live zählen
  const zaehlen = () => {
    const gew = $$('[name="gruppen[]"]:checked', form).map(c => c.value);
    const n = gew.length ? gruppenListe.filter(g => g.some(x => gew.includes(x))).length : gruppenListe.length;
    $$('[data-anzahl]').forEach(el => { el.textContent = n; });
    $('[data-anzahl-text]').textContent = gew.length ? 'Kontakte in mindestens einer der gewählten Gruppen' : 'Keine Gruppe gewählt: alle Kontakte';
  };
  $$('[name="gruppen[]"]', form).forEach(c => c.addEventListener('change', () => { zaehlen(); sync(); }));
  $$('[name="betreff"], [name="preheader"]', form).forEach(el => el.addEventListener('input', sync));

  // Test senden
  $('[data-test]').addEventListener('click', () => {
    const msg = $('[data-test-meldung]');
    const fd = new FormData(form); fd.set('an', $('[data-test-an]').value);
    msg.textContent = 'Wird gesendet …';
    window.dvPost(`kampagne.php?id=${id}&aktion=test`, fd).then(r => { msg.textContent = r.meldung || r.fehler || 'Fehler'; msg.style.color = r.ok ? 'var(--ok)' : 'var(--err)'; })
      .catch(() => { msg.textContent = 'Fehler beim Senden.'; msg.style.color = 'var(--err)'; });
  });
  // Versand nur mit Haken
  $('[data-starten]').addEventListener('click', e => {
    if (!$('[name="bestaetigt"]', form).checked) { e.preventDefault(); $('[name="bestaetigt"]', form).closest('.check').style.color = 'var(--err)'; $('[name="bestaetigt"]', form).focus(); return; }
    const spaeter = $('[name="wann"]:checked', form).value === 'spaeter';
    const zp = $('[name="zeitpunkt"]', form).value;
    const frage = spaeter ? 'Newsletter für ' + new Date(zp).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' }) + ' Uhr planen?'
      : 'Newsletter jetzt an ' + $('[data-anzahl]').textContent + ' Empfänger senden? Das lässt sich nicht rückgängig machen.';
    if (!confirm(frage)) e.preventDefault();
  });
  // Jetzt oder später
  const wann = () => {
    const spaeter = $('[name="wann"]:checked', form).value === 'spaeter';
    $$('[data-zeitpunkt]').forEach(el => { el.hidden = !spaeter; });
    $('[data-starten-text]').textContent = spaeter ? 'Versand planen' : 'Jetzt versenden';
  };
  $$('[data-wann]').forEach(r => r.addEventListener('change', wann));
  // Vorschaubreite
  $$('[data-breite]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault();
    $$('[data-breite]').forEach(x => x.classList.toggle('is-active', x === a));
    $('[data-vorschau]').classList.toggle('ist-mobil', a.dataset.breite === 'mobil');
  }));
  // Strg+S speichert
  document.addEventListener('keydown', e => { if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); form.requestSubmit(); } });

  zeichnen();
})();
