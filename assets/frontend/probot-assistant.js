/* ProBot Assistant — JSON-driven + fuzzy matching + color vars from admin */
jQuery(function ($) {
  const cfg           = window.pbot_cfg || {};
  const brandTitle    = cfg.brand_title || 'ProBot Assistant';
  const sideIsLeft    = (cfg.bubble_position === 'left');
  const pulseEnabled  = (typeof cfg.pulse_enabled  === 'boolean') ? cfg.pulse_enabled  : true;
  const teaserEnabled = (typeof cfg.teaser_enabled === 'boolean') ? cfg.teaser_enabled : true;

  /* ---------------- Colors from Settings ---------------- */
  if (cfg.brand_color) document.documentElement.style.setProperty('--brand', cfg.brand_color);
  if (cfg.panel_color) { /* reserved for future */ }

  // Teaser colors
  if (cfg.teaser_bg_color)   document.documentElement.style.setProperty('--teaser-bg', cfg.teaser_bg_color);
  if (cfg.teaser_text_color) document.documentElement.style.setProperty('--teaser-fg', cfg.teaser_text_color);

  /* ---------------- Button border options (preserved) ---------------- */
  // Close/Minimize icon buttons
  if (typeof cfg.btn_border_enabled !== 'undefined') {
    const enabled = Number(cfg.btn_border_enabled) === 1;
    const weight  = Number(cfg.btn_border_weight) || 1;
    const color   = (cfg.btn_border_color || '#d0d0d0');
    document.documentElement.style.setProperty('--icon-btn-border-width', enabled ? (weight + 'px') : '0px');
    document.documentElement.style.setProperty('--icon-btn-border-color', color);
  }
  // Send button
  if (typeof cfg.send_border_enabled !== 'undefined') {
    const enabled = Number(cfg.send_border_enabled) === 1;
    const weight  = Number(cfg.send_border_weight) || 1;
    const color   = (cfg.send_border_color || '#d0d0d0');
    document.documentElement.style.setProperty('--send-border-width', enabled ? (weight + 'px') : '0px');
    document.documentElement.style.setProperty('--send-border-color', color);
  }

  /* ---------------- Color helpers for halo ---------------- */
  function parseColor(str){
    if (!str || typeof str !== 'string') return null;
    const s = str.trim();

    // rgba()/rgb()
    let m = s.match(/^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)(?:\s*,\s*([\d.]+))?\s*\)$/i);
    if (m) {
      return {
        r: Math.max(0, Math.min(255, parseFloat(m[1]))),
        g: Math.max(0, Math.min(255, parseFloat(m[2]))),
        b: Math.max(0, Math.min(255, parseFloat(m[3]))),
        a: m[4] != null ? Math.max(0, Math.min(1, parseFloat(m[4]))) : 1
      };
    }

    // #rrggbb or #rgb
    m = s.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
    if (m) {
      let hex = m[1];
      if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
      const r = parseInt(hex.slice(0,2),16);
      const g = parseInt(hex.slice(2,4),16);
      const b = parseInt(hex.slice(4,6),16);
      return { r, g, b, a: 1 };
    }
    return null;
  }
  function rgbaString(c){ return `rgba(${c.r|0},${c.g|0},${c.b|0},${Math.max(0,Math.min(1,c.a)).toFixed(3)})`; }

  // from PHP we cast to float, but be defensive
  function num(v, d){ const n = typeof v === 'number' ? v : parseFloat(v); return Number.isFinite(n) ? n : d; }

  /* ---------------- Intensities (RESTORED “confirmed working” mapping) ---------------- */
  const haloIntensity  = num(cfg.halo_intensity, 0.7);   // 0..1  (faint → bold)
  const pulseIntensity = num(cfg.pulse_intensity, 1.0);  // 0.5..2.0 (weak/slow → strong/fast)

  // Apply halo + pulse AFTER the bubble exists; set vars on :root AND on the button node
  function applyHaloAndPulse($btn){
    // --- HALO: opacity + spread scale with halo slider (faint → bold)
    const base = parseColor(cfg.halo_color || 'rgba(255,255,255,.7)') || {r:255,g:255,b:255,a:.7};
    // Non-linear ramp for better feel: faint region is more granular
    const haloA = Math.max(0.02, Math.min(1, Math.pow(haloIntensity, 0.65)));  // 0..1
    const spreadPx = Math.round(12 + haloIntensity * 44);                       // 12..56

    const haloRGBA = rgbaString({ r: base.r, g: base.g, b: base.b, a: haloA });

    document.documentElement.style.setProperty('--halo-color', haloRGBA);
    document.documentElement.style.setProperty('--pulse-spread', spreadPx + 'px');

    if ($btn && $btn[0]) {
      $btn[0].style.setProperty('--halo-color', haloRGBA);
      $btn[0].style.setProperty('--pulse-spread', spreadPx + 'px');
    }

    // --- PULSE: bubble size + speed scale with pulse slider (more dramatic at high end)
    const clampedPulse = Math.max(0.5, Math.min(2.0, pulseIntensity));
    // Scale range ~0.012 .. ~0.065 (very obvious at max)
    const scale  = 0.012 + (clampedPulse - 0.5) * (0.065 - 0.012) / 1.5;
    // Period range 2.6s .. 0.7s (faster at higher intensity)
    const period = 2.6 - (clampedPulse - 0.5) * (2.6 - 0.7) / 1.5;

    document.documentElement.style.setProperty('--pulse-scale',  scale.toFixed(3));
    document.documentElement.style.setProperty('--pulse-period', period.toFixed(2) + 's');

    if ($btn && $btn[0]) {
      $btn[0].style.setProperty('--pulse-scale',  scale.toFixed(3));
      $btn[0].style.setProperty('--pulse-period', period.toFixed(2) + 's');
    }
  }

  /* ---------------- BODY SCROLL LOCK ---------------- */
  let scrollY = 0;
  function lockBody() {
    scrollY = window.scrollY || document.documentElement.scrollTop || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.classList.add('probot-locked');
  }
  function unlockBody() {
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.classList.remove('probot-locked');
    window.scrollTo(0, scrollY);
  }

  /* ---------------- DOM ---------------- */
  const sideAttr = sideIsLeft ? 'left' : 'right';
  const sideCSS  = sideIsLeft ? 'left:20px; right:auto;' : 'right:20px; left:auto;';

  $('body').append(`
    <div id="probot-chat-overlay" aria-hidden="true" style="display:none;">
      <div id="probot-chat-mask"></div>
      <div id="probot-chat-panel" role="dialog" aria-label="${brandTitle}">
        <div id="probot-topbar">
          <div class="probot-topbar-title">
            <span class="bot-avatar" aria-hidden="true">🤖</span>${brandTitle}
          </div>
          <div id="probot-actions" class="probot-actions">
            <button id="probot-minimize" class="probot-icon-btn" aria-label="Minimize" title="Minimize">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14"/></svg>
              <span class="icon-fallback" aria-hidden="true">–</span>
            </button>
            <button id="probot-close" class="probot-icon-btn" aria-label="Close" title="Close">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
              <span class="icon-fallback" aria-hidden="true">×</span>
            </button>
          </div>
        </div>

        <div id="probot-chat-scroll"><div id="probot-chat-body"></div></div>

        <div id="probot-inputbar">
          <form id="probot-form" autocomplete="off">
            <input type="text" id="probot-chat-input" placeholder="Type here..." autocomplete="off" />
            <button id="probot-send" type="submit" aria-label="Send" title="Send">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 12h14M14 7l5 5-5 5"/></svg>
            </button>
          </form>
        </div>
      </div>
      <audio id="probot-notify-sound">
        <source src="https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg" type="audio/ogg">
        <source src="https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.mp3" type="audio/mpeg">
      </audio>
    </div>

    <div id="probot-chat-button" ${sideAttr} role="button" tabindex="0" aria-label="Open chat" title="Open chat" style="${sideCSS}">
      <svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false">
        <path d="M4 5h16v10H7l-3 3V5z" fill="currentColor"></path>
      </svg>
    </div>
    <div id="probot-teaser" style="display:none;"><div class="probot-teaser-inner">${cfg.teaser_message ? String(cfg.teaser_message) : `Hi there! I’m your new ${brandTitle}.`}</div></div>
  `);

  // Cache
  const $overlay = $('#probot-chat-overlay');
  const $scroll  = $('#probot-chat-scroll');
  const $body    = $('#probot-chat-body');
  const $input   = $('#probot-chat-input');
  const $button  = $('#probot-chat-button');
  const $teaser  = $('#probot-teaser');
  const $sendBtn = $('#probot-send');

  // Apply side attrs for teaser anchoring
  $button.attr(sideAttr, '');
  $teaser.attr(sideAttr, '');

  // Apply halo & pulse now that the button exists (root + element-level vars)
  applyHaloAndPulse($button);

  /* ---------------- NEW: desktop anchor fallback if :has() unsupported ---------------- */
  (function desktopAnchorFallback(){
    try {
      const supportsHas = window.CSS && CSS.supports && CSS.supports('selector(:has(*))');
      if (supportsHas) return; // native CSS handles it
    } catch(e) { /* continue with fallback */ }

    const setSide = () => {
      document.body.classList.toggle('pbot-left',  $button.is('[left]'));
      document.body.classList.toggle('pbot-right', $button.is('[right]'));
    };
    setSide();
    // Watch for attribute flips (future-proofing)
    if ($button[0]) {
      new MutationObserver(setSide).observe($button[0], { attributes:true, attributeFilter:['left','right'] });
    }
  })();

  /* ---------------- pin header actions seatbelt (no border clobber) ---------------- */
  function pinHeaderActions(){
    const topbar  = document.getElementById('probot-topbar');
    const actions = document.getElementById('probot-actions');
    if (!topbar || !actions) return;
    topbar.style.setProperty('position','relative','important');
    actions.style.setProperty('position','absolute','important');
    actions.style.setProperty('right','14px','important');
    actions.style.setProperty('bottom','6px','important');
    actions.style.setProperty('display','flex','important');
    actions.style.setProperty('gap','10px','important');
    actions.querySelectorAll('.probot-icon-btn').forEach(b=>{
      b.style.setProperty('margin','0','important');
      // DO NOT zero borders here; CSS vars control visibility
    });
  }
  pinHeaderActions();
  $(window).on('resize orientationchange', pinHeaderActions);

