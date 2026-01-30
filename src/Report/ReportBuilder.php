<?php
namespace BCMAuditor\Report;

final class ReportBuilder {
  public function build(): array {
    global $wpdb;

    $root = ABSPATH;
    $wpContent = WP_CONTENT_DIR;
    $uploads = wp_get_upload_dir();

    $paths = [
      'ABSPATH' => $root,
      'wp_content' => $wpContent,
      'uploads' => $uploads['basedir'] ?? null,
      'plugins' => WP_PLUGIN_DIR,
      'themes' => get_theme_root(),
    ];

    $sizes = [];
    foreach ($paths as $name => $path) {
      if (!is_string($path) || $path === '' || !is_dir($path)) {
        $sizes[$name] = ['path' => $path, 'error' => 'missing'];
        continue;
      }
      $sizes[$name] = $this->dir_stats($path);
    }

    $disk = [
      'path' => $root,
      'total_bytes' => @disk_total_space($root) ?: null,
      'free_bytes' => @disk_free_space($root) ?: null,
    ];

    $db = $this->db_stats($wpdb);

    $pluginData = [];
    if (function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
      $all = get_plugins();
      foreach ($all as $file => $info) {
        $pluginData[] = [
          'file' => $file,
          'name' => $info['Name'] ?? '',
          'version' => $info['Version'] ?? '',
          'active' => is_plugin_active($file),
        ];
      }
    }

    $theme = wp_get_theme();

    return [
      'generated_at' => gmdate('c'),
      'site' => [
        'home_url' => home_url(),
        'site_url' => site_url(),
        'is_multisite' => is_multisite(),
        'language' => get_locale(),
        'wp_version' => get_bloginfo('version'),
      ],
      'runtime' => [
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_input_vars' => ini_get('max_input_vars'),
        'opcache_enabled' => function_exists('opcache_get_status') ? (bool) (opcache_get_status(false)['opcache_enabled'] ?? false) : null,
      ],
      'disk' => $disk,
      'paths' => $sizes,
      'database' => $db,
      'plugins' => $pluginData,
      'theme' => [
        'name' => $theme->get('Name'),
        'stylesheet' => $theme->get_stylesheet(),
        'template' => $theme->get_template(),
        'version' => $theme->get('Version'),
      ],
    ];
  }

  private function dir_stats(string $dir): array {
    $total = 0;
    $count = 0;

    $it = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it as $item) {
      /** @var \SplFileInfo $item */
      if ($item->isFile()) {
        $count++;
        $size = $item->getSize();
        $total += (int)$size;
      }
    }

    return [
      'path' => $dir,
      'file_count' => $count,
      'total_bytes' => $total,
    ];
  }

  private function db_stats($wpdb): array {
    $out = [
      'db_name' => defined('DB_NAME') ? DB_NAME : null,
      'tables' => [],
      'total_bytes' => null,
      'error' => null,
    ];

    if (!isset($wpdb) || !is_object($wpdb)) {
      $out['error'] = 'wpdb unavailable';
      return $out;
    }

    try {
      $dbName = $out['db_name'];
      if (!$dbName) {
        $out['error'] = 'DB_NAME missing';
        return $out;
      }

      // Total size
      $total = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = %s",
          $dbName
        )
      );
      $out['total_bytes'] = $total !== null ? (int)$total : null;

      // Top tables
      $rows = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT table_name, (data_length+index_length) AS bytes, table_rows FROM information_schema.TABLES WHERE table_schema = %s ORDER BY bytes DESC LIMIT 25",
          $dbName
        ),
        ARRAY_A
      );

      if (is_array($rows)) {
        foreach ($rows as $r) {
          $out['tables'][] = [
            'table' => $r['table_name'] ?? '',
            'bytes' => isset($r['bytes']) ? (int)$r['bytes'] : null,
            'rows' => isset($r['table_rows']) ? (int)$r['table_rows'] : null,
          ];
        }
      }

      return $out;
    } catch (\Throwable $e) {
      $out['error'] = $e->getMessage();
      return $out;
    }
  }
}
