<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap probot-admin-wrap pbot-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="pbot-glitch-title">AI Answering Dashboard</h1>
        <div id="pbot-hud-status" style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; background: #fff; border: 1px solid #d0d7de; border-radius: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-right: 0;">
            <div id="pbot-terminal-pulse" class="pbot-status-pulse <?php echo $enabled ? 'is-active' : 'is-offline'; ?>"></div>
            <span id="pbot-connection-status" style="font-weight: 600; color: <?php echo $enabled ? '#238636' : '#cf222e'; ?>;">
                <?php echo $enabled ? 'Service Active' : 'Service Disabled'; ?>
            </span>
        </div>
    </div>

    <div class="pbot-dashboard-grid" style="display: grid; grid-template-columns: 1fr 450px; gap: 20px; align-items: start;">
        
        <!-- LEFT COLUMN: Settings & Webhooks -->
        <div class="pbot-main-panel">

            <!-- Twilio Settings -->
            <div class="pbot-card" style="margin-bottom: 20px;">
                <div class="pbot-card-header">
                    <h2>Twilio Settings</h2>
                </div>
                <div class="pbot-card-body">
                    <form method="post" action="options.php">
                        <?php settings_fields('pbot_answering'); ?>
                        
                        <div class="pbot-row pbot-row-stacked" style="margin-bottom: 15px;">
                            <label><input type="checkbox" name="pbot_answering_enabled" value="1" <?php checked($enabled,'1'); ?>> <strong>Enable AI Answering</strong></label>
                        </div>

                        <div class="pbot-row pbot-row-stacked" style="margin-bottom: 12px;">
                            <label>Account SID</label>
                            <input type="text" name="pbot_twilio_sid" style="width: 100%;" value="<?php echo esc_attr($twilio_sid); ?>">
                        </div>

                        <div class="pbot-row pbot-row-stacked" style="margin-bottom: 12px;">
                            <label>Auth Token</label>
                            <input type="password" name="pbot_twilio_token" style="width: 100%;" value="<?php echo esc_attr($twilio_token); ?>">
                        </div>

                        <div class="pbot-row pbot-row-stacked" style="margin-bottom: 12px;">
                            <label>Twilio Number</label>
                            <input type="text" name="pbot_twilio_number" style="width: 100%;" placeholder="+15551234567" value="<?php echo esc_attr($twilio_num); ?>">
                        </div>

                        <div class="pbot-row pbot-row-stacked" style="margin-bottom: 12px;">
                            <label>Forward To</label>
                            <input type="text" name="pbot_answering_forward_to" style="width: 100%;" value="<?php echo esc_attr($forward_to); ?>">
                        </div>

                        <div class="pbot-row pbot-row-stacked" style="margin-bottom: 15px;">
                            <label>Greeting / Prompt</label>
                            <textarea name="pbot_answering_greeting" style="width: 100%; height: 80px;"><?php echo esc_textarea($greeting); ?></textarea>
                        </div>

                        <?php submit_button('Save Config', 'primary', 'submit', true, ['style' => 'width:100%;']); ?>
                    </form>
                </div>
            </div>

            <!-- Webhooks -->
            <div class="pbot-card" style="margin-bottom: 20px; background: #f6f8fa;">
                <div class="pbot-card-header">
                    <h2>Webhooks</h2>
                </div>
                <div class="pbot-card-body" style="font-size: 12px;">
                    <p>Set these in Twilio for <strong>Voice & Fax</strong>:</p>
                    
                    <div style="margin-bottom: 10px;">
                        <strong>Incoming Call:</strong><br>
                        <code style="display:block; margin: 5px 0; padding: 5px; background: #fff; border: 1px solid #d0d7de;" id="pba-answer-url"><?php echo esc_html($answer_url); ?></code>
                        <button class="button button-small copy" data-target="pba-answer-url">Copy URL</button>
                    </div>

                    <div>
                        <strong>Status Callback:</strong><br>
                        <code style="display:block; margin: 5px 0; padding: 5px; background: #fff; border: 1px solid #d0d7de;" id="pba-status-url"><?php echo esc_html($status_url); ?></code>
                        <button class="button button-small copy" data-target="pba-status-url">Copy URL</button>
                    </div>
                </div>
            </div>

            <!-- License Info -->
            <div class="pbot-card" style="background: #fff8e1; border-color: #ffe082;">
                <div class="pbot-card-body" style="font-size: 12px; color: #856404;">
                    <strong>AI Shield Status:</strong> Active<br>
                    <strong>Model:</strong> Llama 3.1 8b<br>
                    <strong>Protocol:</strong> Secure Proxy
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Main Activity HUD -->
        <div class="pbot-sidebar" style="position: sticky; top: 32px; align-self: start;">
            
            <!-- Alchemist Business HUD (New Leads & Revenue) -->
            <div class="pbot-card" style="background: #0d1117; border-color: #30363d; margin-bottom: 20px;">
                <div class="pbot-card-header" style="border-bottom-color: #30363d;">
                    <h2 style="color: #58a6ff; display: flex; align-items: center; gap: 8px;">📈 Alchemist Business HUD</h2>
                </div>
                <div class="pbot-card-body" style="padding-top: 10px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
                        <div style="background: #161b22; padding: 10px; border-radius: 6px; border: 1px solid #30363d; text-align: center;">
                            <div style="color: #8b949e; font-size: 11px; text-transform: uppercase;">👤 New Leads</div>
                            <div id="pbot-stat-leads" style="font-size: 20px; font-weight: bold; color: #7ee787;">0</div>
                        </div>
                        <div style="background: #161b22; padding: 10px; border-radius: 6px; border: 1px solid #30363d; text-align: center;">
                            <div style="color: #8b949e; font-size: 11px; text-transform: uppercase;">📅 Bookings</div>
                            <div id="pbot-stat-bookings" style="font-size: 20px; font-weight: bold; color: #58a6ff;">0</div>
                        </div>
                        <div style="background: #161b22; padding: 10px; border-radius: 6px; border: 1px solid #30363d; text-align: center;">
                            <div style="color: #8b949e; font-size: 11px; text-transform: uppercase;">💬 AI Handled</div>
                            <div id="pbot-stat-chats" style="font-size: 20px; font-weight: bold; color: #d299ff;">0</div>
                        </div>
                        <div style="background: #161b22; padding: 10px; border-radius: 6px; border: 1px solid #30363d; text-align: center;">
                            <div style="color: #8b949e; font-size: 11px; text-transform: uppercase;">💰 Revenue Est.</div>
                            <div id="pbot-stat-revenue" style="font-size: 20px; font-weight: bold; color: #aff5b4;">$0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pbot-card" style="margin-bottom: 20px; padding: 0; overflow: hidden; background: #0d1117; border-color: #30363d;">
                <div class="pbot-card-header" style="padding: 10px 12px; border-bottom: 1px solid #30363d; background: #161b22;">
                    <h2 style="margin: 0; color: #c9d1d9; font-size: 14px;">📩 Recent Texts</h2>
                </div>
                <div id="pbot-hud-texts" style="min-height: 120px; background: #0d1117; color: #c9d1d9; font-family: 'Courier New', monospace; padding: 10px 5px; font-size: 13px; line-height: 1.6; text-align: left; overflow-y: auto;">
                    <span style="color: #8b949e; font-style: italic;">Monitoring for incoming SMS...</span>
                </div>
            </div>

            <div class="pbot-card" style="margin-bottom: 20px; padding: 0; overflow: hidden; background: #0d1117; border-color: #30363d;">
                <div class="pbot-card-header" style="padding: 10px 12px; border-bottom: 1px solid #30363d; background: #161b22;">
                    <h2 style="margin: 0; color: #c9d1d9; font-size: 14px;">📞 Recent Calls</h2>
                </div>
                <div id="pbot-hud-calls" style="min-height: 120px; background: #0d1117; color: #c9d1d9; font-family: 'Courier New', monospace; padding: 10px 5px; font-size: 13px; line-height: 1.6; text-align: left; overflow-y: auto;">
                    <span style="color: #8b949e; font-style: italic;">Monitoring for voice interactions...</span>
                </div>
            </div>

            <div class="pbot-card" style="padding: 0; overflow: hidden; background: #0d1117; border-color: #30363d;">
                <div class="pbot-card-header" style="padding: 10px 12px; border-bottom: 1px solid #30363d; background: #161b22;">
                    <h2 style="margin: 0; color: #c9d1d9; font-size: 14px;">🧠 AI Activity Logs</h2>
                </div>
                <div id="pbot-hud-activity" style="min-height: 150px; background: #0d1117; color: #c9d1d9; font-family: 'Courier New', monospace; padding: 10px 5px; font-size: 13px; line-height: 1.6; text-align: left; overflow-y: auto;">
                    <span style="color: #8b949e;">[SYSTEM]: AI Shield initialized and standing by.</span>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
jQuery(function($){
    // Copy to clipboard helper
    $('.copy').on('click', function(e){
        e.preventDefault();
        const id = $(this).data('target');
        const el = document.getElementById(id);
        if(!el) return;
        navigator.clipboard.writeText(el.textContent.trim()).then(() => {
            const $btn = $(this);
            const oldText = $btn.text();
            $btn.text('Copied!');
            setTimeout(() => $btn.text(oldText), 1200);
        });
    });
});
</script>
