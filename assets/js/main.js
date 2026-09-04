'use strict';
// --- BRANDING CONSOLE ---
console.log("%c======================================", "color: #6366F1; font-weight: bold;");
console.log("%c Le Vu Phong Ecosystem ", "color: white; background: #6366F1; font-size: 16px; font-weight: bold; padding: 4px; border-radius: 4px;");
console.log("%c Website: https://dichvugiare.net ", "color: #10B981; font-size: 14px;");
console.log("%c Zalo: 0855550612 ", "color: #3B82F6; font-size: 14px;");
console.log("%c======================================", "color: #6366F1; font-weight: bold;");
// ------------------------
/* ============================================================
   MAIN.JS — Cyber Profile V4 Global Earth Experience
   Earth Engine loaded separately via earth-engine.js
   This file handles: Init Earth, Typewriter, Scroll Reveal,
   Counters, Skill Bars, Carousel, World Map, Map Connections,
   Contact, Bank Copy, PWA, Back-to-top, Announcement
   ============================================================ */

const qs  = (s, ctx = document) => ctx.querySelector(s);
const qsa = (s, ctx = document) => [...ctx.querySelectorAll(s)];
const on  = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);

/* ── Toast ────────────────────────────────────────────────────── */
function showToast(type, msg, duration = 3500) {
  const icons = { success:'fa-check-circle', error:'fa-times-circle', info:'fa-info-circle', warning:'fa-exclamation-circle' };
  const c = qs('#toast-container');
  if (!c) return;
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<i class="fas ${icons[type]||icons.info}"></i><span>${msg}</span>`;
  c.appendChild(el);
  setTimeout(() => {
    el.style.cssText = 'opacity:0;transform:translateX(100%);transition:all 0.4s ease';
    setTimeout(() => el.remove(), 400);
  }, duration);
}

/* ═══════════════════════════════════════════════════════════════
   EARTH ENGINE V4 — Initialize
   ═══════════════════════════════════════════════════════════════ */
(function initEarth() {
  if (typeof EarthEngine === 'undefined') return;
  EarthEngine.init('earth-canvas');

  /* ── Earth Mode Badge update ── */
  const modeNames = {
    NETWORK:  'Network Globe',
    WIREFRAME:'Wireframe Globe',
    DATA:     'Data Globe',
    ENERGY:   'Energy Globe',
    HOLOGRAM: 'Hologram Globe',
  };
  const modeTextEl = qs('#earthModeText');
  let lastMode = '';

  function pollMode() {
    if (!window._earthCurrentMode) return;
    const mode = window._earthCurrentMode;
    if (mode !== lastMode && modeTextEl) {
      modeTextEl.textContent = modeNames[mode] || mode;
      lastMode = mode;
    }
  }
  setInterval(pollMode, 800);

  /* ── Drag Hint auto-hide ── */
  const hint = qs('#earthDragHint');
  if (hint) {
    let dragged = false;
    const canvas = qs('#earth-canvas');
    on(canvas, 'mousedown', () => {
      if (!dragged) {
        dragged = true;
        setTimeout(() => { hint.classList.add('hidden'); }, 1200);
      }
    });
    on(canvas, 'touchstart', () => {
      if (!dragged) {
        dragged = true;
        setTimeout(() => { hint.classList.add('hidden'); }, 1200);
      }
    }, { passive: true });
  }
})();

/* ═══════════════════════════════════════════════════════════════
   CARD TILT EFFECT
   ═══════════════════════════════════════════════════════════════ */
(function initTilt() {
  if (window.matchMedia('(hover: none)').matches) return;
  qsa('.tilt-card').forEach(el => {
    on(el, 'mousemove', e => {
      const rect = el.getBoundingClientRect();
      const cx   = rect.left + rect.width  / 2;
      const cy   = rect.top  + rect.height / 2;
      const rotX = ((e.clientY - cy) / (rect.height / 2)) * -5;
      const rotY = ((e.clientX - cx) / (rect.width  / 2)) *  5;
      el.style.transform = `perspective(900px) rotateX(${rotX}deg) rotateY(${rotY}deg) scale(1.02) translateY(-8px)`;
      el.style.transition = 'transform 0.1s ease';
    });
    on(el, 'mouseleave', () => {
      el.style.transform  = 'perspective(900px) rotateX(0) rotateY(0) scale(1) translateY(0)';
      el.style.transition = 'transform 0.5s cubic-bezier(0.4,0,0.2,1)';
    });
  });
})();

/* ═══════════════════════════════════════════════════════════════
   TYPEWRITER
   ═══════════════════════════════════════════════════════════════ */
(function initTypewriter() {
  const el = qs('#typewriter-text');
  if (!el) return;
  const texts = (typeof TYPEWRITER_TEXTS !== 'undefined' && TYPEWRITER_TEXTS.length)
    ? TYPEWRITER_TEXTS
    : ['Fullstack Developer', 'Founder', 'System Architect', 'Tech Ecosystem Builder'];
  let ti = 0, ci = 0, deleting = false;
  const DELAY_TYPE = 75, DELAY_DEL = 38, DELAY_PAUSE = 2400, DELAY_NEXT = 380;
  function tick() {
    const cur = texts[ti];
    if (!deleting) {
      el.textContent = cur.slice(0, ++ci);
      if (ci === cur.length) { deleting = true; setTimeout(tick, DELAY_PAUSE); return; }
    } else {
      el.textContent = cur.slice(0, --ci);
      if (ci === 0) { deleting = false; ti = (ti + 1) % texts.length; setTimeout(tick, DELAY_NEXT); return; }
    }
    setTimeout(tick, deleting ? DELAY_DEL : DELAY_TYPE);
  }
  setTimeout(tick, 800);
})();

/* ═══════════════════════════════════════════════════════════════
   SCROLL REVEAL
   ═══════════════════════════════════════════════════════════════ */
(function initReveal() {
  const els = qsa('.reveal, .reveal-left, .reveal-right');
  if (!els.length) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
  els.forEach(el => io.observe(el));
})();

/* ═══════════════════════════════════════════════════════════════
   COUNTER ANIMATION
   ═══════════════════════════════════════════════════════════════ */
(function initCounters() {
  const els = qsa('.counter-val');
  if (!els.length) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      const el     = e.target;
      const target = parseFloat(el.dataset.target);
      const suffix = el.dataset.suffix || '';
      if (isNaN(target)) return;
      const isDecimal = String(el.dataset.target).includes('.');
      const duration  = 2000;
      const start     = performance.now();
      const easeOut   = t => 1 - Math.pow(1 - t, 3);
      function update(now) {
        const t   = Math.min((now - start) / duration, 1);
        const val = target * easeOut(t);
        const display = isDecimal ? val.toFixed(1) : Math.floor(val).toLocaleString('vi-VN');
        const numEl = el.querySelector('.num');
        const sufEl = el.querySelector('.suffix');
        if (numEl) { numEl.textContent = display; if (sufEl) sufEl.textContent = t >= 1 ? suffix : ''; }
        else { el.textContent = display + (t >= 1 ? suffix : ''); }
        if (t < 1) requestAnimationFrame(update);
      }
      requestAnimationFrame(update);
    });
  }, { threshold: 0.3 });
  els.forEach(el => io.observe(el));
})();

/* ═══════════════════════════════════════════════════════════════
   SKILL BARS
   ═══════════════════════════════════════════════════════════════ */
(function initSkillBars() {
  const bars = qsa('.skill-bar-fill');
  if (!bars.length) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { io.unobserve(e.target); e.target.style.width = (e.target.dataset.level||80)+'%'; }
    });
  }, { threshold: 0.4 });
  bars.forEach(b => io.observe(b));
})();

/* ═══════════════════════════════════════════════════════════════
   TESTIMONIALS CAROUSEL
   ═══════════════════════════════════════════════════════════════ */
(function initCarousel() {
  const track   = qs('#reviewsTrack');
  if (!track) return;
  const slides  = qsa('.review-slide', track);
  const dotsEl  = qs('#carouselDots');
  const prevBtn = qs('#prevBtn');
  const nextBtn = qs('#nextBtn');
  if (!slides.length) return;

  let current = 0, autoTimer;

  function getVis() {
    if (window.innerWidth <= 640)  return 1;
    if (window.innerWidth <= 1024) return 2;
    return 3;
  }
  function getTotal() { return Math.ceil(slides.length / getVis()); }

  function buildDots() {
    if (!dotsEl) return;
    dotsEl.innerHTML = '';
    for (let i = 0; i < getTotal(); i++) {
      const d = document.createElement('div');
      d.className = 'carousel-dot' + (i === current ? ' active' : '');
      d.onclick = () => goTo(i);
      dotsEl.appendChild(d);
    }
  }
  function goTo(idx) {
    const total = getTotal();
    current = (idx + total) % total;
    const vis = getVis();
    const pct = current * vis * (100 / slides.length);
    track.style.transform = `translateX(-${pct}%)`;
    buildDots();
  }
  function startAuto() { stopAuto(); autoTimer = setInterval(() => goTo(current+1), 5000); }
  function stopAuto()  { clearInterval(autoTimer); }

  on(prevBtn, 'click', () => { goTo(current-1); startAuto(); });
  on(nextBtn, 'click', () => { goTo(current+1); startAuto(); });

  let startX = 0;
  on(track, 'touchstart', e => { startX = e.touches[0].clientX; stopAuto(); }, { passive: true });
  on(track, 'touchend',   e => {
    const diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) goTo(diff > 0 ? current+1 : current-1);
    startAuto();
  }, { passive: true });

  on(window, 'resize', () => { buildDots(); goTo(0); });
  buildDots();
  startAuto();
})();

/* ═══════════════════════════════════════════════════════════════
   WORLD MAP CANVAS (Global Presence Section)
   ═══════════════════════════════════════════════════════════════ */
(function initWorldMap() {
  const canvas = qs('#worldMapCanvas');
  const mapContainer = qs('.world-map-container');
  if (!canvas || !mapContainer) return;
  const ctx = canvas.getContext('2d');

  function resize() {
    canvas.width  = mapContainer.clientWidth;
    canvas.height = mapContainer.clientHeight;
  }

  function drawMap() {
    const W = canvas.width, H = canvas.height;
    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = 'rgba(3,3,10,0.8)';
    ctx.fillRect(0, 0, W, H);

    // Grid
    ctx.strokeStyle = 'rgba(99,102,241,0.05)';
    ctx.lineWidth = 0.5;
    const gx = W / 12, gy = H / 6;
    for (let x = 0; x <= W; x += gx) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,H); ctx.stroke(); }
    for (let y = 0; y <= H; y += gy) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(W,y); ctx.stroke(); }

    // Continents
    ctx.fillStyle = 'rgba(99,102,241,0.06)';
    ctx.strokeStyle = 'rgba(99,102,241,0.14)';
    ctx.lineWidth = 1;

    const polys = [
      [[0.08,0.10],[0.28,0.08],[0.30,0.15],[0.25,0.25],[0.20,0.50],[0.14,0.55],[0.10,0.45],[0.06,0.30]],
      [[0.20,0.55],[0.28,0.53],[0.30,0.65],[0.25,0.82],[0.20,0.85],[0.16,0.75],[0.16,0.60]],
      [[0.43,0.10],[0.56,0.08],[0.58,0.20],[0.52,0.30],[0.44,0.30],[0.42,0.20]],
      [[0.44,0.32],[0.58,0.30],[0.60,0.55],[0.55,0.75],[0.50,0.78],[0.44,0.70],[0.42,0.50]],
      [[0.56,0.08],[0.87,0.05],[0.90,0.20],[0.85,0.40],[0.75,0.50],[0.60,0.48],[0.56,0.35],[0.58,0.20]],
      [[0.77,0.57],[0.91,0.55],[0.93,0.72],[0.82,0.78],[0.75,0.70]],
    ];
    polys.forEach(pts => {
      ctx.beginPath();
      ctx.moveTo(pts[0][0]*W, pts[0][1]*H);
      pts.slice(1).forEach(p => ctx.lineTo(p[0]*W, p[1]*H));
      ctx.closePath(); ctx.fill(); ctx.stroke();
    });
  }

  const connections = [
    { from:[0.72,0.38], to:[0.725,0.47] },
    { from:[0.72,0.38], to:[0.80, 0.30] },
    { from:[0.72,0.38], to:[0.49, 0.23] },
    { from:[0.72,0.38], to:[0.21, 0.28] },
    { from:[0.49,0.23], to:[0.46, 0.20] },
    { from:[0.46,0.20], to:[0.21, 0.28] },
    { from:[0.21,0.28], to:[0.10, 0.30] },
  ];

  let animFrame = 0;

  const io = new IntersectionObserver(entries => {
    if (!entries[0].isIntersecting) return;
    io.disconnect();
    resize();
    on(window, 'resize', resize, { passive: true });
    animateMap();
  }, { threshold: 0.1 });
  io.observe(mapContainer);

  function animateMap() {
    animFrame++;
    const W = canvas.width, H = canvas.height;
    drawMap();

    connections.forEach((conn, i) => {
      const progress = ((animFrame * 0.018 + i * 0.65) % 1);
      const x1 = conn.from[0]*W, y1 = conn.from[1]*H;
      const x2 = conn.to[0]*W,   y2 = conn.to[1]*H;

      ctx.beginPath();
      ctx.strokeStyle = 'rgba(99,102,241,0.12)';
      ctx.lineWidth = 0.8;
      ctx.setLineDash([4,4]);
      ctx.moveTo(x1,y1); ctx.lineTo(x2,y2);
      ctx.stroke();
      ctx.setLineDash([]);

      const bx = x1 + (x2-x1)*progress;
      const by = y1 + (y2-y1)*progress;
      const grad = ctx.createRadialGradient(bx,by,0, bx,by,10);
      grad.addColorStop(0, 'rgba(99,102,241,0.9)');
      grad.addColorStop(1, 'rgba(99,102,241,0)');
      ctx.beginPath();
      ctx.arc(bx,by,5,0,Math.PI*2);
      ctx.fillStyle = grad;
      ctx.fill();
    });

    requestAnimationFrame(animateMap);
  }
})();

/* ═══════════════════════════════════════════════════════════════
   MAP CONNECTIONS SVG
   ═══════════════════════════════════════════════════════════════ */
(function initMapConnections() {
  const svg   = qs('#mapConnections');
  const nodes = qs('#mapNodes');
  if (!svg || !nodes) return;

  const io = new IntersectionObserver(entries => {
    if (!entries[0].isIntersecting) return;
    io.disconnect();
    setTimeout(drawConnections, 300);
  }, { threshold: 0.1 });
  io.observe(nodes);

  function drawConnections() {
    const dots = qsa('.map-node', nodes);
    if (!dots.length) return;
    const W = nodes.clientWidth, H = nodes.clientHeight;
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);

    const positions = dots.map(d => ({
      x: parseFloat(d.style.getPropertyValue('--nx')) / 100 * W,
      y: parseFloat(d.style.getPropertyValue('--ny')) / 100 * H,
    }));

    const pairs = [[0,1],[0,2],[0,3],[0,5],[1,2],[1,4],[2,3],[5,6],[5,7],[6,7],[7,8]];
    pairs.forEach(([a,b], i) => {
      const pA = positions[a], pB = positions[b];
      if (!pA || !pB) return;
      const line = document.createElementNS('http://www.w3.org/2000/svg','line');
      line.setAttribute('x1',pA.x); line.setAttribute('y1',pA.y);
      line.setAttribute('x2',pB.x); line.setAttribute('y2',pB.y);
      line.setAttribute('stroke','rgba(99,102,241,0.18)');
      line.setAttribute('stroke-width','1');
      line.setAttribute('stroke-dasharray','4 4');
      svg.appendChild(line);

      const circle = document.createElementNS('http://www.w3.org/2000/svg','circle');
      circle.setAttribute('r','3'); circle.setAttribute('fill','#6366F1');
      const anim = document.createElementNS('http://www.w3.org/2000/svg','animateMotion');
      anim.setAttribute('dur', `${2 + i * 0.3}s`);
      anim.setAttribute('repeatCount','indefinite');
      anim.setAttribute('path',`M ${pA.x} ${pA.y} L ${pB.x} ${pB.y}`);
      anim.setAttribute('begin',`${i * 0.4}s`);
      circle.appendChild(anim);
      svg.appendChild(circle);
    });
  }
})();

/* ═══════════════════════════════════════════════════════════════
   BANK COPY
   ═══════════════════════════════════════════════════════════════ */
window.copyBankNumber = function(num) {
  navigator.clipboard.writeText(num)
    .then(() => showToast('success', `Đã sao chép: ${num}`))
    .catch(() => showToast('error', 'Không thể sao chép!'));
};

/* ═══════════════════════════════════════════════════════════════
   CONTACT FORM
   ═══════════════════════════════════════════════════════════════ */
(function initContact() {
  const form   = qs('#contact-form');
  const submit = qs('#contact-submit');
  if (!form) return;
  on(form, 'submit', async e => {
    e.preventDefault();
    if (submit.disabled) return;
    submit.disabled = true;
    submit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
    try {
      const res  = await fetch('api/contact.php', { method:'POST', body:new FormData(form), credentials:'same-origin' });
      const data = await res.json();
      if (data.success) { showToast('success', data.message||'Tin nhắn đã được gửi!'); form.reset(); }
      else { showToast('error', data.message||'Có lỗi xảy ra!'); }
    } catch { showToast('error', 'Không thể kết nối!'); }
    finally {
      submit.disabled = false;
      submit.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi Tin Nhắn';
    }
  });
})();

/* ═══════════════════════════════════════════════════════════════
   BACK TO TOP
   ═══════════════════════════════════════════════════════════════ */
(function initBTT() {
  const btn = qs('#back-to-top');
  if (!btn) return;
  on(window, 'scroll', () => btn.classList.toggle('visible', window.scrollY > 400), { passive: true });
  on(btn, 'click', () => window.scrollTo({ top:0, behavior:'smooth' }));
})();

/* ═══════════════════════════════════════════════════════════════
   ANNOUNCEMENT BAR
   ═══════════════════════════════════════════════════════════════ */
(function initAnn() {
  const btn = qs('.btn-close-ann');
  const bar = qs('.announcement-bar');
  if (!btn || !bar) return;
  on(btn, 'click', () => {
    bar.style.transition = 'transform 0.3s ease';
    bar.style.transform  = 'translateY(-100%)';
    document.body.classList.remove('has-announcement');
  });
})();

/* ═══════════════════════════════════════════════════════════════
   PWA
   ═══════════════════════════════════════════════════════════════ */
(function initPWA() {
  let deferredPrompt;
  const banner  = qs('#pwa-install-banner');
  const instBtn = qs('#pwa-install-btn');
  const dimBtn  = qs('#pwa-dismiss-btn');
  if (!banner) return;
  if (sessionStorage.getItem('pwa-dismissed')) return;

  on(window, 'beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    setTimeout(() => banner.classList.add('show'), 4000);
  });
  on(instBtn, 'click', async () => {
    banner.classList.remove('show');
    if (deferredPrompt) { deferredPrompt.prompt(); await deferredPrompt.userChoice; deferredPrompt = null; }
  });
  on(dimBtn, 'click', () => { banner.classList.remove('show'); sessionStorage.setItem('pwa-dismissed','1'); });

  if ('serviceWorker' in navigator) {
    on(window, 'load', () => navigator.serviceWorker.register('sw.js').catch(()=>{}));
  }
})();

/* ── Track visit ──────────────────────────────────────────────── */
if (typeof fetch !== 'undefined') {
  const fd = new FormData();
  fd.append('page', location.pathname);
  fetch('api/track.php', { method:'POST', body:fd, credentials:'same-origin' }).catch(()=>{});
}
