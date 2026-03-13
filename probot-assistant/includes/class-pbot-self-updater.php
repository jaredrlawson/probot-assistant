<?php
/**
 * Lightweight GitHub self-updater (class-only)
 * File: includes/class-pbot-self-updater.php
 *
 * - Checks GitHub latest release
 * - Uses a real asset zip (prefers "probot-assistant.zip")
 * - Injects update into WP's plugin updates list
 * - Normalizes extracted folder to match plugin slug
 * - Remembers active state and re-activates after update
 *
 * Requirements:
 * - Your release must include an asset zip whose top-level folder is:
 *     probot-assistant/
 *   and contains probot-assistant.php at: probot-assistant/probot-assistant.php
 *
 * Instantiate from your main plugin file (admin-only), e.g.:
 *   if ( is_admin() ) {
 *     add_action('admin_init', function () {
 *       if ( current_user_can('update_plugins') && class_exists('PBot_Self_Updater') ) {
 *         new PBot_Self_Updater([
 *           'file' => PROBOT_FILE,
 *           'slug' => plugin_basename(PROBOT_FILE), // "probot-assistant/probot-assistant.php"
 *           'user' => 'jaredrlawson',
 *           'repo' => 'probot-assistant',
 *         ]);
 *       }
 *     }, 1);
 *   }
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('PBot_Self_Updater')) {
  final class PBot_Self_Updater {
    private $file;
    private $slug;       // e.g. "probot-assistant/probot-assistant.php"
    private $repo_user;
    private $repo_name;
    private $cache_key;
    private $timeout;

    // Track active state across upgrade
    private $was_active = false;

    public function __construct($args) {
      $this->file      = $args['file'];
      $this->slug      = $args['slug'];
      $this->repo_user = $args['user'];
      $this->repo_name = $args['repo'];
      $this->cache_key = 'pbot_upd_' . md5($this->repo_user . '/' . $this->repo_name);
      $this->timeout   = 6 * HOUR_IN_SECONDS;

      add_filter('pre_set_site_transient_update_plugins', [$this, 'inject_update']);
      add_filter('plugins_api',                            [$this, 'plugins_api'], 10, 3);

      // Normalize extracted folder, and remember/reactivate active state
      add_filter('upgrader_source_selection',              [$this, 'rename_source'], 10, 4);
      add_action('upgrader_pre_install',                   [$this, 'pre_install'],  10, 2);
      add_action('upgrader_post_install',                  [$this, 'post_install'], 10, 2);
    }

    /* ---------------- Utilities ---------------- */

    private function is_me($hook_extra) {
      return !empty($hook_extra['plugin']) && $hook_extra['plugin'] === $this->slug;
    }

    /** Pick the best asset download URL (prefer probot-assistant.zip) */
    private function pick_asset_url($release_body) {
      if (empty($release_body['assets']) || !is_array($release_body['assets'])) return null;

      $preferred = null;
      $first     = null;

      foreach ($release_body['assets'] as $asset) {
        if (empty($asset['browser_download_url']) || empty($asset['name'])) continue;
        $url  = $asset['browser_download_url'];
        $name = $asset['name'];

        if ($first === null) $first = $url;
        if (!$preferred && preg_match('/^probot-assistant\.zip$/i', $name)) {
          $preferred = $url;
        }
      }

      return $preferred ?: $first;
    }

    /** Query GitHub Releases (cached) */
    private function get_latest_release() {
      $cached = get_site_transient($this->cache_key);
      if ($cached) return $cached;

      $url = "https://api.github.com/repos/{$this->repo_user}/{$this->repo_name}/releases/latest";
      $res = wp_remote_get($url, [
        'timeout' => 15,
        'headers' => ['User-Agent' => 'WordPress/ProBot-Assistant-Updater']
      ]);
      if (is_wp_error($res)) return null;

      $code = wp_remote_retrieve_response_code($res);
      $body = json_decode(wp_remote_retrieve_body($res), true);
      if ($code !== 200 || !is_array($body)) return null;

      // version from tag (strip "v")
      $tag = isset($body['tag_name']) ? ltrim((string)$body['tag_name'], 'vV') : null;

      // only use a real asset; avoid zipball_url to prevent random folder names
      $download = $this->pick_asset_url($body);
      if (!$download) {
        // No valid asset → don't advertise update (prevents "package could not be installed")
        return null;
      }

      $info = [
        'version'      => $tag,
        'name'         => $body['name'] ?? $tag,
        'changelog'    => $body['body'] ?? '',
        'download_url' => $download,
        'html_url'     => $body['html_url'] ?? ''
      ];

      set_site_transient($this->cache_key, $info, $this->timeout);
      return $info;
    }

    /* ---------------- Update injection ---------------- */

    /** Add our update into WP’s plugins transient if newer */
    public function inject_update($transient) {
      if (empty($transient->checked)) return $transient;

      $current_version = $transient->checked[$this->slug] ?? null;
      if (!$current_version) return $transient;

      $release = $this->get_latest_release();
      if (!$release || empty($release['version']) || empty($release['download_url'])) return $transient;

      if (version_compare($release['version'], $current_version, '>')) {
        $obj = (object)[
          'slug'        => dirname($this->slug), // "probot-assistant"
          'plugin'      => $this->slug,          // "probot-assistant/probot-assistant.php"
          'new_version' => $release['version'],
          'url'         => $release['html_url'],
          'package'     => $release['download_url'],
          'tested'      => get_bloginfo('version'),
          'requires'    => '5.3',
        ];
        $transient->response[$this->slug] = $obj;
      }

      return $transient;
    }

    /** Plugins screen → “View version x.x details” */
    public function plugins_api($result, $action, $args) {
      if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== dirname($this->slug)) {
        return $result;
      }

      $release = $this->get_latest_release();
      if (!$release) return $result;

      $info = (object)[
        'name'          => 'ProBot Assistant',
        'slug'          => dirname($this->slug),
        'version'       => $release['version'],
        'author'        => '<a href="https://github.com/jaredrlawson">Jared Я Lawson</a>',
        'homepage'      => $release['html_url'],
        'requires'      => '5.3',
        'tested'        => get_bloginfo('version'),
        'download_link' => $release['download_url'],
        'sections'      => [
          'description' => 'Front-end chat assistant with teaser, JSON intents, fuzzy matching, and admin Knowledge Base.',
          'changelog'   => wp_kses_post( wpautop( $release['changelog'] ?: 'No changelog provided.' ) ),
        ],
      ];
      return $info;
    }

    /* ---------------- Upgrade lifecycle hooks ---------------- */

    /** Before install: remember if our plugin is currently active */
    public function pre_install($return, $hook_extra) {
      if ( ! $this->is_me($hook_extra) ) return $return;

      if ( ! function_exists('is_plugin_active') ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
      }
      $this->was_active = is_plugin_active($this->slug);
      return $return;
    }

    /**
     * After unzip but before copy: normalize extracted folder name
     * GitHub assets sometimes unzip into an unexpected folder name.
     * We rename it to match dirname($this->slug) so WP replaces correctly.
     */
    public function rename_source($source, $remote_source, $upgrader, $hook_extra) {
      if ( ! $this->is_me($hook_extra) ) return $source;

      $target_dir = dirname($this->slug); // "probot-assistant"
      $desired    = trailingslashit($remote_source) . $target_dir;

      // If already correct, no-op.
      if ( trailingslashit($source) === trailingslashit($desired) ) {
        return $source;
      }

      // $source is usually the single extracted folder; if not, try first dir
      if ( ! is_dir($source) ) {
        $dirs = glob(trailingslashit($remote_source) . '*', GLOB_ONLYDIR);
        if (!empty($dirs[0])) $source = $dirs[0];
      }

      // Ensure parent exists and try to rename
      if ( ! is_dir($desired) ) {
        @mkdir($desired, 0775, true);
      }
      if (@rename($source, $desired)) {
        return $desired;
      }
      return $source; // fallback; WP may still handle it if names happen to match
    }

    /** After install: re-activate if it was active before */
    public function post_install($return, $hook_extra) {
      if ( ! $this->is_me($hook_extra) ) return $return;

      if ( $this->was_active ) {
        if ( ! function_exists('activate_plugin') || ! function_exists('is_plugin_active') ) {
          require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( ! is_plugin_active($this->slug) ) {
          // Silently reactivate (network: false, silent: true)
          activate_plugin($this->slug, '', false, true);
        }
      }
      return $return;
    }
  }
}