<?php
if (!defined('ABSPATH')) exit;

/**
 * ProBot Assistant — AI Answering: controller
 * Gathers context + license state and renders the view
 */

function probot_render_answering_page() {
  if (!current_user_can('manage_options')) return;

  // License
  $lic = function_exists('pbot_license_check') ? pbot_license_check() : ['valid'=>false,'tier'=>'free','unlimited'=>false,'left'=>0];
  $is_paid = !empty($lic['valid']) && in_array(($lic['tier'] ?? 'free'), ['starter','pro'], true);

  // Settings
  $twilio_sid    = trim((string) get_option('pbot_twilio_sid', ''));
  $twilio_token  = trim((string) get_option('pbot_twilio_token', ''));
  $twilio_number = trim((string) get_option('pbot_twilio_number', ''));
  $enabled       = (bool) get_option('pbot_answering_enabled', 0);
  $forward_to    = trim((string) get_option('pbot_answering_forward_to', ''));
  $notify_email  = sanitize_email(get_option('pbot_answering_notify_email', ''));

  $webhook_url   = rest_url('pbot/v1/voice/twilio');

  // Pass to view
  $ctx = compact(
    'lic','is_paid',
    'twilio_sid','twilio_token','twilio_number',
    'enabled','forward_to','notify_email','webhook_url'
  );
  extract($ctx, EXTR_SKIP);
  require dirname(__FILE__).'/../includes/views/answering-page.php';
}