/* =========================================================
   DAVINCI · Interaktionen
   ========================================================= */

/* Preise werden zentral in inc/site.php gepflegt. */

/* ---------------------------------------------------------
   HUBSPOT TERMINBUCHUNG  ← Ihren Meetings-Link hier eintragen
   z. B. 'https://meetings-eu1.hubspot.com/ihr-name'
   Solange leer, zeigt das Fenster Telefon & E-Mail an.
   --------------------------------------------------------- */
const HUBSPOT_MEETING_URL = ''; // HubSpot ist vorerst aus: Termine laufen per E-Mail (Link später wieder eintragen: https://meetings-eu1.hubspot.com/constantin7)

/* Dauer pro Hero-Bild in Millisekunden */
const HERO_INTERVALL = 6500;

/* Jede neue Seite beginnt oben (außer bei Sprungmarken wie #produkte). */
(() => {
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  if (location.hash) return;
  let user = false;
  ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(ev => addEventListener(ev, () => { user = true; }, { once: true, passive: true }));
  const top = () => { if (!user && window.scrollY > 0) window.scrollTo({ top: 0, left: 0, behavior: 'instant' }); };
  top();
  document.addEventListener('DOMContentLoaded', top, { once: true });
  addEventListener('load', () => { top(); setTimeout(top, 150); }, { once: true });
  addEventListener('pageshow', e => { if (e.persisted) top(); });
})();

/* ---------------------------------------------------------
   GOOGLE reCAPTCHA v3  ← Website-Schlüssel hier eintragen
   (der geheime Schlüssel gehört in lib/config.php)
   Leer = reCAPTCHA aus, Honeypot, Zeitprüfung und Limit bleiben aktiv
   --------------------------------------------------------- */
const RECAPTCHA_SITE_KEY = '';

/* ---------- Google Ads ----------
   GOOGLE_ADS_ID: Konto-ID aus Google Ads → Tools → Conversions → Tag einrichten (Format 'AW-123456789').
   Labels: je Conversion-Aktion der Teil nach dem Schrägstrich ('AW-123456789/AbCdEf…' → 'AbCdEf…').
   Solange die ID leer ist, wird nichts geladen und kein Cookie-Banner gezeigt. */
const GOOGLE_ADS_ID = '';
const GOOGLE_ADS_LABELS = {
  designbook: '',      // Design-Book angefordert
  grundriss: '',       // Grundriss angefordert
  terminanfrage: '',   // Terminanfrage
  kontakt: ''          // Kontaktformular
};

