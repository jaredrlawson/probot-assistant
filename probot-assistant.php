<?php
/**
 * Plugin Name: ProBot Assistant
 * Description: Front-end chat assistant with teaser, JSON intents (packaged or manual), fuzzy matching, and admin Knowledge Base manager.
 * Version: 1.6.0-beta.3
 * Author: Jared Я Lawson
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) exit;

define('PROBOT_FILE', __FILE__);
define('PROBOT_PATH', plugin_dir_path(__FILE__));
define('PROBOT_URL',  plugin_dir_url(__FILE__));

/* --------------------------------------------------------------------------
 * Version helpers (supports alpha/beta/rc with numbers, e.g. 1.6.0-beta.1)
 * ----------------------------------------------------------------------- */
if ( ! defined('PROBOT_VERSION') ) {
  $data = get_file_data(__FILE__, ['Version' => 'Version'], false);
  define('PROBOT_VERSION', $data['Version']); // e.g. "1.6.0-beta.1"
}

if ( ! function_exists('pbot_version_display') ) {
  function pbot_version_display(){
    // Strip prerelease suffix for the plain version
    return preg_replace('/-(alpha|beta|rc)(?:\.\d+)?$/i', '', PROBOT_VERSION);
  }
}
if ( ! function_exists('pbot_version_phase') ) {
  // Returns 'alpha' | 'beta' | 'rc' | 'stable'
  function pbot_version_phase(){
    if (preg_match('/-alpha(?:\.\d+)?$/i', PROBOT_VERSION)) return 'alpha';
    if (preg_match('/-beta(?:\.\d+)?$/i',  PROBOT_VERSION)) return 'beta';
    if (preg_match('/-rc(?:\.\d+)?$/i',    PROBOT_VERSION)) return 'rc';
    return 'stable';
  }
}
if ( ! function_exists('pbot_version_prerelease') ) {
  // Returns normalized label like "Beta 1", "RC 2", or '' for stable
  function pbot_version_prerelease(){
    if (preg_match('/-(alpha|beta|rc)(?:\.(\d+))?$/i', PROBOT_VERSION, $m)) {
      $type = ucfirst(strtolower($m[1]));         // Alpha/Beta/RC
      $n    = isset($m[2]) ? (int)$m[2] : 0;      // 1,2,... (optional)
      return $n ? "{$type} {$n}" : $type;
    }
    return '';
  }
}
if ( ! function_exists('pbot_version_is_beta') ) {
  function pbot_version_is_beta(){ return pbot_version_phase() === 'beta'; }
}

/* Plugins list: show plain version, and a short status badge */
add_filter('all_plugins', function($plugins){
  $base = plugin_basename(__FILE__);
  if (isset($plugins[$base])) $plugins[$base]['Version'] = pbot_version_display();
  return $plugins;
});
add_filter('plugin_row_meta', function($meta, $file){
  if ($file !== plugin_basename(__FILE__)) return $meta;
  $phase = pbot_version_phase();
  $label = ($phase === 'stable') ? 'Stable' : strtoupper($phase); // Alpha/Beta/RC
  $color = ($phase === 'alpha') ? '#6f42c1' : (($phase === 'beta') ? '#d97b00' : (($phase === 'rc') ? '#0a7ea4' : '#22863a'));
  $meta[] = sprintf(
    '<span class="pbot-badge is-%s" style="background:%s;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;line-height:1.6;">%s</span>',
    esc_attr($phase), esc_attr($color), esc_html($label)
  );
  return $meta;
}, 10, 2);

/* --------------------------------------------------------------------------
 * Helpers
 * ----------------------------------------------------------------------- */
if ( ! function_exists( 'probot_asst_asset_ver' ) ) {
  function probot_asst_asset_ver( $rel ) {
    $abs = PROBOT_PATH . ltrim( $rel, '/' );
    $ver = @filemtime( $abs );
    if ( ! $ver ) { $ver = time(); }
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { $ver .= '.dev'; }
    return (string) $ver;
  }
}
function pbot_rel($p){ return ltrim($p,'/'); }
function pbot_url($rel){ return plugins_url( pbot_rel($rel), PROBOT_FILE ); }
function pbot_path($rel){ return PROBOT_PATH . pbot_rel($rel); }

