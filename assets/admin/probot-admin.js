/* ProBot Admin — UI helpers (autosize + color sync + live slider readouts) */
(function () {
  /* ---------------- Autosize helpers ---------------- */
  function setWidthToContent(input) {
    if (!input) return;
    const style   = window.getComputedStyle(input);
    const padL    = parseFloat(style.paddingLeft)  || 0;
    const padR    = parseFloat(style.paddingRight) || 0;
    const borderL = parseFloat(style.borderLeftWidth)  || 0;
    const borderR = parseFloat(style.borderRightWidth) || 0;

    // Hidden mirror span for measuring content width
    let mirror = input._pbotMirror;
    if (!mirror) {
      mirror = document.createElement('span');
      input._pbotMirror = mirror;
      mirror.style.position = 'absolute';
      mirror.style.visibility = 'hidden';
      mirror.style.whiteSpace = 'pre';
      mirror.style.font = style.font;
      mirror.style.letterSpacing = style.letterSpacing;
      mirror.style.padding = '0';
      mirror.style.border = '0';
      mirror.style.margin = '0';
      document.body.appendChild(mirror);
    }

    const val = input.value || input.placeholder || '';
    mirror.textContent = val;

    const content = Math.ceil(mirror.getBoundingClientRect().width);
    const min = 40; // base minimum px

    // Per-class caps (keep things compact)
    const max =
      input.classList.contains('pbot-color-text') ? 220 :   // color hex/rgb fields
      input.classList.contains('pbot-auto-num')    ? 110 :   // autosized numeric text fields
      input.classList.contains('pbot-num-mid')     ? 70  :   // legacy classes
      input.classList.contains('pbot-num-short')   ? 55  :
      420;

    const target = Math.max(min, Math.min(content + padL + padR + borderL + borderR + 6, max));
    input.style.width = target + 'px';
  }

  function autosizeAll(scope) {
    const root = scope || document;
    root.querySelectorAll('.pbot-auto-text, .pbot-auto-num, .pbot-color-text').forEach(setWidthToContent);
  }

  function bindAutosize(input) {
    if (!input) return;
    const handler = () => setWidthToContent(input);
    ['input', 'change', 'keyup', 'blur'].forEach(ev => input.addEventListener(ev, handler));
    handler(); // initial measure
  }

  function initAutosize() {
    document.querySelectorAll('.pbot-auto-text, .pbot-auto-num, .pbot-color-text').forEach(bindAutosize);
  }

  /* ---------------- Color picker <-> text sync ---------------- */
  function syncColor(pickerId, inputId) {
    const p = document.getElementById(pickerId);
    const i = document.getElementById(inputId);
    if (!p || !i) return;
    const isHex = s => /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test((s || '').trim());

    if (isHex(i.value)) p.value = i.value.trim(); // seed

    p.addEventListener('input', () => { i.value = p.value; setWidthToContent(i); });
    i.addEventListener('input', () => { if (isHex(i.value)) p.value = i.value.trim(); setWidthToContent(i); });
  }

  function initColorSync() {
    // Toast
    syncColor('pbot_toast_bg_color_picker',   'pbot_toast_bg_color');
    syncColor('pbot_toast_text_color_picker', 'pbot_toast_text_color');
    // Brand & panel
    syncColor('pbot_brand_color_picker', 'pbot_brand_color');
    syncColor('pbot_panel_color_picker', 'pbot_panel_color');

    // Halo: picker mirrors only when hex; text can be rgba()
    (function () {
      const p = document.getElementById('pbot_halo_color_picker');
      const i = document.getElementById('pbot_halo_color');
      if (!p || !i) return;
      const isHex = s => /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test((s || '').trim());
      if (isHex(i.value)) p.value = i.value.trim();
      p.addEventListener('input', () => { i.value = p.value; setWidthToContent(i); });
      i.addEventListener('input', () => { if (isHex(i.value)) p.value = i.value.trim(); setWidthToContent(i); });
    })();
  }

  /* ---------------- Live slider readouts (Halo & Pulse) ---------------- */
  function initSliders() {
    const haloRange = document.getElementById('pbot_halo_intensity');
    const haloVal   = document.querySelector('.pbot-halo-val');
    if (haloRange && haloVal) {
      const updateHalo = () => { haloVal.textContent = Number(haloRange.value).toFixed(2); };
      haloRange.addEventListener('input', updateHalo);
      haloRange.addEventListener('change', updateHalo);
      updateHalo();
    }

    const pulseRange = document.getElementById('pbot_pulse_intensity');
    const pulseVal   = document.querySelector('.pbot-pulse-val');
    if (pulseRange && pulseVal) {
      const updatePulse = () => { pulseVal.textContent = Number(pulseRange.value).toFixed(2); };
      pulseRange.addEventListener('input', updatePulse);
      pulseRange.addEventListener('change', updatePulse);
      updatePulse();
    }
  }

  /* ---------------- Opt-in autosize for numeric fields ---------------- */
  function initAutoNum() {
    // Add autosize behavior to specific numeric inputs (keeps native height)
    const ids = [
      'pbot_btn_border_weight',
      'pbot_send_border_weight',
      'pbot_teaser_show_count',
      'pbot_match_threshold',
      'pbot_greeting_delay_ms',
      'pbot_teaser_duration_ms'
    ];
    ids.forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.classList.add('pbot-auto-num');           // CSS makes width auto + compact
      // Keep type="number" for native spinners; just hint keyboards on mobile
      if (!el.hasAttribute('inputmode')) {
        el.setAttribute('inputmode', /threshold/i.test(id) ? 'decimal' : 'numeric');
      }
      bindAutosize(el);                             // mirror-measured width
    });
  }

  /* ---------------- Boot ---------------- */
  function boot() {
    initColorSync();
    initSliders();
    initAutoNum();
    initAutosize();
    autosizeAll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  // Defensive: re-measure on change
  document.addEventListener('change', e => {
    const t = e.target;
    if (t && (t.classList?.contains('pbot-auto-text') ||
              t.classList?.contains('pbot-auto-num')  ||
              t.classList?.contains('pbot-color-text'))) {
      setWidthToContent(t);
    }
  });
})();