<?php
// ---------------------------------------------
// ProBot Assistant — Admin: Knowledge Base (controller)
// Splits the previous monolithic file into: register + controller + view
// View file: includes/views/knowledge-page.php
// ---------------------------------------------
if (!defined('ABSPATH')) exit;

require_once dirname(__FILE__) . '/admin-knowledge-register.php';

/** Helper: current JSON as string (mirrors behavior used by front-end) */
if (!function_exists('probot_admin_get_current_intents_json_for_admin_page')) {
  function probot_admin_get_current_intents_json_for_admin_page() {
    $source = get_option('pbot_intents_source', 'packaged');
    $manual = trim(get_option('pbot_manual_intents', ''));
    if ($source === 'manual' && $manual !== '') return $manual;

    $file = plugin_dir_path(PROBOT_FILE) . 'assets/json/intents.json';
    return file_exists($file) ? file_get_contents($file) : '';
  }
}

/** Controller: gather context and render view */
function probot_render_responses_page() {
  if (!current_user_can('manage_options')) return;

  // Handle Import POST (kept here to keep the view pure)
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
    if ($notice) {
      add_action('admin_notices', function() use ($notice){
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($notice) . '</p></div>';
      });
    }
  }

  // Handle Export GET
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

  // Build context for the view
  $source       = get_option('pbot_intents_source', 'packaged');
  $manual       = get_option('pbot_manual_intents', '');
  $current_json = probot_admin_get_current_intents_json_for_admin_page();

  // Parse for preview dropdown
  $intents_arr = [];
  if ($current_json) {
    $decoded = json_decode($current_json, true);
    if (is_array($decoded)) {
      if (!empty($decoded['intents']) && is_array($decoded['intents'])) {
        $intents_arr = $decoded['intents'];
      } elseif (isset($decoded[0])) {
        $intents_arr = $decoded;
      }
    }
  }

  $ctx = [
    'source'       => $source,
    'manual'       => $manual,
    'current_json' => $current_json,
    'intents_arr'  => $intents_arr,
    'ajax_url'     => admin_url('admin-ajax.php'),
    'nonce'        => wp_create_nonce('probot_nonce'),
    'preview_url'  => add_query_arg(['action'=>'probot_intents'], admin_url('admin-ajax.php')),
    'export_url'   => wp_nonce_url(add_query_arg('pbot_export','1'), 'pbot_export_json'),
    'packaged_url' => plugins_url('assets/json/intents.json', PROBOT_FILE),
  ];

  // Expose $ctx vars to the template as in your settings split
  extract($ctx, EXTR_SKIP);

  require dirname(__FILE__) . '/views/knowledge-page.php';
}