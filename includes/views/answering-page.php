<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <h1>ProBot – AI Answering</h1>
  <p class="description">Connect Twilio, verify your Product Key, then set the following webhook URLs on your Twilio number.</p>

  <style>
    .pba-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px}
    .row{display:flex;gap:8px;align-items:center;margin:8px 0}
    .mono{font-family:ui-monospace,Menlo,monospace;background:#f6f7f7;border:1px solid #e2e4e7;border-radius:6px;padding:4px 8px;overflow:auto}
    .copy{margin-left:8px}
    @media (max-width:900px){ .pba-grid{grid-template-columns:1fr} }
  </style>

  <div class="pba-grid">
    <div class="card">
      <h2>Twilio Settings</h2>
      <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
        <?php settings_fields('probot_answering'); ?>
        <table class="form-table" role="presentation"><tbody>
          <tr>
            <th scope="row"><label>Enable AI Answering</label></th>
            <td><label><input type="checkbox" name="pbot_answering_enabled" value="1" <?php checked($enabled,'1'); ?>> Enable</label></td>
          </tr>
          <tr>
            <th scope="row"><label>Account SID</label></th>
            <td><input type="text" name="pbot_twilio_account_sid" class="regular-text" value="<?php echo esc_attr($twilio_sid); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label>Auth Token</label></th>
            <td><input type="password" name="pbot_twilio_auth_token" class="regular-text" value="<?php echo esc_attr($twilio_token); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label>Twilio Number</label></th>
            <td><input type="text" name="pbot_twilio_number" class="regular-text" placeholder="+15551234567" value="<?php echo esc_attr($twilio_num); ?>"></td>
          </tr>
          <tr>
            <th scope="row"><label>Forward To</label></th>
            <td><input type="text" name="pbot_answering_forward_to" class="regular-text" placeholder="+15557654321, +15550001111" value="<?php echo esc_attr($forward_to); ?>"><p class="description">Comma-separated E.164 numbers. Used when caller presses 1.</p></td>
          </tr>
          <tr>
            <th scope="row"><label>Greeting / Prompt</label></th>
            <td><textarea name="pbot_answering_greeting" class="large-text" rows="3"><?php echo esc_textarea($greeting); ?></textarea></td>
          </tr>
          <tr>
            <th scope="row"><label>License Server Base URL</label></th>
            <td><input type="url" name="pbot_license_server_url" class="regular-text code" placeholder="https://example.com/wp-json/pbls/v1" value="<?php echo esc_attr($lic_base); ?>"></td>
          </tr>
        </tbody></table>
        <p><button class="button button-primary">Save Settings</button></p>
      </form>
    </div>

    <div class="card">
      <h2>Webhooks</h2>
      <p>Paste these in Twilio → Phone Numbers → Your Number → <strong>Voice &amp; Fax</strong>:</p>
      <div class="row"><strong>Voice webhook (A CALL COMES IN):</strong></div>
      <div class="row">
        <code class="mono" id="pba-answer-url"><?php echo esc_html($answer_url); ?></code>
        <button class="button copy" data-target="pba-answer-url">Copy</button>
      </div>
      <div class="row">HTTP Method: <code class="mono">POST</code></div>

      <div class="row" style="margin-top:12px;"><strong>Status Callback URL:</strong></div>
      <div class="row">
        <code class="mono" id="pba-status-url"><?php echo esc_html($status_url); ?></code>
        <button class="button copy" data-target="pba-status-url">Copy</button>
      </div>
      <div class="row">HTTP Method: <code class="mono">POST</code></div>

      <hr>
      <h2>License Check</h2>
      <?php if ($lic_base && $pkey = get_option('pbot_product_key','')): ?>
        <?php
          $verify_url = trailingslashit($lic_base). 'keys/verify?key='.rawurlencode($pkey);
          $resp = wp_remote_get($verify_url, ['timeout'=>10]);
          $ok = !is_wp_error($resp) && wp_remote_retrieve_response_code($resp)===200;
          $data = $ok ? json_decode(wp_remote_retrieve_body($resp), true) : null;
        ?>
        <?php if ($ok && !empty($data['ok'])): ?>
          <p>Plan: <strong><?php echo esc_html(strtoupper($data['tier'] ?? 'FREE')); ?></strong> &middot;
             Calls: <strong><?php echo !empty($data['calls']['unlimited'])?'∞': (int)($data['calls']['left'] ?? 0); ?></strong> left this month</p>
        <?php else: ?>
          <p><em>License verify failed.</em></p>
        <?php endif; ?>
      <?php else: ?>
        <p class="description">Enter your License Server URL and Product Key in settings to see status here.</p>
      <?php endif; ?>

      <hr>
      <h2>Quick Test (no charge)</h2>
      <p>Open the Voice webhook URL in your browser (GET) to preview the initial TwiML response.</p>
    </div>
  </div>

  <script>
  (function(){
    document.querySelectorAll('.copy').forEach(btn=>{
      btn.addEventListener('click', e=>{
        e.preventDefault();
        const id=btn.getAttribute('data-target'); const el=document.getElementById(id);
        if(!el) return; navigator.clipboard.writeText(el.textContent.trim()).then(()=>{btn.textContent='Copied!'; setTimeout(()=>btn.textContent='Copy',1200);});
      });
    });
  })();
  </script>
</div>