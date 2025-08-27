<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Knowledge Base Page (drop-in)
// - Source switch (packaged/manual)
// - Manual JSON editor + Import/Export
// - Intents preview dropdown
// - Quick Add Q/A (AJAX) + Set Greeting (AJAX)
// - FIX: Raw JSON viewer wraps lines (no horizontal scroll) + Copy JSON button
// ---------------------------------------------

if (!defined('ABSPATH')) exit;

/** Register settings for the Knowledge Base screen */
add_action('admin_init', function () {
  register_setting('pbot_responses', 'pbot_intents_source'); // packaged|manual
  register_setting('pbot_responses', 'pbot_manual_intents', [
    'type'              => 'string',
    'sanitize_callback' => function($v){ return is_string($v) ? $v : ''; },
  ]);
});

/** Utility to get current JSON string (shared with main file shape) */
function probot_admin_get_current_intents_json_for_admin_page() {
  $source = get_option('pbot_intents_source', 'packaged');
  $manual = trim(get_option('pbot_manual_intents', ''));
  if ($source === 'manual' && $manual !== '') return $manual;
  $file = plugin_dir_path(PROBOT_FILE) . 'assets/json/intents.json';
  return file_exists($file) ? file_get_contents($file) : '';
}

/** Render page */
function probot_render_responses_page() {
  if (!current_user_can('manage_options')) return;

  // --- Import
  if (isset($_POST['pbot_import_json_nonce']) && wp_verify_nonce($_POST['pbot_import_json_nonce'], 'pbot_import_json')) {
    $notice = '';
    if (!empty($_FILES['pbot_import_file']['name'])) {
      $file = $_FILES['pbot_import_file'];
      $move = wp_handle_upload($file, ['test_form' => false, 'mimes' => ['json'=>'application/json','txt'=>'text/plain']]);
      if (!isset($move['error']) && isset($move['file'])) {
        $contents = file_get_contents($move['file']);
        if ($contents !== false) {
          update_option('pbot_manual_intents', $contents);
          update_option('pbot_intents_source', 'manual');
          $notice = 'Imported JSON and switched source to Manual.';
        } else {
          $notice = 'Unable to read uploaded file.';
        }
        @unlink($move['file']);
      } else {
        $notice = isset($move['error']) ? $move['error'] : 'Upload failed.';
      }
    } else {
      $notice = 'No file selected.';
    }
    if ($notice) echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($notice) . '</p></div>';
  }

  // --- Export
  if (isset($_GET['pbot_export']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'pbot_export_json')) {
    $json = probot_admin_get_current_intents_json_for_admin_page();
    if ($json === '') $json = '{}';
    nocache_headers();
    header('Content-Description: File Transfer');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=probot-intents.json');
    header('Content-Length: ' . strlen($json));
    echo $json;
    exit;
  }

  $source = get_option('pbot_intents_source', 'packaged');
  $manual = get_option('pbot_manual_intents', '');
  $current_json = probot_admin_get_current_intents_json_for_admin_page();

  // Parse for dropdown preview
  $intents_arr = [];
  if ($current_json) {
    $decoded = json_decode($current_json, true);
    if (is_array($decoded)) {
      if (isset($decoded['intents']) && is_array($decoded['intents'])) {
        $intents_arr = $decoded['intents'];
      } elseif (isset($decoded[0])) {
        $intents_arr = $decoded;
      }
    }
  }

  $ajax_url = admin_url('admin-ajax.php');
  $nonce    = wp_create_nonce('probot_nonce');
  ?>
  <div class="wrap pbot-wrap">
    <h1>ProBot Assistant — Knowledge Base</h1>
    <p class="pbot-muted">This is your chatbot’s knowledge base. Upload a JSON, paste it manually, or add Q/A below. You can also set the greeting (trigger <code>__open__</code>).</p>

    <!-- Source switch + Manual editor -->
    <div class="pbot-card">
      <form method="post" action="options.php">
        <?php settings_fields('pbot_responses'); ?>
        <div class="pbot-row">
          <label><strong>Response Source</strong></label>
          <label>
            <input type="radio" name="pbot_intents_source" value="packaged" <?php checked($source,'packaged'); ?> />
            Packaged JSON (<?php echo esc_html(plugins_url('assets/json/intents.json', PROBOT_FILE)); ?>)
          </label>
          <label>
            <input type="radio" name="pbot_intents_source" value="manual" <?php checked($source,'manual'); ?> />
            Manual (use the JSON below)
          </label>
        </div>

        <p class="pbot-muted">When set to “Manual”, the front‑end loads JSON from Admin‑AJAX. When “Packaged”, it uses the <code>assets/json/intents.json</code> shipped with this plugin.</p>

        <h2 style="margin-top:18px;">Manual JSON</h2>
        <p class="pbot-muted">Paste a full JSON object (<code>{ help, intents: [...] }</code>) or just an array (<code>[...]</code>).</p>
        <textarea class="pbot-json pbot-mono" name="pbot_manual_intents" spellcheck="false"><?php echo esc_textarea($manual); ?></textarea>

        <div class="pbot-actions-bar">
          <?php submit_button('Save Responses', 'primary', 'submit', false); ?>
          <a class="button" href="<?php echo esc_url(add_query_arg(['action'=>'probot_intents'], $ajax_url)); ?>" target="_blank">Preview JSON (front‑end URL)</a>
          <a class="button" href="<?php echo esc_url(wp_nonce_url(add_query_arg('pbot_export', '1'), 'pbot_export_json')); ?>">Export Current JSON</a>
        </div>
      </form>
    </div>

    <!-- Set Greeting (own block) -->
    <div class="pbot-card">
      <h2 style="margin-top:0;">Set Greeting</h2>
      <p class="pbot-muted">Updates/creates the <code>__open__</code> intent in Manual JSON.</p>
      <div class="pbot-row">
        <textarea id="pbot_greeting_text" class="pbot-mono" style="width:min(520px,100%);min-height:120px" placeholder="Hi there! I’m your ProBot Assistant..."><?php
          echo esc_textarea(get_option('pbot_greeting_text',''));
        ?></textarea>
      </div>
      <div class="pbot-actions-bar">
        <button type="button" class="button" id="pbot_set_greeting_btn">Make it the greeting</button>
      </div>
    </div>

    <!-- Quick Add -->
    <div class="pbot-card">
      <h2 style="margin-top:0;">Quick Add</h2>
      <div class="pbot-kb-cols">
        <div class="pbot-kb-col">
          <h3 style="margin:0 0 8px;">Add a Q/A</h3>
          <p class="pbot-muted">Adds one intent to Manual JSON and switches source to Manual.</p>
          <div class="pbot-row">
            <label for="pbot_quick_question" style="min-width:110px"><strong>Question</strong></label>
            <input type="text" id="pbot_quick_question" class="regular-text" placeholder="e.g. hours, pricing, refund policy" maxlength="140" style="width:min(520px,100%)">
          </div>
          <div class="pbot-row">
            <label for="pbot_quick_answer" style="min-width:110px"><strong>Answer</strong></label>
            <textarea id="pbot_quick_answer" class="pbot-mono" style="width:min(520px,100%);min-height:120px" placeholder="Type the bot’s reply..."></textarea>
          </div>
          <div class="pbot-row">
            <label><input type="checkbox" id="pbot_quick_make_greeting" value="1"> Make this the greeting</label>
          </div>
          <div class="pbot-actions-bar">
            <button type="button" class="button button-primary" id="pbot_quick_add_btn">Add to Manual</button>
          </div>
        </div>

        <div class="pbot-kb-col">
          <h3 style="margin:0 0 8px;">Tips</h3>
          <p class="pbot-muted">Use short, natural triggers (e.g., “hours”, “pricing”, “refund”). Keep answers scannable. Include an <code>unknown</code> catch‑all.</p>
        </div>
      </div>
    </div>

    <!-- Current Intents Preview -->
    <div class="pbot-card">
      <h2 style="margin-top:0;">Current Intents Preview</h2>
      <p class="pbot-muted">Preview the intents currently in use (based on the selected source).</p>

      <?php if (!empty($intents_arr)) : ?>
        <div class="pbot-row">
          <label for="pbot-intent-select"><strong>Intents</strong></label>
          <select id="pbot-intent-select" class="pbot-select" data-intents="<?php echo esc_attr( wp_json_encode($intents_arr) ); ?>">
            <?php foreach ($intents_arr as $idx => $it) :
              $id = isset($it['id']) ? $it['id'] : ('intent_'.$idx);
              $tr = isset($it['triggers']) && is_array($it['triggers']) && !empty($it['triggers']) ? $it['triggers'][0] : '';
              ?>
              <option value="<?php echo esc_attr($idx); ?>">
                <?php echo esc_html($id . ($tr ? " — {$tr}" : '')); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="pbot-preview">
          <label><strong>Response</strong></label>
          <textarea id="pbot-intent-response" readonly class="pbot-mono"></textarea>
        </div>

        <!-- RAW JSON (wrapped, vertical-only scroll) -->
        <details class="pbot-details">
          <summary>Show raw JSON</summary>

          <div class="pbot-copy-wrap">
            <button type="button" class="button" id="pbot-copy-json">Copy JSON</button>
          </div>

          <textarea id="pbot-raw-json"
                    class="pbot-json-view"
                    readonly
                    aria-label="Raw intents JSON"><?php
            echo esc_textarea( $current_json !== '' ? $current_json : "{}" );
          ?></textarea>
        </details>
      <?php else: ?>
        <p class="pbot-muted">No intents found. Save Manual JSON or ensure the packaged <code>assets/json/intents.json</code> exists.</p>
      <?php endif; ?>
    </div>

    <!-- Import JSON -->
    <div class="pbot-card">
      <h2 style="margin-top:0;">Import JSON</h2>
      <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('pbot_import_json', 'pbot_import_json_nonce'); ?>
        <div class="pbot-row">
          <input type="file" name="pbot_import_file" accept=".json,application/json,text/plain" />
          <button type="submit" class="button button-secondary">Import &amp; Switch to Manual</button>
        </div>
        <p class="pbot-muted">This will store the file contents in “Manual JSON” above and set the source to Manual.</p>
      </form>
    </div>
  </div>

  <!-- AJAX + Copy helpers -->
  <script>
    jQuery(function($){
      const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
      const nonce   = <?php echo wp_json_encode(wp_create_nonce('probot_nonce')); ?>;

      function notice(kind, msg){
        const $n = $('<div class="notice is-dismissible"><p></p></div>').addClass(kind);
        $n.find('p').text(msg);
        $('.wrap').first().find('h1').after($n);
        setTimeout(()=>{ $n.fadeOut(200, ()=> $n.remove()); }, 4000);
      }

      // Intent preview
      (function(){
        const sel  = document.getElementById('pbot-intent-select');
        const out  = document.getElementById('pbot-intent-response');
        if (sel && out){
          const data = JSON.parse(sel.getAttribute('data-intents') || '[]');
          const render = () => {
            const i = parseInt(sel.value, 10);
            if (!isNaN(i) && data[i]) {
              const r = data[i].response || data[i].reply || '';
              out.value = (typeof r === 'string') ? r : JSON.stringify(r, null, 2);
            } else { out.value = ''; }
          };
          sel.addEventListener('change', render); render();
        }
      })();

      // Copy JSON
      $('#pbot-copy-json').on('click', async function(){
        const ta = document.getElementById('pbot-raw-json'); if (!ta) return;
        try {
          await navigator.clipboard.writeText(ta.value);
          notice('notice-success','JSON copied to clipboard.');
        } catch(e){
          ta.select(); document.execCommand('copy'); notice('notice-success','JSON copied to clipboard.');
        }
      });

      // Quick Add
      $('#pbot_quick_add_btn').on('click', function(){
        const q = $('#pbot_quick_question').val().trim();
        const a = $('#pbot_quick_answer').val().trim();
        const g = $('#pbot_quick_make_greeting').is(':checked') ? 1 : 0;
        if (!q && !g) { notice('notice-error','Please enter a question or check “Make this the greeting”.'); return; }
        if (!a)       { notice('notice-error','Please enter an answer.'); return; }
        $.post(ajaxUrl, { action:'probot_add_manual_intent', _wpnonce:nonce, question:q, answer:a, make_greeting:g })
          .done(r => r && r.success ? notice('notice-success','Saved! Source switched to Manual.') : notice('notice-error','Could not save.'))
          .fail(() => notice('notice-error','AJAX error. Please try again.'));
      });

      // Set Greeting
      $('#pbot_set_greeting_btn').on('click', function(){
        const gtxt = $('#pbot_greeting_text').val().trim();
        if (!gtxt){ notice('notice-error','Please enter a greeting.'); return; }
        $.post(ajaxUrl, { action:'probot_set_greeting_intent', _wpnonce:nonce, greeting:gtxt })
          .done(r => r && r.success ? notice('notice-success','Greeting saved to Manual JSON.') : notice('notice-error','Unable to save greeting.'))
          .fail(() => notice('notice-error','AJAX error. Please try again.'));
      });
    });
  </script>
  <?php
}