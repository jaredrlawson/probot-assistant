<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap pbot-wrap pbot-writer">
  <h1>ProBot Assistant — Article Writer</h1>

  <?php if (!empty($notices)) foreach ($notices as $n) echo $n; ?>

  <div class="pbot-card">
    <p class="pbot-muted pbot-mt-0">
      Generate with <strong>your OpenAI API key</strong> (you pay OpenAI) or with
      <strong>ProBot Credits</strong> using your Product Key (we meter &amp; bill usage).
      Same fields—only billing route changes. Title is optional; if left blank,
      <strong>AI will generate a title</strong>.
    </p>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="pbot-writer-form">
      <?php wp_nonce_field('pbot_generate_article_nonce','pbot_nonce'); ?>
      <input type="hidden" name="action" value="pbot_generate_article" />

      <!-- Billing route -->
      <fieldset class="pbot-fieldset">
        <legend>Billing route</legend>

        <div class="pbot-route-group">
          <!-- My API key -->
          <label class="pbot-route-option">
            <input type="radio" name="pbot_billing_route" value="api"
              <?php checked(empty($lic['valid']) || !empty($api_key), true); ?>>
            <strong>My OpenAI API key</strong>
            <span class="pbot-badge <?php echo $api_key ? '' : 'is-beta'; ?>">
              <?php echo $api_key ? 'set' : 'not set'; ?>
            </span>
          </label>

          <!-- ProBot Credits -->
          <label class="pbot-route-option">
            <input type="radio" name="pbot_billing_route" value="credits"
              <?php checked(!empty($lic['valid']) && empty($api_key)); ?>>
            <strong>ProBot Credits (Product Key)</strong>
            <span class="pbot-badge <?php echo !empty($lic['valid']) ? '' : 'is-beta'; ?>">
              <?php echo !empty($lic['valid']) ? 'set' : 'not set'; ?>
            </span>
            <?php if (!empty($lic['valid'])): ?>
              <span class="pbot-badge">
                <span class="pbot-credits-left">
                  <?php
                    echo !empty($lic['unlimited'])
                      ? '∞'
                      : (int)max(0, ($lic['limit'] ?? 0) - pbot_writer_usage_get());
                  ?>
                </span>
                left
              </span>
            <?php endif; ?>
          </label>
        </div>

        <p class="pbot-muted pbot-mt-8">
          API route: unlimited by us. Credits route: cadence/limits may apply by tier.
        </p>
      </fieldset>

      <!-- Title -->
      <div class="pbot-row">
        <label for="pbot_post_title"><strong>Title</strong> <span class="pbot-muted">(optional — leave blank for AI)</span></label>
        <input type="text" id="pbot_post_title" name="pbot_post_title" class="regular-text" placeholder="AI will generate if blank">
      </div>

      <!-- Brief -->
      <div class="pbot-row">
        <label for="pbot_brief"><strong>Brief / Context</strong></label>
        <textarea id="pbot_brief" name="pbot_brief" class="pbot-json-view" placeholder="Target audience, angle, must-include points, internal links, etc." required></textarea>
      </div>

      <!-- Wordcount -->
      <div class="pbot-row">
        <label for="pbot_wordcount"><strong>Target length</strong></label>
        <input type="number" id="pbot_wordcount" name="pbot_wordcount" min="300" max="3000" step="50" value="1000">
        <span class="pbot-muted">words</span>
        <!-- Plan cap hint (JS fills this) -->
        <div id="pbot_wordcap_hint" class="pbot-muted" style="width:100%;"></div>
      </div>

      <!-- Keywords -->
      <div class="pbot-row">
        <label for="pbot_keywords"><strong>Primary keywords</strong></label>
        <input type="text" id="pbot_keywords" name="pbot_keywords" class="regular-text" placeholder="comma-separated (optional)">
      </div>

      <!-- Tone -->
      <div class="pbot-row">
        <label for="pbot_tone"><strong>Tone</strong></label>
        <select id="pbot_tone" name="pbot_tone">
          <option value="helpful, friendly, professional" selected>Helpful, Friendly, Professional</option>
          <option value="formal and authoritative">Formal &amp; Authoritative</option>
          <option value="conversational and engaging">Conversational &amp; Engaging</option>
          <option value="technical and precise">Technical &amp; Precise</option>
        </select>
      </div>

      <!-- Required headings -->
      <div class="pbot-row">
        <label for="pbot_headings"><strong>Required headings</strong></label>
        <textarea id="pbot_headings" name="pbot_headings" class="pbot-json-view" placeholder="H2: …&#10;H2: …&#10;H3: … (optional)"></textarea>
      </div>

      <!-- Category -->
      <div class="pbot-row">
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
        <label class="pbot-check-inline">
          <input type="checkbox" name="pbot_aw_ai_category" value="1" <?php checked(1, (int)$ai_category); ?> />
          Let AI choose the category
        </label>
      </div>

      <!-- Slug -->
      <div class="pbot-row">
        <label for="pbot_slug"><strong>Custom slug</strong></label>
        <input type="text" id="pbot_slug" name="pbot_slug" class="regular-text" placeholder="optional">
      </div>

      <!-- Checkboxes -->
      <div class="pbot-row">
        <label><input type="checkbox" name="pbot_include_outline" value="1" checked> Include outline</label>
        <label><input type="checkbox" name="pbot_include_meta" value="1" checked> Generate SEO title &amp; meta</label>
        <label><input type="checkbox" name="pbot_publish_immediately" value="1"> Publish immediately</label>
      </div>

      <!-- Cadence -->
      <div class="pbot-row">
        <label for="pbot_aw_schedule"><strong>Posting cadence</strong></label>
        <select id="pbot_aw_schedule" name="pbot_aw_schedule">
          <option value="monthly"  <?php selected($schedule,'monthly');  ?>>Monthly (basic)</option>
          <option value="biweekly" <?php selected($schedule,'biweekly'); ?>>Bi-weekly</option>
          <option value="weekly"   <?php selected($schedule,'weekly');   ?>>Weekly</option>
        </select>
        <span class="pbot-muted" id="pbot_cadence_hint"></span>
      </div>

      <!-- Actions -->
      <div class="pbot-actions-bar">
        <button class="button button-primary" id="pbot_generate_btn" type="submit" <?php echo esc_attr($disabled_btn); ?>>Generate Draft</button>

        <?php if (empty($api_key) && empty($lic['valid'])): ?>
          <button class="button" type="submit" name="pbot_preview" value="1">Preview (no draft)</button>
        <?php endif; ?>

        <button class="button" type="reset">Reset</button>
      </div>

      <!-- Route messages / credit meter -->
      <div class="pbot-muted pbot-route-msgs">
        <div id="pbot_route_msg_api">This route uses <strong>your</strong> OpenAI billing. We don’t meter or charge anything on this path.</div>
        <div id="pbot_route_msg_credits">
          This route meters usage against your <strong>Product Key</strong>.
          Remaining this month:
          <strong class="pbot-credits-left">
            <?php echo !empty($lic['unlimited']) ? '∞' : (int)max(0, ($lic['limit'] ?? 0) - pbot_writer_usage_get()); ?>
          </strong>
          <?php if (empty($lic['unlimited']) && !empty($lic['limit'])): ?>
            of <strong><?php echo (int)$lic['limit']; ?></strong>.
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <?php if (!empty($pbot_preview) && !empty($pbot_preview['html'])): ?>
    <div class="pbot-card">
      <h2>Preview</h2>
      <?php
        echo $pbot_preview['html'];
        if (!empty($pbot_preview['meta_title']) || !empty($pbot_preview['meta_desc'])) {
          echo '<div class="pbot-muted"><strong>SEO (preview):</strong> ';
          if (!empty($pbot_preview['meta_title']))  echo 'Title: '.esc_html($pbot_preview['meta_title']).' &nbsp; ';
          if (!empty($pbot_preview['meta_desc']))   echo 'Meta: '.esc_html($pbot_preview['meta_desc']);
          echo '</div>';
        }
      ?>
    </div>
  <?php endif; ?>

  <div class="pbot-card">
    <h2>Tips for best results</h2>
    <ul>
      <li>Provide a focused Topic / Prompt and a short brief in the first field.</li>
      <li>Use Required Headings to enforce structure (H2/H3/H4 prefixes are supported).</li>
      <li>Turn on “Generate SEO title &amp; meta” to prefill Yoast/RankMath fields when available.</li>
      <li>Leave Category on “Let AI choose” if you don’t have a strong preference.</li>
    </ul>
  </div>
