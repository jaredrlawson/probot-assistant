<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap pbot-wrap pbot-knowledge">
  <h1>ProBot Assistant — Knowledge Base</h1>
  <p class="pbot-muted">This is your chatbot’s knowledge base. Feed it data, set the greeting, and tune its intelligence.</p>

  <div class="pbot-settings-layout">
    
    <!-- MAIN COLUMN: Editor & Preview -->
    <div class="pbot-settings-main">
      
      <!-- Manual JSON Editor -->
      <div class="pbot-card">
        <div class="pbot-card-header">
          <h2 style="margin-top:0;">Manual Knowledge (JSON)</h2>
          <p class="pbot-muted">Paste a full JSON object (<code>{ help, intents: [...] }</code>) or just an array (<code>[...]</code>).</p>
        </div>
        <form method="post" action="options.php">
          <?php settings_fields('pbot_responses'); ?>
          <textarea class="pbot-json pbot-mono" name="pbot_manual_intents" spellcheck="false"><?php echo esc_textarea($manual); ?></textarea>
          
          <div class="pbot-actions-bar" style="margin-top: 15px;">
            <?php submit_button('Save Manual JSON', 'primary', 'submit', false); ?>
            <a class="button" href="<?php echo esc_url($preview_url); ?>" target="_blank">Preview (Live URL)</a>
          </div>
        </form>
      </div>

      <!-- Set Greeting -->
      <div class="pbot-card">
        <h2 style="margin-top:0;">Set Greeting</h2>
        <p class="pbot-muted">Updates the <code>__open__</code> intent in your Manual JSON. This is what the bot says when it first opens.</p>
        
        <div style="margin-top: 15px;">
          <textarea id="pbot_greeting_text" class="pbot-mono" style="width:100%; min-height:120px" placeholder="Hi there! I’m your ProBot Assistant..."><?php
            echo esc_textarea(get_option('pbot_greeting_text',''));
          ?></textarea>
          <div class="pbot-actions-bar" style="margin-top: 10px;">
            <button type="button" class="button button-secondary" id="pbot_set_greeting_btn">Update Greeting</button>
          </div>
        </div>
      </div>

      <!-- Current Intents Preview -->
      <div class="pbot-card">
        <h2 style="margin-top:0;">Current Intents Preview</h2>
        <p class="pbot-muted">Inspect the intents currently in use based on your selected source.</p>

        <?php if (!empty($intents_arr)) : ?>
          <div class="pbot-row">
            <label for="pbot-intent-select"><strong>Select Intent</strong></label>
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

          <details class="pbot-details">
            <summary>Show raw JSON</summary>
            <div class="pbot-copy-wrap">
              <button type="button" class="button" id="pbot-copy-json">Copy JSON</button>
            </div>
            <textarea id="pbot-raw-json" class="pbot-json-view" readonly aria-label="Raw intents JSON"><?php
              echo esc_textarea( $current_json !== '' ? $current_json : "{}" );
            ?></textarea>
          </details>
        <?php else: ?>
          <p class="pbot-muted">No intents found. Save Manual JSON or ensure <code>assets/json/intents.json</code> exists.</p>
        <?php endif; ?>
      </div>

      <?php if (function_exists('pbot_render_knowledge_ingestion_section')) pbot_render_knowledge_ingestion_section(); ?>
    </div>

    <!-- SIDEBAR: Settings & Tools -->
    <div class="pbot-settings-sidebar">
      
      <!-- Engine & Source -->
      <div class="pbot-card">
        <h2>Response Engine</h2>
        <form method="post" action="options.php">
          <?php settings_fields('pbot_responses'); ?>
          
          <div class="pbot-row pbot-row-stacked">
            <label><strong>Source Selection</strong></label>
            <label><input type="radio" name="pbot_knowledge_source" value="packaged" <?php checked(get_option('pbot_knowledge_source', 'manual'),'packaged'); ?> /> Packaged JSON</label>
            <label><input type="radio" name="pbot_knowledge_source" value="manual" <?php checked(get_option('pbot_knowledge_source', 'manual'),'manual'); ?> /> Manual JSON</label>
            <label><input type="radio" name="pbot_knowledge_source" value="brain" <?php checked(get_option('pbot_knowledge_source', 'manual'),'brain'); ?> /> <strong>AI Brain Core</strong></label>
          </div>

          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <h3 style="font-size:14px;">Intelligence</h3>
            <div class="pbot-row pbot-row-stacked">
              <label for="pbot_match_threshold">Fuzzy threshold</label>
              <div style="display:flex; align-items:center; gap:10px;">
                <input type="number" id="pbot_match_threshold" name="pbot_match_threshold"
                       class="pbot-num-mid pbot-auto-num"
                       min="0" max="1" step="0.01"
                       value="<?php echo esc_attr($thresh); ?>" />
                <span class="pbot-muted" style="font-size:11px;">0 = loose, 1 = strict</span>
              </div>
            </div>

            <div class="pbot-row pbot-row-stacked" style="margin-top:10px;">
              <label for="pbot_greeting_delay_ms">Greeting delay (ms)</label>
              <input type="number" id="pbot_greeting_delay_ms" name="pbot_greeting_delay_ms"
                     class="pbot-num-mid pbot-auto-num"
                     min="0" step="50"
                     value="<?php echo esc_attr($gDelay); ?>" />
            </div>
          </div>

          <div style="margin-top: 15px;">
            <?php submit_button('Save Settings', 'secondary', 'submit', true, ['style'=>'width:100%; margin:0;']); ?>
          </div>
        </form>
      </div>

      <!-- Quick Add -->
      <div class="pbot-card">
        <h2 style="margin-top:0;">Quick Add Q/A</h2>
        <p class="pbot-muted">Instantly add a new response to your Manual JSON.</p>
        
        <div class="pbot-row pbot-row-stacked" style="margin-top:15px;">
          <label for="pbot_quick_question"><strong>Question</strong></label>
          <input type="text" id="pbot_quick_question" style="width:100%" placeholder="e.g. hours, pricing">
        </div>
        
        <div class="pbot-row pbot-row-stacked" style="margin-top:10px;">
          <label for="pbot_quick_answer"><strong>Answer</strong></label>
          <textarea id="pbot_quick_answer" class="pbot-mono" style="width:100%; min-height:80px" placeholder="Type the reply..."></textarea>
        </div>

        <div class="pbot-row" style="margin-top:10px;">
          <label><input type="checkbox" id="pbot_quick_make_greeting" value="1"> Make greeting</label>
        </div>

        <div class="pbot-actions-bar" style="margin-top: 15px;">
          <button type="button" class="button button-primary" id="pbot_quick_add_btn" style="width:100%">Add to Knowledge</button>
        </div>
      </div>

      <!-- Utilities -->
      <div class="pbot-card">
        <h2>Backup & Export</h2>
        <div class="pbot-actions-bar">
          <a class="button" href="<?php echo esc_url($export_url); ?>" style="width:100%">Export Current JSON</a>
        </div>
        
        <h3 style="margin-top:18px; font-size:14px;">Import JSON</h3>
        <form method="post" enctype="multipart/form-data">
          <?php wp_nonce_field('pbot_import_json', 'pbot_import_json_nonce'); ?>
          <input type="file" name="pbot_import_file" accept=".json,application/json,text/plain" style="width:100%; margin-bottom:10px;" />
          <button type="submit" class="button button-secondary" style="width:100%">Import Knowledge</button>
        </form>
      </div>

      <!-- Creative Status (Matches Settings) -->
      <div class="pbot-card">
        <div style="font-size:13px; color: #57606a;">
          <strong>Brain Core:</strong> Llama 3.3 70B<br>
          <strong>Status:</strong> <span style="color: #238636;">● Creative Suite Active</span>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
