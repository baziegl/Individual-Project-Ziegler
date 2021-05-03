<?php
/**
 * Plugin Name: Crio Premium
 * Plugin URI: https://www.boldgrid.com/crio/
 * Description: Premium features plugin for the BoldGrid Crio theme.
 * Version: 1.2.0
 * Author: BoldGrid
 * Author URI: https://www.boldgrid.com/
 * License: GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: crio-premium
 * Domain Path: /languages
 *
 * @package Crio_Premium
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check PHP/WP Versions.
if ( ! class_exists( 'Wp_Php_Version_Check' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/boldgrid/wp-php-version-check/class-wp-php-version-check.php';
}

// Define plugin filepath.
if ( ! defined( 'CRIO_PREMIUM_FILEPATH' ) ) {
	define( 'CRIO_PREMIUM_FILEPATH', __FILE__ );
}

// Define plugin URL.
if ( ! defined( 'CRIO_PREMIUM_URL' ) ) {
	define( 'CRIO_PREMIUM_URL', plugin_dir_url( __FILE__ ) );
}

// Set plugin version reference.
if ( ! defined( 'CRIO_PREMIUM_VERSION' ) ) {
	define( 'CRIO_PREMIUM_VERSION', implode( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
}

if ( ! defined( 'REQUIRED_PPB_VERSION' ) ) {
	define( 'REQUIRED_PPB_VERSION', '1.14.0' );
}

if ( ! defined( 'REQUIRED_CRIO_VERSION' ) ) {
	define( 'REQUIRED_CRIO_VERSION', '2.7.0-r4' );
}

// Initalize the version checking.  This checks that the user has at least WordPress v4.9 and PHP v5.4.
Wp_Php_Version_Check::init( plugin_basename( __FILE__ ), '4.9', '5.4', 'run_crio_premium' );

/**
 * Clears the BG Library license transient.
 *
 * This is ran on activation/deactivation of the plugin.
 *
 * @since 1.0.0
 */
function crio_premium_clear_transient() {
	do_action( 'Boldgrid\Library\License\clearTransient' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
}

register_activation_hook( __FILE__, 'crio_premium_clear_transient' );
register_deactivation_hook( __FILE__, 'crio_premium_clear_transient' );

/**
 * Loads the BG Library.
 *
 * This is ran on activation/deactivation of the plugin.
 *
 * @since 1.0.0
 */
function crio_premium_load_library() {
	// Include the autoloader to set plugin options and create instance.
	$loader = require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	// Load Library.
	new Boldgrid\Library\Util\Load(
		[
			'type'            => 'plugin',
			'file'            => plugin_basename( __FILE__ ),
			'loader'          => $loader,
			'keyValidate'     => true,
			'licenseActivate' => false,
		]
	);
}

/**
 * Add Crio theme nag notice.
 *
 * This is added when Crio is not the current active theme,
 * or the parent theme in the case of a child theme of Crio
 * being used.  The nag, if dismissed, appears again after 7
 * days.  Change '7' where it appears below to desired number
 * of days if it needs to be different.
 *
 * @since 1.0.0
 */
function crio_premium_theme_nag() {
	if ( 'prime' === get_template() || 'crio' === get_template() ) {
		return;
	}

	if ( ! PAnD::is_admin_notice_active( 'crio-theme-nag-7' ) ) {
		return;
	}

	$link = '';
	$crio = wp_get_theme( 'crio' );

	if ( $crio->exists() ) {
		$link = sprintf(
			// translators: 1: Theme page URL.
			__( '<a href="%1$s">Install & Activate Now</a>', 'crio-premium' ),
			esc_url( admin_url( 'themes.php?theme=crio' ) )
		);
	}

	?>
	<div data-dismissible="crio-theme-nag-7" class="notice notice-error is-dismissible">
		<p><?php printf( __( '<b>Crio</b> needs to be the active theme on your site for you to use the features of <b>Crio Premium</b> plugin! %1$s', 'crio-premium' ), $link ); // phpcs:ignore ?></p>
	</div>
	<?php
}

/**
 * Add Crio theme nag notice.
 *
 * This is added when Crio is not the current active theme,
 * or the parent theme in the case of a child theme of Crio
 * being used.  The nag, if dismissed, appears again after 7
 * days.  Change '7' where it appears below to desired number
 * of days if it needs to be different.
 *
 * @since 1.0.0
 */
function crio_version_notice() {
	if ( ! PAnD::is_admin_notice_active( 'crio_version_notice-7' ) ) {
		return;
	}

	?>
	<div data-dismissible="crio_version_notice-7" class="notice notice-error is-dismissible">
		<p><?php printf( __( '<b>Crio</b> needs to be version %1$s or newer to use this version of Crio Premium!', 'crio-premium' ), REQUIRED_CRIO_VERSION ); // phpcs:ignore ?></p>
	</div>
	<?php
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_crio_premium() {

	// Load BG Lib if PHP/WP requirements checks pass.
	crio_premium_load_library();

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-crio-premium.php';

	// Add theme nag notices if Crio is not the active theme.
	add_action( 'admin_init', array( 'PAnD', 'init' ) );
	add_action( 'admin_notices', 'crio_premium_theme_nag' );

	$theme = wp_get_theme();
	if ( version_compare( REQUIRED_CRIO_VERSION, $theme->version, 'gt' ) ) {
		add_action( 'admin_notices', 'crio_version_notice' );
	} else {
		// Create and run instance of Crio Premium.
		$plugin = new Crio_Premium();
		$plugin->run();
	}
}
