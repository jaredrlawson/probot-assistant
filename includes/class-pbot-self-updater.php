<?php
// Lightweight GitHub self-updater (class-only)
// Place: includes/class-pbot-self-updater.php
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
      $this->slug      = $args['slug']; // e.g. probot-assistant/probot-assistant.php
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
        'timeout' => 12,
        'headers' => ['User-Agent' => 'WordPress/ProBot-Assistant-Updater']
      ]);
      if (is_wp_error($res)) return null;

      $code = wp_remote_retrieve_response_code($res);
      $body = json_decode(wp_remote_retrieve_body($res), true);
      if ($code !== 200 || !is_array($body)) return null;

      $tag = isset($body['tag_name']) ? ltrim((string)$body['tag_name'], 'vV') : null;

      // Choose a zip to install
      $download = null;
      if (!empty($body['assets'][0]['browser_download_url'])) {
        $download = $body['assets'][0]['browser_download_url'];
      } elseif (!empty($body['zipball_url'])) {
        $download = $body['zipball_url'];
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

    /** Inject update data into WP’s plugins transient */
    public function inject_update($transient) {
      if (empty($transient->checked)) return $transient;

      $current_version = $transient->checked[$this->slug] ?? null;
      if (!$current_version) return $transient;

      $release = $this->get_latest_release();
      if (!$release || empty($release['version']) || empty($release['download_url'])) return $transient;

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

    /** Details popup (Plugins screen → “View details”) */
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
          'changelog'   => wp_kses_post(wpautop($release['changelog'] ?: 'No changelog provided.')),
        ],
      ];
      return $info;
    }

    /**
     * Normalize extracted folder name so WP replaces the correct plugin dir.
     * GitHub zips typically extract as {user}-{repo}-{hash}/ → rename to plugin dir.
     */
    public function rename_source($source, $remote_source, $upgrader, $hook_extra) {
      if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->slug) return $source;

      $desired = trailingslashit($remote_source) . dirname($this->slug);
      // $source is usually the *single* extracted dir inside $remote_source
      // If it’s already correct, no-op.
      if (trailingslashit($source) === trailingslashit($desired)) return $source;

      // Find the first subdir if $source isn’t a dir (defensive)
      if (!is_dir($source)) {
        $dirs = glob(trailingslashit($remote_source) . '*', GLOB_ONLYDIR);
        if (!empty($dirs[0])) $source = $dirs[0];
      }

      @mkdir($desired, 0775, true);
      if (@rename($source, $desired)) return $desired;
      return $source;
    }
  }
}