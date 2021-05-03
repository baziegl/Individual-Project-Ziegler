<?php
/**
 * Admin Register.
 */

namespace NJT\FastDup;

defined('ABSPATH') || exit;
class Admin {
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
    $meta_box = Admin\MetaBox::get_instance();
  }

}
