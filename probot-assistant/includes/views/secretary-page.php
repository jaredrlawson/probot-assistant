<?php
/**
 * ProBot Assistant — Admin View: Secretary Dashboard
 * Beta 3 Upgrade: Two-Column Pinned HUD & Terminal
 */
if (!defined('ABSPATH')) exit;

$vps_url    = get_option('pbot_vps_url', '');
$secret_key = get_option('pbot_secret_key', '');
$owner_name = get_option('pbot_owner_name', 'The Admin');
$gh_repo    = get_option('pbot_secretary_github_repo', '');
$wp_user    = get_option('pbot_secretary_wp_user', '');
$wp_pass    = get_option('pbot_secretary_wp_pass', '');
$is_online  = get_option('pbot_vps_online', 0);
?>

<div class="wrap probot-admin-wrap pbot-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="pbot-glitch-title">Secretary Master Brain</h1>
        
        <div style="display: flex; align-items: center; gap: 15px;">
            <!-- Status Toggle -->
            <?php $owner_status = get_option('pbot_owner_status', 'online'); ?>
            <div id="pbot-owner-status-wrap" style="display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #d0d7de; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                <span>🔮 Alchemist:</span>
                <select id="pbot-owner-status-toggle" style="border: none; background: transparent; font-weight: 700; color: <?php echo ($owner_status === 'online') ? '#238636' : '#cf222e'; ?>; cursor: pointer; outline: none; padding: 0;">
                    <option value="online" <?php selected($owner_status, 'online'); ?>>Online</option>
                    <option value="busy" <?php selected($owner_status, 'busy'); ?>>Busy</option>
                </select>
            </div>

            <div id="pbot-hud-status" style="display: flex; align-items: center; gap: 12px; padding: 10px 20px; background: #fff; border: 1px solid #d0d7de; border-radius: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-right: 0;">
            <div id="pbot-terminal-pulse" class="pbot-status-pulse <?php echo $is_online ? 'is-active' : 'is-offline'; ?>"></div>
            <span id="pbot-connection-status" style="font-weight: 600; color: <?php echo $is_online ? '#238636' : '#cf222e'; ?>;">
                <?php echo $is_online ? 'Mirror Mode Active' : 'Offline'; ?>
            </span>
        </div>
    </div>

    <!-- Assistant Tips (Prominent Top Position) -->
    <div class="pbot-card" style="background: #fff8e1; border-color: #ffe082; margin-bottom: 25px;">
        <div class="pbot-card-header" style="border-bottom-color: #ffd54f; margin-bottom: 10px;">
            <h2 style="color: #856404; font-size: 1.1em; margin: 0;">💡 Quick Start: Mastering the Mirror</h2>
        </div>
        <div class="pbot-card-body" style="font-size: 13px; color: #856404; line-height: 1.6;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                    <li><strong>Dashboard Terminal:</strong> This terminal is permanently locked into <em>Mirror Mode</em> for you.</li>
                    <li><strong>Frontend Activation:</strong> Type your <em>Master Realm Key</em> into the website widget to enable Creator Mode.</li>
                </ul>
                <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                    <li><strong>Exit Mirror:</strong> Type <code>!exit</code> or <code>logout</code> in the widget to return to standard mode.</li>
                    <li><strong>Locking Traits:</strong> Save specific behaviors manually in the <em>Identity Mirror Settings</em> below.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="pbot-dashboard-grid" style="display: grid; grid-template-columns: 1fr 450px; gap: 20px; align-items: start;">
        
        <!-- LEFT COLUMN: Main Configuration -->
        <div class="pbot-main-panel">
            
            <div class="pbot-card" style="margin-bottom: 20px;">
                <div class="pbot-card-header">
                    <h2>Identity Access</h2>
                </div>
                <div class="pbot-card-body">
                    <table class="form-table">
                        <tr>
                            <th><label for="pbot_vps_url">Brain Endpoint</label></th>
                            <td>
                                <input type="url" id="pbot_vps_url" class="pbot-auto-text" 
                                       value="<?php echo esc_url($vps_url); ?>" placeholder="http://endpoint:3000">
                                <p class="description">Secure gateway to the Llama 3.1 core.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_owner_name">Identity Owner</label></th>
                            <td>
                                <input type="text" id="pbot_owner_name" class="pbot-auto-text" 
                                       value="<?php echo esc_attr($owner_name); ?>" placeholder="Your Name">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secret_key">Master Realm Key</label></th>
                            <td>
                                <input type="password" id="pbot_secret_key" class="pbot-auto-text" 
                                       value="<?php echo esc_attr($secret_key); ?>" placeholder="Secret Key">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secretary_github_repo">Sync Repository</label></th>
                            <td>
                                <input type="text" id="pbot_secretary_github_repo" class="pbot-auto-text" 
                                       value="<?php echo esc_attr($gh_repo); ?>" placeholder="username/repo">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secretary_wp_user">Access User</label></th>
                            <td>
                                <input type="text" id="pbot_secretary_wp_user" class="pbot-auto-text" 
                                       value="<?php echo esc_attr($wp_user); ?>" placeholder="WP Username">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secretary_wp_pass">Access Token</label></th>
                            <td>
                                <input type="password" id="pbot_secretary_wp_pass" class="pbot-auto-text" 
                                       value="<?php echo esc_attr($wp_pass); ?>" placeholder="Application Password">
                                <p class="description">Used for article drafting and content mirroring.</p>
                            </td>
                        </tr>
                    </table>

                    <div style="padding-top: 20px; margin-top: 20px; border-top: 1px solid #d0d7de; display: flex; align-items: center; gap: 15px;">
                        <button type="button" id="pbot-save-settings-btn" class="button button-primary">Save Identity Access</button>
                        <button type="button" id="pbot-register-btn" class="button button-secondary">Force Link Handshake</button>
                        <span id="pbot-status-msg" style="font-size: 13px; font-weight: 600;"></span>
                    </div>
                </div>
            </div>

            <!-- Identity Mirror Settings -->
            <div class="pbot-card">
                <div class="pbot-card-header">
                    <h2>Identity Mirror Settings</h2>
                </div>
                <div class="pbot-card-body">
                    
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 13px; margin-bottom: 10px; color: #57606a;">PERSONALITY TRAITS</h3>
                        <textarea id="pbot_personality_notes" class="pbot-auto-text" style="width: 100%; max-width: 600px; height: 100px; resize: vertical; padding: 12px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; color: #24292f;" placeholder="e.g. Professional, friendly, expert..."><?php echo esc_textarea(get_option('pbot_personality_notes', '')); ?></textarea>
                        <p class="description" style="margin-top: 5px;">Define the core identity of the assistant. (Saved automatically when clicking 'Save Identity Access' above).</p>
                    </div>

                    <div style="margin-top: 20px; font-size: 13px; color: #57606a; line-height: 1.6;">
                        <strong>Mirroring Active:</strong> Your assistant is currently syncing traits from this dashboard's identity fields. Ensure "Access Token" is a valid WordPress Application Password for full capability.
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Sidebar (Terminal) -->
        <div class="pbot-sidebar" style="position: sticky; top: 32px; align-self: start;">

            <!-- Synaptic Memory (NEW: Space Filler) -->
            <div class="pbot-card" style="background: #0d1117; color: #c9d1d9; border: 1px solid #30363d; margin-bottom: 20px;">
                <div class="pbot-card-header" style="border-bottom-color: #30363d; display: flex; align-items: center; justify-content: space-between;">
                    <h2 style="color: #58a6ff; font-size: 14px; display: flex; align-items: center; gap: 8px; margin: 0;">🧠 Synaptic Memory</h2>
                    <div class="pbot-status-pulse is-active" style="width: 6px; height: 6px;"></div>
                </div>
                <div class="pbot-card-body" style="padding-top: 15px;">
                    <div style="font-size: 12px; color: #8b949e; margin-bottom: 12px; text-transform: uppercase; font-weight: 600;">Recently Learned Traits:</div>
                    <div id="pbot-learned-traits-hud" style="font-family: monospace; font-size: 11px; line-height: 1.8;">
                        <div style="color: #7ee787;">+ "Prefers concise, blunt responses"</div>
                        <div style="color: #7ee787;">+ "Adopted mystical terminology"</div>
                        <div style="color: #7ee787;">+ "Remembered owner's secondary cell"</div>
                        <div style="color: #8b949e; font-style: italic; margin-top: 10px;">Awaiting new neural patterns...</div>
                    </div>
                </div>
            </div>

            <!-- Terminal Interface -->
            <div class="pbot-card" style="background: #0d1117; color: #c9d1d9; border: none; border-radius: 8px; overflow: hidden; padding: 0;">
                <div style="background: #161b22; padding: 10px 20px; border-bottom: 1px solid #30363d; font-family: monospace; font-size: 12px; color: #8b949e; display: flex; justify-content: space-between; align-items: center;">
                    <span>COMMAND TERMINAL: ID_MIRROR_BRIDGE</span>
                    <div id="pbot-terminal-pulse-terminal" class="pbot-status-pulse <?php echo $is_online ? 'is-active' : 'is-offline'; ?>" style="width: 8px; height: 8px;"></div>
                </div>
                <div id="pbot-admin-chat-log" style="height: 400px; overflow-y: auto; padding: 10px 4px; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6;">
                    <div style="color: #8b949e;">[SYSTEM]: Connection established. System ready.</div>
                </div>
                <div class="pbot-terminal-input-wrap" style="display: flex; align-items: center; background: #0d1117; padding: 15px; border-top: 1px solid #30363d;">
                    <span style="color: #58a6ff; margin-right: 10px; font-weight: bold;">></span>
                    <input type="text" id="pbot-admin-chat-input" style="background: transparent; border: none; color: #f0f6fc; flex-grow: 1; outline: none; box-shadow: none;" placeholder="Command or Mirror Key...">
                    <button id="pbot-admin-chat-send" class="button button-primary" style="margin-left: 10px; background: #238636; border: none;">Execute</button>
                </div>
            </div>

        </div>

    </div>
</div>
