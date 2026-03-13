<?php
/**
 * ProBot Assistant — Admin: Answering Registration
 * Beta 3 Upgrade: Protocol Bridge + Issue & Answering Configuration Proxy
 */
if (!defined('ABSPATH')) exit;

// ---------- 1. Options Registration ----------
add_action('admin_init', function () {
    // Twilio / Voice Settings
    register_setting('pbot_answering', 'pbot_twilio_sid');
    register_setting('pbot_answering', 'pbot_twilio_token');
    register_setting('pbot_answering', 'pbot_twilio_number');
    register_setting('pbot_answering', 'pbot_answering_enabled', ['type' => 'boolean', 'default' => 0]);
    register_setting('pbot_answering', 'pbot_answering_forward_to');
    register_setting('pbot_answering', 'pbot_answering_notify_email');
    register_setting('pbot_answering', 'pbot_answering_greeting');

    // GitHub Issue Configuration
    register_setting('pbot_answering', 'pbot_gh_issue_proxy_enabled', ['type' => 'boolean', 'default' => 0]);
});

/**
 * 2. SYNC CONFIGURATION TO VPS
 * Pushes answering and issue configurations to the VPS brain dynamically.
 */
function pbot_sync_answering_config_to_vps() {
    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) return;

    $payload = [
        'answering_enabled' => get_option('pbot_answering_enabled'),
        'twilio_number'     => get_option('pbot_twilio_number'),
        'forward_to'        => get_option('pbot_answering_forward_to'),
        'greeting'          => get_option('pbot_answering_greeting'),
        'issue_proxy'       => get_option('pbot_gh_issue_proxy_enabled'),
        'notify_email'      => get_option('pbot_answering_notify_email')
    ];

    wp_remote_post($vps_url . '/secretary/config-sync', [
        'method'    => 'POST',
        'timeout'   => 15,
        'sslverify' => false, // Protocol Bridge: HTTPS -> HTTP
        'headers'   => [
            'Content-Type'      => 'application/json',
            'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
        ],
        'body' => wp_json_encode($payload)
    ]);
}

// Trigger sync when answering settings are updated
add_action('update_option_pbot_answering_enabled', 'pbot_sync_answering_config_to_vps');
add_action('update_option_pbot_gh_issue_proxy_enabled', 'pbot_sync_answering_config_to_vps');

/**
 * 3. REST API: PROXY ENDPOINTS
 */
add_action('rest_api_init', function () {
    // Twilio Voice Webhook
    register_rest_route('pbot/v1', '/voice/twilio', [
        'methods'             => 'POST',
        'callback'            => 'pbot_proxy_twilio_voice',
        'permission_callback' => '__return_true',
    ]);

    // GitHub Issue Webhook Proxy (WP acts as middleman)
    register_rest_route('pbot/v1', '/gh/webhook', [
        'methods'             => 'POST',
        'callback'            => 'pbot_proxy_github_issues',
        'permission_callback' => '__return_true',
    ]);
});

/**
 * TWILIO VOICE PROXY
 * Gathers speech and forwards it to the VPS Chat route with Voice Context.
 */
function pbot_proxy_twilio_voice(\WP_REST_Request $req) {
    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    $enabled = get_option('pbot_answering_enabled');
    $step    = $req->get_param('step') ?: '';

    if (!$enabled || empty($vps_url)) {
        return new \WP_REST_Response('<?xml version="1.0" encoding="UTF-8"?><Response><Say>Service unavailable.</Say></Response>', 200, ['Content-Type' => 'text/xml']);
    }

    // Step 2: Handle gathered speech by proxying to VPS
    if ($step === 'gathered') {
        $speech = $req->get_param('SpeechResult');
        
        $response = wp_remote_post($vps_url . '/secretary/chat', [
            'timeout'   => 30,
            'sslverify' => false,
            'headers'   => [
                'Content-Type'      => 'application/json',
                'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
            ],
            'body' => wp_json_encode([
                'message'      => $speech,
                'is_voice'     => true, // Signal Voice context to ai-service.ts
                'site_context' => 'Inbound Phone Call to ' . get_bloginfo('name')
            ])
        ]);

        $reply = "I'm sorry, I couldn't process your request.";
        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            $reply = $data['reply'] ?? $reply;
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Response><Say>{$reply}</Say><Hangup/></Response>";
        return new \WP_REST_Response($xml, 200, ['Content-Type' => 'text/xml']);
    }

    // Step 1: Initial Greeting
    $greeting = get_option('pbot_answering_greeting', 'Hello. Please state your name and the reason for your call.');
    $gatherUrl = rest_url('pbot/v1/voice/twilio') . '?step=gathered';
    
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Response><Say>{$greeting}</Say><Gather input=\"speech\" action=\"{$gatherUrl}\" method=\"POST\" timeout=\"5\"/></Response>";
    return new \WP_REST_Response($xml, 200, ['Content-Type' => 'text/xml']);
}

/**
 * GITHUB ISSUE PROXY
 * Forwards GitHub Issue events to the VPS Brain for article generation/processing.
 */
function pbot_proxy_github_issues(\WP_REST_Request $req) {
    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) return new \WP_Error('offline', 'VPS Unavailable', ['status' => 503]);

    // Simply forward the entire GitHub payload to the VPS
    $response = wp_remote_post($vps_url . '/secretary/webhook/github', [
        'method'    => 'POST',
        'timeout'   => 30,
        'sslverify' => false,
        'headers'   => [
            'Content-Type'             => 'application/json',
            'X-GitHub-Event'           => $req->get_header('X-GitHub-Event'),
            'X-Hub-Signature-256'      => $req->get_header('X-Hub-Signature-256'),
            'X-Pbot-Secret-Key'        => get_option('pbot_secret_key')
        ],
        'body' => $req->get_body()
    ]);

    if (is_wp_error($response)) {
        return new \WP_REST_Response(['success' => false, 'error' => $response->get_error_message()], 500);
    }

    return new \WP_REST_Response(json_decode(wp_remote_retrieve_body($response)), 200);
}
