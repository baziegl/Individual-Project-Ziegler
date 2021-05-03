<?php

/**
 * Plugin Name:       FastDup
 * Plugin URI:        https://ninjateam.gitbook.io/fastdup/
 * Description:       WordPress Fastest Duplicator and Migration
 * Version:           2.0
 * Author:            Ninja Team
 * Author URI:        https://ninjateam.org/
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       njt-fastdup
 * Domain Path:       /languages
 */

/**
 * MAKE SURE WE DON'T EXPOSE ANY INFO IF CALLED DIRECTLY
 */
if (!function_exists('add_action')) {
  die;
}

/**
 * AUTOLOADER
 */
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
  require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * DEFINE
 */
if (file_exists(dirname(__FILE__) . '/define.php')) {
  require_once dirname(__FILE__) . '/define.php';
}

/**
 * REGISTER ACTIVATION AND DEACTIVATION HOOKS
 */
register_activation_hook(__FILE__, array('NJT\\FastDup\\Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('NJT\\FastDup\\Plugin', 'deactivate'));

/**
 * INITIALIZE PLUGIN
 */
function njt_fastdup_init() {
  $njt_fastdup = NJT\FastDup\Plugin::get_instance();
  $njt_fastdup_admin = NJT\FastDup\Admin::get_instance();
  $njt_fastdup_api = NJT\FastDup\Endpoint::get_instance();
}

add_action('plugins_loaded', 'njt_fastdup_init');
