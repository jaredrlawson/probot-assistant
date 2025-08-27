<?php
/**
 * Plugin Name: ProBot Assistant
 * Description: Front-end chat assistant with teaser, JSON intents (packaged or manual), fuzzy matching, and admin Knowledge Base manager.
 * Version: 1.5.7-dev-testing
 * Author: Jared Я Lawson
 * License: GPLv2 or later
 */

if (!defined('ABSPATH')) exit;

define('PROBOT_FILE', __FILE__);
define('PROBOT_PATH', plugin_dir_path(__FILE__));
define('PROBOT_URL',  plugin_dir_url(__FILE__));

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
  add_option('pbot_bubble_position',   'right');
  add_option('pbot_pulse_enabled',     1);
  add_option('pbot_teaser_enabled',    1);
  add_option('pbot_sound_enabled',     1);
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
});

/* --------------------------------------------------------------------------
 * Front-end assets + config
 * ----------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
  $css_rel = 'assets/frontend/probot-assistant.css';
  $js_rel  = 'assets/frontend/probot-assistant.js';

  wp_enqueue_style('probot-assistant-css', pbot_url($css_rel), array(), probot_asst_asset_ver($css_rel));
  wp_enqueue_script('probot-assistant-js', pbot_url($js_rel), array('jquery'), probot_asst_asset_ver($js_rel), true);

  $source      = get_option('pbot_intents_source', 'packaged');
  $manual      = trim(get_option('pbot_manual_intents', ''));
  $packagedURL = pbot_url('assets/json/intents.json');
  $intents_url = ($source === 'manual' && $manual !== '')
    ? add_query_arg(array('action'=>'probot_intents'), admin_url('admin-ajax.php'))
    : $packagedURL;

  $toastMsg  = get_option('pbot_teaser_message', '');
  $toastDur  = (int) get_option('pbot_teaser_duration_ms', 4500);
  $toastShow = (int) get_option('pbot_teaser_show_count', 3);

  wp_localize_script('probot-assistant-js', 'pbot_cfg', array(
    'ajax_url'           => admin_url('admin-ajax.php'),
    'nonce'              => wp_create_nonce('probot_nonce'),
    'brand_title'        => get_option('pbot_brand_title', 'ProBot Assistant'),
    'bubble_position'    => get_option('pbot_bubble_position', 'right'),
    'pulse_enabled'      => (bool) get_option('pbot_pulse_enabled', 1),
    'teaser_enabled'     => (bool) get_option('pbot_teaser_enabled', 1),
    'sound_enabled'      => (bool) get_option('pbot_sound_enabled', 1),
    'intents_url'        => esc_url_raw($intents_url),
    'match_threshold'    => (float) get_option('pbot_match_threshold', 0.52),
    'greeting_delay_ms'  => (int) get_option('pbot_greeting_delay_ms', 2200),

    // Teaser config
    'teaser_message'     => $toastMsg,
    'teaser_duration_ms' => $toastDur,
    'teaser_show_count'  => $toastShow,

    // Toast colors
    'teaser_bg_color'    => get_option('pbot_toast_bg_color', 'rgba(18,18,18,.92)'),
    'teaser_text_color'  => get_option('pbot_toast_text_color', '#ffffff'),

    // Colors
    'brand_color'        => get_option('pbot_brand_color', '#444444'),
    'halo_color'         => get_option('pbot_halo_color', 'rgba(255,255,255,.7)'),
    'panel_color'        => get_option('pbot_panel_color', '#ffffff'),

    // Intensities
    'halo_intensity'     => (float) get_option('pbot_halo_intensity', 0.70),
    'pulse_intensity'    => (float) get_option('pbot_pulse_intensity', 1.00),

    // Button borders → used by frontend JS to set CSS variables
    'btn_border_enabled' => (int)   get_option('pbot_btn_border_enabled', 0),
    'btn_border_weight'  => (float) get_option('pbot_btn_border_weight',  1),
    'btn_border_color'   =>          get_option('pbot_btn_border_color',   '#d0d0d0'),

    'send_border_enabled'=> (int)   get_option('pbot_send_border_enabled', 0),
    'send_border_weight' => (float) get_option('pbot_send_border_weight',  1),
    'send_border_color'  =>          get_option('pbot_send_border_color',   '#d0d0d0'),
  ));
});

/* --------------------------------------------------------------------------
 * Admin assets
 * ----------------------------------------------------------------------- */
add_action('admin_enqueue_scripts', function($hook){
  if (strpos($hook, 'probot-assistant') === false) return;
  wp_enqueue_style('pbot-admin', pbot_url('assets/admin/probot-admin.css'), array(), probot_asst_asset_ver('assets/admin/probot-admin.css'));
  wp_enqueue_script('pbot-admin', pbot_url('assets/admin/probot-admin.js'), array('jquery'), probot_asst_asset_ver('assets/admin/probot-admin.js'), true);
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
  add_menu_page('ProBot Assistant','ProBot Assistant','manage_options','probot-assistant','probot_render_settings_page','dashicons-format-chat',58);
  add_submenu_page('probot-assistant','Settings','Settings','manage_options','probot-assistant','probot_render_settings_page');
  add_submenu_page('probot-assistant','Knowledge Base','Knowledge Base','manage_options','probot-assistant-knowledge','probot_render_responses_page');
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
 * Includes (drop-ins)
 * ----------------------------------------------------------------------- */

/* --------------------------------------------------------------------------
 * Optional GitHub self-updater (drop-in)
 * ----------------------------------------------------------------------- */
if ( is_admin() ) {
  add_action('admin_init', function () {
    // must be in wp-admin and able to update plugins
    if ( ! current_user_can('update_plugins') ) return;

    // include class file if present
    $updater_file = pbot_path('includes/class-pbot-self-updater.php');
    if ( ! file_exists($updater_file) ) return;
    require_once $updater_file;

    // instantiate once
    if ( ! class_exists('PBot_Self_Updater') ) return;
    if ( isset($GLOBALS['pbot_updater']) && $GLOBALS['pbot_updater'] instanceof PBot_Self_Updater ) return;

    $GLOBALS['pbot_updater'] = new PBot_Self_Updater([
      'file' => PROBOT_FILE,
      'slug' => plugin_basename(PROBOT_FILE), // e.g. probot-assistant/probot-assistant.php
      'user' => 'jaredrlawson',
      'repo' => 'probot-assistant',
    ]);
  }, 1);
}

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
  // Fallback placeholder so the submenu callback won't fatal if file is missing
  if ( ! function_exists('probot_render_article_writer_page') ) {
    function probot_render_article_writer_page(){
      echo '<div class="wrap"><h1>Article Writer</h1><p>Missing <code>includes/admin-article-writer.php</code>.</p></div>';
    }
  }
}

// TEMP: one-time cache flush for the updater
add_action('admin_init', function () {
  if ( ! current_user_can('update_plugins') ) return;
  if ( ! isset($_GET['pbot_flush_updater']) ) return;
  // cache key used by the class below (user/repo md5)
  delete_site_transient('pbot_upd_' . md5('jaredrlawson/probot-assistant'));
  wp_safe_redirect( admin_url('plugins.php?flushed=1') );
  exit;
});