/* --------------------------------------------------------------------------
 * Activation defaults
 * ----------------------------------------------------------------------- */
register_activation_hook(PROBOT_FILE, function () {
  add_option('pbot_brand_title',       'ProBot Assistant');
  add_option('pbot_bubble_icon',        'original');
  add_option('pbot_bubble_position',   'right');
  add_option('pbot_pulse_enabled',     1);
  add_option('pbot_teaser_enabled',  1);
  add_option('pbot_sound_enabled',   1);
  add_option('pbot_reply_sound',     'mystical-chime');
  add_option('pbot_intents_source',    'packaged');
  add_option('pbot_manual_intents',    '');
  add_option('pbot_match_threshold',   0.52);
  add_option('pbot_greeting_delay_ms', 2200);

  // Teaser controls
  add_option('pbot_teaser_message',     '');
  add_option('pbot_teaser_duration_ms', 4500);
  add_option('pbot_teaser_show_count',  3);

  // Colors
  add_option('pbot_brand_color', '#444444');
  add_option('pbot_halo_color',  'rgba(255,255,255,.7)');
  add_option('pbot_panel_color', '#ffffff');
  add_option('pbot_panel_radius', 16);

  // Toast colors
  add_option('pbot_toast_bg_color',   'rgba(18,18,18,.92)');
  add_option('pbot_toast_text_color', '#ffffff');

  // Intensities
  add_option('pbot_halo_intensity',  0.70);
  add_option('pbot_pulse_intensity', 1.00);

  // Button borders (Close/Minimize + Send)
  add_option('pbot_btn_border_enabled', 0);
  add_option('pbot_btn_border_weight',  1);
  add_option('pbot_btn_border_color',   '#d0d0d0');

  add_option('pbot_send_border_enabled', 0);
  add_option('pbot_send_border_weight',  1);
  add_option('pbot_send_border_color',   '#d0d0d0');

  // Article Writer scaffold
  add_option('pbot_writer_enabled', 0);
  add_option('pbot_writer_min_words', 800);
  add_option('pbot_writer_max_words', 1000);
  add_option('pbot_writer_ai_category', 1);
  add_option('pbot_writer_schedule', 'monthly');
  add_option('pbot_writer_monthly_limit', 1);

  // Keys
  add_option('pbot_openai_api_key', '');
  add_option('pbot_product_key',    '');
  add_option('pbot_greeting_text',  '');

  // New Beta 3 Connection Defaults
  add_option('pbot_vps_url', 'http://localhost:3000');
  add_option('pbot_vps_online', 0);
  add_option('pbot_secret_key', '');
  add_option('pbot_owner_name', 'The Admin');

  // Trigger Deep Scan on Activation
  add_option('pbot_activation_scan_pending', '1');
});

