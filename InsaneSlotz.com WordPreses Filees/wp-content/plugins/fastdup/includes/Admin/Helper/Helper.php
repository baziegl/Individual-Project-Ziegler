<?php

namespace NJT\FastDup\Admin\Helper;

class Helper {

  public static function get_version() {
    if (WP_DEBUG) {
      return time();
    }
    return NJT_FASTDUP_VERSION;
  }

  public static function format_path_filter($string, $is_to_array) {
    $string = str_replace(array("\r\n", "\n", "\r"), ';', $string);
    $string = trim(preg_replace('/;([\s\t]*;)+/', ';', $string), "; \t\n\r\0\x0B");
    $line_array = preg_split('/[\s\t]*;[\s\t]*/', $string);

    $result = array();
    foreach ($line_array as $val) {
      if (strlen($val) == 0 || preg_match('/^[\s\t]*?#/', $val)) {
        if (!$is_to_array) {
          $result[] = trim($val);
        }
      } else {
        $safe_path = str_replace(array("\t", "\r"), '', $val);
        $safe_path = self::safe_path_slash(trim(rtrim($safe_path, "/\\")));
        if (strlen($safe_path) >= 2) {
          $result[] = $safe_path;
        }
      }
    }

    if ($is_to_array) {
      $result = array_unique($result);
      sort($result);
      return $result;
    } else {
      return implode(";", $result);
    }
  }

  public static function safe_path_slash($path) {
    return str_replace('\\', '/', $path);
  }

  public static function safe_path($path) {
    return str_replace('\\', '/', realpath($path));
  }

  public static function default_package_name($name) {
    $name = $name ? sanitize_text_field($name) : 'package';
    return $name;
  }

  // Init directory for store packages
  public static function init_archive_directory() {
    $path_web_root = self::safe_path_slash(NJT_FASTDUP_WEB_ROOTPATH);
    $path_archive_dir = self::safe_path_slash(NJT_FASTDUP_ARCHIVE_DIR_PATH);
    $path_plugin = self::safe_path_slash(NJT_FASTDUP_PLUGIN_PATH);

    if (!is_dir($path_archive_dir)) {
      $old_web_root_perm = @fileperms($path_web_root);
      //---- UPDATE PERMISSION DIR ACCESS WEB ROOT ----//
      // Archive directory
      @chmod($path_web_root, 0755);
      @mkdir($path_archive_dir, 0755);
      @chmod($path_archive_dir, 0755);

      // Restore original web root permission
      @chmod($path_web_root, $old_web_root_perm);
    }

    $path_archive_dir_tmp = $path_archive_dir . '/tmp';
    if (!file_exists($path_archive_dir_tmp)) {
      //Archive tmp directory
      @mkdir($path_archive_dir_tmp, 0755);
      @chmod($path_archive_dir_tmp, 0755);
    }

    $path_arc_package = $path_archive_dir . '/packages';
    if (!file_exists($path_arc_package)) {
      //Archive tmp directory
      @mkdir($path_arc_package, 0755);
      @chmod($path_arc_package, 0755);
    }

    /**
     * Create file in njt-fastdup
     */
    // Create file: Index File
    $file_name = $path_archive_dir . '/index.php';
    if (!file_exists($file_name)) {
      $nta_file = @fopen($file_name, 'w');
      @fwrite($nta_file,
        '<?php // Silence is golden ?>');
      @fclose($nta_file);
    }

    // Create file: .htaccess
    $file_name = $path_archive_dir . '/.htaccess';
    if (!file_exists($file_name)) {
      $htaccess_file = @fopen($file_name, 'w');
      $htoutput = "Options -Indexes";
      @fwrite($htaccess_file, $htoutput);
      @fclose($htaccess_file);
    }

    // Create file: robots.txt
    $file_name = $path_archive_dir . '/robots.txt';
    if (!file_exists($file_name)) {
      $robot_file = @fopen($file_name, 'w');
      @fwrite($robot_file, "User-agent: * \nDisallow: /" . NJT_FASTDUP_ARCHIVE_DIR_NAME . '/');
      @fclose($robot_file);
    }
  }

  public static function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }

}
