<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap pbot-wrap pbot-settings">
  <h1>
    ProBot Assistant — Settings
    <?php
      // Defensive helpers so badge works even if prerelease helpers aren’t loaded yet.
      $version_clean = function_exists('pbot_version_display') ? pbot_version_display() : (defined('PROBOT_VERSION') ? preg_replace('/-(alpha|beta|rc(\.\d+)?)$/i','',PROBOT_VERSION) : '');
      $is_beta       = function_exists('pbot_version_is_beta') ? pbot_version_is_beta() : (defined('PROBOT_VERSION') && stripos(PROBOT_VERSION,'beta') !== false);
      $phase         = function_exists('pbot_version_phase') ? pbot_version_phase() : ($is_beta ? 'beta' : 'stable'); // 'alpha'|'beta'|'rc'|'stable'
      $pre           = function_exists('pbot_version_prerelease') ? pbot_version_prerelease() : ($is_beta ? 'beta' : '');
      // Build badge class + text. If pre like "beta.1" exists, we show it; else fallback to "Beta".
      $badge_class   = 'pbot-badge ml-8 is-' . esc_attr($phase);
      $badge_text    = 'v' . $version_clean . ( $pre ? ' ' . strtoupper($pre) : ($is_beta ? ' Beta' : '') );
    ?>
    <span class="<?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></span>
  </h1>

  <?php
  // Optional: let add-ons inject notices or sections above the card.
  if (isset($ctx) && is_array($ctx)) {
    do_action('pbot_admin_settings_top', $ctx);
  } else {
    // Build a minimal $ctx if this view is ever called directly (defensive)
    $ctx = isset($ctx) && is_array($ctx) ? $ctx : compact(
      'brand','pos','pulse','teaserT','sound','replySound','thresh','gDelay',
      'brandColor','haloColor','panelColor','panelRadius',
      'toastBg','toastFg','haloInt','pulseInt',
      'toastMsg','toastMs','toastCount',
      'openaiKey','productKey',
      'btnBorderEnabled','btnBorderWeight','btnBorderColor',
      'sendBorderEnabled','sendBorderWeight','sendBorderColor'
    );
  }
  ?>

  <form method="post" action="options.php">
    <?php settings_fields('pbot_settings'); ?>

    <div class="pbot-settings-layout">
      
      <!-- LEFT COLUMN: Settings -->
      <div class="pbot-settings-main">
        
        <div class="pbot-card">
          <div class="pbot-row pbot-row-stacked">
            <label for="pbot_brand_title"><strong>Brand title</strong></label>
            <input type="text" id="pbot_brand_title" name="pbot_brand_title"
                   value="<?php echo esc_attr($brand); ?>" class="regular-text" />
          </div>

          <fieldset class="pbot-fieldset">
            <legend>Bubble position</legend>
            <div class="pbot-inline">
              <label class="pbot-inline-item">
                <input type="radio" name="pbot_bubble_position" value="left"  <?php checked($pos,'left');  ?>> Left
              </label>
              <label class="pbot-inline-item">
                <input type="radio" name="pbot_bubble_position" value="right" <?php checked($pos,'right'); ?>> Right
              </label>
            </div>
          </fieldset>

          <fieldset class="pbot-fieldset">
            <legend>Visuals &amp; Behavior</legend>
            <div class="pbot-row">
              <label><input type="checkbox" name="pbot_pulse_enabled" id="pbot_pulse_enabled" value="1" <?php checked($pulse, 1);  ?>> Pulse halo</label>
              <label><input type="checkbox" name="pbot_teaser_enabled" id="pbot_teaser_enabled" value="1" <?php checked($teaserT,1);  ?>> Teaser toast</label>
              <label><input type="checkbox" name="pbot_sound_enabled" id="pbot_sound_enabled" value="1" <?php checked($sound, 1);  ?>> Sound on reply</label>
            </div>

            <div class="pbot-row" style="margin-top: 10px;">
              <label for="pbot_reply_sound"><strong>Reply sound:</strong></label>
              <select id="pbot_reply_sound" name="pbot_reply_sound">
                <option value="mystical-chime" <?php selected($replySound, 'mystical-chime'); ?>>Mystical Chime</option>
                <option value="crystal-ping" <?php selected($replySound, 'crystal-ping'); ?>>Crystal Ping</option>
                <option value="soft-notification" <?php selected($replySound, 'soft-notification'); ?>>Soft Notification</option>
                <option value="digital-pulse" <?php selected($replySound, 'digital-pulse'); ?>>Digital Pulse</option>
                <option value="none" <?php selected($replySound, 'none'); ?>>None</option>
              </select>
              <button type="button" class="button" id="pbot_test_sound">Test</button>
            </div>
          </fieldset>

          <fieldset class="pbot-fieldset">
            <legend>Teaser toast</legend>

            <div class="pbot-row pbot-row-stacked">
              <label for="pbot_teaser_message"><strong>Message</strong></label>
              <input type="text" id="pbot_teaser_message" name="pbot_teaser_message"
                     maxlength="120" class="regular-text"
                     placeholder="Short, single-line toast (max 120 chars)"
                     value="<?php echo esc_attr($toastMsg); ?>" />
            </div>

            <div class="pbot-row">
              <label for="pbot_teaser_duration_ms"><strong>Duration (ms)</strong></label>
              <input type="number" id="pbot_teaser_duration_ms" name="pbot_teaser_duration_ms"
                     class="pbot-num-mid pbot-auto-num"
                     min="1000" max="20000" step="100"
                     value="<?php echo esc_attr($toastMs); ?>" />
              <span class="pbot-muted">1–20s</span>
            </div>

            <div class="pbot-row">
              <label for="pbot_teaser_show_count"><strong>Show count (per visit)</strong></label>
              <input type="number" id="pbot_teaser_show_count" name="pbot_teaser_show_count"
                     class="pbot-num-short pbot-auto-num"
                     min="0" max="20" step="1"
                     value="<?php echo esc_attr($toastCount); ?>" />
              <span class="pbot-muted">0 = never show (client-side tracked).</span>
            </div>

            <!-- Toast colors -->
            <div class="pbot-row pbot-color-row">
              <label for="pbot_toast_bg_color"><strong>Toast background</strong></label>
              <input type="color" class="pbot-color" id="pbot_toast_bg_color_picker"
                     value="<?php echo esc_attr(preg_match('/^#[0-9a-f]{3,6}$/i',$toastBg) ? $toastBg : '#121212'); ?>" />
              <input type="text" id="pbot_toast_bg_color" name="pbot_toast_bg_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($toastBg); ?>" />
            </div>

            <div class="pbot-row">
              <label for="pbot_toast_bg_opacity"><strong>Toast opacity</strong></label>
              <input type="range" id="pbot_toast_bg_opacity" name="pbot_toast_bg_opacity"
                     min="0" max="1" step="0.01" value="<?php echo esc_attr($toastOpacity); ?>" />
              <span class="pbot-toast-opacity-val"><?php echo number_format($toastOpacity,2); ?></span>
            </div>

            <div class="pbot-row pbot-color-row">
              <label for="pbot_toast_text_color"><strong>Toast text</strong></label>
              <input type="color" class="pbot-color" id="pbot_toast_text_color_picker"
                     value="<?php echo esc_attr(preg_match('/^#|^rgb|^hsl/i',$toastFg) ? $toastFg : '#ffffff'); ?>" />
              <input type="text" id="pbot_toast_text_color" name="pbot_toast_text_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($toastFg); ?>" />
            </div>
          </fieldset>

          <fieldset class="pbot-fieldset">
            <legend>Colors</legend>

            <div class="pbot-row pbot-color-row">
              <label for="pbot_brand_color"><strong>Chat bubble</strong></label>
              <input type="color" class="pbot-color" id="pbot_brand_color_picker" value="<?php echo esc_attr($brandColor); ?>" />
              <input type="text" id="pbot_brand_color" name="pbot_brand_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($brandColor); ?>" />
            </div>

            <div class="pbot-row pbot-color-row">
              <label for="pbot_halo_color"><strong>Halo</strong></label>
              <input type="color" class="pbot-color" id="pbot_halo_color_picker" 
                     value="<?php echo esc_attr(preg_match('/^#[0-9a-f]{3,6}$/i',$haloColor) ? $haloColor : '#ffffff'); ?>" />
              <input type="text" id="pbot_halo_color" name="pbot_halo_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($haloColor); ?>" />
            </div>

            <div class="pbot-row">
              <label for="pbot_halo_intensity"><strong>Halo intensity</strong></label>
              <input type="range" id="pbot_halo_intensity" name="pbot_halo_intensity"
                     min="0" max="1" step="0.01" value="<?php echo esc_attr($haloInt); ?>" />
              <span class="pbot-halo-val"><?php echo number_format($haloInt,2); ?></span>
            </div>

            <div class="pbot-row">
              <label for="pbot_pulse_intensity"><strong>Pulse intensity (bubble)</strong></label>
              <input type="range" id="pbot_pulse_intensity" name="pbot_pulse_intensity"
                     min="0.5" max="2.0" step="0.01" value="<?php echo esc_attr($pulseInt); ?>" />
              <span class="pbot-pulse-val" id="pbot-pulse-val"><?php echo number_format($pulseInt,2); ?></span>
            </div>

            <div class="pbot-row pbot-color-row">
              <label for="pbot_panel_color"><strong>Chat window</strong></label>
              <input type="color" class="pbot-color" id="pbot_panel_color_picker" value="<?php echo esc_attr($panelColor); ?>" />
              <input type="text" id="pbot_panel_color" name="pbot_panel_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($panelColor); ?>" />
            </div>

            <div class="pbot-row">
              <label for="pbot_panel_radius"><strong>Window corner radius (px)</strong></label>
              <input type="number" id="pbot_panel_radius" name="pbot_panel_radius"
                     min="0" max="25" step="1" value="<?php echo esc_attr($panelRadius); ?>" />
            </div>
          </fieldset>

          <?php do_action('pbot_admin_settings_before_buttons', $ctx); ?>

          <!-- === Buttons styling === -->
          <fieldset class="pbot-fieldset">
            <legend>Buttons</legend>

            <h3>Close &amp; Minimize</h3>
            <div class="pbot-row">
              <label><input type="checkbox" name="pbot_btn_border_enabled" value="1" <?php checked($btnBorderEnabled,1); ?>> Show border</label>
              <label for="pbot_btn_border_weight" class="pbot-muted" style="margin-left:10px;">Line (px)</label>
              <input type="number" id="pbot_btn_border_weight" name="pbot_btn_border_weight"
                     class="pbot-num-mid pbot-auto-num"
                     min="0" max="6" step="0.5"
                     value="<?php echo esc_attr($btnBorderWeight); ?>" />
            </div>
            <div class="pbot-row pbot-color-row">
              <label for="pbot_btn_border_color"><strong>Border color</strong></label>
              <input type="color" class="pbot-color" id="pbot_btn_border_color_picker" value="<?php echo esc_attr($btnBorderColor); ?>" />
              <input type="text" id="pbot_btn_border_color" name="pbot_btn_border_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($btnBorderColor); ?>" />
            </div>

            <h3 style="margin-top:14px;">Send Button</h3>
            <div class="pbot-row">
              <label><input type="checkbox" name="pbot_send_border_enabled" value="1" <?php checked($sendBorderEnabled,1); ?>> Show border</label>
              <label for="pbot_send_border_weight" class="pbot-muted" style="margin-left:10px;">Line (px)</label>
              <input type="number" id="pbot_send_border_weight" name="pbot_send_border_weight"
                     class="pbot-num-mid pbot-auto-num"
                     min="0" max="6" step="0.5"
                     value="<?php echo esc_attr($sendBorderWeight); ?>" />
            </div>
            <div class="pbot-row pbot-color-row">
              <label for="pbot_send_border_color"><strong>Border color</strong></label>
              <input type="color" class="pbot-color" id="pbot_send_border_color_picker" value="<?php echo esc_attr($sendBorderColor); ?>" />
              <input type="text" id="pbot_send_border_color" name="pbot_send_border_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($sendBorderColor); ?>" />
            </div>

            <div class="pbot-row pbot-color-row">
              <label for="pbot_send_bg_color"><strong>Background color</strong></label>
              <input type="color" class="pbot-color" id="pbot_send_bg_color_picker" value="<?php echo esc_attr($sendBgColor); ?>" />
              <input type="text" id="pbot_send_bg_color" name="pbot_send_bg_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($sendBgColor); ?>" />
            </div>

            <div class="pbot-row pbot-color-row">
              <label for="pbot_send_hover_color"><strong>Hover color</strong></label>
              <input type="color" class="pbot-color" id="pbot_send_hover_color_picker" value="<?php echo esc_attr($sendHoverColor); ?>" />
              <input type="text" id="pbot_send_hover_color" name="pbot_send_hover_color"
                     class="pbot-color-text"
                     value="<?php echo esc_attr($sendHoverColor); ?>" />
            </div>
          </fieldset>

          <fieldset class="pbot-fieldset">
            <legend>Keys (for paid features)</legend>
            <div class="pbot-row pbot-row-stacked">
              <label for="pbot_openai_api_key"><strong>OpenAI API Key</strong></label>
              <input type="text" id="pbot_openai_api_key" name="pbot_openai_api_key"
                     class="regular-text"
                     value="<?php echo esc_attr($openaiKey); ?>" autocomplete="off" />
            </div>
            <div class="pbot-row pbot-row-stacked">
              <label for="pbot_product_key"><strong>Product Key</strong></label>
              <input type="text" id="pbot_product_key" name="pbot_product_key"
                     class="regular-text"
                     value="<?php echo esc_attr($productKey); ?>" autocomplete="off" />
            </div>
          </fieldset>

          <?php do_action('pbot_admin_settings_after_form', $ctx); ?>
        </div>

      </div>

      <!-- RIGHT COLUMN: Preview & Actions -->
      <div class="pbot-settings-sidebar">
        
        <!-- Live Preview Card -->
        <div class="pbot-card" style="position: sticky; top: 32px;">
          <div class="pbot-card-header">
            <h2>🎨 Live Design Preview</h2>
          </div>
          <div class="pbot-preview-stage" id="pbot-preview-stage">
            <div class="pbot-mini-teaser" id="pbot-mini-teaser"><?php echo esc_html($toastMsg ?: 'Hi there!'); ?></div>
            <div class="pbot-mini-panel" id="pbot-mini-panel" style="opacity: 0; transform: translateY(10px) scale(0.95);">
              <div class="pbot-mini-topbar"></div>
            </div>
            <div class="pbot-mini-bubble" id="pbot-mini-bubble">🤖</div>
            <div class="pbot-preview-footer"></div>
          </div>
          <p class="description" style="margin-top:10px;">Visual representation of your current settings. Click bubble to toggle window.</p>
          
          <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
             <h2 style="font-size: 14px; margin-bottom: 15px;">⚡ Creative Actions</h2>
             <div style="margin-bottom:15px; font-size:13px; color: #57606a;">
              <strong>Brain Core:</strong> Llama 3.3 70B<br>
              <strong>Status:</strong> <span style="color: #238636;">● Creative Suite Active</span>
            </div>
            <?php submit_button('Save All Changes', 'primary', 'submit', true, ['style'=>'width:100%; margin:0;']); ?>
          </div>
        </div>

      </div>

    </div>
  </form>
</div>
