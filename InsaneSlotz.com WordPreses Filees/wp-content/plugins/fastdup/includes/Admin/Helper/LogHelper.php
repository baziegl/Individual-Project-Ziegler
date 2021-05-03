<?php
/**
 * The Logging Helper
 */
namespace NJT\FastDup\Admin\Helper;

use NJT\FastDup;

defined('ABSPATH') || exit;
class LogHelper {
  public static function write_log($id, $type_log, $message) {
    if (!is_string($message)) {
      $message = print_r($message, true);
    }

    $folder = NJT_FASTDUP_PATH_LOG;
    if (!file_exists($folder)) {
      @mkdir($folder, 0755);
      @chmod($folder, 0755);

    }

    $filename = $folder . DIRECTORY_SEPARATOR . "logs-" . $id . ".txt";

    clearstatcache(); // Remove filesize cache

    $handle = fopen($filename, "a");
    if (filesize($filename) == 0) {
      fwrite($handle, self::get_system_stats());
    }

    fwrite($handle, current_time('mysql') . ' [' . strtoupper($type_log) . '] ' . $message . PHP_EOL);
    fclose($handle);
  }

  private static function get_system_stats() {
    global $wpdb;

    if (!function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $system_stats = "====  SYSTEM STATS  ====" . PHP_EOL;
    $system_stats .= "Wordpress Version: " . get_bloginfo('version') . PHP_EOL;
    $system_stats .= "PHP Version: " . phpversion() . PHP_EOL;
    $system_stats .= "MySQL Version: " . $wpdb->db_version() . PHP_EOL;
    $system_stats .= "Website Name: " . get_bloginfo() . PHP_EOL;
    $system_stats .= "Theme: " . wp_get_theme() . PHP_EOL;
    $system_stats .= "Wordpress URL: " . site_url() . PHP_EOL;
    $system_stats .= "Site URL: " . home_url() . PHP_EOL;
    $system_stats .= "Multisite: " . (is_multisite() ? "yes" : "no") . PHP_EOL;
    $system_stats .= "PHP Extensions: " . json_encode(get_loaded_extensions()) . PHP_EOL;
    $system_stats .= "Server Info: " . $_SERVER['SERVER_SOFTWARE'] . PHP_EOL;
    $system_stats .= "WP Memory Limit: " . WP_MEMORY_LIMIT . PHP_EOL;
    $system_stats .= "WP Admin Memory Limit: " . WP_MAX_MEMORY_LIMIT . PHP_EOL;
    $system_stats .= "PHP Memory Limit: " . ini_get('memory_limit') . PHP_EOL;
    $system_stats .= "Max Execution Time: " . ini_get("max_execution_time") . PHP_EOL;
    $system_stats .= "Open BaseDir: " . ini_get("open_basedir") . PHP_EOL;
    $system_stats .= "Wordpress Plugins: " . json_encode(get_plugins()) . PHP_EOL;
    $system_stats .= "Wordpress Active Plugins: " . json_encode(get_site_option('active_plugins')) . PHP_EOL;
    $system_stats .= "====  SYSTEM STATS  ====" . PHP_EOL . PHP_EOL;
    return $system_stats;
  }
}
