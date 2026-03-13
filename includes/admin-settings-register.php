<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Settings Registration (pure PHP)
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

add_action('admin_init', function () {
  // Core toggles and basics
  register_setting('pbot_settings', 'pbot_brand_title');
  register_setting('pbot_settings', 'pbot_bubble_icon',     ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'chat']);
  register_setting('pbot_settings', 'pbot_bubble_position', ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'right']);
  register_setting('pbot_settings', 'pbot_pulse_enabled');
  register_setting('pbot_settings', 'pbot_teaser_enabled');
  register_setting('pbot_settings', 'pbot_sound_enabled');
  register_setting('pbot_settings', 'pbot_reply_sound', ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'mystical-chime']);
  // Colors
  register_setting('pbot_settings', 'pbot_brand_color', ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
  register_setting('pbot_settings', 'pbot_halo_color',  ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#ffffff']);
  register_setting('pbot_settings', 'pbot_panel_color', ['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
  register_setting('pbot_settings', 'pbot_panel_radius', ['type'=>'integer','sanitize_callback'=>function($v){ return max(0, min(25, (int)$v)); }, 'default'=>16]);

  // Send button colors
  register_setting('pbot_settings', 'pbot_send_bg_color',    ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#ffffff']);
  register_setting('pbot_settings', 'pbot_send_hover_color', ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#f7f7f7']);

  // Toast colors
  register_setting('pbot_settings', 'pbot_toast_bg_color',   ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#121212']);
  register_setting('pbot_settings', 'pbot_toast_bg_opacity', [
    'type'=>'number',
    'sanitize_callback'=>function($v){ $v=(float)$v; return max(0.0, min(1.0, $v)); },
    'default'=>0.92
  ]);
  register_setting('pbot_settings', 'pbot_toast_text_color', ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#ffffff']);

  // Sliders (halo/pulse)
  register_setting('pbot_settings', 'pbot_halo_intensity', [
    'type'=>'number',
    'sanitize_callback'=>function($v){ $v=(float)$v; return max(0.0, min(1.0, $v)); },
    'default'=>0.70
  ]);
  register_setting('pbot_settings', 'pbot_pulse_intensity', [
    'type'=>'number',
    'sanitize_callback'=>function($v){ $v=(float)$v; return max(0.5, min(2.0, $v)); }, // 0.5x .. 2.0x
    'default'=>1.00
  ]);

  // Teaser config
  register_setting('pbot_settings', 'pbot_teaser_message', [
    'type'=>'string','sanitize_callback'=>function($v){ return mb_substr(sanitize_text_field($v),0,120); }
  ]);
  register_setting('pbot_settings', 'pbot_teaser_duration_ms', [
    'type'=>'integer','sanitize_callback'=>function($v){ $v=(int)$v; return max(1000,min(20000,$v)); }, 'default'=>4500
  ]);
  register_setting('pbot_settings', 'pbot_teaser_show_count', [
    'type'=>'integer','sanitize_callback'=>function($v){ $v=(int)$v; return max(0,min(20,$v)); }, 'default'=>3
  ]);

  // Keys
  register_setting('pbot_settings', 'pbot_openai_api_key', ['type'=>'string','sanitize_callback'=>function($v){ return trim(sanitize_text_field($v)); }]);
  register_setting('pbot_settings', 'pbot_product_key',    ['type'=>'string','sanitize_callback'=>function($v){ return trim(sanitize_text_field($v)); }]);

  // Button borders (Close/Minimize + Send)
  register_setting('pbot_settings', 'pbot_btn_border_enabled', ['type'=>'integer','sanitize_callback'=>function($v){ return empty($v)?0:1; }, 'default'=>0]);
  register_setting('pbot_settings', 'pbot_btn_border_weight',  ['type'=>'number', 'sanitize_callback'=>function($v){ $v=(float)$v; return max(0, min(6, $v)); }, 'default'=>1]);
  register_setting('pbot_settings', 'pbot_btn_border_color',   ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#d0d0d0']);

  register_setting('pbot_settings', 'pbot_send_border_enabled', ['type'=>'integer','sanitize_callback'=>function($v){ return empty($v)?0:1; }, 'default'=>0]);
  register_setting('pbot_settings', 'pbot_send_border_weight',  ['type'=>'number', 'sanitize_callback'=>function($v){ $v=(float)$v; return max(0, min(6, $v)); }, 'default'=>1]);
  register_setting('pbot_settings', 'pbot_send_border_color',   ['type'=>'string','sanitize_callback'=>'sanitize_text_field', 'default'=>'#d0d0d0']);
});