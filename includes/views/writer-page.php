<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap pbot-wrap pbot-writer">
  <h1>ProBot Assistant — Article Writer</h1>

  <?php 
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
      echo '<div class="notice notice-success is-dismissible"><p><strong>Success:</strong> Article Writer route settings saved.</p></div>';
    }
    if (!empty($notices)) foreach ($notices as $n) echo $n; 
  ?>

  <div class="pbot-settings-layout">
    
    <!-- LEFT COLUMN: Content Briefing & Specs -->
    <div class="pbot-settings-main">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="pbot-writer-form">
        <?php wp_nonce_field('pbot_generate_article_nonce','pbot_nonce'); ?>
        <input type="hidden" name="action" value="pbot_generate_article" />
        
        <!-- Synchronized hidden route field -->
        <input type="hidden" name="pbot_billing_route" id="pbot_billing_route_hidden" value="<?php echo esc_attr($billing_route); ?>" />

        <div class="pbot-card">
          <div class="pbot-card-header">
            <h2>📝 Content Strategy &amp; Specs</h2>
            <p class="pbot-muted">Define the focus and technical specifications for your article.</p>
          </div>

          <!-- Strategy Section -->
          <div class="pbot-row pbot-row-stacked">
            <label for="pbot_post_title"><strong>Title</strong> <span class="pbot-muted">(optional — leave blank for AI)</span></label>
            <input type="text" id="pbot_post_title" name="pbot_post_title" class="regular-text" style="width:100%;" placeholder="AI will generate a compelling title if blank">
          </div>

          <div class="pbot-row pbot-row-stacked" style="margin-top:20px;">
            <label for="pbot_brief"><strong>Brief / Context</strong></label>
            <textarea id="pbot_brief" name="pbot_brief" class="pbot-json-view pbot-textarea-lg" style="height:180px;" placeholder="Target audience, angle, must-include points, internal links, etc." required></textarea>
          </div>

          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px; align-items: start;">
            <div class="pbot-row pbot-row-stacked" style="margin:0;">
              <label for="pbot_keywords"><strong>Primary keywords</strong></label>
              <input type="text" id="pbot_keywords" name="pbot_keywords" class="regular-text" style="width:100%;" placeholder="comma-separated">
            </div>
            <div class="pbot-row pbot-row-stacked" style="margin:0;">
              <label for="pbot_tone"><strong>Tone</strong></label>
              <select id="pbot_tone" name="pbot_tone" style="width:100%; height: 30px;">
                <option value="helpful, friendly, professional" selected>Helpful, Friendly, Professional</option>
                <option value="formal and authoritative">Formal &amp; Authoritative</option>
                <option value="conversational and engaging">Conversational &amp; Engaging</option>
                <option value="technical and precise">Technical &amp; Precise</option>
              </select>
            </div>
          </div>

          <div class="pbot-row pbot-row-stacked" style="margin-top:20px;">
            <label for="pbot_headings"><strong>Required headings</strong></label>
            <textarea id="pbot_headings" name="pbot_headings" class="pbot-json-view pbot-textarea-md" placeholder="H2: First Heading&#10;H2: Second Heading..."></textarea>
          </div>

          <hr style="margin:25px 0; border:0; border-top:1px solid #eee;" />

          <!-- Specs Section -->
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; align-items: start;">
            <div>
              <div class="pbot-row pbot-row-stacked" style="margin-bottom:15px;">
                <label for="pbot_wordcount"><strong>Target length</strong></label>
                <div style="display:flex; align-items:center; gap:8px;">
                  <input type="number" id="pbot_wordcount" name="pbot_wordcount" min="300" max="6000" step="50" value="<?php echo (int)$max_words; ?>" style="width:90px;">
                  <span class="pbot-muted">words</span>
                </div>
                <div id="pbot_wordcap_hint" class="pbot-muted" style="font-size:11px; margin-top:4px;"></div>
              </div>

              <div class="pbot-row pbot-row-stacked" style="margin-bottom:15px;">
                <label for="pbot_aw_category"><strong>Category</strong></label>
                <?php
                  wp_dropdown_categories([
                    'show_option_all' => '— Let me pick —',
                    'hide_empty'      => 0,
                    'name'            => 'pbot_aw_category',
                    'id'              => 'pbot_aw_category',
                    'class'           => '',
                    'selected'        => 0,
                  ]);
                ?>
                <label class="pbot-check-inline" style="margin-top:5px; font-size:12px;">
                  <input type="checkbox" name="pbot_aw_ai_category" value="1" <?php checked(1, (int)$ai_category); ?> />
                  Let AI choose
                </label>
              </div>
            </div>

            <div>
              <div class="pbot-row pbot-row-stacked" style="margin-bottom:15px;">
                <label for="pbot_slug"><strong>Custom slug</strong></label>
                <input type="text" id="pbot_slug" name="pbot_slug" style="width:100%;" placeholder="optional">
              </div>

              <div class="pbot-row pbot-row-stacked" style="gap:6px; margin-bottom:15px;">
                <label style="font-size:13px;"><input type="checkbox" name="pbot_include_outline" value="1" <?php checked($inc_outline,1); ?>> Include outline</label>
                <label style="font-size:13px;"><input type="checkbox" name="pbot_include_meta" value="1" <?php checked($inc_meta,1); ?>> SEO title &amp; meta</label>
                <label style="font-size:13px;"><input type="checkbox" name="pbot_publish_immediately" value="1" <?php checked($pub_now,1); ?>> Publish immediately</label>
              </div>

              <div class="pbot-row pbot-row-stacked" style="margin:0;">
                <label for="pbot_aw_schedule"><strong>Posting cadence</strong></label>
                <select id="pbot_aw_schedule" name="pbot_aw_schedule" style="width:100%;">
                  <option value="monthly"  <?php selected($schedule,'monthly');  ?>>Monthly (basic)</option>
                  <option value="biweekly" <?php selected($schedule,'biweekly'); ?>>Bi-weekly</option>
                  <option value="weekly"   <?php selected($schedule,'weekly');   ?>>Weekly</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </form>

      <?php if (!empty($pbot_preview) && !empty($pbot_preview['html'])): ?>
        <div class="pbot-card">
          <h2>Preview</h2>
          <?php
            echo $pbot_preview['html'];
            if (!empty($pbot_preview['meta_title']) || !empty($pbot_preview['meta_desc'])) {
              echo '<div class="pbot-muted" style="margin-top:15px; padding-top:15px; border-top:1px solid #eee;"><strong>SEO (preview):</strong> ';
              if (!empty($pbot_preview['meta_title']))  echo 'Title: '.esc_html($pbot_preview['meta_title']).' &nbsp; ';
              if (!empty($pbot_preview['meta_desc']))   echo 'Meta: '.esc_html($pbot_preview['meta_desc']);
              echo '</div>';
            }
          ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT COLUMN: Actions & Configuration -->
    <div class="pbot-settings-sidebar">
      
      <!-- Actions Card -->
      <div class="pbot-card">
        <div class="pbot-card-header">
          <h2>⚡ Actions</h2>
        </div>
        
        <div class="pbot-actions-bar" style="flex-direction:column; align-items:stretch;">
          <button class="button button-primary button-hero" id="pbot_generate_btn" type="submit" form="pbot-writer-form" <?php echo esc_attr($disabled_btn); ?> style="width:100%; margin-bottom:10px;">Generate Draft</button>
          <button class="button" type="submit" name="pbot_preview" value="1" form="pbot-writer-form" style="width:100%; margin-bottom:10px;">Preview (no draft)</button>
          <button class="button" type="reset" form="pbot-writer-form" style="width:100%;">Reset Form</button>
        </div>

        <div class="pbot-muted pbot-route-msgs" style="margin-top:15px; font-size:12px; background:#f9f9f9; padding:8px; border-radius:6px;">
          <div id="pbot_route_msg_api" style="display:none;">Route: <strong>Your API</strong> (OpenAI direct)</div>
          <div id="pbot_route_msg_credits" style="display:none;">
            Route: <strong>Credits</strong> (<span class="pbot-credits-left"><?php echo !empty($lic['unlimited']) ? '∞' : (int)max(0, ($lic['limit'] ?? 0) - pbot_writer_usage_get()); ?></span> left)
          </div>
          <div id="pbot_route_msg_brain" style="display:none;">Route: <strong>AI Brain</strong></div>
        </div>
      </div>

      <!-- Route Selection Card -->
      <div class="pbot-card">
        <form method="post" action="options.php" id="pbot-route-form">
          <?php settings_fields('pbot_writer_settings'); ?>
          <div class="pbot-card-header">
            <h2>⚙️ Route</h2>
          </div>

          <fieldset class="pbot-fieldset" style="border:none; padding:0; margin:0 0 15px 0;">
            <legend class="pbot-muted" style="margin-bottom:10px; font-size:12px;">Choose your billing path</legend>
            <div class="pbot-route-group">
              <label class="pbot-route-option">
                <input type="radio" name="pbot_billing_route" value="api" <?php checked($billing_route,'api'); ?>>
                <span>My OpenAI Key</span>
                <span id="pbot-badge-api" class="pbot-badge" style="font-size:10px; padding:1px 5px;">set</span>
              </label>

              <label class="pbot-route-option">
                <input type="radio" name="pbot_billing_route" value="credits" <?php checked($billing_route,'credits'); ?>>
                <span>ProBot Credits</span>
                <span id="pbot-badge-credits" class="pbot-badge" style="font-size:10px; padding:1px 5px;">set</span>
              </label>

              <label class="pbot-route-option">
                <input type="radio" name="pbot_billing_route" value="brain" <?php checked($billing_route,'brain'); ?>>
                <span>AI Brain</span>
                <span id="pbot-badge-brain" class="pbot-badge" style="font-size:10px; padding:1px 5px;">set</span>
              </label>
            </div>
          </fieldset>

          <?php submit_button('Save Route', 'secondary', 'submit', false, ['style'=>'width:100%;', 'id'=>'pbot_save_route_btn']); ?>
        </form>
      </div>

      <div class="pbot-card">
        <h3>💡 Tips</h3>
        <ul class="pbot-muted" style="font-size:12px; padding-left:15px; margin:0;">
          <li>Detailed briefs produce better results.</li>
          <li>Keywords help SEO targeting.</li>
          <li>Required Headings ensure article structure.</li>
        </ul>
      </div>

    </div>
  </div>
