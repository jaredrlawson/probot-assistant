<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Settings Page (controller)
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

// Pull in pure registration (ensures settings exist even if view loads first)
require_once dirname(__FILE__) . '/admin-settings-register.php';

/**
 * Render Settings page — collects options, passes to a view template.
 * View: includes/views/settings-page.php
 */
function probot_render_settings_page() {
  if (!current_user_can('manage_options')) return;

  // Gather options (no markup here)
  $ctx = array(
    // Core
    'brand'   => get_option('pbot_brand_title', 'ProBot Assistant'),
    'pos'     => get_option('pbot_bubble_position', 'right'),
    'pulse'   => (bool) get_option('pbot_pulse_enabled', 1),
    'teaserT' => (bool) get_option('pbot_teaser_enabled', 1),
    'sound'   => (bool) get_option('pbot_sound_enabled', 1),
    'thresh'  => (float) get_option('pbot_match_threshold', 0.52),
    'gDelay'  => (int)   get_option('pbot_greeting_delay_ms', 2200),

    // Colors
    'brandColor' => get_option('pbot_brand_color', '#444444'),
    'haloColor'  => get_option('pbot_halo_color',  'rgba(255,255,255,.7)'),
    'panelColor' => get_option('pbot_panel_color', '#ffffff'),

    // Toast colors
    'toastBg'    => get_option('pbot_toast_bg_color',   'rgba(18,18,18,.92)'),
    'toastFg'    => get_option('pbot_toast_text_color', '#ffffff'),

    // Sliders
    'haloInt'   => (float) get_option('pbot_halo_intensity', 0.70),
    'pulseInt'  => (float) get_option('pbot_pulse_intensity', 1.00),

    // Teaser copy
    'toastMsg'   => get_option('pbot_teaser_message', ''),
    'toastMs'    => (int) get_option('pbot_teaser_duration_ms', 4500),
    'toastCount' => (int) get_option('pbot_teaser_show_count', 3),

    // Keys
    'openaiKey'  => get_option('pbot_openai_api_key', ''),
    'productKey' => get_option('pbot_product_key', ''),

    // Buttons (Close/Min + Send)
    'btnBorderEnabled'  => (int) get_option('pbot_btn_border_enabled', 0),
    'btnBorderWeight'   => (float) get_option('pbot_btn_border_weight', 1),
    'btnBorderColor'    => get_option('pbot_btn_border_color', '#d0d0d0'),

    'sendBorderEnabled' => (int) get_option('pbot_send_border_enabled', 0),
    'sendBorderWeight'  => (float) get_option('pbot_send_border_weight', 1),
    'sendBorderColor'   => get_option('pbot_send_border_color', '#d0d0d0'),
  );

  // Expose $ctx vars in local scope for the view
  extract($ctx, EXTR_SKIP);

  // Render the view template (pure markup)
  require dirname(__FILE__) . '/views/settings-page.php';
}