/* --------------------------------------------------------------------------
 * Front-end assets + Protocol Bridge Config
 * ----------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
  $css_rel = 'assets/frontend/probot-assistant.css';
  $js_rel  = 'assets/frontend/probot-assistant.js';
  
  // Hard version bust for Beta 3 Cross-Browser Sync
  $ver = probot_asst_asset_ver($js_rel) . '.1615'; 

  wp_enqueue_style('probot-assistant-css', pbot_url($css_rel), array(), $ver);
  wp_enqueue_script('probot-assistant-js', pbot_url($js_rel), array('jquery'), $ver, true);

  // Determine JSON source from unified Beta 3 toggle
  $kb_source   = get_option('pbot_knowledge_source', 'manual');
  $manual_data = trim(get_option('pbot_manual_intents', ''));
  $packagedURL = pbot_url('assets/json/intents.json');
  
  // If set to Brain, we still load the Packaged JSON for the greeting/tour
  // If set to Manual, we load from AJAX
  $intents_url = ($kb_source === 'manual' && $manual_data !== '')
    ? add_query_arg(array('action'=>'probot_intents'), admin_url('admin-ajax.php'))
    : $packagedURL;

  $toastMsg  = get_option('pbot_teaser_message', '');
  $toastDur  = (int) get_option('pbot_teaser_duration_ms', 4500);
  $toastShow = (int) get_option('pbot_teaser_show_count', 3);

  wp_localize_script('probot-assistant-js', 'pbot_cfg', array(
    'ajax_url'           => admin_url('admin-ajax.php'),
    'plugin_url'         => pbot_url(''),
    'nonce'              => wp_create_nonce('probot_nonce'),
    'brand_title'        => get_option('pbot_brand_title', 'ProBot Assistant'),
    'bubble_icon'        => get_option('pbot_bubble_icon', 'original'),
    'bubble_position'    => get_option('pbot_bubble_position', 'right'),
    'pulse_enabled'      => (bool) get_option('pbot_pulse_enabled', 1),
    'teaser_enabled'     => (bool) get_option('pbot_teaser_enabled', 1),
    'sound_enabled'      => (bool) get_option('pbot_sound_enabled', 1),
    'reply_sound'        =>         get_option('pbot_reply_sound', 'mystical-chime'),
    'intents_url'        => esc_url_raw($intents_url),
    'match_threshold'    => (float) get_option('pbot_match_threshold', 0.52),
    'greeting_delay_ms'  => (int) get_option('pbot_greeting_delay_ms', 2200),
    'cache_ver'          => $ver,

    // Teaser config
    'teaser_message'     => $toastMsg,
    'teaser_duration_ms' => $toastDur,
    'teaser_show_count'  => $toastShow,

    // Toast colors
    'teaser_bg_color'    => get_option('pbot_toast_bg_color', '#121212'),
    'teaser_bg_opacity'  => (float) get_option('pbot_toast_bg_opacity', 0.92),
    'teaser_text_color'  => get_option('pbot_toast_text_color', '#ffffff'),

    // Colors
    'brand_color'        => get_option('pbot_brand_color', '#444444'),
    'halo_color'         => get_option('pbot_halo_color', '#ffffff'),
    'panel_color'        => get_option('pbot_panel_color', '#ffffff'),
    'panel_radius'       => (int) get_option('pbot_panel_radius', 16),
    'send_bg_color'      => get_option('pbot_send_bg_color', '#ffffff'),
    'send_hover_color'   => get_option('pbot_send_hover_color', '#f7f7f7'),

    // Intensities
    'halo_intensity'     => (float) get_option('pbot_halo_intensity', 0.70),
    'pulse_intensity'    => (float) get_option('pbot_pulse_intensity', 1.00),

    // Button borders
    'btn_border_enabled' => (int)   get_option('pbot_btn_border_enabled', 0),
    'btn_border_weight'  => (float) get_option('pbot_btn_border_weight',  1),
    'btn_border_color'   =>          get_option('pbot_btn_border_color',   '#d0d0d0'),

    'send_border_enabled'=> (int)   get_option('pbot_send_border_enabled', 0),
    'send_border_weight' => (float) get_option('pbot_send_border_weight',  1),
    'send_border_color'  =>          get_option('pbot_send_border_color',   '#d0d0d0'),

    // Mirror Bridge Context (Beta 3)
    'response_engine'    => $kb_source,
    'owner_name'         => get_option('pbot_owner_name', 'The Admin'),
    'personality_notes'  => get_option('pbot_personality_notes', ''),
  ));
});

/* --------------------------------------------------------------------------
 * Admin assets
 * ----------------------------------------------------------------------- */
add_action('admin_enqueue_scripts', function($hook){
  // Load our admin CSS/JS on ProBot admin pages and on Plugins list (so badges style correctly)
  $is_probot_screen = (strpos($hook, 'probot-assistant') !== false);
  $is_plugins_list  = in_array($hook, array('plugins.php','plugin-install.php'), true);

  if ( ! $is_probot_screen && ! $is_plugins_list ) return;

  $ver = probot_asst_asset_ver('assets/admin/probot-admin.js') . '.1615';

  wp_enqueue_style('pbot-admin', pbot_url('assets/admin/probot-admin.css'), array(), $ver);
  if ( $is_probot_screen ) {
    wp_enqueue_script('pbot-admin', pbot_url('assets/admin/probot-admin.js'), array('jquery'), $ver, true);
    
    // Admin Bridge Data
    wp_localize_script('pbot-admin', 'pbotData', array(
        'ajax_url'   => admin_url('admin-ajax.php'),
        'plugin_url' => pbot_url(''),
        'nonce'      => wp_create_nonce('probot_nonce'),
        'secret_key' => get_option('pbot_secret_key', ''),
        'is_online'  => get_option('pbot_vps_online', 0),
        'status'     => get_option('pbot_owner_status', 'online')
    ));
  }
});

