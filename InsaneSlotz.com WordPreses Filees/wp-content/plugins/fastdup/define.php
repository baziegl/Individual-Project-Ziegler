<?php

// Prevent directly browsing to the file
if (function_exists('plugin_dir_url')) {
  if (!defined('NJT_FASTDUP_VERSION')) {
    define('NJT_FASTDUP_VERSION', '1.2.2');
  }

  if (!defined('NJT_FASTDUP_PACKAGE_ACTIVE')) {
    define('NJT_FASTDUP_PACKAGE_ACTIVE', 'njt_fastdup_package_id_active');
  }

  //PATH CONSTANTS
  if (!defined('NJT_FASTDUP_PLUGIN')) {
    define('NJT_FASTDUP_PLUGIN', plugin_basename(__FILE__));
  }

  if (!defined('NJT_FASTDUP_PLUGIN_PATH')) {
    define('NJT_FASTDUP_PLUGIN_PATH', str_replace('\\', '/', plugin_dir_path(__FILE__)));
  }

  if (!defined('NJT_FASTDUP_WEB_ROOTPATH')) {
    define('NJT_FASTDUP_WEB_ROOTPATH', str_replace('\\', '/', ABSPATH));
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_NAME')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_NAME', 'njt-fastdup');
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_NAME')) {
    define('NJT_FASTDUP_ARCHIVE_NAME', basename(WP_CONTENT_DIR) . '/njt-fastdup;');
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_PATH') && defined('NJT_FASTDUP_ARCHIVE_DIR_NAME')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_PATH', str_replace('\\', '/', WP_CONTENT_DIR . '/' . NJT_FASTDUP_ARCHIVE_DIR_NAME));
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_LOG_NAME')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_LOG_NAME', 'logs');
  }

  if (!defined('NJT_FASTDUP_PATH_LOG')) {
    define('NJT_FASTDUP_PATH_LOG', NJT_FASTDUP_PLUGIN_PATH . NJT_FASTDUP_ARCHIVE_DIR_LOG_NAME);
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_TEMP_NAME')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_TEMP_NAME', '/tmp');
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_PATH_TMP')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_PATH_TMP', NJT_FASTDUP_ARCHIVE_DIR_PATH . NJT_FASTDUP_ARCHIVE_DIR_TEMP_NAME);
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_PACKAGE_NAME')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_PACKAGE_NAME', '/packages');
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES')) {
    define('NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES', NJT_FASTDUP_ARCHIVE_DIR_PATH . NJT_FASTDUP_ARCHIVE_DIR_PACKAGE_NAME);
  }

  if (!defined('NJT_FASTDUP_ARCHIVE_URL_PACKAGES')) {
    define('NJT_FASTDUP_ARCHIVE_URL_PACKAGES', WP_CONTENT_URL . '/' . NJT_FASTDUP_ARCHIVE_DIR_NAME . NJT_FASTDUP_ARCHIVE_DIR_PACKAGE_NAME . '/');
  }

  if (!defined('NJT_FASTDUP_FILE')) {
    define('NJT_FASTDUP_FILE', __FILE__);
  }

  if (!defined('NJT_FASTDUP_ENDPOINT')) {
    define('NJT_FASTDUP_ENDPOINT', 'njt-fastdup/v1');
  }

  if (!defined('NJT_FASTDUP_DIR')) {
    define('NJT_FASTDUP_DIR', realpath(plugin_dir_path(NJT_FASTDUP_FILE)));
  }

  if (!defined('NJT_FASTDUP_URL')) {
    define('NJT_FASTDUP_URL', plugins_url('', NJT_FASTDUP_FILE));
  }
  //GLOBALS FILTER
  $GLOBALS['NJT_FASTDUP_GLOBAL_PREG'] = "/(^(([\.]){1,2})$|(\.(svn|git|gitignore|gitkeep|gitmodules|gitattributes|github|md|vscode|cache))|(node_modules|Thumbs\.db|\.DS_STORE))$/iu";
} else {
  error_reporting(0);
  $port = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https://" : "http://";
  $url = $port . $_SERVER["HTTP_HOST"];
  header("HTTP/1.1 404 Not Found", true, 404);
  header("Status: 404 Not Found");
  exit();
}
