/* =========================================================
   DAVINCI – Animationen mit GSAP (https://gsap.com)
   GSAP und Plugins liegen lokal unter /assets/js/gsap/.
   Wer „Bewegung reduzieren“ eingestellt hat, bekommt die Seite ohne Animation.
   ========================================================= */
(() => {
  'use strict';
  if (!window.gsap) return;
  const { gsap } = window;
  const plugins = [window.ScrollTrigger, window.SplitText, window.Flip, window.DrawSVGPlugin].filter(Boolean);
  gsap.registerPlugin(...plugins);
  const ST = window.ScrollTrigger;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const mm = gsap.matchMedia();
  const RUHIG = '(prefers-reduced-motion: reduce)';
  const BEWEGT = '(prefers-reduced-motion: no-preference)';
  const DESKTOP = '(min-width: 861px) and (prefers-reduced-motion: no-preference)';
  const MOBIL = '(max-width: 860px) and (prefers-reduced-motion: no-preference)';
  const EASE = 'power3.out';

  /* ---------- 1 · Startseite: Titel Buchstabe für Buchstabe, Bild zoomt beim Scrollen heraus ---------- */
  const titel = $('.hero__title');
  if (titel && window.SplitText) {
    mm.add(BEWEGT, () => {
      document.fonts.ready.then(() => {
        titel.classList.remove('load-in'); titel.classList.add('is-split');
        const split = new SplitText(titel, { type: 'lines,chars', mask: 'lines', linesClass: 'line' });
        gsap.from(split.chars, { yPercent: 115, duration: 1.15, ease: 'power4.out', stagger: 0.035, delay: 0.25 });
      });
    });
  }
  const hero = $('.hero');
  if (hero && ST) {
    mm.add(BEWEGT, () => {
      gsap.fromTo('.hero__slide', { scale: 1.12 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
    });
  }

  /* ---------- 2 · Häuser: Parallaxe der Hausbilder und Filter mit fließendem Umsortieren ---------- */
  if (ST) {
    mm.add(BEWEGT, () => {
      $$('.product__img img[data-speed]').forEach(img => {
        gsap.fromTo(img, { yPercent: -6 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: img.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } });
      });
    });
  }
  const filter = $('[data-filter]');
  const grid = $('[data-filter-grid]');
  if (filter && grid) {
    const karten = $$('.product', grid);
    const buttons = $$('[data-f]', filter);
    const ruhig = window.matchMedia(RUHIG).matches;
    filter.addEventListener('click', e => {
      const btn = e.target.closest('[data-f]'); if (!btn || btn.classList.contains('is-active')) return;
      buttons.forEach(b => { const on = b === btn; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on); });
      const f = btn.dataset.f;
      const state = window.Flip && !ruhig ? Flip.getState(karten) : null;
      karten.forEach(k => { k.classList.add('is-in'); k.classList.toggle('is-hidden', f !== 'alle' && !k.dataset.kat.split(' ').includes(f)); });
      if (state) {
        Flip.from(state, {
          duration: 0.7, ease: 'power2.inOut', absolute: false, scale: false, nested: true,
          onEnter: el => gsap.fromTo(el, { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: 0.6, ease: EASE }),
          onComplete: () => ST && ST.refresh()
        });
      } else if (ST) ST.refresh();
    });
  }

  /* ---------- 3 · System: Raster zeichnet sich, dann füllen sich die Räume ---------- */
  const raster = $('[data-raster]');
  if (raster && ST) {
    const svg = $('.raster-svg', raster);
    const steps = $$('[data-rstep]', raster);
    const aktiv = i => steps.forEach((s, k) => s.classList.toggle('is-active', k === i));
    const schritt = tl => { const t = tl.time(); aktiv(t >= tl.labels.privat ? 2 : t >= tl.labels.offen ? 1 : 0); };
    const baue = () => {
      const tl = gsap.timeline({ defaults: { ease: 'none' } });
      tl.set($$('.r-room', svg), { opacity: 0 })
        .from($('.r-border', svg), { drawSVG: '0%', duration: 1 })
        .from($$('.r-grid', svg), { drawSVG: '0%', duration: 1, stagger: 0.08 }, '<0.2')
        .from($$('.r-post', svg), { scale: 0, transformOrigin: '50% 50%', duration: 0.3, stagger: { each: 0.03, from: 'center' } }, '<0.4')
        .addLabel('offen', '+=0.3')
        .to($$('.r-room.r-a', svg), { opacity: 1, duration: 0.6, stagger: 0.15 }, 'offen')
        .addLabel('privat', '+=0.6')
        .to($$('.r-room.r-a:not(.r-b)', svg), { opacity: 0, duration: 0.4 }, 'privat')
        .fromTo($$('.r-room.r-b:not(.r-a)', svg), { opacity: 0, scale: 0.96, transformOrigin: '50% 50%' }, { opacity: 1, scale: 1, duration: 0.6, stagger: 0.12 }, 'privat+=0.1')
        .to({}, { duration: 0.4 });
      tl.eventCallback('onUpdate', () => schritt(tl));
      return tl;
    };
    mm.add(DESKTOP, () => {
      const tl = baue();
      ST.create({ trigger: raster, start: 'top top', end: '+=220%', pin: true, scrub: 0.6, animation: tl, anticipatePin: 1 });
    });
    mm.add(MOBIL, () => {
      const tl = baue();
      ST.create({ trigger: $('.raster__visual', raster), start: 'top 75%', end: 'bottom 15%', scrub: 0.6, animation: tl });
    });
  }

  /* ---------- 4 · Manufaktur: fünf Schritte horizontal, Montagezeit zählt hoch ---------- */
  const hsteps = $('[data-hsteps]');
  if (hsteps && ST) {
    const track = $('.hsteps__track', hsteps);
    const bar = $('.hsteps__progress i', hsteps);
    const karten = $$('.hstep', hsteps);
    mm.add(DESKTOP, () => {
      const weg = () => Math.max(0, track.scrollWidth - document.documentElement.clientWidth);
      const tween = gsap.to(track, { x: () => -weg(), ease: 'none' });
      ST.create({ trigger: hsteps, start: 'top top', end: () => '+=' + weg(), pin: $('.hsteps__pin', hsteps), scrub: 0.5, animation: tween, invalidateOnRefresh: true, anticipatePin: 1,
        onUpdate: s => gsap.set(bar, { scaleX: s.progress }) });
      karten.forEach(k => ST.create({ trigger: k, containerAnimation: tween, start: 'left 70%', end: 'right 30%', toggleClass: 'is-active' }));
    });
    mm.add(MOBIL, () => {
      karten.forEach(k => {
        gsap.from(k, { opacity: 0, y: 40, duration: 0.8, ease: EASE, scrollTrigger: { trigger: k, start: 'top 85%', toggleActions: 'play none none reverse' } });
        ST.create({ trigger: k, start: 'top 70%', end: 'bottom 30%', toggleClass: 'is-active' });
      });
    });
    mm.add(RUHIG, () => { karten.forEach(k => k.classList.add('is-active')); });
  }
  $$('[data-count]').forEach(el => {
    const [a, b] = el.dataset.count.split('-').map(Number);
    const klein = el.querySelector('small'); const rest = klein ? klein.outerHTML : '';
    mm.add(BEWEGT, () => {
      const z = { a: 0, b: 0 };
      const zeige = () => { el.innerHTML = Math.round(z.a) + (b ? '–' + Math.round(z.b) : '') + rest; };
      zeige();
      gsap.to(z, { a, b: b || 0, duration: 1.6, ease: 'power2.out', onUpdate: zeige, scrollTrigger: ST ? { trigger: el, start: 'top 85%', once: true } : undefined });
      return () => { el.innerHTML = a + (b ? '–' + b : '') + rest; };
    });
  });

  /* ---------- 5 · Geschichte: Linie füllt sich von 1936 bis heute ---------- */
  const tlEl = $('[data-timeline]');
  if (tlEl && ST) {
    mm.add(BEWEGT, () => {
      gsap.to($('.timeline__progress i', tlEl), { scaleY: 1, ease: 'none', scrollTrigger: { trigger: tlEl, start: 'top 60%', end: 'bottom 60%', scrub: true } });
      $$('li[data-bild]', tlEl).forEach(li => ST.create({ trigger: li, start: 'top 60%', end: 'bottom 60%', toggleClass: 'is-current' }));
    });
  }

  /* Nach dem Laden aller Bilder Positionen neu berechnen */
  if (ST) window.addEventListener('load', () => ST.refresh());
})();
