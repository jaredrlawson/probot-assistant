<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Knowledge Base (registration only)
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

add_action('admin_init', function () {
  // Source: packaged|manual
  register_setting('pbot_responses', 'pbot_intents_source');

  // Manual JSON (raw string)
  register_setting('pbot_responses', 'pbot_manual_intents', [
    'type'              => 'string',
    'sanitize_callback' => function($v){ return is_string($v) ? $v : ''; },
    'default'           => '',
  ]);
});