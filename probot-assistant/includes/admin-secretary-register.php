<?php
/**
 * ProBot Assistant — Admin: Secretary Registration & Protocol Bridge
 * Beta 3 Upgrade: Secure VPS Tunneling + Booking Sync Hooks
 */
if (!defined('ABSPATH')) exit;

// ---------- 1. Options Registration ----------
add_action('admin_init', function () {
    register_setting('pbot_secretary', 'pbot_vps_url');
    register_setting('pbot_secretary', 'pbot_owner_name');
    register_setting('pbot_secretary', 'pbot_secret_key');
    register_setting('pbot_secretary', 'pbot_secretary_github_repo');
    register_setting('pbot_secretary', 'pbot_secretary_wp_user');
    register_setting('pbot_secretary', 'pbot_secretary_wp_pass');
    register_setting('pbot_secretary', 'pbot_owner_status', ['type' => 'string', 'default' => 'online']);
});

/**
 * 2c. TOGGLE OWNER STATUS
 * Updates availability and logs to the HUD.
 */
add_action('wp_ajax_pbot_toggle_status', function() {
    $status = sanitize_text_field($_POST['status'] ?? 'online');
    update_option('pbot_owner_status', $status);

    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (!empty($vps_url)) {
        $label = ($status === 'online') ? 'Online' : 'Busy';
        wp_remote_post($vps_url . '/secretary/log-event', [
            'method'    => 'POST',
            'timeout'   => 15,
            'sslverify' => false,
            'headers'   => [
                'Content-Type'      => 'application/json',
                'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
            ],
            'body' => wp_json_encode([
                'msg'          => "🔮 [OWNER STATUS]: {$label} for Readings.",
                'owner_status' => $status
            ])
        ]);
    }
    wp_send_json_success();
});

/**
 * 2. SYNC BOOKING TO HUD
 * Increments Business metrics and logs the new lead.
 * 
 * @param array $data {
 *     @type string $phone   Customer phone number.
 *     @type int    $revenue Estimated revenue from this booking.
 * }
 */
function pbot_sync_booking_to_hud($data) {
    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) return;

    $phone   = $data['phone'] ?? 'Unknown';
    $revenue = (int) ($data['revenue'] ?? 0);
    $service = $data['service'] ?? 'Reading';

    wp_remote_post($vps_url . '/secretary/log-event', [
        'method'    => 'POST',
        'timeout'   => 15,
        'sslverify' => false,
        'headers'   => [
            'Content-Type'      => 'application/json',
            'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
        ],
        'body' => wp_json_encode([
            'msg'        => "📞 [NEW LEAD]: {$phone} booked a {$service} reading.",
            'increments' => ['live_bookings', 'revenue_est']
        ])
    ]);
}

/**
 * 2b. REGISTER LEAD (From Popup or Booking)
 * Specifically for lead generation tracking.
 */
function pbot_register_lead($phone, $source = 'Popup', $service = 'General') {
    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) return;

    wp_remote_post($vps_url . '/secretary/log-event', [
        'method'    => 'POST',
        'timeout'   => 15,
        'sslverify' => false,
        'headers'   => [
            'Content-Type'      => 'application/json',
            'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
        ],
        'body' => wp_json_encode([
            'msg'        => "📞 [NEW LEAD]: {$phone} via {$source} ({$service})",
            'increments' => ['new_leads']
        ])
    ]);
}

/**
 * HUD ACTION HOOKS
 */
add_action('pbot_booking_completed', 'pbot_sync_booking_to_hud');
add_action('pbot_lead_captured',     'pbot_register_lead', 10, 3);

/**
 * AJAX ENDPOINT FOR POPUP
 */
