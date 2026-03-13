/* ProBot Admin — UI helpers (autosize + color sync + live slider readouts) */
(function ($) {
  /* ---------------- Autosize helpers ---------------- */
  function setWidthToContent(input) {
    if (!input) return;
    const style   = window.getComputedStyle(input);
    const padL    = parseFloat(style.paddingLeft)  || 0;
    const padR    = parseFloat(style.paddingRight) || 0;
    const borderL = parseFloat(style.borderLeftWidth)  || 0;
    const borderR = parseFloat(style.borderRightWidth) || 0;

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
    const min = 40; 

    const max =
      input.classList.contains('pbot-color-text') ? 220 : 
      input.classList.contains('pbot-auto-num')    ? 110 : 
      input.classList.contains('pbot-num-mid')     ? 70  : 
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
    handler(); 
  }

  function initAutosize() {
    document.querySelectorAll('.pbot-auto-text, .pbot-auto-num, .pbot-color-text').forEach(bindAutosize);
  }

  /* ---------------- Live Preview Sync ---------------- */
  function initLivePreview() {
    const stage = document.getElementById('pbot-preview-stage');
    if (!stage) return;

    const bubble  = document.getElementById('pbot-mini-bubble');
    const panel   = document.getElementById('pbot-mini-panel');
    const teaser  = document.getElementById('pbot-mini-teaser');

    const icons = {
      'chat':         '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
      'robot':        '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line></svg>',
      'sparkles':     '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"></path><path d="M5 3l1 1"></path><path d="M19 3l-1 1"></path><path d="M5 21l1-1"></path><path d="M19 21l-1-1"></path></svg>',
      'crystal-ball': '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="8"></circle><path d="M12 18v4"></path><path d="M8 22h8"></path><path d="M12 7v1"></path></svg>',
      'magic-wand':   '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4l2 2"></path><path d="M19 8l2 2"></path><path d="M20 2l1 1"></path><path d="M11 7l9 9-4 4-9-9 4-4z"></path><path d="M3 21l4-4"></path></svg>'
    };

    const inputs = {
      brandColor: document.getElementById('pbot_brand_color'),
      haloColor:  document.getElementById('pbot_halo_color'),
      radius:     document.getElementById('pbot_panel_radius'),
      teaserMsg:  document.getElementById('pbot_teaser_message'),
      teaserBg:   document.getElementById('pbot_toast_bg_color'),
      teaserFg:   document.getElementById('pbot_toast_text_color'),
      pulseOn:    document.getElementById('pbot_pulse_enabled'),
      teaserOn:   document.getElementById('pbot_teaser_enabled'),
      bubbleIcon: document.getElementById('pbot_bubble_icon'),
      bubblePos:  document.querySelectorAll('input[name="pbot_bubble_position"]')
    };

    function update() {
      if (inputs.brandColor) document.documentElement.style.setProperty('--pbot-preview-brand', inputs.brandColor.value);
      if (inputs.haloColor)  document.documentElement.style.setProperty('--pbot-preview-halo',  inputs.haloColor.value);
      if (inputs.radius)     document.documentElement.style.setProperty('--pbot-preview-radius', inputs.radius.value + 'px');
      if (inputs.teaserBg)   document.documentElement.style.setProperty('--pbot-preview-teaser-bg', inputs.teaserBg.value);
      if (inputs.teaserFg)   document.documentElement.style.setProperty('--pbot-preview-teaser-fg', inputs.teaserFg.value);
      
      if (teaser && inputs.teaserMsg) teaser.innerText = inputs.teaserMsg.value || "Hello there!";
      if (teaser && inputs.teaserOn)  teaser.style.display = inputs.teaserOn.checked ? 'block' : 'none';
      
      if (bubble && inputs.bubbleIcon) {
          bubble.innerHTML = icons[inputs.bubbleIcon.value] || '🤖';
      }

      if (bubble && inputs.pulseOn) {
        bubble.classList.toggle('is-pulsing', inputs.pulseOn.checked);
      }

      // Handle Position
      let currentPos = 'right';
      inputs.bubblePos.forEach(rb => { if (el = rb, el.checked) currentPos = el.value; });

      if (currentPos === 'left') {
          bubble.style.right = 'auto'; bubble.style.left = '15px';
          panel.style.right = 'auto';  panel.style.left = '15px';
          teaser.style.right = 'auto'; teaser.style.left = '65px';
      } else {
          bubble.style.left = 'auto';  bubble.style.right = '15px';
          panel.style.left = 'auto';   panel.style.right = '15px';
          teaser.style.left = 'auto';  teaser.style.right = '65px';
      }
    }

    // Bind all inputs
    Object.values(inputs).forEach(el => {
      if (!el) return;
      const ev = el.type === 'checkbox' || el.type === 'radio' ? 'change' : 'input';
      el.addEventListener(ev, update);
    });

    // Toggle panel on bubble click
    if (bubble && panel) {
      bubble.addEventListener('click', () => {
        const isVis = panel.style.opacity !== '0';
        panel.style.opacity = isVis ? '0' : '1';
        panel.style.transform = isVis ? 'translateY(10px) scale(0.95)' : 'translateY(0) scale(1)';
      });
    }

    update(); // Initial sync
  }

  /* ---------------- Test Sound ---------------- */
  function initTestSound() {
    const btn = document.getElementById('pbot_test_sound');
    const select = document.getElementById('pbot_reply_sound');
    if (!btn || !select) return;

    let player = null;
    btn.addEventListener('click', () => {
      const sound = select.value;
      if (sound === 'none') return;

      if (player) {
        player.pause();
        player.currentTime = 0;
      }
      
      const pData = window.pbotData || {};
      let baseUrl = pData.plugin_url || '';
      if (baseUrl && !baseUrl.endsWith('/')) baseUrl += '/';
      
      let audioUrl = baseUrl + 'assets/frontend/' + sound + '.mp3';

      // Fallbacks for missing local assets during recovery
      const fallbacks = {
          'mystical-chime':    'https://assets.mixkit.co/active_storage/sfx/2430/2430-preview.mp3', // Magical Sparkle
          'crystal-ping':      'https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3',
          'soft-notification': 'https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3',
          'digital-pulse':     'https://assets.mixkit.co/active_storage/sfx/2359/2359-preview.mp3'
      };

      if (fallbacks[sound]) {
          audioUrl = fallbacks[sound];
      }

      player = new Audio(audioUrl);
      player.play().catch(e => {
          console.error('Sound play failed', e);
          alert('Asset missing: ' + audioUrl);
      });
    });
  }

  /* ---------------- Color picker <-> text sync ---------------- */
  function syncColor(pickerId, inputId) {
    const p = document.getElementById(pickerId);
    const i = document.getElementById(inputId);
    if (!p || !i) return;
    const isHex = s => /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test((s || '').trim());
    if (isHex(i.value)) p.value = i.value.trim();
    p.addEventListener('input', () => { i.value = p.value; setWidthToContent(i); });
    i.addEventListener('input', () => { if (isHex(i.value)) p.value = i.value.trim(); setWidthToContent(i); });
  }

  function initColorSync() {
    syncColor('pbot_toast_bg_color_picker',   'pbot_toast_bg_color');
    syncColor('pbot_toast_text_color_picker', 'pbot_toast_text_color');
    syncColor('pbot_brand_color_picker', 'pbot_brand_color');
    syncColor('pbot_panel_color_picker', 'pbot_panel_color');
    syncColor('pbot_send_bg_color_picker', 'pbot_send_bg_color');
    syncColor('pbot_send_hover_color_picker', 'pbot_send_hover_color');

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

  /* ---------------- Live slider readouts ---------------- */
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
    const toastOpRange = document.getElementById('pbot_toast_bg_opacity');
    const toastOpVal   = document.querySelector('.pbot-toast-opacity-val');
    if (toastOpRange && toastOpVal) {
      const updateToastOp = () => { toastOpVal.textContent = Number(toastOpRange.value).toFixed(2); };
      toastOpRange.addEventListener('input', updateToastOp);
      toastOpRange.addEventListener('change', updateToastOp);
      updateToastOp();
    }
  }

  /* ---------------- Opt-in autosize for numeric fields ---------------- */
  function initAutoNum() {
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
      el.classList.add('pbot-auto-num');
      if (!el.hasAttribute('inputmode')) {
        el.setAttribute('inputmode', /threshold/i.test(id) ? 'decimal' : 'numeric');
      }
      bindAutosize(el);
    });
  }

  /* ---------------- Boot ---------------- */
  function boot() {
    initColorSync();
    initSliders();
    initAutoNum();
    initAutosize();
    initTestSound();
    initLivePreview();
    autosizeAll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  document.addEventListener('change', e => {
    const t = e.target;
    if (t && (t.classList?.contains('pbot-auto-text') ||
              t.classList?.contains('pbot-auto-num')  ||
              t.classList?.contains('pbot-color-text'))) {
      setWidthToContent(t);
    }
  });

  /**
   * SECRETARY DASHBOARD: Beta 3 HUD & Handshake Logic
   */
  document.addEventListener('DOMContentLoaded', function() {
      // Configuration Scraped from window.ajaxurl (WP Default)
      const ajaxUrl = window.ajaxurl;
      const cfg = window.pbotData || {};
      
      const regBtn      = document.getElementById('pbot-register-btn');
      const saveBtn     = document.getElementById('pbot-save-settings-btn');
      const statusMsg   = document.getElementById('pbot-status-msg');
      const connStatus  = document.getElementById('pbot-connection-status');
      const traitContainer = document.getElementById('pbot-learned-traits');
      
      let isLinkingInProgress = false;

      // Update UI Connection Status
      if (connStatus) {
          const $pulse = $('.pbot-status-pulse');
          const isAnsweringPage = !!document.getElementById('pbot-hud-texts');
          if (cfg.is_online == 1) {
              connStatus.innerText = isAnsweringPage ? "Service Active" : "Mirror Mode Active";
              connStatus.style.color = "#238636";
              $pulse.removeClass('is-offline').addClass('is-active');
          } else {
              connStatus.innerText = isAnsweringPage ? "Service Disabled" : "Offline (Unreachable)";
              connStatus.style.color = "#cf222e";
              $pulse.removeClass('is-active').addClass('is-offline');
          }
      }

      /**
       * SCRAPE LIVE UI FIELDS
       * Dynamically captures Application Passwords and credentials from the UI.
       */
      function getLivePayload() {
          return {
              vps_url: document.getElementById('pbot_vps_url')?.value.trim() || '',
              owner_name: document.getElementById('pbot_owner_name')?.value.trim() || '',
              secret_key: document.getElementById('pbot_secret_key')?.value.trim() || '',
              gh_repo: document.getElementById('pbot_secretary_github_repo')?.value.trim() || '',
              wp_user: document.getElementById('pbot_secretary_wp_user')?.value.trim() || '',
              wp_pass: document.getElementById('pbot_secretary_wp_pass')?.value.trim() || '',
              personality_notes: document.getElementById('pbot_personality_notes')?.value.trim() || '',
          };
      }

      function vpsRequest(endpoint, payload = {}, callback) {
          jQuery.post(ajaxUrl, {
              action: 'pbot_vps_proxy',
              nonce: cfg.nonce,
              endpoint: endpoint,
              source: 'dashboard', // Explicitly identify as Dashboard Terminal
              payload: payload
          }, callback);
      }

      function runHeartbeat() {
          if (isLinkingInProgress) return;
          vpsRequest('/status', {}, function(res) {
              const time = new Date().toLocaleTimeString();
              const $pulse = $('.pbot-status-pulse');
              const isAnsweringPage = !!document.getElementById('pbot-hud-texts');
              const activeLabel = isAnsweringPage ? "Service Active" : "Mirror Mode Active";

              if (res && res.status === 'online') {
                  if (connStatus) {
                      connStatus.innerText = activeLabel + " (" + time + ")";
                      connStatus.style.color = "#238636";
                      $pulse.removeClass('is-offline').addClass('is-active');
                  }
              } else {
                  if (connStatus) {
                      connStatus.innerText = isAnsweringPage ? "Service Disabled" : "Offline";
                      connStatus.style.color = "#cf222e";
                      $pulse.removeClass('is-active').addClass('is-offline');
                  }
              }
          });
      }

      /**
       * AI ANSWERING HUD
       * Pings the VPS /secretary/hud endpoint to fetch recent activity.
       */
      function updateHud() {
          const textsEl    = document.getElementById('pbot-hud-texts');
          const callsEl    = document.getElementById('pbot-hud-calls');
          const activityEl = document.getElementById('pbot-hud-activity');

          // Metrics Elements (Answering Page)
          const leadsEl    = document.getElementById('pbot-stat-leads');
          const bookingsEl = document.getElementById('pbot-stat-bookings');
          const chatsEl    = document.getElementById('pbot-stat-chats');
          const revenueEl  = document.getElementById('pbot-stat-revenue');

          // Traits HUD (Secretary Page)
          const traitsEl  = document.getElementById('pbot-learned-traits-hud');

          if (!textsEl && !callsEl && !activityEl && !traitsEl && !leadsEl) return;

          vpsRequest('/hud', {}, function(res) {
              if (res && res.success) {
                  // Update Metrics
                  if (res.stats) {
                      if (leadsEl)    leadsEl.innerText    = res.stats.new_leads || 0;
                      if (bookingsEl) bookingsEl.innerText = res.stats.live_bookings || 0;
                      if (chatsEl)    chatsEl.innerText    = res.stats.ai_interactions || 0;
                      if (revenueEl)  revenueEl.innerText  = '$' + (res.stats.revenue_est || 0);
                  }

                  // Update Texts
                  if (textsEl && res.texts) {
                      textsEl.innerHTML = res.texts.length ? res.texts.map(t => `<div title="${t.body}">${t.from}: ${t.body.substring(0, 25)}${t.body.length > 25 ? '...' : ''}</div>`).join('') : '<span style="color:#8b949e; font-style:italic;">No recent texts.</span>';
                  }
                  // Update Calls
                  if (callsEl && res.calls) {
                      callsEl.innerHTML = res.calls.length ? res.calls.map(c => `<div>${c.from}: ${c.duration}s (${c.status})</div>`).join('') : '<span style="color:#8b949e; font-style:italic;">No recent calls.</span>';
                  }
                  // Update Activity
                  if (activityEl && res.activity) {
                      activityEl.innerHTML = res.activity.length ? res.activity.map(a => `<div title="${a.msg}">${a.msg.substring(0, 35)}${a.msg.length > 35 ? '...' : ''}</div>`).join('') : '<span style="color:#8b949e; font-style:italic;">System idle.</span>';
                  }
              }
          });

          // Also update Traits if we are on the Secretary Page
          if (traitsEl) {
              vpsRequest('/personality-status', {}, function(res) {
                  if (res && res.traits && res.traits.length) {
                      traitsEl.innerHTML = res.traits.map(t => `<div style="color: #7ee787;">+ "${t}"</div>`).join('') + 
                                         '<div style="color: #8b949e; font-style: italic; margin-top: 10px;">Awaiting new neural patterns...</div>';
                  }
              });
          }
      }

      // Status Toggle Logic
      $('#pbot-owner-status-toggle').on('change', function() {
          const newStatus = $(this).val();
          const $select = $(this);
          
          $select.css('color', (newStatus === 'online') ? '#238636' : '#cf222e');

          $.post(ajaxUrl, {
              action: 'pbot_toggle_status',
              nonce: cfg.nonce,
              status: newStatus
          }, function(res) {
              if (!res.success) alert('Failed to sync status.');
          });
      });

      // Initial checks
      runHeartbeat();
      updateHud();

      /**
       * COMPLIANCE MODAL (Stay Connected)
       * Cedar Point-style lead generation popup.
       */
      window.pbot_show_compliance_modal = function(source = 'Business HUD', service = 'General') {
          if (localStorage.getItem('pbot_lead_captured')) return;

          const modalHtml = `
            <div id="pbot-compliance-overlay">
                <div id="pbot-compliance-modal">
                    <h2>✨ Stay Connected</h2>
                    <p>Get instant Tarot alerts and mystical updates delivered directly to your phone.</p>
                    <div class="pbot-input-wrap">
                        <input type="tel" id="pbot-lead-phone" placeholder="+1 (555) 000-0000">
                    </div>
                    <button id="pbot-lead-submit">Stay Connected</button>
                    <div class="pbot-terms">
                        By clicking, you agree to receive automated tarot alerts from The Alchemist. Msg & data rates may apply. Reply STOP to cancel.
                    </div>
                    <a href="#" class="pbot-close-link" id="pbot-lead-close">No thanks, I'll stay in the dark</a>
                </div>
            </div>
          `;

          $('body').append(modalHtml);

          $('#pbot-lead-submit').on('click', function() {
              const phone = $('#pbot-lead-phone').val().trim();
              if (!phone) return alert('Please enter a valid phone number.');

              $(this).prop('disabled', true).text('Syncing...');

              $.post(ajaxUrl, {
                  action: 'pbot_submit_lead',
                  nonce: cfg.nonce,
                  phone: phone,
                  source: source,
                  service: service
              }, function(res) {
                  if (res.success) {
                      localStorage.setItem('pbot_lead_captured', '1');
                      $('#pbot-compliance-modal').html('<h2>🔮 You are Connected</h2><p>Your path is now synchronized with our alchemical alerts.</p><button onclick="$(\'#pbot-compliance-overlay\').remove()">Continue</button>');
                  } else {
                      alert('Connection interrupted. Please try again.');
                      $('#pbot-lead-submit').prop('disabled', false).text('Stay Connected');
                  }
              });
          });

          $('#pbot-lead-close').on('click', function(e) {
              e.preventDefault();
              $('#pbot-compliance-overlay').remove();
          });
      };

      // Optional: Auto-trigger for testing if on the answering page
      if (document.getElementById('pbot-stat-leads')) {
          // setTimeout(() => window.pbot_show_compliance_modal('Answering Page'), 2000);
      }
      setInterval(runHeartbeat, 30000);
      setInterval(updateHud, 15000); // Update HUD every 15s if on the answering page

      /**
       * TERMINAL CHAT LOGIC (Dynamic Key Handshake)
       */
      const $log = $('#pbot-admin-chat-log');
      const $input = $('#pbot-admin-chat-input');
      const $sendBtn = $('#pbot-admin-chat-send');

      function handleTerminal() {
          const msg = $input.val().trim();
          if (!msg) return;

          $log.append(`<div style="color:#f0f6fc; margin-top:10px;">> ${msg}</div>`);
          $input.val('');

          const livePayload = getLivePayload();

          // BETA 3 Handshake Intercept: Trigger ONLY if the message matches the Master Realm Key exactly
          if (msg === livePayload.secret_key && msg.length > 0) { 
              isLinkingInProgress = true;
              $log.append(`<div style="color:#8b949e;">[SYSTEM]: Intercepting Mirror Key. Scaping site context...</div>`);
              
              jQuery.post(ajaxUrl, { 
                  action: 'pbot_identity_link_handshake', 
                  nonce: cfg.nonce,
                  secret_key: msg, // Scraped from terminal input
                  ...livePayload
              }, function(res) {
                  if (res.success) {
                      $log.append(`<div style="color:#7ee787;">[SYSTEM]: Identity Mirror Active. Realm Key Linked.</div>`);
                      isLinkingInProgress = false;
                      runHeartbeat();
                  } else {
                      const reason = res.data?.message || res.message || 'VPS Rejected Handshake';
                      $log.append(`<div style="color:#d73a49;">[SYSTEM]: Link Failed. ${reason}</div>`);
                      isLinkingInProgress = false;
                  }
              });
              return;
          }

          // Regular chat command
          vpsRequest('/chat', { message: msg }, function(res) {
              // Handle VPS or Proxy Errors
              if (res && (res.success === false || res.error || (res.data && res.data.message))) {
                  const errorMsg = res.error || (res.data && res.data.message) || res.message || 'Unknown VPS Error';
                  $log.append(`<div style="color:#d73a49; margin-bottom:10px;">[ERROR]: ${errorMsg}</div>`);
                  $log.scrollTop($log[0].scrollHeight);
                  return;
              }

              if (res && res.reply) {
                  // Clean out the hidden LEARNED: markers from AI logic
                  const cleanReply = res.reply.replace(/LEARNED:.*$/, '').trim();
                  $log.append(`<div style="color:#8b949e; margin-bottom:10px;">${cleanReply}</div>`);
                  $log.scrollTop($log[0].scrollHeight);
              } else if (res && res.message) {
                  $log.append(`<div style="color:#8b949e; margin-bottom:10px;">${res.message}</div>`);
                  $log.scrollTop($log[0].scrollHeight);
              } else {
                  $log.append(`<div style="color:#d73a49; margin-bottom:10px;">[ERROR]: Invalid response from VPS.</div>`);
                  $log.scrollTop($log[0].scrollHeight);
              }
          }).fail(function(jqXHR) {
              let failMsg = "Proxy connection failed.";
              if (jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message) {
                  failMsg = jqXHR.responseJSON.data.message;
              } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                  failMsg = jqXHR.responseJSON.message;
              }
              $log.append(`<div style="color:#d73a49; margin-bottom:10px;">[SYSTEM ERROR]: ${failMsg}</div>`);
              $log.scrollTop($log[0].scrollHeight);
          });
      }

      if ($sendBtn.length) {
          $sendBtn.on('click', handleTerminal);
          $input.on('keypress', e => { if (e.which === 13) handleTerminal(); });
      }

      /**
       * SETTINGS FORM ACTIONS
       */
      if (saveBtn) {
          saveBtn.addEventListener('click', function() {
              const livePayload = getLivePayload();
              saveBtn.disabled = true;
              statusMsg.innerText = "Syncing local profile...";
              
              jQuery.post(ajaxUrl, {
                  action: 'pbot_save_secretary_settings',
                  nonce: cfg.nonce,
                  ...livePayload
              }, function(res) {
                  statusMsg.innerText = res.success ? "✅ Local Profile Synced" : "❌ Sync Failed";
                  statusMsg.style.color = res.success ? "green" : "red";
                  if(res.success) cfg.secret_key = livePayload.secret_key;
              }).always(() => { saveBtn.disabled = false; });
          });
      }

      if (regBtn) {
          regBtn.addEventListener('click', function() {
              const livePayload = getLivePayload();
              regBtn.disabled = true;
              statusMsg.innerText = "Linking to VPS Brain...";
              isLinkingInProgress = true;

              jQuery.post(ajaxUrl, {
                  action: 'pbot_identity_link_handshake',
                  nonce: cfg.nonce,
                  ...livePayload
              }, function(res) {
                  if (res.success) {
                      statusMsg.innerText = "✅ Handshake Complete";
                      statusMsg.style.color = "green";
                      isLinkingInProgress = false;
                      runHeartbeat();
                  } else {
                      const reason = res.data?.message || res.message || 'Handshake Failed';
                      statusMsg.innerText = "❌ " + reason;
                      statusMsg.style.color = "red";
                      isLinkingInProgress = false;
                  }
              }).always(() => { regBtn.disabled = false; });
          });
      }
  });

})(jQuery);