jQuery(function($){
  const ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;
  const nonce   = <?php echo wp_json_encode($nonce); ?>;

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
      ta.select(); document.execCommand('copy');
      notice('notice-success','JSON copied to clipboard.');
    }
  });

  // Quick Add
  $('#pbot_quick_add_btn').on('click', function(){
    const q = $('#pbot_quick_question').val().trim();
    const a = $('#pbot_quick_answer').val().trim();
    const g = $('#pbot_quick_make_greeting').is(':checked') ? 1 : 0;
    if (!q && !g) { notice('notice-error','Please enter a question or check “Make greeting”.'); return; }
    if (!a)       { notice('notice-error','Please enter an answer.'); return; }
    
    const $btn = $(this);
    $btn.prop('disabled', true).text('Adding...');
    
    $.post(ajaxUrl, { action:'probot_add_manual_intent', _wpnonce:nonce, question:q, answer:a, make_greeting:g })
      .done(r => {
        if (r && r.success) {
          notice('notice-success','Saved! Source switched to Manual.');
          $('#pbot_quick_question').val('');
          $('#pbot_quick_answer').val('');
        } else {
          notice('notice-error','Could not save.');
        }
      })
      .fail(() => notice('notice-error','AJAX error.'))
      .always(() => $btn.prop('disabled', false).text('Add to Knowledge'));
  });

  // Set Greeting
  $('#pbot_set_greeting_btn').on('click', function(){
    const gtxt = $('#pbot_greeting_text').val().trim();
    if (!gtxt){ notice('notice-error','Please enter a greeting.'); return; }
    
    const $btn = $(this);
    $btn.prop('disabled', true).text('Updating...');

    $.post(ajaxUrl, { action:'probot_set_greeting_intent', _wpnonce:nonce, greeting:gtxt })
      .done(r => r && r.success ? notice('notice-success','Greeting updated in Manual JSON.') : notice('notice-error','Unable to update.'))
      .fail(() => notice('notice-error','AJAX error.'))
      .always(() => $btn.prop('disabled', false).text('Update Greeting'));
  });
});
</script>