/* --------------------------------------------------------------------------
 * Admin notices
 * ----------------------------------------------------------------------- */
add_action('admin_notices', function () {
  if (!current_user_can('manage_options') || !is_admin()) return;
  $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
  $is_settings = ($page === 'probot-assistant');
  $is_kb       = ($page === 'probot-assistant-knowledge');
  $was_updated = ((isset($_GET['settings-updated']) && $_GET['settings-updated']) || (isset($_GET['updated']) && $_GET['updated']));
  if (($is_settings || $is_kb) && $was_updated) {
    $msg = $is_settings ? 'Settings saved.' : 'Responses saved.';
    echo '<div class="notice notice-success is-dismissible"><p><strong>ProBot Assistant:</strong> ' . esc_html($msg) . '</p></div>';
  }
}, 2);

add_action('admin_notices', function(){
  if (!current_user_can('manage_options') || !is_admin()) return;
  
  if (get_option('pbot_activation_scan_pending')) {
      echo '<div class="notice notice-info is-dismissible" id="pbot-scanning-notice"><p>🤖 <strong>ProBot Assistant:</strong> Performing an initial deep scan of your website content for the AI Brain...</p></div>';
      ?>
      <script>
      jQuery(function($) {
          async function performBackendDeepScan() {
              try {
                  const postRes = await fetch('<?php echo esc_url(rest_url('wp/v2/posts?per_page=5&_fields=title')); ?>');
                  const pageRes = await fetch('<?php echo esc_url(rest_url('wp/v2/pages?search=about,booking,service&_fields=title,content')); ?>');
                  
                  let scanData = "INITIAL DEEP SCAN LOG:\n\n";
                  
                  if (postRes.ok) {
                      const posts = await postRes.json();
                      if (posts && posts.length) {
                          scanData += "RECENT POSTS:\n" + posts.map(p => p.title.rendered).join("\n") + "\n\n";
                      }
                  }

                  if (pageRes.ok) {
                      const pages = await pageRes.json();
                      if (pages && pages.length) {
                          scanData += "CORE PAGES:\n" + pages.map(p => {
                              // Strip tags safely
                              let tmp = document.createElement("DIV");
                              tmp.innerHTML = p.content.rendered;
                              let content = tmp.textContent || tmp.innerText || "";
                              return p.title.rendered + ": " + content.replace(/\s+/g, ' ').substring(0, 500);
                          }).join("\n\n") + "\n\n";
                      }
                  }

                  // Send to VPS via proxy
                  $.post(ajaxurl, {
                      action: 'pbot_vps_proxy',
                      nonce: '<?php echo wp_create_nonce("probot_nonce"); ?>',
                      endpoint: '/knowledge/train',
                      payload: { knowledge: scanData }
                  });

                  // Tell WP to remove the flag
                  $.post(ajaxurl, {
                      action: 'pbot_complete_activation_scan',
                      nonce: '<?php echo wp_create_nonce("probot_nonce"); ?>'
                  }, function() {
                      $('#pbot-scanning-notice p').html('✅ <strong>ProBot Assistant:</strong> Deep scan complete. Brain initialized.');
                  });

              } catch (e) {
                  console.error("ProBot Scan Error:", e);
              }
          }
          performBackendDeepScan();
      });
      </script>
      <?php
  }

  $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
  if ($page !== 'probot-assistant' && $page !== 'probot-assistant-knowledge') return;
  $source = get_option('pbot_intents_source', 'packaged');
  $manual = trim(get_option('pbot_manual_intents', ''));
  if ($source === 'packaged' && $manual === '') {
    $kb_url = admin_url('admin.php?page=probot-assistant-knowledge');
    echo '<div class="notice notice-warning is-dismissible"><p><strong>ProBot Assistant:</strong> You\'re using the stock intents. <a href="' . esc_url($kb_url) . '">Open the Knowledge Base</a> to upload your JSON or start adding Q&amp;A manually.</p></div>';
  }
}, 3);

/* --------------------------------------------------------------------------
 * JSON AJAX + helpers
 * ----------------------------------------------------------------------- */
add_action('wp_ajax_pbot_complete_activation_scan', function() {
    check_ajax_referer('probot_nonce', 'nonce');
    if (current_user_can('manage_options')) {
        delete_option('pbot_activation_scan_pending');
        wp_send_json_success();
    }
});