add_action('wp_ajax_pbot_submit_lead', function() {
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $source  = sanitize_text_field($_POST['source'] ?? 'Popup');
    $service = sanitize_text_field($_POST['service'] ?? 'General');

    if (!empty($phone)) {
        pbot_register_lead($phone, $source, $service);
        wp_send_json_success();
    }
    wp_send_json_error();
});
add_action('wp_ajax_nopriv_pbot_submit_lead', function() {
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $source  = sanitize_text_field($_POST['source'] ?? 'Popup');
    $service = sanitize_text_field($_POST['service'] ?? 'General');

    if (!empty($phone)) {
        pbot_register_lead($phone, $source, $service);
        wp_send_json_success();
    }
    wp_send_json_error();
});

/**
 * 3. VPS PROXY & AUTH
 */
add_action('wp_ajax_pbot_check_auth', 'pbot_check_auth_handler');
add_action('wp_ajax_nopriv_pbot_check_auth', 'pbot_check_auth_handler');

function pbot_check_auth_handler() {
    $msg = sanitize_text_field($_POST['message'] ?? '');
    $secret_key = get_option('pbot_secret_key');

    if (!empty($secret_key) && $msg === $secret_key) {
        wp_send_json_success(['is_owner' => true]);
    } else {
        wp_send_json_error(['is_owner' => false]);
    }
}

add_action('wp_ajax_pbot_vps_proxy',        'pbot_handle_vps_proxy');
add_action('wp_ajax_nopriv_pbot_vps_proxy', 'pbot_handle_vps_proxy');
add_action('wp_ajax_pbot_verify_key',        'pbot_handle_vps_proxy');
add_action('wp_ajax_nopriv_pbot_verify_key', 'pbot_handle_vps_proxy');

function pbot_handle_vps_proxy() {
    $nonce = $_POST['nonce'] ?? ($_POST['_wpnonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'probot_nonce') && is_user_logged_in()) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }
    
    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) wp_send_json_error(['message' => 'VPS Offline (No URL)']);

    $secret_key = get_option('pbot_secret_key');
    
    $endpoint = sanitize_text_field($_POST['endpoint'] ?? ($_POST['route'] ?? ''));
    if (strpos($endpoint, '/secretary') === false) {
        $endpoint = '/secretary' . (strpos($endpoint, '/') === 0 ? '' : '/') . $endpoint;
    }

    $payload = isset($_POST['payload']) ? $_POST['payload'] : [];
    if (!is_array($payload)) $payload = [];

    $payload['secret_key']   = $secret_key;
    $payload['repo']         = get_option('pbot_secretary_github_repo');

    $source = sanitize_text_field($_POST['source'] ?? 'frontend');
    $active_session_key = sanitize_text_field($_POST['active_key'] ?? '');
    
    $is_authorized_owner = false;

    if ($source === 'dashboard' && current_user_can('manage_options')) {
        $is_authorized_owner = true;
    } elseif ($active_session_key === $secret_key && !empty($secret_key)) {
        $is_authorized_owner = true;
    } elseif (isset($payload['message']) && trim($payload['message']) === $secret_key && !empty($secret_key)) {
        $is_authorized_owner = true;
    }

    if ($is_authorized_owner) {
        $payload['provided_key'] = $secret_key;
    } else {
        $payload['provided_key'] = 'public_user';
    }

    $args = [
        'timeout'   => 30,
        'sslverify' => false,
        'headers'   => [
            'Content-Type'      => 'application/json',
            'X-Pbot-Secret-Key' => $secret_key
        ]
    ];

    if ($endpoint === '/secretary/status' || $endpoint === '/secretary/personality-status') {
        $response = wp_remote_get($vps_url . $endpoint, $args);
    } else {
        $args['body'] = wp_json_encode($payload);
        $response = wp_remote_post($vps_url . $endpoint, $args);
    }

    if (is_wp_error($response)) {
        update_option('pbot_vps_online', 0);
        wp_send_json_error(['message' => $response->get_error_message(), 'status' => 'offline']);
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    update_option('pbot_vps_online', ($code >= 200 && $code < 300) ? 1 : 0);

    header('Content-Type: application/json');
    echo $body;
    wp_die();
}
