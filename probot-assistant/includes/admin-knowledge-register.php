<?php
/**
 * ProBot Assistant — Admin: Knowledge Base Registration
 * Beta 3 Upgrade: RAG Knowledge Ingestion + Proxy Bridge
 */
if (!defined('ABSPATH')) exit;

// ---------- 1. Options Registration ----------
add_action('admin_init', function () {
    // Response Source Toggles
    register_setting('pbot_responses', 'pbot_knowledge_source'); // manual | brain
    register_setting('pbot_responses', 'pbot_intents_source');    // packaged | manual

    // Matching & Timing
    register_setting('pbot_responses', 'pbot_match_threshold', [
        'type'=>'number',
        'sanitize_callback'=>function($v){ $v = (float)$v; if($v<0)$v=0; if($v>1)$v=1; return $v; },
        'default'=>0.52
    ]);
    register_setting('pbot_responses', 'pbot_greeting_delay_ms', [
        'type'=>'integer',
        'sanitize_callback'=>function($v){ $v = (int)$v; return max(0,$v); },
        'default'=>2200
    ]);

    // Local JSON Storage
    register_setting('pbot_responses', 'pbot_manual_intents', [
        'type'              => 'string',
        'sanitize_callback' => function($v){ return is_string($v) ? $v : ''; },
        'default'           => '',
    ]);

    // RAG Knowledge Ingestion Storage
    register_setting('pbot_responses', 'pbot_knowledge_ingestion_text', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ]);
});

/**
 * 2. KNOWLEDGE INGESTION UI (Hookable Section)
 * Renders the RAG Ingestion interface for the Brain.
 */
function pbot_render_knowledge_ingestion_section() {
    $ingestion_text = get_option('pbot_knowledge_ingestion_text', '');
    ?>
    <div class="pbot-card" style="margin-top: 20px;">
        <div class="pbot-card-header">
            <h2>Knowledge Ingestion (RAG)</h2>
            <p class="description">Feed raw data directly into the AI Brain's long-term memory.</p>
        </div>
        <div class="pbot-card-body">
            <div class="pbot-row">
                <label for="pbot_knowledge_ingestion_text"><strong>Raw Knowledge Data</strong></label>
                <textarea id="pbot_knowledge_ingestion_text" name="pbot_knowledge_ingestion_text" 
                          class="pbot-json-view" style="height: 200px; margin-top: 10px;" 
                          placeholder="Paste articles, company facts, or specific instructions here..."><?php echo esc_textarea($ingestion_text); ?></textarea>
            </div>
            <div class="pbot-actions-bar" style="margin-top: 15px; display: flex; align-items: center; gap: 15px;">
                <button type="button" id="pbot-sync-knowledge-btn" class="button button-primary">Sync to Brain</button>
                <span id="pbot-sync-status" style="font-weight: 600; font-size: 13px;"></span>
            </div>
        </div>
    </div>

    <script>
    jQuery(function($) {
        $('#pbot-sync-knowledge-btn').on('click', function() {
            const $btn = $(this);
            const $status = $('#pbot-sync-status');
            const text = $('#pbot_knowledge_ingestion_text').val().trim();

            if (!text) {
                $status.text('⚠️ Please enter knowledge data.').css('color', '#d73a49');
                return;
            }

            $btn.disabled = true;
            $status.text('📡 Absorbing knowledge...').css('color', '#0969da');

            $.post(window.ajaxurl, {
                action: 'pbot_sync_knowledge',
                nonce: '<?php echo wp_create_nonce("probot_nonce"); ?>',
                knowledge_data: text
            }, function(res) {
                if (res.success) {
                    $status.text('✅ Knowledge Absorbed.').css('color', '#238636');
                } else {
                    $status.text('❌ Sync Failed: ' + (res.data?.message || 'Unknown error')).css('color', '#cf222e');
                }
            }).fail(function() {
                $status.text('❌ Connection Error.').css('color', '#cf222e');
            }).always(function() {
                $btn.disabled = false;
            });
        });
    });
    </script>
    <?php
}

/**
 * 3. KNOWLEDGE SYNC AJAX PROXY
 * Forwards raw knowledge data to the VPS /knowledge/train endpoint.
 */
add_action('wp_ajax_pbot_sync_knowledge', 'pbot_handle_knowledge_sync');
function pbot_handle_knowledge_sync() {
    check_ajax_referer('probot_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized'], 403);

    $knowledge_data = isset($_POST['knowledge_data']) ? wp_unslash($_POST['knowledge_data']) : '';
    if (empty($knowledge_data)) wp_send_json_error(['message' => 'No data provided']);

    // Persist locally first
    update_option('pbot_knowledge_ingestion_text', $knowledge_data);

    $vps_url = untrailingslashit(get_option('pbot_vps_url'));
    if (empty($vps_url)) wp_send_json_error(['message' => 'Brain Endpoint not configured']);

    // Proxy to VPS /secretary/knowledge/train
    $response = wp_remote_post($vps_url . '/secretary/knowledge/train', [
        'method'    => 'POST',
        'timeout'   => 45, // Training can take longer
        'sslverify' => false, // Protocol Bridge: HTTPS -> HTTP
        'headers'   => [
            'Content-Type'      => 'application/json',
            'X-Pbot-Secret-Key' => get_option('pbot_secret_key')
        ],
        'body' => wp_json_encode([
            'knowledge' => $knowledge_data,
            'repo'      => get_option('pbot_secretary_github_repo'),
            'timestamp' => current_time('mysql')
        ])
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