add_action('wp_ajax_probot_intents',        'probot_intents_json');
add_action('wp_ajax_nopriv_probot_intents', 'probot_intents_json');

function probot_intents_json() {
  $json = probot_get_current_intents_json();
  if ($json === '') $json = wp_json_encode(array('version'=>'1.5.0','brand'=>'ProBot Assistant','help'=>''));
  nocache_headers();
  header('Content-Type: application/json; charset=utf-8');
  echo $json;
  wp_die();
}
function probot_get_current_intents_json(){
  $source = get_option('pbot_intents_source', 'packaged');
  $manual = trim(get_option('pbot_manual_intents', ''));
  if ($source === 'manual' && $manual !== '') return $manual;
  $file = pbot_path('assets/json/intents.json');
  return file_exists($file) ? file_get_contents($file) : '';
}

/* --------------------------------------------------------------------------
 * Manual intents add / set greeting
 * ----------------------------------------------------------------------- */
function probot_normalize_intents_array_from_json($json){
  $out = array('help'=>'', 'intents'=>array());
  if (!$json) return $out;
  $data = json_decode($json, true);
  if (is_array($data)) {
    if (isset($data['intents']) && is_array($data['intents'])) {
      $out['help']    = isset($data['help']) && is_string($data['help']) ? $data['help'] : '';
      $out['intents'] = $data['intents'];
    } elseif (isset($data[0])) {
      $out['intents'] = $data;
    }
  }
  return $out;
}
function probot_save_manual_intents_array($arr){
  if (!isset($arr['intents']) || !is_array($arr['intents'])) $arr = array('help'=>'','intents'=>array());
  update_option('pbot_intents_source','manual');
  update_option('pbot_manual_intents', wp_json_encode($arr));
}
add_action('wp_ajax_probot_add_manual_intent', function(){
  if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
  check_ajax_referer('probot_nonce');
  $q = isset($_POST['question']) ? sanitize_text_field(wp_unslash($_POST['question'])) : '';
  $a = isset($_POST['answer'])   ? wp_kses_post(wp_unslash($_POST['answer'])) : '';
  $g = !empty($_POST['make_greeting']) ? 1 : 0;
  if ($q === '' && !$g) wp_send_json_error('missing_question', 400);
  if ($a === '')        wp_send_json_error('missing_answer', 400);
  $current = probot_normalize_intents_array_from_json( get_option('pbot_manual_intents','') );
  if ($g) {
    $found = false;
    foreach ($current['intents'] as &$it) {
      $tr = isset($it['triggers']) && is_array($it['triggers']) ? $it['triggers'] : array();
      if (in_array('__open__', $tr, true)) { $it['response'] = $a; $found = true; break; }
    }
    if (!$found) $current['intents'][] = array('triggers'=>array('__open__'),'response'=>$a);
  } else {
    $current['intents'][] = array('triggers'=>array($q),'response'=>$a);
  }
  probot_save_manual_intents_array($current);
  wp_send_json_success(array('ok'=>true));
});
add_action('wp_ajax_probot_set_greeting_intent', function(){
  if (!current_user_can('manage_options')) wp_send_json_error('forbidden', 403);
  check_ajax_referer('probot_nonce');
  $greet = isset($_POST['greeting']) ? wp_kses_post(wp_unslash($_POST['greeting'])) : '';
  if ($greet === '') wp_send_json_error('empty', 400);
  $current = probot_normalize_intents_array_from_json( get_option('pbot_manual_intents','') );
  $found = false;
  foreach ($current['intents'] as &$it) {
    $tr = isset($it['triggers']) && is_array($it['triggers']) ? $it['triggers'] : array();
    if (in_array('__open__', $tr, true)) { $it['response'] = $greet; $found = true; break; }
  }
  if (!$found) $current['intents'][] = array('triggers'=>array('__open__'),'response'=>$greet);
  probot_save_manual_intents_array($current);
  wp_send_json_success(array('ok'=>true));
});

/* --------------------------------------------------------------------------
 * Register extra settings keys (misc)
 * ----------------------------------------------------------------------- */
add_action('admin_init', function () {
  register_setting('pbot_settings', 'pbot_openai_api_key');
  register_setting('pbot_settings', 'pbot_product_key');
  register_setting('pbot_settings', 'pbot_greeting_text');
});

