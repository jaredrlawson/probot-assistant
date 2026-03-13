<?php
/**
 * ProBot Assistant — Admin: Article Writer Registration
 * Beta 3 Upgrade: REST API Draft Receiver + Article Generation Proxy
 */
if (!defined('ABSPATH')) exit;

// ---------- 1. Options Registration ----------
add_action('admin_init', function () {
    // Membership / Tier Settings
    register_setting('pbot_writer_settings', 'pbot_membership_tier', [
        'type'              => 'string',
        'sanitize_callback' => function($v){
            $v = is_string($v) ? strtolower(trim($v)) : 'free';
            return in_array($v, ['free','starter','pro'], true) ? $v : 'free';
        },
        'default'           => 'free',
    ]);

    // Writer Toggles & Config
    register_setting('pbot_writer_settings', 'pbot_writer_enabled',       ['type'=>'integer','default'=>0]);
    register_setting('pbot_writer_settings', 'pbot_writer_min_words',     ['type'=>'integer','default'=>800]);
    register_setting('pbot_writer_settings', 'pbot_writer_max_words',     ['type'=>'integer','default'=>1000]);
    register_setting('pbot_writer_settings', 'pbot_writer_ai_category',   ['type'=>'integer','default'=>1]);
    register_setting('pbot_writer_settings', 'pbot_writer_schedule',      ['type'=>'string', 'default'=>'monthly']);
    register_setting('pbot_writer_settings', 'pbot_writer_monthly_limit', ['type'=>'integer','default'=>1]);
});

/**
 * 2. REST API: DRAFT RECEIVER
 * Endpoint for the VPS (wordpress-service.ts) to push generated content.
 * URL: yoursite.com/wp-json/pbot/v1/article/receive
 */
add_action('rest_api_init', function () {
    register_rest_route('pbot/v1', '/article/receive', [
        'methods'             => 'POST',
        'callback'            => 'pbot_handle_incoming_vps_draft',
        'permission_callback' => '__return_true', // Verified via Secret Key header
    ]);
});

function pbot_handle_incoming_vps_draft(\WP_REST_Request $req) {
    $provided_key = $req->get_header('X-Pbot-Secret-Key');
    $master_key   = get_option('pbot_secret_key');

    if (empty($provided_key) || $provided_key !== $master_key) {
        return new \WP_Error('forbidden', 'Invalid Secret Key', ['status' => 403]);
    }

    $params  = $req->get_json_params();
    $title   = sanitize_text_field($params['title']   ?? 'New AI Draft');
    $content = wp_kses_post($params['content']         ?? '');
    $excerpt = sanitize_textarea_field($params['excerpt'] ?? '');

    if (empty($content)) {
        return new \WP_Error('missing_content', 'No content provided', ['status' => 400]);
    }

    // Identify the Owner User ID
    $owner_user = get_user_by('login', get_option('pbot_secretary_wp_user'));
    $author_id  = $owner_user ? $owner_user->ID : get_current_user_id();

    $post_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_status'  => 'draft',
        'post_author'  => $author_id,
        'post_type'    => 'post',
    ]);

    if (is_wp_error($post_id)) {
        return new \WP_Error('post_error', $post_id->get_error_message(), ['status' => 500]);
    }

    return new \WP_REST_Response([
        'success' => true,
        'post_id' => $post_id,
        'message' => 'Draft saved for ' . get_option('pbot_owner_name')
    ], 200);
}

/**
 * 3. TRIGGER GENERATION AJAX
 * Proxies the "Generate Now" command to the VPS Brain.
 */
add_action('wp_ajax_pbot_trigger_article_generation', 'pbot_handle_article_trigger');
function pbot_handle_article_trigger() {
    check_ajax_referer('probot_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error(['message' => 'Unauthorized'], 403);

    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) wp_send_json_error(['message' => 'Brain Endpoint not configured']);

    // Capture UI inputs
    $payload = [
        'title'    => sanitize_text_field($_POST['title']    ?? ''),
        'context'  => sanitize_textarea_field($_POST['context']  ?? ''),
        'length'   => (int)($_POST['length']   ?? 1000),
        'keywords' => sanitize_text_field($_POST['keywords'] ?? ''),
        'tone'     => sanitize_text_field($_POST['tone']     ?? 'professional'),
        'headings' => sanitize_textarea_field($_POST['headings'] ?? ''),
        'category' => (int)($_POST['category'] ?? 0)
    ];

    // Proxy to VPS /secretary/generate-article
    $response = wp_remote_post($vps_url . '/secretary/generate-article', [
        'method'    => 'POST',
        'timeout'   => 60, // Article generation is heavy
        'sslverify' => false, // Protocol Bridge: HTTPS -> HTTP
        'headers'   => [
            'Content-Type'      => 'application/json',
            'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
        ],
        'body' => wp_json_encode($payload)
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()]);
    }

    $status = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status >= 200 && $status < 300) {
        wp_send_json_success($body);
    } else {
        $error = !empty($body['error']) ? $body['error'] : 'VPS Error ' . $status;
        wp_send_json_error(['message' => $error]);
    }
}
