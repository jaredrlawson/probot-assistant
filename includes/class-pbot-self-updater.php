<?php
/**
 * Lightweight GitHub self-updater for ProBot Assistant
 * - Shows “There is a new version…” notice on Plugins screen
 * - Pulls latest release info from GitHub
 * - Works as a drop-in: just place this file and it auto-registers
 *
 * Repo: https://github.com/jaredrlawson/probot-assistant
 */
if (!defined('ABSPATH')) exit;

if (!class_exists('PBot_Self_Updater')) {

  final class PBot_Self_Updater {
    private $file;
    private $slug;
    private $repo_user;
    private $repo_name;
    private $cache_key;
    private $timeout;

    public function __construct($args) {
      $this->file      = $args['file'];
      $this->slug      = $args['slug'];
      $this->repo_user = $args['user'];
      $this->repo_name = $args['repo'];
      $this->cache_key = 'pbot_upd_' . md5($this->repo_user . '/' . $this->repo_name);
      $this->timeout   = 6 * HOUR_IN_SECONDS;

      add_filter('pre_set_site_transient_update_plugins', [$this, 'inject_update']);
      add_filter('plugins_api',                            [$this, 'plugins_api'], 10, 3);
      add_filter('upgrader_source_selection',              [$this, 'rename_source'], 10, 4);
    }

    /** Query GitHub Releases (cached) */
    private function get_latest_release() {
      $cached = get_site_transient($this->cache_key);
      if ($cached) return $cached;

      $url = "https://api.github.com/repos/{$this->repo_user}/{$this->repo_name}/releases/latest";
      $res = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => ['User-Agent' => 'WordPress/ProBot-Assistant-Updater']
      ]);
      if (is_wp_error($res)) return null;

      $code = wp_remote_retrieve_response_code($res);
      $body = json_decode(wp_remote_retrieve_body($res), true);
      if ($code !== 200 || !is_array($body)) return null;

      // Prefer tag_name as version (strip 'v' prefix if present)
      $tag = isset($body['tag_name']) ? ltrim($body['tag_name'], 'vV') : null;

      // Best download: first asset; fallback to zipball_url
      $download = null;
      if (!empty($body['assets'][0]['browser_download_url'])) {
        $download = $body['assets'][0]['browser_download_url'];
      } elseif (!empty($body['zipball_url'])) {
        $download = $body['zipball_url'];
      }

      $info = [
        'version'      => $tag,
        'name'         => isset($body['name']) ? $body['name'] : $tag,
        'changelog'    => isset($body['body']) ? $body['body'] : '',
        'download_url' => $download,
        'html_url'     => isset($body['html_url']) ? $body['html_url'] : ''
      ];

      set_site_transient($this->cache_key, $info, $this->timeout);
      return $info;
    }

    /** Inject update data into WP’s plugins transient */
    public function inject_update($transient) {
      if (empty($transient->checked)) return $transient;

      $current_version = $transient->checked[$this->slug] ?? null;
      if (!$current_version) return $transient;

      $release = $this->get_latest_release();
      if (!$release || empty($release['version'])) return $transient;

      // Compare versions (supports dev labels; WordPress handles semver-ish compare)
      if (version_compare($release['version'], $current_version, '>')) {
        $obj = (object)[
          'slug'        => dirname($this->slug),
          'plugin'      => $this->slug,
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

    /** Details popup (when clicking “View version x.x details”) */
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
          'changelog'   => wp_kses_post(wpautop($release['changelog'] ?: 'No changelog provided.'))
        ],
      ];
      return $info;
    }

    /**
     * Rename source folder after unzip so WordPress replaces the right directory.
     * (GitHub zips extract as user-repo-hash; we normalize to plugin folder name.)
     */
    public function rename_source($source, $remote_source, $upgrader, $hook_extra) {
      if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->slug) return $source;

      $desired = trailingslashit($remote_source) . dirname($this->slug);
      if (!@rename($source, $desired)) return $source;
      return $desired;
    }
  }
}

/* Auto-register the updater when this file is loaded */
if (defined('PROBOT_FILE')) {
  $slug = plugin_basename(PROBOT_FILE); // e.g. probot-assistant/probot-assistant.php
  new PBot_Self_Updater([
    'file' => PROBOT_FILE,
    'slug' => $slug,
    'user' => 'jaredrlawson',
    'repo' => 'probot-assistant',
  ]);
}