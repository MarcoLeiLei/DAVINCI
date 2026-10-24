/* =========================================================
   DAVINCI – Bildbetrachter
   Bilder in Inhaltsbereichen lassen sich anklicken und groß ansehen.
   Öffnen: das Bild fliegt aus seiner Position leicht gedreht in die Mitte.
   Blättern: per Pfeil, Taste oder Wischen, das Bild kippt wie eine Karte zur Seite.
   Schließen: das Bild fliegt an seinen Platz zurück (oder fällt sanft nach unten).
   ========================================================= */
(() => {
  'use strict';
  const SEL = '.echt__i img, .mood__i img, .split__img img, .features__media img, .feature__img-m img, .history__media img';
  const alle = [...document.querySelectorAll(SEL)];
  if (!alle.length) return;

  const g = window.gsap;
  const anim = !!g && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const en = document.documentElement.lang === 'en';
  const T = en
    ? { dialog: 'Image viewer', close: 'Close', prev: 'Previous image', next: 'Next image', ki: 'AI visualisation', open: 'Enlarge image' }
    : { dialog: 'Bildbetrachter', close: 'Schließen', prev: 'Vorheriges Bild', next: 'Nächstes Bild', ki: 'KI-Visualisierung', open: 'Bild vergrößern' };
  const svg = d => `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="${d}" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>`;

  /* ---------- Aufbau ---------- */
  const lb = document.createElement('div');
  lb.className = 'lb'; lb.setAttribute('role', 'dialog'); lb.setAttribute('aria-modal', 'true'); lb.setAttribute('aria-label', T.dialog); lb.hidden = true;
  lb.innerHTML = '<div class="lb__bg"></div>'
    + '<div class="lb__stage"><div class="lb__frame"><img class="lb__img" alt="" draggable="false"><span class="lb__ki"></span></div></div>'
    + '<p class="lb__cap"><span class="lb__count"></span><span class="lb__text"></span></p>'
    + `<button type="button" class="lb__btn lb__close" aria-label="${T.close}">${svg('M6 6l12 12M18 6L6 18')}</button>`
    + `<button type="button" class="lb__btn lb__prev" aria-label="${T.prev}">${svg('M15 5l-7 7 7 7')}</button>`
    + `<button type="button" class="lb__btn lb__next" aria-label="${T.next}">${svg('M9 5l7 7-7 7')}</button>`;
  document.body.appendChild(lb);
  const q = s => lb.querySelector(s);
  const bg = q('.lb__bg'), stage = q('.lb__stage'), frame = q('.lb__frame'), bild = q('.lb__img'), ki = q('.lb__ki');
  const zahl = q('.lb__count'), text = q('.lb__text'), btnZu = q('.lb__close'), btnZur = q('.lb__prev'), btnVor = q('.lb__next');
  const knoepfe = [btnZu, btnZur, btnVor, q('.lb__cap')];
  ki.textContent = T.ki;

  /* ---------- Gruppen: alle sichtbaren Bilder desselben Abschnitts ---------- */
  const sichtbar = img => img.getClientRects().length > 0 && getComputedStyle(img.closest('figure, div') || img).display !== 'none';
  const gruppeVon = img => {
    const sek = img.closest('section, .mood') || document.body;
    const gesehen = new Set();
    return alle.filter(i => sek.contains(i) && sichtbar(i)).filter(i => { const k = i.getAttribute('src'); if (gesehen.has(k)) return false; gesehen.add(k); return true; });
  };
  // Bei Bildstapeln (Chronik, Raum) zählt das gerade aktive Bild
  const aktivesBild = img => {
    const stapel = img.closest('.history__media, .features__media');
    return (stapel && stapel.querySelector('img.is-active')) || img;
  };

  alle.forEach(img => {
    const box = img.closest('.history__media, .features__media') || img;
    if (box.dataset.lb) return; box.dataset.lb = '1';
    box.classList.add('lb-auf');
    if (box === img) { img.tabIndex = 0; img.setAttribute('role', 'button'); img.setAttribute('aria-label', T.open + ': ' + (img.alt || '')); }
  });

  let liste = [], idx = 0, offen = false, beschaeftigt = false, zuletzt = null;

  const quelle = img => img.currentSrc || img.src;
  const laden = src => new Promise(ok => { const i = new Image(); i.onload = i.onerror = () => (i.decode ? i.decode().catch(() => {}).then(ok) : ok()); i.src = src; });
  const zeigeDaten = () => {
    const img = liste[idx];
    bild.src = quelle(img); bild.alt = img.alt || '';
    text.textContent = img.alt || '';
    zahl.textContent = liste.length > 1 ? (idx + 1) + ' / ' + liste.length : '';
    ki.hidden = !/\/dv-/.test(img.getAttribute('src'));
    lb.classList.toggle('lb--einzeln', liste.length < 2);
  };
  // Lage des Vorschaubilds relativ zum großen Bild (für den Flug hin und zurück)
  const versatz = img => {
    const a = img.getBoundingClientRect(), b = frame.getBoundingClientRect();
    if (!a.width || a.bottom < 0 || a.top > innerHeight) return null;
    return { x: (a.left + a.width / 2) - (b.left + b.width / 2), y: (a.top + a.height / 2) - (b.top + b.height / 2), scale: Math.max(a.width / b.width, a.height / b.height) };
  };

  /* ---------- Öffnen ---------- */
  const oeffnen = async img => {
    if (offen || beschaeftigt) return;
    img = aktivesBild(img);
    liste = gruppeVon(img); idx = Math.max(0, liste.indexOf(img)); if (!liste.length) liste = [img];
    zuletzt = document.activeElement;
    beschaeftigt = true;
    await laden(quelle(img));
    zeigeDaten();
    lb.hidden = false; offen = true;
    document.documentElement.classList.add('lb-offen');
    btnZu.focus({ preventScroll: true });
    if (!anim) { beschaeftigt = false; return; }
    const v = versatz(img) || { x: 0, y: 80, scale: 0.6 };
    g.timeline({ onComplete: () => { beschaeftigt = false; g.set(knoepfe, { clearProps: 'transform,opacity' }); } })
      .fromTo(bg, { opacity: 0 }, { opacity: 1, duration: 0.45, ease: 'power2.out' })
      .fromTo(frame, { x: v.x, y: v.y, scale: v.scale, rotation: -4, borderRadius: 18 },
        { x: 0, y: 0, scale: 1, rotation: 0, borderRadius: 4, duration: 0.8, ease: 'back.out(1.25)' }, 0)
      .fromTo(knoepfe, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.45, ease: 'back.out(2)', stagger: 0.06 }, 0.35);
  };

  /* ---------- Blättern ---------- */
  const blaettern = async (dir, schwung = 0) => {
    if (!offen || beschaeftigt || liste.length < 2) return;
    beschaeftigt = true;
    const neu = (idx + dir + liste.length) % liste.length;
    const geladen = laden(quelle(liste[neu]));
    if (anim) {
      await g.to(frame, { x: -dir * Math.max(innerWidth * 0.55, Math.abs(schwung)), rotation: -dir * 14, opacity: 0, scale: 0.92, duration: 0.32, ease: 'power2.in' });
    }
    await geladen;
    idx = neu; zeigeDaten();
    if (anim) {
      g.fromTo(zahl, { scale: 1.35 }, { scale: 1, duration: 0.5, ease: 'back.out(3)' });
      await g.fromTo(frame, { x: dir * innerWidth * 0.4, y: 0, rotation: dir * 9, opacity: 0, scale: 0.88 },
        { x: 0, rotation: 0, opacity: 1, scale: 1, duration: 0.6, ease: 'back.out(1.6)' });
    }
    beschaeftigt = false;
  };

  /* ---------- Schließen ---------- */
  const schliessen = async (fallen = false) => {
    if (!offen || beschaeftigt) return;
    beschaeftigt = true;
    if (anim) {
      const ziel = liste[idx];
      const v = !fallen && sichtbar(ziel) && aktivesBild(ziel) === ziel ? versatz(ziel) : null;
      const tl = g.timeline();
      tl.to(knoepfe, { opacity: 0, y: 10, duration: 0.2, ease: 'power1.in' })
        .to(bg, { opacity: 0, duration: 0.45, ease: 'power2.inOut' }, 0.05);
      if (v) tl.to(frame, { x: v.x, y: v.y, scale: v.scale, rotation: 3, borderRadius: 18, duration: 0.55, ease: 'power3.inOut' }, 0);
      else tl.to(frame, { y: '+=' + innerHeight * 0.35, rotation: 10, scale: 0.85, opacity: 0, duration: 0.5, ease: 'power2.in' }, 0);
      await tl;
      g.set([frame, bg, ...knoepfe], { clearProps: 'all' });
    }
    lb.hidden = true; offen = false; beschaeftigt = false;
    document.documentElement.classList.remove('lb-offen');
    if (zuletzt && zuletzt.focus) zuletzt.focus({ preventScroll: true });
  };

  /* ---------- Bedienung ---------- */
  document.addEventListener('click', e => {
    const img = e.target.closest('.lb-auf');
    if (!img || e.target.closest('a, button')) return;
    const ziel = img.tagName === 'IMG' ? img : img.querySelector('img.is-active') || img.querySelector('img');
    if (ziel) { e.preventDefault(); oeffnen(ziel); }
  });
  document.addEventListener('keydown', e => {
    if (!offen) {
      if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('lb-auf') && e.target.tagName === 'IMG') { e.preventDefault(); oeffnen(e.target); }
      return;
    }
    if (e.key === 'Escape') schliessen();
    else if (e.key === 'ArrowRight') blaettern(1);
    else if (e.key === 'ArrowLeft') blaettern(-1);
    else if (e.key === 'Tab') {
      const f = [btnZu, btnZur, btnVor].filter(b => b.offsetParent);
      const i = f.indexOf(document.activeElement);
      e.preventDefault(); f[(i + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus();
    }
  });
  btnZu.addEventListener('click', () => schliessen());
  btnVor.addEventListener('click', () => blaettern(1));
  btnZur.addEventListener('click', () => blaettern(-1));
  bg.addEventListener('click', () => schliessen());
  stage.addEventListener('click', e => { if (e.target === stage) schliessen(); });

  // Wischen: das Bild folgt dem Finger und kippt dabei leicht
  let start = null;
  frame.addEventListener('pointerdown', e => {
    if (beschaeftigt) return;
    start = { x: e.clientX, y: e.clientY, t: performance.now() };
    frame.setPointerCapture(e.pointerId); frame.classList.add('is-ziehen');
  });
  frame.addEventListener('pointermove', e => {
    if (!start) return;
    const dx = e.clientX - start.x, dy = e.clientY - start.y;
    if (anim) g.set(frame, { x: dx, y: Math.max(0, dy) * 0.9, rotation: dx * 0.03, scale: 1 - Math.min(Math.max(0, dy) / 1500, 0.15) });
  });
  const loslassen = e => {
    if (!start) return;
    const dx = e.clientX - start.x, dy = e.clientY - start.y, dt = Math.max(1, performance.now() - start.t);
    start = null; frame.classList.remove('is-ziehen');
    const schnell = Math.abs(dx) / dt > 0.5;
    if (dy > 120 && dy > Math.abs(dx)) schliessen(true);
    else if (liste.length > 1 && (Math.abs(dx) > 90 || (schnell && Math.abs(dx) > 30))) blaettern(dx < 0 ? 1 : -1, dx);
    else if (anim) g.to(frame, { x: 0, y: 0, rotation: 0, scale: 1, duration: 0.7, ease: 'elastic.out(1, 0.5)' });
  };
  frame.addEventListener('pointerup', loslassen);
  frame.addEventListener('pointercancel', loslassen);
})();