</div>

<script>
(function(){
  const routeRadios = document.querySelectorAll('input[name="pbot_billing_route"]');
  const hiddenRoute = document.getElementById('pbot_billing_route_hidden');
  const btn         = document.getElementById('pbot_generate_btn');
  const saveBtn     = document.getElementById('pbot_save_route_btn');
  const routeForm   = document.getElementById('pbot-route-form');
  const msgApi      = document.getElementById('pbot_route_msg_api');
  const msgCred     = document.getElementById('pbot_route_msg_credits');
  const msgBrain    = document.getElementById('pbot_route_msg_brain');
  const wcInput     = document.getElementById('pbot_wordcount');
  const wcHint      = document.getElementById('pbot_wordcap_hint');

  const hasApi    = <?php echo json_encode(!empty($api_key)); ?>;
  const hasCreds  = <?php echo json_encode(!empty($lic['valid'])); ?>;
  const hasBrain  = true;
  const unlimited = <?php echo json_encode(!empty($lic['unlimited'])); ?>;
  const tier      = <?php echo json_encode($lic['tier'] ?? 'free'); ?>;

  const TIER_CAPS = { free: 600, starter: 1200, pro: 6000 };
  const HARD_MAX  = 6000;

  function enforceWordCapByKey() {
    if (!hasCreds) {
      const cap = TIER_CAPS.free;
      wcInput.max = cap;
      if (+wcInput.value > cap) wcInput.value = cap;
      wcHint.textContent = 'Plan cap: ' + cap + ' words.';
      return;
    }
    if (unlimited) {
      wcInput.max = HARD_MAX;
      wcHint.textContent = 'Plan cap: Unlimited';
      return;
    }
    const cap = TIER_CAPS[tier] || TIER_CAPS.free;
    wcInput.max = cap;
    if (+wcInput.value > cap) wcInput.value = cap;
    wcHint.textContent = 'Plan cap: ' + cap + ' words.';
  }

  function refreshRouteUI() {
    const route = document.querySelector('input[name="pbot_billing_route"]:checked')?.value || 'api';
    if (hiddenRoute) hiddenRoute.value = route;

    msgApi.style.display = 'none';
    msgCred.style.display = 'none';
    if (msgBrain) msgBrain.style.display = 'none';

    const badgeApi = document.getElementById('pbot-badge-api');
    const badgeCred = document.getElementById('pbot-badge-credits');
    const badgeBrain = document.getElementById('pbot-badge-brain');

    [badgeApi, badgeCred, badgeBrain].forEach(b => {
      if (b) {
        b.textContent = 'not set';
        b.className = 'pbot-badge is-beta';
      }
    });

    let activeBadge = null;
    let isRouteValid = true;

    if (route === 'api') {
      activeBadge = badgeApi;
      btn.disabled = !hasApi;
      msgApi.style.display = 'block';
      if (!hasApi) isRouteValid = false;
    } else if (route === 'credits') {
      activeBadge = badgeCred;
      btn.disabled = !hasCreds;
      msgCred.style.display = 'block';
      if (!hasCreds) isRouteValid = false;
    } else if (route === 'brain') {
      activeBadge = badgeBrain;
      btn.disabled = !hasBrain;
      if (msgBrain) msgBrain.style.display = 'block';
      if (!hasBrain) isRouteValid = false;
    }

    if (activeBadge) {
      activeBadge.textContent = 'set';
      activeBadge.className = 'pbot-badge is-stable';
    }

    // Disable Save button if route is invalid (missing keys)
    if (saveBtn) saveBtn.disabled = !isRouteValid;
  }

  // Submit validation with message pop up
  if (routeForm) {
    routeForm.addEventListener('submit', function(e) {
      const route = document.querySelector('input[name="pbot_billing_route"]:checked')?.value;
      if (route === 'api' && !hasApi) {
        alert("You cannot save this route:\nMy OpenAI Key is not set.");
        e.preventDefault();
      } else if (route === 'credits' && !hasCreds) {
        alert("You cannot save this route:\nProBot Credits (Product Key) is not set.");
        e.preventDefault();
      }
    });
  }

  routeRadios.forEach(r => r.addEventListener('change', refreshRouteUI));
  enforceWordCapByKey();
  refreshRouteUI();
})();
</script>