/* --------------------------------------------------------------------------
 * Menus + includes
 * ----------------------------------------------------------------------- */
add_action('admin_menu', function () {
  // Main Menu - points to Dashboard (Secretary HUD)
  add_menu_page('ProBot Assistant','ProBot Assistant','manage_options','probot-assistant','pbot_render_secretary_page','dashicons-format-chat',58);
  
  // Submenus
  add_submenu_page('probot-assistant','Dashboard','Dashboard','manage_options','probot-assistant','pbot_render_secretary_page');
  add_submenu_page('probot-assistant','UI Settings','UI Settings','manage_options','probot-assistant-settings','probot_render_settings_page');
  add_submenu_page('probot-assistant','Knowledge Base','Knowledge Base','manage_options','probot-assistant-knowledge','probot_render_responses_page');
  add_submenu_page('probot-assistant','AI Answering','AI Answering','manage_options','probot-assistant-answering','probot_render_answering_page');
  add_submenu_page('probot-assistant','Article Writer','Article Writer','manage_options','probot-assistant-writer','probot_render_article_writer_page');
});

/* Back-compat: old slug redirect */
add_action('admin_init', function () {
  if (!is_admin() || !current_user_can('manage_options')) return;
  if (isset($_GET['page']) && $_GET['page'] === 'probot-assistant-responses') {
    wp_safe_redirect( add_query_arg('page','probot-assistant-knowledge', admin_url('admin.php')) ); exit;
  }
});

/* --------------------------------------------------------------------------
 * Optional GitHub self-updater (drop-in)
 * ----------------------------------------------------------------------- */
if ( is_admin() ) {
  add_action('admin_init', function () {
    if ( ! current_user_can('update_plugins') ) return;
    $updater_file = pbot_path('includes/class-pbot-self-updater.php');
    if ( ! file_exists($updater_file) ) return;
    require_once $updater_file;
    if ( ! class_exists('PBot_Self_Updater') ) return;
    if ( isset($GLOBALS['pbot_updater']) && $GLOBALS['pbot_updater'] instanceof PBot_Self_Updater ) return;

    $GLOBALS['pbot_updater'] = new PBot_Self_Updater([
      'file' => PROBOT_FILE,
      'slug' => plugin_basename(PROBOT_FILE),
      'user' => 'jaredrlawson',
      'repo' => 'probot-assistant',
      'include_prereleases' => true, // allow -beta.1 etc
    ]);
  }, 1);
}

/** SECRETARY (Dashboard & Terminal) */
require_once pbot_path('includes/admin-secretary-register.php');
require_once pbot_path('includes/admin-secretary-page.php');

/** SETTINGS (split: register + page) */
require_once pbot_path('includes/admin-settings-register.php');
require_once pbot_path('includes/admin-settings-page.php');

/** KNOWLEDGE BASE (split: register + page) */
require_once pbot_path('includes/admin-knowledge-register.php');
require_once pbot_path('includes/admin-knowledge-page.php');

/** ARTICLE WRITER (register first if present, then controller/view) */
if ( file_exists( pbot_path('includes/article-writer-register.php') ) ) {
  require_once pbot_path('includes/article-writer-register.php');
}
if ( file_exists( pbot_path('includes/admin-article-writer.php') ) ) {
  require_once pbot_path('includes/admin-article-writer.php');
} else {
  if ( ! function_exists('probot_render_article_writer_page') ) {
    function probot_render_article_writer_page(){
      echo '<div class="wrap"><h1>Article Writer</h1><p>Missing <code>includes/admin-article-writer.php</code>.</p></div>';
    }
  }
}

// === AI Answering (Twilio) ===
require_once __DIR__ . '/includes/admin-answering-register.php';
require_once __DIR__ . '/includes/admin-answering-page.php';

// // TEMP: manual flush for the self-updater cache
add_action('admin_init', function () {
  if ( ! current_user_can('update_plugins') ) return;
  if ( isset($_GET['pbot_flush_updater']) ) {
    delete_site_transient('pbot_upd_' . md5('jaredrlawson/probot-assistant'));
    wp_safe_redirect( admin_url('plugins.php?flushed=1') );
    exit;
  }
}, 99);