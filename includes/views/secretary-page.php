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
        <h1 class="pbot-glitch-title">AI Master Brain</h1>
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
        <div class="pbot-card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 13px; line-height: 1.6;">
                    <li><strong>Link:</strong> Enter your <em>Master Realm Key</em> into the command terminal to synchronize your identity.</li>
                    <li><strong>Training:</strong> Chat naturally. The AI learns your vocabulary and style automatically.</li>
                </ul>
                <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 13px; line-height: 1.6;">
                    <li><strong>Logout:</strong> Type <code>!exit</code> in the chat window to return to standard mode.</li>
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
                    <h2>Identity Mirror Settings</h2>
                </div>
                <div class="pbot-card-body">
                    <table class="form-table">
                        <tr>
                            <th><label for="pbot_vps_url">Brain API (VPS)</label></th>
                            <td>
                                <input type="url" id="pbot_vps_url" class="pbot-auto-text" 
                                       style="width: 100%;" placeholder="http://your-vps-ip:3000"
                                       value="<?php echo esc_attr($vps_url); ?>">
                                <p class="description">The central processing unit for your AI's personality.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_owner_name">Owner Name</label></th>
                            <td>
                                <input type="text" id="pbot_owner_name" class="pbot-auto-text"
                                       style="width: 100%;" value="<?php echo esc_attr($owner_name); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secret_key">Master Realm Key</label></th>
                            <td>
                                <input type="password" id="pbot_secret_key" class="pbot-auto-text"
                                       style="width: 100%;" value="<?php echo esc_attr($secret_key); ?>">
                                <p class="description">Used to authenticate "Mirror Mode" sessions.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="pbot-card" style="margin-bottom: 20px;">
                <div class="pbot-card-header">
                    <h2>GitHub & WordPress Bridge</h2>
                </div>
                <div class="pbot-card-body">
                    <table class="form-table">
                        <tr>
                            <th><label for="pbot_secretary_github_repo">GitHub Repo</label></th>
                            <td>
                                <input type="text" id="pbot_secretary_github_repo" class="pbot-auto-text"
                                       style="width: 100%;" placeholder="username/repo"
                                       value="<?php echo esc_attr($gh_repo); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secretary_wp_user">WP Username</label></th>
                            <td>
                                <input type="text" id="pbot_secretary_wp_user" class="pbot-auto-text"
                                       style="width: 100%;" value="<?php echo esc_attr($wp_user); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="pbot_secretary_wp_pass">Access Token</label></th>
                            <td>
                                <input type="password" id="pbot_secretary_wp_pass" class="pbot-auto-text"
                                       style="width: 100%;" placeholder="Application Password"
                                       value="<?php echo esc_attr($wp_pass); ?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="pbot-card">
                <div class="pbot-card-header">
                    <h2>Manual Personality Notes</h2>
                </div>
                <div class="pbot-card-body">
                    <p class="description" style="margin-bottom: 10px;">Traits the AI has already absorbed or your manual directives.</p>
                    <textarea id="pbot_personality_notes" class="pbot-auto-text" style="width: 100%; max-width: 600px; height: 100px; resize: vertical; padding: 12px; border: 1px solid #d0d7de; border-radius: 6px; font-size: 14px; color: #24292f;" placeholder="e.g. Professional, friendly, expert..."><?php echo esc_textarea(get_option('pbot_personality_notes', '')); ?></textarea>
                    
                    <div style="margin-top: 20px;">
                        <button id="pbot-secretary-save" class="button button-primary" style="background: #238636; border: none;">Update Mirror Profile</button>
                        <span id="pbot-secretary-status" style="margin-left: 10px; font-weight: 600; font-size: 13px;"></span>
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
                    <span style="color: #7ee787; margin-right: 10px; font-weight: bold;">></span>
                    <input type="text" id="pbot-admin-chat-input" style="background: transparent; border: none; color: #f0f6fc; flex-grow: 1; outline: none; box-shadow: none;" placeholder="Command or Mirror Key...">
                    <button id="pbot-admin-chat-send" class="button button-primary" style="margin-left: 10px; background: #238636; border: none;">Execute</button>
                </div>
            </div>

        </div>

    </div>
</div>