/* ---------------- SOUND (single ding manager) ---------------- */
  const audioEl = document.getElementById('probot-notify-sound');

  // Session flags (kept if already set by other code)
  window.__pbotIsClosed    = !!window.__pbotIsClosed;
  window.__pbotIsMinimized = !!window.__pbotIsMinimized;

  // Reopen window (for greeting clamp when reopening from minimize)
  let reopenAt = 0;
  let reopenOnePlayed = false;
  const REOPEN_WINDOW = 1800; // ms

  // Hard dedupe for rapid plays
  let lastDingAt = 0;
  function recently(ms){ return (Date.now() - lastDingAt) < ms; }
  function markDing(){ lastDingAt = Date.now(); }

  // Only our manager is allowed to actually play()
  if (audioEl && !audioEl.__pbotPlayGuard) {
    const origPlay = audioEl.play.bind(audioEl);
    audioEl.play = function(){
      // Backstop: if someone calls audio.play() directly, still obey our rules
      if (window.__pbotIsClosed) return Promise.resolve();
      if (recently(500))         return Promise.resolve();
      markDing();
      try { return origPlay(); } catch { return Promise.resolve(); }
    };
    audioEl.__pbotPlayGuard = true;
  }

  // Controlled ding() – use everywhere instead of direct audio.play()
  function ding(){
    if (!cfg.sound_enabled) return;
    if (!audioEl) return;

    // allow dings while minimized, but never when CLOSED
    if (window.__pbotIsClosed) return;

    // Clamp greeting right after reopen to one ding total
    const insideReopen = (Date.now() - reopenAt) <= REOPEN_WINDOW;
    if (insideReopen) {
      if (reopenOnePlayed) return; // already spent the greet ding
      reopenOnePlayed = true;
    }

    if (recently(500)) return; // hard dedupe
    audioEl.currentTime = 0;
    audioEl.play().catch(()=>{});
  }

  // Unlock sound on any first interaction (kept)
  let audioUnlocked = false;
  function unlockAudio() {
    if (audioUnlocked) return;
    const a = audioEl;
    if (!a) return;
    a.volume = 1;
    a.play().then(()=>{ a.pause(); a.currentTime = 0; audioUnlocked = true; }).catch(()=>{});
  }
  $button.on('click', unlockAudio);
  $(document).on('click touchstart keydown', function one(){ unlockAudio(); $(document).off('click touchstart keydown', one); });

  /* ---------------- UTILITIES ---------------- */
  function scrollToBottom(){ $scroll.scrollTop($scroll[0].scrollHeight); }
  function ensureScrollable(){
    const el = $scroll[0]; if(!el) return;
    $scroll.css('overflow', el.scrollHeight > el.clientHeight + 2 ? 'auto' : 'hidden');
  }
  function measureIB(){
    const ib = $('#probot-inputbar').outerHeight() || 62;
    document.documentElement.style.setProperty('--ib', ib + 'px');
  }
  function minDelay(ms){ return new Promise(res => setTimeout(res, ms)); }

  // Dynamic typing delay based on reply length
  function typingDelayFor(text, options){
    const t = (text || '').toString();
    const len = t.length;
    const words = t.trim().split(/\s+/).filter(Boolean).length;
    const base    = options?.base    ?? 500;
    const perChar = options?.perChar ?? 38;
    const perWord = options?.perWord ?? 35;
    const punct   = /[.,;:!?]/.test(t) ? 180 : 0;
    const min = options?.min ?? 700;
    const max = options?.max ?? 12000;
    const raw = base + len*perChar + words*perWord + punct;
    return Math.max(min, Math.min(max, raw));
  }

  /* ---------------- Keyboard overlap ---------------- */
  function kbOverlap(){
    if (!window.visualViewport) return 0;
    const vv = window.visualViewport;
    return Math.max(0, (window.innerHeight - (vv.height + (vv.offsetTop||0))));
  }
  function onVV(){
    document.documentElement.style.setProperty('--kb', Math.round(kbOverlap()) + 'px');
    measureIB(); ensureScrollable(); requestAnimationFrame(scrollToBottom);
  }
  if (window.visualViewport){
    window.visualViewport.addEventListener('resize', onVV);
    window.visualViewport.addEventListener('scroll', onVV);
  }
  $(window).on('orientationchange', onVV);
  document.addEventListener('focusin', e => { if(e.target === $input[0]) onVV(); });
  document.addEventListener('focusout', e => { if(e.target === $input[0]) {
    document.documentElement.style.setProperty('--kb','0px'); ensureScrollable();
  }});

  /* ---------------- INTENTS ---------------- */
  let intents = null;
  function normalizeIntents(data){
    const out = { help: '', intents: [] };
    if (Array.isArray(data)) {
      out.intents = data;
    } else if (data && typeof data === 'object') {
      out.help    = typeof data.help === 'string' ? data.help : '';
      out.intents = Array.isArray(data.intents) ? data.intents : [];
    }
    out.intents = out.intents.map(it => ({
      triggers: Array.isArray(it.triggers) ? it.triggers : [],
      reply: typeof it.response === 'string' ? it.response
           : (typeof it.reply === 'string' ? it.reply : '')
    }));
    return out;
  }
  function loadIntents(force=false){
    return new Promise((resolve) => {
      if (intents && !force) return resolve(intents);
      const url = (cfg.intents_url || '').trim();
      if (!url){ intents = null; return resolve(null); }
      $.getJSON(url + (url.includes('?') ? '&' : '?') + 'v=' + Date.now())
        .done(data => { intents = normalizeIntents(data); resolve(intents); })
        .fail(()   => { intents = null; resolve(null); });
    });
  }

  // Fuzzy helpers
  function norm(s){ return (s||'').toLowerCase().replace(/[^a-z0-9\s]/g,' ').replace(/\s+/g,' ').trim(); }
  function tokens(s){ return norm(s).split(' ').filter(Boolean); }
  function jaccard(a, b){
    if (!a.length || !b.length) return 0;
    const A = new Set(a), B = new Set(b);
    let inter = 0;
    for (const t of A) if (B.has(t)) inter++;
    return inter / (A.size + B.size - inter);
  }
  function levDist(a, b){
    const m=a.length, n=b.length;
    if(!m) return n; if(!n) return m;
    const dp = Array.from({length:m+1},()=>Array(n+1));
    for(let i=0;i<=m;i++) dp[i][0]=i;
    for(let j=0;j<=n;j++) dp[0][j]=j;
    for(let i=1;i<=m;i++){
      for(let j=1;j<=n;j++){
        const cost = a[i-1]===b[j-1]?0:1;
        dp[i][j] = Math.min(
          dp[i-1][j]+1,
          dp[i][j-1]+1,
          dp[i-1][j-1]+cost
        );
      }
    }
    return dp[m][n];
  }
  function levRatio(a, b){
    const d = levDist(a, b);
    const M = Math.max(a.length, b.length) || 1;
    return 1 - (d / M);
  }
  function bestFuzzyMatch(q, intentsList){
    const tq = tokens(q);
    let best = {score:0, reply:null};
    for (const it of intentsList){
      for (const trg of (it.triggers||[])){
        if (!trg) continue;
        const t = String(trg);
        if (q.includes(t.toLowerCase())){
          return {score: 1, reply: it.reply || null};
        }
        const score = Math.max(
          jaccard(tq, tokens(t)),
          levRatio(norm(q), norm(t))
        );
        if (score > best.score){
          best = {score, reply: it.reply || null};
        }
      }
    }
    return best;
  }
  function matchIntent(q){
    if (!intents) return null;
    const query = (q||'').toLowerCase().trim();
    for (const it of intents.intents){
      for (const t of (it.triggers||[])){
        if (t && query.includes(String(t).toLowerCase())){
          return it.reply || null;
        }
      }
    }
    if (query.length >= 3){
      const {score, reply} = bestFuzzyMatch(query, intents.intents);
      if (score >= (typeof cfg.match_threshold === 'number' ? cfg.match_threshold : 0.52)) return reply;
    }
    return null;
  }
  loadIntents(true);

  /* ---------------- Teaser placement ---------------- */
  function cssRaw(name){ return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
  function cssPx(name){ const n = parseFloat(cssRaw(name)); return Number.isFinite(n) ? n : 0; }

  function placeTeaser(){
    if (!$button[0] || !$teaser[0]) return;

    let wasHidden = false;
    if ($teaser.css('display') === 'none'){
      wasHidden = true;
      $teaser.css({ display:'block', visibility:'hidden' });
    }

    const r  = $button[0].getBoundingClientRect();
    const ax = sideIsLeft ? r.right : r.left;
    const ay = r.top + r.height/2;

    const gapX = cssPx('--teaser-gap-x');
    const gapY = cssPx('--teaser-gap-y');

    const leftOverrideRaw = cssRaw('--teaser-left-override');
    const topOverrideRaw  = cssRaw('--teaser-top-override');
    const leftOverride    = parseFloat(leftOverrideRaw);
    const topOverride     = parseFloat(topOverrideRaw);
    const hasLeftOverride = leftOverrideRaw.endsWith('px') && Number.isFinite(leftOverride);
    const hasTopOverride  = topOverrideRaw.endsWith('px')  && Number.isFinite(topOverride);

    const el = $teaser[0];
    const tw = el.offsetWidth;
    const th = el.offsetHeight;

    $teaser.removeAttr('left right').attr(sideIsLeft ? 'left' : 'right', '');

    let leftPx, topPx;
    if (hasLeftOverride) {
      leftPx = leftOverride;
    } else {
      leftPx = $teaser.is('[right]') ? (ax - tw - gapX) : (ax + gapX);
    }
    topPx = hasTopOverride ? topOverride : (ay - th / 2 + gapY);

    el.style.setProperty('left', leftPx + 'px', 'important');
    el.style.setProperty('top',  topPx  + 'px', 'important');
    el.style.setProperty('transform', 'none', 'important');

    if (wasHidden) $teaser.css({ visibility:'', display:'none' });
  }
  function showTeaser(){
    if (!teaserEnabled || $overlay.is(':visible')) return;
    const wasHidden = $teaser.css('display') === 'none';
    if (wasHidden) $teaser.css({ display:'block', visibility:'hidden' });
    placeTeaser();
    if (wasHidden) $teaser.css({ visibility:'' });
    $teaser.stop(true,true).addClass('in').attr('aria-live','polite');

    const dur = Math.max(1000, Number(cfg.teaser_duration_ms || 4500));
    setTimeout(()=> $teaser.addClass('out'), Math.max(0, dur - 1000));
    setTimeout(()=> $teaser.removeClass('in out').hide(), dur + 800);
  }

/* ---------------- OPEN/CLOSE (no greet on reopen; sound rules) ---------------- */
  async function maybeShowOpenGreeting(){
    const $typing = $('<div class="msg bot typing"><div class="avatar" aria-hidden="true">🤖</div><div class="bubble"><span class="typing-dots"><i></i><i></i><i></i></span></div></div>');
    $body.append($typing); scrollToBottom();

    await loadIntents(false);
    const greeting = matchIntent('__open__');

    if (greeting) {
      const scaled = typingDelayFor(greeting, { min: (typeof cfg.greeting_delay_ms === 'number' ? cfg.greeting_delay_ms : 2200), max: 15000, base: 600, perChar: 40, perWord: 35 });
      await minDelay(scaled);
      $typing.removeClass('typing').find('.bubble').html(greeting);
      scrollToBottom(); ensureScrollable();
      ding(); // one ding at greeting finalize
    } else {
      await minDelay(Math.max(0, (typeof cfg.greeting_delay_ms === 'number' ? cfg.greeting_delay_ms : 2200)));
      $typing.remove();
      ensureScrollable();
    }
  }

  function stopAnySound(){ try { audioEl && (audioEl.pause(), audioEl.currentTime = 0); } catch {} }

  function openChat(){
    // Opening from bubble = not closed/minimized; arm "one ding" window for greeting
    window.__pbotIsClosed = false;
    window.__pbotIsMinimized = false;
    reopenAt = Date.now();
    reopenOnePlayed = false;

    $overlay.fadeIn(120, async () => {
      $overlay.attr('aria-hidden','false'); lockBody(); onVV();
      $scroll.css('overflow','hidden');

      // IMPORTANT: greet ONLY if it's a fresh session (no prior messages)
      const fresh = ($body.children().length === 0);
      if (fresh) await maybeShowOpenGreeting();
    });
    $button.fadeOut(100).removeClass('pbot-pulse'); $teaser.hide();
  }

  async function minimizeChat(){
    window.__pbotIsMinimized = true;   // session alive, can still ding
    stopAnySound();                    // but stop any in-flight sound now
    await loadIntents(true);           // refresh for next open
    $overlay.fadeOut(120, () => {
      $overlay.attr('aria-hidden','true'); unlockBody();
      $scroll.css('overflow','hidden');
    });
    $button.fadeIn(120).addClass('pbot-pulse');
  }

  async function closeChat(){
    // Closed = end session: no more dings until reopened
    window.__pbotIsClosed = true;
    window.__pbotIsMinimized = false;
    stopAnySound();
    $body.empty();
    await minimizeChat();
  }

  $button.on('click', openChat);
  $button.on('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openChat(); } });
  $('#probot-minimize').off('click').on('click', () => { minimizeChat(); });
  $('#probot-close').off('click').on('click',    () => { closeChat(); });
  $('#probot-chat-mask').off('click').on('click', () => { minimizeChat(); });

/* ---------------- SUBMIT → JSON ONLY (exact → fuzzy) ---------------- */
  const $form = $('#probot-form');
  let lastSubmitAt = 0;

  $input.on('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); $form.trigger('submit'); }
  });

  $sendBtn.on('mousedown touchstart', function(e){
    e.preventDefault(); $form.trigger('submit');
  });

  $form.off('submit').on('submit', async function(e){
    e.preventDefault();

    // mobile double-tap / mousedown+touchstart guard
    const now = Date.now();
    if (now - lastSubmitAt < 350) return;
    lastSubmitAt = now;

    unlockAudio();

    const msg = $input.val().trim(); if (!msg) return;

    const $m = $('<div class="msg me"><div class="bubble"></div></div>');
    $m.find('.bubble').text(msg); $body.append($m);
    $input.val(''); scrollToBottom(); ensureScrollable();

    await loadIntents(false);
    const jsonReply = matchIntent(msg);
    if (!jsonReply) return;

    // ensure only ONE typing bubble exists
    $body.find('.msg.bot.typing').remove();

    const $typing = $('<div class="msg bot typing"><div class="avatar" aria-hidden="true">🤖</div><div class="bubble"><span class="typing-dots"><i></i><i></i><i></i></span></div></div>');
    $body.append($typing); scrollToBottom();

    const waitMs = typingDelayFor(jsonReply, { min: 700, max: 12000, base: 500, perChar: 38, perWord: 35 });
    setTimeout(()=>{
      $typing.removeClass('typing').find('.bubble').html(jsonReply);
      scrollToBottom(); ensureScrollable();
      ding(); // one ding per finalized reply
    }, waitMs);
  });

  /* ---------------- INIT ---------------- */
  function kbOverlap(){
    if (!window.visualViewport) return 0;
    const vv = window.visualViewport;
    return Math.max(0, (window.innerHeight - (vv.height + (vv.offsetTop||0))));
  }
  function onVV(){
    document.documentElement.style.setProperty('--kb', Math.round(kbOverlap()) + 'px');
    measureIB(); ensureScrollable(); requestAnimationFrame(scrollToBottom);
  }
  measureIB(); onVV();

  if (pulseEnabled) $button.addClass('pbot-pulse');
  setTimeout(showTeaser, 900);

  $(window).on('resize scroll orientationchange', function(){
    if ($teaser.is(':visible')) placeTeaser();
  });
  if (window.visualViewport){
    window.visualViewport.addEventListener('resize', ()=>{ if ($teaser.is(':visible')) placeTeaser(); });
    window.visualViewport.addEventListener('scroll',  ()=>{ if ($teaser.is(':visible')) placeTeaser(); });
  }
  ensureScrollable();
});