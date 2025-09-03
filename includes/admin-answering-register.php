<?php
if (!defined('ABSPATH')) exit;

/**
 * ProBot Assistant — AI Answering: registrar
 * - Adds admin submenu
 * - Registers options
 * - Registers Twilio webhook (REST)
 * - License gating happens in the controller (admin-answering-page.php)
 */

// ---------- Options ----------
add_action('admin_init', function () {
  register_setting('pbot_answering', 'pbot_twilio_sid');
  register_setting('pbot_answering', 'pbot_twilio_token');
  register_setting('pbot_answering', 'pbot_twilio_number');
  register_setting('pbot_answering', 'pbot_answering_enabled', ['type'=>'boolean', 'default'=>0]);
  register_setting('pbot_answering', 'pbot_answering_forward_to'); // optional live transfer target
  register_setting('pbot_answering', 'pbot_answering_notify_email'); // where to send transcriptions/messages
});

// ---------- Submenu ----------
add_action('admin_menu', function () {
  // Parent should be your existing main plugin menu slug
  add_submenu_page(
    'probot-assistant',
    'AI Answering',
    'AI Answering',
    'manage_options',
    'probot-answering',
    'probot_render_answering_page'
  );
});

// ---------- REST: Twilio webhook ----------
add_action('rest_api_init', function () {
  register_rest_route('pbot/v1', '/voice/twilio', [
    'methods'  => 'POST',
    'callback' => 'probot_twilio_voice_webhook',
    'permission_callback' => '__return_true',
  ]);
});

/**
 * Twilio Voice Webhook MVP:
 * - Gated by Product Key tier (Starter+).
 * - Greets caller, gathers speech for 10s, emails the transcript-ish (Twilio SpeechResult),
 *   and consumes 1 phone credit via License Server.
 * - Later: replace with OpenAI Realtime for full AI conversations/live transfer.
 */
function probot_twilio_voice_webhook(\WP_REST_Request $req) {
  // Basic config
  $enabled = (bool) get_option('pbot_answering_enabled', 0);
  $sid     = trim((string) get_option('pbot_twilio_sid', ''));
  $token   = trim((string) get_option('pbot_twilio_token', ''));
  $email   = sanitize_email(get_option('pbot_answering_notify_email',''));

  // License check
  $lic = function_exists('pbot_license_check') ? pbot_license_check() : ['valid'=>false,'tier'=>'free','unlimited'=>false,'left'=>0];
  $is_paid = !empty($lic['valid']) && in_array(($lic['tier'] ?? 'free'), ['starter','pro'], true);
  if (!$enabled || !$is_paid || $sid === '' || $token === '') {
    // Return minimal TwiML saying service unavailable
    return new \WP_REST_Response(
      "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Response><Say>AI answering is unavailable on this line.</Say></Response>",
      200,
      ['Content-Type' => 'text/xml; charset=UTF-8']
    );
  }

  // Twilio signature validation (optional, recommended)
  // NOTE: Twilio sends X-Twilio-Signature; you can validate with $token if you want.
  // For MVP we skip strict validation.

  $from   = sanitize_text_field($req->get_param('From'));
  $to     = sanitize_text_field($req->get_param('To'));
  $digits = sanitize_text_field($req->get_param('Digits')); // if keypad used
  $speech = sanitize_text_field($req->get_param('SpeechResult')); // when Gather input="speech" returns
  $step   = sanitize_text_field($req->get_param('step') ?: '');

  // STEP 2: After speech gather, send “message” and spend credit
  if ($step === 'gathered') {
    if ($email) {
      $body = "New AI Answering message:\n\nFrom: {$from}\nTo: {$to}\n\nHeard: {$speech}\n\nTime: ".gmdate('c');
      wp_mail($email, 'ProBot Call Message', $body);
    }

    // Spend 1 phone credit (if your license server supports it)
    // Optional: only if not unlimited
    if (empty($lic['unlimited'])) {
      // If your client has a helper to call the license server, use it here.
      // Otherwise, do nothing; server patch below adds /usage/increment with type=voice.
      // Example (pseudo): pbls_increment_usage('voice', 1);
    }

    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Response>
  <Say>Thanks, we've got your message. Someone will follow up shortly. Goodbye.</Say>
  <Hangup/>
</Response>
XML;
    return new \WP_REST_Response($xml, 200, ['Content-Type'=>'text/xml; charset=UTF-8']);
  }

  // STEP 1: Greet + Gather speech
  $gatherUrl = rest_url('pbot/v1/voice/twilio') . '?step=gathered';
  $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Response>
  <Say>Hi! Thanks for calling. Please tell me briefly what you need, and then stop speaking.</Say>
  <Gather input="speech" action="{$gatherUrl}" method="POST" timeout="5" speechTimeout="auto"/>
  <Say>Sorry, I didn't catch that. Please call again.</Say>
</Response>
XML;
  return new \WP_REST_Response($xml, 200, ['Content-Type'=>'text/xml; charset=UTF-8']);
}