(() => {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const EN = document.documentElement.lang === 'en';
  const T = EN ? {
    menuAuf: 'Open menu', menuZu: 'Close menu',
    captcha: 'This form is protected by Google reCAPTCHA. The Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.',
    pflicht: 'Please fill in all required fields and confirm your consent.',
    senden: 'Sending …', danke: 'Thank you.', gesendet: 'Request sent.', absenden: 'Send',
    fehler: 'Something went wrong. Please write to us directly at <a href="mailto:bauen@davinci-homes.de" style="text-decoration:underline">bauen@davinci-homes.de</a>.',
    cTitel: 'Your privacy',
    cText: 'We use technically necessary storage. With your consent we use Google Ads to measure how well our ads work. Google may set cookies and process data, including in the USA. You can change your choice at any time via “Cookie settings” at the bottom of the page. More in our <a href="/datenschutz/">privacy policy</a> (German).',
    cNotw: 'Necessary<small>Stores your choice. Always active.</small>', cMedien: 'Marketing<small>Google Ads conversion measurement</small>',
    ki: 'AI visualisation',
    cAlle: 'Accept all', cNur: 'Necessary only', cEinst: 'Settings', cSpeichern: 'Save selection',
    bLead: 'For online booking we load the HubSpot calendar.',
    bText: 'HubSpot may set cookies and receive data. Details in our <a href="/datenschutz/">privacy policy</a> (German).',
    bLaden: 'Load calendar', bDirekt: 'Or directly:'
  } : {
    menuAuf: 'Menü öffnen', menuZu: 'Menü schließen',
    captcha: 'Dieses Formular ist durch Google reCAPTCHA geschützt. Es gelten die <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Datenschutzerklärung</a> und die <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Nutzungsbedingungen</a> von Google.',
    pflicht: 'Bitte füllen Sie alle Pflichtfelder aus und bestätigen Sie die Einwilligung.',
    senden: 'Wird gesendet …', danke: 'Vielen Dank.', gesendet: 'Anfrage gesendet.', absenden: 'Absenden',
    fehler: 'Das hat leider nicht geklappt. Bitte schreiben Sie uns direkt an <a href="mailto:bauen@davinci-homes.de" style="text-decoration:underline">bauen@davinci-homes.de</a>.',
    cTitel: 'Ihre Privatsphäre',
    cText: 'Wir verwenden technisch notwendige Speicherungen. Mit Ihrer Einwilligung nutzen wir Google Ads, um den Erfolg unserer Anzeigen zu messen. Dabei kann Google Cookies setzen und Daten verarbeiten, auch in den USA. Ihre Auswahl können Sie jederzeit über „Cookie-Einstellungen“ unten auf der Seite ändern. Mehr in der <a href="/datenschutz/">Datenschutzerklärung</a>.',
    cNotw: 'Notwendig<small>Speichert Ihre Auswahl. Immer aktiv.</small>', cMedien: 'Marketing<small>Google Ads, Messung von Anzeigenerfolgen</small>',
    ki: 'KI-Visualisierung',
    cAlle: 'Alle akzeptieren', cNur: 'Nur notwendige', cEinst: 'Einstellungen', cSpeichern: 'Auswahl speichern',
    bLead: 'Für die Online-Terminbuchung laden wir den Kalender von HubSpot.',
    bText: 'Dabei können Cookies gesetzt und Daten an HubSpot übertragen werden. Details finden Sie in der <a href="/datenschutz/">Datenschutzerklärung</a>.',
    bLaden: 'Kalender laden', bDirekt: 'Oder direkt:'
  };
  const eur = n => new Intl.NumberFormat('de-DE', { maximumFractionDigits: 0 }).format(Math.round(n)) + ' €';
  const round = (n, s = 1000) => Math.round(n / s) * s;

  /* ---------- Jahr ---------- */
  $$('[data-year]').forEach(el => el.textContent = new Date().getFullYear());

  /* ---------- Navigation ---------- */
  const nav = $('#nav');
  const darkSections = $$('.hero, .phero:not(.phero--light), .dark, .band, .claim');
  const burger = $('#burger');
  const menu = $('#mobile-menu');

  const setMenu = open => {
    document.body.classList.toggle('menu-open', open);
    burger?.setAttribute('aria-expanded', open);
    burger?.setAttribute('aria-label', open ? T.menuZu : T.menuAuf);
    menu?.setAttribute('aria-hidden', !open);
    document.documentElement.style.overflow = open ? 'hidden' : '';
  };
  burger?.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')));
  $$('a', menu || document.createElement('div')).forEach(a => a.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { setMenu(false); closeLightbox(); } });

  const updateNav = () => {
    if (!nav) return;
    const y = window.scrollY;
    const hero = $('.hero, .phero:not(.phero--light)');
    const heroEnd = hero ? hero.offsetHeight - 60 : 0;
    nav.classList.toggle('is-solid', !hero || y > heroEnd);
    // dunkle Sektion unter der Navigation?
    const probe = nav.offsetHeight / 2;
    let dark = false;
    for (const s of darkSections) {
      const r = s.getBoundingClientRect();
      if (r.top <= probe && r.bottom >= probe) { dark = true; break; }
    }
    nav.classList.toggle('is-dark', dark);
  };

  /* ---------- Reveal ---------- */
  const revealEls = $$('.reveal, [data-process]');
  if ('IntersectionObserver' in window && !reduce) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealEls.forEach(el => io.observe(el));
  } else revealEls.forEach(el => el.classList.add('is-in'));

  /* ---------- Statement: Wort für Wort ---------- */
  const statement = $('[data-words]');
  let words = [];
  if (statement) {
    const wrapWords = node => {
      [...node.childNodes].forEach(n => {
        if (n.nodeType === 3) {
          const frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(part => {
            if (!part) return;
            if (/^\s+$/.test(part)) frag.appendChild(document.createTextNode(part));
            else {
              const s = document.createElement('span');
              s.className = 'w' + (node.classList && node.classList.contains('accent') ? ' accent' : '');
              s.textContent = part; frag.appendChild(s);
            }
          });
          n.replaceWith(frag);
        } else if (n.nodeType === 1) wrapWords(n);
      });
    };
    wrapWords(statement);
    words = $$('.w', statement);
    if (reduce) words.forEach(w => w.classList.add('on'));
  }
  const updateWords = () => {
    if (!statement || reduce) return;
    const r = statement.getBoundingClientRect();
    const vh = window.innerHeight;
    const p = Math.min(1, Math.max(0, (vh * 0.85 - r.top) / (r.height + vh * 0.35)));
    const n = Math.round(p * words.length);
    words.forEach((w, i) => w.classList.toggle('on', i < n));
  };

  /* ---------- Parallax ---------- */
  const heroImg = $('[data-hero-img]');
  const heroTitle = $('[data-hero-title]');
  const parallaxEls = $$('[data-parallax]');
  const updateParallax = () => {
    if (reduce) return;
    const y = window.scrollY, vh = window.innerHeight;
    if (heroImg && y < vh * 1.2) {
      heroImg.style.transform = `translate3d(0, ${y * 0.32}px, 0)`;
      if (heroTitle) {
        heroTitle.style.transform = `translate3d(0, ${y * -0.12}px, 0)`;
        heroTitle.parentElement.style.opacity = Math.max(0, 1 - y / (vh * 0.7));
      }
    }
    parallaxEls.forEach(el => {
      const box = el.parentElement.getBoundingClientRect();
      if (box.bottom < -100 || box.top > vh + 100) return;
      const speed = parseFloat(el.dataset.parallax) || 0.1;
      const center = box.top + box.height / 2 - vh / 2;
      el.style.transform = `translate3d(0, ${center * -speed}px, 0)`;
    });
  };

  /* ---------- Feature-Scroller (sticky Bild) ---------- */
  const featureWrap = $('[data-features]');
  if (featureWrap && 'IntersectionObserver' in window) {
    const imgs = $$('[data-feature-img]', featureWrap);
    const tag = $('[data-feature-tag]', featureWrap);
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        const idx = e.target.dataset.feature;
        $$('[data-feature]', featureWrap).forEach(f => f.classList.toggle('is-active', f === e.target));
        imgs.forEach(i => i.classList.toggle('is-active', i.dataset.featureImg === idx));
        if (tag) tag.textContent = e.target.dataset.label;
      });
    }, { rootMargin: '-45% 0px -45% 0px' });
    $$('[data-feature]', featureWrap).forEach(f => io.observe(f));
  }

  /* ---------- Scroll-Loop ---------- */
  let ticking = false;
  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => { updateNav(); updateParallax(); updateWords(); ticking = false; });
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  onScroll();

  /* Kontaktformular: Haus aus der Adresse vorbelegen (/kontakt?haus=bungalow) */
  const hausWahl = new URLSearchParams(location.search).get('haus');
  if (hausWahl && /^[a-z]+$/.test(hausWahl)) { const opt = $(`#f-typ option[data-key="${hausWahl}"]`); if (opt) opt.selected = true; }

  /* ---------- Vergleich zweier Fassaden (Regler) ---------- */
  $$('[data-ba]').forEach(ba => {
    const range = $('.ba__range', ba);
    const set = v => ba.style.setProperty('--p', v + '%');
    set(range.value);
    range.addEventListener('input', () => { set(range.value); ba.classList.add('is-touched'); });
    // kurzer Hinweis-Schwenk, sobald der Vergleich ins Bild kommt
    if ('IntersectionObserver' in window && !reduce) {
      const io = new IntersectionObserver(([e]) => {
        if (!e.isIntersecting) return; io.disconnect();
        let t0 = null; const dauer = 1600;
        const step = ts => { if (ba.classList.contains('is-touched')) return; t0 = t0 || ts; const k = Math.min(1, (ts - t0) / dauer);
          const v = 50 + Math.sin(k * Math.PI * 2) * 18; set(v); range.value = v; if (k < 1) requestAnimationFrame(step); };
        setTimeout(() => requestAnimationFrame(step), 500);
      }, { threshold: 0.6 });
      io.observe(ba);
    }
  });

  /* ---------- Herkunft des Besuchers (UTM) merken ---------- */
  const SEITENSTART = Date.now();
  const herkunft = (() => {
    let h = null;
    try { h = JSON.parse(sessionStorage.getItem('dv-herkunft') || 'null'); } catch (e) {}
    if (!h) {
      const u = new URLSearchParams(location.search);
      h = { landing: location.pathname + location.search };
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(k => { if (u.get(k)) h[k] = u.get(k).slice(0, 120); });
      if (u.get('gclid')) h.utm_source = h.utm_source || 'google-ads';
      if (document.referrer && !document.referrer.includes(location.hostname)) h.referrer = document.referrer.slice(0, 200);
      try { sessionStorage.setItem('dv-herkunft', JSON.stringify(h)); } catch (e) {}
    }
    return h;
  })();

  /* ---------- Spamschutz: reCAPTCHA v3, lädt erst beim Ausfüllen ---------- */
  let recaptchaLaden = null;
  const ladeRecaptcha = () => {
    if (!RECAPTCHA_SITE_KEY) return Promise.resolve(null);
    if (!recaptchaLaden) recaptchaLaden = new Promise(res => {
      const sc = document.createElement('script');
      sc.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(RECAPTCHA_SITE_KEY);
      sc.onload = () => window.grecaptcha.ready(() => res(window.grecaptcha));
      sc.onerror = () => res(null);
      document.head.appendChild(sc);
    });
    return recaptchaLaden;
  };
  const captchaToken = async aktion => {
    const g = await ladeRecaptcha();
    if (!g) return '';
    try { return await g.execute(RECAPTCHA_SITE_KEY, { action: aktion }); } catch (e) { return ''; }
  };
  const zusatzfelder = (fd, aktion) => Promise.resolve(captchaToken(aktion)).then(t => {
    fd.append('ts', SEITENSTART);
    Object.entries(herkunft).forEach(([k, v]) => fd.append(k, v));
    if (t) fd.append('g-recaptcha-response', t);
    return fd;
  });
  $$('[data-contact]').forEach(form => {
    form.addEventListener('focusin', ladeRecaptcha, { once: true });
    if (RECAPTCHA_SITE_KEY) {
      const hinweis = document.createElement('p');
      hinweis.className = 'captcha-hinweis full';
      hinweis.innerHTML = T.captcha;
      form.appendChild(hinweis);
    }
  });

  /* ---------- Kennzeichnung der KI-Visualisierungen ---------- */
  $$('img[src*="/dv-"]').forEach(img => {
    const box = img.closest('.hero__slide, figure, .product__img, .way__img, .mood__i, .split__img, .house__img, .dbook-teaser__cover') || img.parentElement;
    if (!box || box.querySelector(':scope > .ki-badge')) return;
    if (getComputedStyle(box).position === 'static') box.style.position = 'relative';
    const b = document.createElement('span'); b.className = 'ki-badge'; b.textContent = T.ki; b.setAttribute('aria-hidden', 'true');
    box.appendChild(b);
  });

  /* ---------- Bestätigung nach dem Absenden ---------- */
  const SENT_SVG = '<svg class="sent__anim" viewBox="0 0 120 120" aria-hidden="true">'
    + '<g class="sent__plane"><path d="M22 62 L98 30 L80 96 L60 72 Z" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/><path d="M98 30 L60 72 L58 92 L68 78" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linejoin="round"/></g>'
    + '<circle class="sent__ring" cx="60" cy="60" r="44" fill="none" stroke="currentColor" stroke-width="2.4" pathLength="1"/>'
    + '<path class="sent__check" d="M42 61 L55 74 L80 47" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" pathLength="1"/></svg>';
  const zeigeGesendet = form => {
    const box = document.createElement('div');
    box.className = 'sent'; box.setAttribute('role', 'status'); box.tabIndex = -1;
    const titel = form.dataset.successTitle || T.gesendet;
    const text = form.dataset.success || T.danke;
    box.innerHTML = SENT_SVG + '<p class="sent__title"></p><p class="sent__text"></p>';
    box.querySelector('.sent__title').textContent = titel;
    box.querySelector('.sent__text').textContent = text;
    form.hidden = true;
    form.after(box);
    try { window.dvConversion && window.dvConversion(form.dataset.aktion); } catch (e) {}
    const r = box.getBoundingClientRect();
    if (r.top < 0 || r.bottom > innerHeight) box.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' });
    box.focus({ preventScroll: true });
  };
  const formZuruecksetzen = form => {
    if (!form) return;
    const next = form.nextElementSibling;
    if (next && next.classList.contains('sent')) next.remove();
    form.hidden = false;
    const msg = $('[data-form-msg]', form); if (msg) { msg.textContent = ''; msg.className = 'form__msg'; }
  };

  /* ---------- Kontaktformular ---------- */
  $$('[data-contact]').forEach(form => form.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = $('[data-form-msg]', form);
    msg.className = 'form__msg';
    if (!form.checkValidity()) {
      msg.textContent = T.pflicht;
      msg.classList.add('err');
      form.reportValidity();
      return;
    }
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true; btn.textContent = T.senden;
    try {
      const fd = await zusatzfelder(new FormData(form), form.dataset.aktion || 'formular');
      const res = await fetch(form.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || data.ok === false) throw new Error((data.error || 'unbekannt') + ' · HTTP ' + res.status);
      form.reset();
      form.dispatchEvent(new Event('change'));
      zeigeGesendet(form);
    } catch (err) {
      msg.innerHTML = T.fehler + ' <small style="display:block;opacity:.6;margin-top:6px">Code: ' + String(err && err.message || err).replace(/[<>&]/g, '') + '</small>';
      msg.classList.add('err');
    } finally {
      btn.disabled = false; btn.textContent = btn.dataset.label || T.absenden;
    }
  }));

  /* ---------- Chronik: Bild zum Jahr ---------- */
  const hm = $('[data-history-media]');
  if (hm && 'IntersectionObserver' in window) {
    const imgs = $$('img', hm), cap = $('[data-history-cap]', hm);
    let akt = 0;
    const kiAus = i => hm.classList.toggle('ki-aus', !/\/dv-/.test(imgs[i].getAttribute('src')));
    kiAus(0);
    const zeige = i => {
      if (i === akt || !imgs[i]) return; akt = i; kiAus(i);
      imgs.forEach((im, k) => im.classList.toggle('is-active', k === i));
      if (cap) { cap.style.opacity = 0; setTimeout(() => { cap.textContent = imgs[i].dataset.cap || ''; cap.style.opacity = 1; }, 250); }
    };
    const io = new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting) zeige(+en.target.dataset.bild); }), { rootMargin: '-45% 0px -45% 0px' });
    $$('.timeline li[data-bild]').forEach(li => io.observe(li));
  }

  /* ---------- Intro: Sicherung, falls die Animation nicht startet ---------- */
  if (document.documentElement.classList.contains('intro')) setTimeout(() => { if (!window.dvIntroLaeuft) { document.documentElement.classList.remove('intro'); $('.intro-overlay')?.remove(); } }, 1200);

  /* ---------- Hero-Slider ---------- */
  const heroEl = $('.hero');
  const slides = $$('.hero__slide');
  const dots = $$('.hero__dots [data-slide]');
  if (slides.length > 1) {
    let cur = 0, timer = null;
    heroEl.style.setProperty('--slide-ms', HERO_INTERVALL + 'ms');
    $('.hero__media')?.classList.add('is-first');
    const go = i => {
      $('.hero__media')?.classList.remove('is-first');
      const prev = cur;
      cur = (i + slides.length) % slides.length;
      if (cur === prev) return;
      if (window.dvHeroWechsel && !reduce) window.dvHeroWechsel(slides[prev], slides[cur], slides);
      else slides.forEach((sl, k) => sl.classList.toggle('is-active', k === cur));
      dots.forEach((d, k) => {
        d.setAttribute('aria-selected', k === cur);
        const bar = d.querySelector('i'); if (bar) { bar.style.animation = 'none'; bar.offsetWidth; bar.style.animation = ''; }
      });
    };
    const start = () => { clearInterval(timer); if (!reduce) timer = setInterval(() => go(cur + 1), HERO_INTERVALL); };
    dots.forEach(d => d.addEventListener('click', () => { go(+d.dataset.slide); start(); }));
    document.addEventListener('visibilitychange', () => document.hidden ? clearInterval(timer) : start());
    start();
  }

  /* ---------- Cookie-Einwilligung ---------- */
  const CONSENT_KEY = 'davinci-consent-v2';
  const readConsent = () => { try { return JSON.parse(localStorage.getItem(CONSENT_KEY)); } catch (e) { return null; } };
  let consent = readConsent();
  const saveConsent = medien => {
    consent = { notwendig: true, medien: !!medien, marketing: !!medien, datum: new Date().toISOString() };
    try { localStorage.setItem(CONSENT_KEY, JSON.stringify(consent)); } catch (e) {}
    closeCookie();
    document.dispatchEvent(new CustomEvent('consentchange'));
  };
  const cookie = document.createElement('div');
  cookie.className = 'cookie';
  cookie.setAttribute('role', 'dialog');
  cookie.setAttribute('aria-labelledby', 'cookie-title');
  cookie.innerHTML = `
    <div class="cookie__main">
      <p class="cookie__title" id="cookie-title">${T.cTitel}</p>
      <p class="cookie__text">${T.cText}</p>
      <div class="cookie__settings" hidden>
        <label class="check"><span>${T.cNotw}</span><span class="switch"><input type="checkbox" checked disabled><i></i></span></label>
        <label class="check"><span>${T.cMedien}</span><span class="switch"><input type="checkbox" data-consent="medien"><i></i></span></label>
      </div>
    </div>
    <div class="cookie__actions">
      <button type="button" class="btn btn--dark" data-cookie="all">${T.cAlle}</button>
      <button type="button" class="btn btn--ghost on-light" data-cookie="necessary"><span>${T.cNur}</span></button>
      <button type="button" class="cookie__more" data-cookie="settings">${T.cEinst}</button>
      <button type="button" class="btn btn--dark" data-cookie="save" hidden>${T.cSpeichern}</button>
    </div>`;
  document.body.appendChild(cookie);
  const cSettings = $('.cookie__settings', cookie);
  const cMedien = $('[data-consent="medien"]', cookie);
  function openCookie(showSettings = false) {
    cMedien.checked = !!(consent && consent.medien);
    cSettings.hidden = !showSettings;
    $('[data-cookie="save"]', cookie).hidden = !showSettings;
    $('[data-cookie="settings"]', cookie).hidden = showSettings;
    cookie.classList.add('is-open');
  }
  function closeCookie() { cookie.classList.remove('is-open'); }
  $('[data-cookie="all"]', cookie).addEventListener('click', () => saveConsent(true));
  $('[data-cookie="necessary"]', cookie).addEventListener('click', () => saveConsent(false));
  $('[data-cookie="settings"]', cookie).addEventListener('click', () => openCookie(true));
  $('[data-cookie="save"]', cookie).addEventListener('click', () => saveConsent(cMedien.checked));
  $$('[data-cookie-settings]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); openCookie(true); }));
  if (!consent && (GOOGLE_ADS_ID || HUBSPOT_MEETING_URL)) setTimeout(() => openCookie(false), 900);  // Banner nur, wenn etwas Einwilligungspflichtiges eingebunden ist
  if (!GOOGLE_ADS_ID && !HUBSPOT_MEETING_URL) $$('[data-cookie-settings]').forEach(a => a.hidden = true);

  /* ---------- Google Ads: erst nach Einwilligung laden ---------- */
  let adsGeladen = false;
  window.dataLayer = window.dataLayer || [];
  const gtag = function () { window.dataLayer.push(arguments); };
  const ladeAds = () => {
    if (!GOOGLE_ADS_ID || !(consent && consent.marketing)) {
      if (adsGeladen) gtag('consent', 'update', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
      return;
    }
    if (adsGeladen) { gtag('consent', 'update', { ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted' }); return; }
    adsGeladen = true;
    gtag('consent', 'default', { ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted', analytics_storage: 'denied' });
    gtag('js', new Date());
    gtag('config', GOOGLE_ADS_ID);
    const sc = document.createElement('script');
    sc.async = true; sc.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GOOGLE_ADS_ID);
    document.head.appendChild(sc);
  };
  document.addEventListener('consentchange', ladeAds);
  ladeAds();
  window.dvConversion = aktion => {
    const label = GOOGLE_ADS_LABELS[aktion];
    if (adsGeladen && label && consent && consent.marketing) gtag('event', 'conversion', { send_to: GOOGLE_ADS_ID + '/' + label });
  };

  /* ---------- Terminbuchung (HubSpot) ---------- */
  const booking = $('[data-booking-modal]');
  let hubspotLoaded = false;
  const loadHubspot = () => {
    if (!booking || !HUBSPOT_MEETING_URL || hubspotLoaded) return;
    const body = $('[data-booking-body]', booking);
    if (!(consent && consent.medien)) {
      body.innerHTML = `<div class="booking__fallback">
        <p class="lead">${T.bLead}</p>
        <p>${T.bText}</p>
        <button type="button" class="btn btn--dark" data-load-hubspot>${T.bLaden}</button>
        <p>${T.bDirekt} <a href="tel:+4915786832859">+49 157 868 328 59</a> · <a href="mailto:bauen@davinci-homes.de">bauen@davinci-homes.de</a></p>
      </div>`;
      $('[data-load-hubspot]', body).addEventListener('click', () => saveConsent(true));
      return;
    }
    hubspotLoaded = true;
    const url = HUBSPOT_MEETING_URL + (HUBSPOT_MEETING_URL.includes('?') ? '&' : '?') + 'embed=true';
    body.innerHTML = `<div class="meetings-iframe-container" data-src="${url}"></div>`;
    const sc = document.createElement('script');
    sc.src = 'https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js';
    document.body.appendChild(sc);
  };
  document.addEventListener('consentchange', () => { if (booking?.classList.contains('is-open')) loadHubspot(); });
  const openBooking = () => {
    if (!booking) return;
    setMenu(false);
    formZuruecksetzen($('form[data-contact]', booking));
    booking.classList.add('is-open');
    booking.setAttribute('aria-hidden', 'false');
    document.documentElement.style.overflow = 'hidden';
    loadHubspot();
    $('[data-booking-close]', booking)?.focus();
  };
  const closeBooking = () => {
    if (!booking?.classList.contains('is-open')) return;
    booking.classList.remove('is-open');
    booking.setAttribute('aria-hidden', 'true');
    document.documentElement.style.overflow = '';
  };
  $$('[data-booking]').forEach(b => b.addEventListener('click', e => { e.preventDefault(); openBooking(); }));
  $$('[data-booking-close]').forEach(b => b.addEventListener('click', closeBooking));
  booking?.addEventListener('click', e => { if (e.target === booking) closeBooking(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBooking(); });
  if (location.hash === '#termin') openBooking();

  /* ---------- Grundriss anfordern (Lead) ---------- */
  const gr = $('[data-gr-modal]');
  const closeGr = () => { if (!gr?.classList.contains('is-open')) return; gr.classList.remove('is-open'); gr.setAttribute('aria-hidden', 'true'); document.documentElement.style.overflow = ''; };
  $$('[data-grundriss]').forEach(b => b.addEventListener('click', () => {
    if (!gr) return;
    const name = b.dataset.name || '';
    const haus = $('[data-gr-haus]', gr), hn = $('[data-gr-name]', gr);
    haus.value = b.dataset.grundriss; haus.setAttribute('value', b.dataset.grundriss);
    hn.value = name; hn.setAttribute('value', name);
    $('[data-gr-title]', gr).textContent = (document.documentElement.lang === 'en' ? 'Floor plan · ' : 'Grundriss · ') + name;
    formZuruecksetzen($('form[data-contact]', gr));
    gr.classList.add('is-open'); gr.setAttribute('aria-hidden', 'false');
    document.documentElement.style.overflow = 'hidden';
    setTimeout(() => $('input[name="vorname"]', gr)?.focus(), 60);
  }));
  $$('[data-gr-close]').forEach(b => b.addEventListener('click', closeGr));
  gr?.addEventListener('click', e => { if (e.target === gr) closeGr(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeGr(); });

  /* ---------- HubSpot: Buchung abfangen → eigene Bestätigungs-E-Mail ---------- */
  const findeWert = (obj, key, tiefe = 0) => {
    if (!obj || typeof obj !== 'object' || tiefe > 6) return null;
    if (obj[key] != null && typeof obj[key] !== 'object') return obj[key];
    for (const v of Object.values(obj)) { const r = findeWert(v, key, tiefe + 1); if (r != null) return r; }
    return null;
  };
  window.addEventListener('message', e => {
    if (!/hubspot\.com$/.test(new URL(e.origin || 'http://x').hostname)) return;
    const data = e.data || {};
    if (!data.meetingBookSucceeded) return;
    const p = data.meetingsPayload || {};
    const br = p.bookingResponse || {};
    const post = br.postResponse || {};
    const c = post.contact || {};
    const email = c.email || findeWert(p, 'email');
    if (!email) return;
    const fd = new FormData();
    fd.append('anfrage_typ', 'Termin');
    fd.append('sprache', EN ? 'en' : 'de');
    fd.append('email', email);
    fd.append('vorname', c.firstName || findeWert(p, 'firstName') || '');
    fd.append('nachname', c.lastName || findeWert(p, 'lastName') || '');
    fd.append('start', (post.timerange && post.timerange.start) || (br.event && br.event.dateTime) || findeWert(p, 'start') || '');
    zusatzfelder(fd, 'termin').then(f => fetch('/send.php', { method: 'POST', body: f })).catch(() => {});
  });

  /* ---------- Design-Book: Adressfelder bei Print ---------- */
  $$('.dbook__form').forEach(f => {
    const fields = $('[data-print-fields]', f);
    if (!fields) return;  // Design-Book gibt es derzeit nur als PDF
    const sync = () => {
      const print = f.querySelector('input[name="format"]:checked')?.value !== 'PDF';
      fields.hidden = !print;
      $$('input', fields).forEach(i => i.required = print);
    };
    f.addEventListener('change', sync); sync();
  });

  /* ---------- Prozess-Leiste unter dem Hero ---------- */
  const flow = $('[data-flow]');
  if (flow) {
    const steps = $$('.flow__step', flow);
    const bar = $('.flow__bar', flow);
    const textEl = $('.flow__text', flow);
    const titleEl = $('[data-flow-title]', flow);
    const descEl = $('[data-flow-text]', flow);
    const FLOW_MS = 3800;
    let cur = 0, timer = null, sichtbar = false, pause = false, scrub = false;
    const track = $('.flow__track', flow);
    const runner = $('.flow__runner', flow);
    const setzePos = p => {
      if (bar) bar.style.width = (p * 100) + '%';
      if (runner) runner.style.left = (p * 100) + '%';
    };
    const zeige = i => {
      cur = (i + steps.length) % steps.length;
      steps[cur].classList.remove('is-active'); void steps[cur].offsetWidth;   // Zeichen-Animation neu starten
      steps.forEach((s, k) => {
        s.classList.toggle('is-active', k === cur);
        s.classList.toggle('is-done', k < cur);
        const b = $('button', s); if (b) b.setAttribute('aria-current', k === cur ? 'step' : 'false');
      });
      if (!scrub) setzePos(cur / (steps.length - 1));
      textEl.classList.add('is-fading');
      setTimeout(() => {
        titleEl.textContent = $('.flow__title', steps[cur]).textContent;
        descEl.textContent = steps[cur].dataset.text;
        textEl.classList.remove('is-fading');
      }, reduce ? 0 : 260);
    };
    const takt = () => {
      clearInterval(timer);
      if (reduce || !sichtbar || pause) return;
      timer = setInterval(() => zeige(cur + 1), FLOW_MS);
    };
    steps.forEach((s, k) => {
      const b = $('button', s);
      b.addEventListener('click', () => { zeige(k); takt(); });
      // Maus drüber: Station aktivieren und ihre Szene (neu) abspielen
      b.addEventListener('mouseenter', () => { if (window.matchMedia('(hover: hover)').matches) zeige(k); });
      b.addEventListener('focus', () => zeige(k));
    });
    flow.addEventListener('mouseenter', () => { pause = true; takt(); });
    flow.addEventListener('mouseleave', () => { pause = false; takt(); });
    // Signet läuft auf der Linie mit der Maus mit
    const rail = $('.flow__rail', flow);
    if (rail && track && window.matchMedia('(hover: hover)').matches) {
      rail.addEventListener('mousemove', e => {
        const r = track.getBoundingClientRect();
        const p = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
        scrub = true; flow.classList.add('is-scrubbing');
        setzePos(p);
        const naechste = Math.round(p * (steps.length - 1));
        if (naechste !== cur) zeige(naechste);
      });
      rail.addEventListener('mouseleave', () => {
        scrub = false; flow.classList.remove('is-scrubbing');
        setzePos(cur / (steps.length - 1));
      });
    }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(([e]) => {
        const war = sichtbar; sichtbar = e.isIntersecting;
        if (sichtbar && !war) zeige(cur);   // Icons beim Hereinscrollen neu zeichnen
        takt();
      }, { threshold: 0.35 }).observe(flow);
    }
    document.addEventListener('visibilitychange', () => { if (document.hidden) clearInterval(timer); else takt(); });
  }

  /* ---------- Lightbox ---------- */
  const lb = $('[data-lightbox]');
  const lbImg = lb ? $('img', lb) : null;
  function closeLightbox() { lb?.classList.remove('is-open'); lb?.setAttribute('aria-hidden', 'true'); }
  $$('[data-gallery] .g').forEach(g => g.addEventListener('click', () => {
    const img = $('img', g);
    lbImg.src = img.src; lbImg.alt = img.alt;
    lb.classList.add('is-open'); lb.setAttribute('aria-hidden', 'false');
  }));
  lb?.addEventListener('click', closeLightbox);
})();
