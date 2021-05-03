<?php
/**
 * Admin Plugin.
 */
namespace NJT\FastDup;

use NJT\FastDup\Admin as Admin;

defined('ABSPATH') || exit;
class Plugin {
  /**
   * Instance of this class.
   */
  protected static $instance = null;

  /**
   * Return an instance of this class.
   */
  public static function get_instance() {
    // If the single instance hasn't been set, set it now.
    if (null == self::$instance) {
      self::$instance = new self;
      self::$instance->load_locale_translate();
    }

    return self::$instance;
  }

  public function __construct() {

  }

  public function load_locale_translate() {
    $current_user = wp_get_current_user();

    if (!($current_user)) {
      return;
    }

    if (function_exists('get_user_locale')) {
      $language = get_user_locale($current_user);
    } else {
      $language = get_locale();
    }
    load_textdomain("njt-fastdup", NJT_FASTDUP_PLUGIN_PATH . '/languages/' . $language . '.mo');
  }

  // Fired when the plugin is activated.
  public static function activate() {
    Admin\Database\Database::create_table_njt_fastdup_packages();
    Admin\Database\Database::create_table_njt_fastdup_entities();
    Admin\Helper\Helper::init_archive_directory();
  }

  // Fired when the plugin is deactivated.
  public static function deactivate() {}

}