</div>

<script>
(function(){
  const routeRadios = document.querySelectorAll('input[name="pbot_billing_route"]');
  const cadence     = document.getElementById('pbot_aw_schedule');
  const cadenceHint = document.getElementById('pbot_cadence_hint');
  const btn         = document.getElementById('pbot_generate_btn');
  const msgApi      = document.getElementById('pbot_route_msg_api');
  const msgCred     = document.getElementById('pbot_route_msg_credits');
  const wcInput     = document.getElementById('pbot_wordcount');
  const wcHint      = document.getElementById('pbot_wordcap_hint');

  // Server flags
  const hasApi    = <?php echo json_encode(!empty($api_key)); ?>;
  const hasCreds  = <?php echo json_encode(!empty($lic['valid'])); ?>;
  const unlimited = <?php echo json_encode(!empty($lic['unlimited'])); ?>;
  const tier      = <?php echo json_encode($lic['tier'] ?? 'free'); ?>;

  // Plan caps (only used when NOT unlimited)
  const TIER_CAPS = { free: 600, starter: 1200, pro: 6000 };
  const HARD_MAX  = 6000; // safety ceiling

  // If no API key and no Product Key, default the radio to API (UI only)
  if (!hasApi && !hasCreds) {
    const apiRadio = document.querySelector('input[name="pbot_billing_route"][value="api"]');
    if (apiRadio) apiRadio.checked = true;
  }

  function enforceCadenceByKey() {
    const mustBeBasic = !hasCreds || (!unlimited && tier === 'free');
    [...cadence.options].forEach(opt => {
      if (mustBeBasic && (opt.value === 'weekly' || opt.value === 'biweekly')) {
        opt.disabled = true;
        if (opt.selected) cadence.value = 'monthly';
      } else {
        opt.disabled = false;
      }
    });
    cadenceHint.textContent = mustBeBasic ? 'Weekly/Bi-weekly require a paid tier.' : '';
  }

  function enforceWordCapByKey() {
    if (!hasCreds) {
      const cap = TIER_CAPS.free;
      wcInput.max = cap;
      if (+wcInput.value > cap) wcInput.value = cap;
      wcHint.textContent = 'Your plan cap: up to ' + cap + ' words.';
      return;
    }
    if (unlimited) {
      wcInput.max = HARD_MAX;
      wcHint.textContent = 'Your plan cap: Unlimited';
      return;
    }
    const cap = TIER_CAPS[tier] || TIER_CAPS.free;
    wcInput.max = cap;
    if (+wcInput.value > cap) wcInput.value = cap;
    wcHint.textContent = 'Your plan cap: up to ' + cap + ' words.';
  }

  function refreshRouteMsgsAndButton() {
    const route = document.querySelector('input[name="pbot_billing_route"]:checked')?.value || 'api';
    if (route === 'api') {
      btn.disabled = !hasApi;
      msgApi.classList.add('is-active');
      msgCred.classList.remove('is-active');
    } else {
      btn.disabled = !hasCreds;
      msgCred.classList.add('is-active');
      msgApi.classList.remove('is-active');
    }
  }

  function refreshAll() {
    enforceCadenceByKey();
    enforceWordCapByKey();
    refreshRouteMsgsAndButton();
  }

  routeRadios.forEach(r => r.addEventListener('change', refreshAll));
  refreshAll();
})();
</script>