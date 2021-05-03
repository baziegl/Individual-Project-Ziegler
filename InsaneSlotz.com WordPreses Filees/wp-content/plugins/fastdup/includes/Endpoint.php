<?php
/**
 * Endpoint Register.
 */

namespace NJT\FastDup;

defined('ABSPATH') || exit;
class Endpoint {
  /**
   * Instance of this class.
   *
   * @since    1.0
   * @var      object
   */
  protected static $instance = null;

  /**
   * Return an instance of this class.
   *
   * @since     1.0
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new self;
      self::$instance->do_hooks();
    }
    return self::$instance;
  }

  /**
   * Init props
   *
   * @since     1.0
   */
  private function __construct() {
  }

  private function do_hooks() {
    $package_api = Endpoint\PackageApi::get_instance();
    $template_api = Endpoint\TemplateApi::get_instance();
  }
}
