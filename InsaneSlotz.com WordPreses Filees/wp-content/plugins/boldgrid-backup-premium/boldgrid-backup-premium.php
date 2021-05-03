<?php
/**
 * File: boldgrid-backup-premium.php
 *
 * @link    http://www.boldgrid.com
 * @since   1.0.0
 * @package Boldgrid_Backup
 *
 *          @wordpress-plugin
 *          Plugin Name: Total Upkeep Premium
 *          Plugin URI: https://www.boldgrid.com/boldgrid-backup/
 *          Description: Premium extension for the Total Upkeep plugin.
 *          Version: 1.5.8
 *          Author: BoldGrid
 *          Author URI: https://www.boldgrid.com/
 *          License: GPL-2.0+
 *          License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *          Text Domain: boldgrid-backup
 *          Domain Path: /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die();
}

if ( ! defined( 'BOLDGRID_BACKUP_PREMIUM_VERSION' ) ) {
	define( 'BOLDGRID_BACKUP_PREMIUM_VERSION', implode( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
}

if ( ! defined( 'BOLDGRID_BACKUP_PREMIUM_PATH' ) ) {
	define( 'BOLDGRID_BACKUP_PREMIUM_PATH', dirname( __FILE__ ) );
}

// The minimum version required of the parent plugin to support this premium version.
if ( ! defined( 'BOLDGRID_BACKUP_MIN_VERSION_FOR_PREMIUM' ) ) {
	define( 'BOLDGRID_BACKUP_MIN_VERSION_FOR_PREMIUM', '1.14.3' );
}

// Define The plugin title.
if ( ! defined( 'BOLDGRID_BACKUP_PREMIUM_TITLE' ) ) {
	define( 'BOLDGRID_BACKUP_PREMIUM_TITLE', 'Total Upkeep Premium' );
}

/**
 * Activation.
 */
function activate_boldgrid_backup_premium() {
	require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/includes/class-boldgrid-backup-premium-activator.php';
	Boldgrid_Backup_Premium_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_boldgrid_backup_premium' );

/**
 * Deactivation.
 */
function deactivate_boldgrid_backup_premium() {
	require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/includes/class-boldgrid-backup-premium-deactivator.php';
	Boldgrid_Backup_Premium_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_boldgrid_backup_premium' );

/*
 * We must include this file so the "Boldgrid_Backup_Premium" class exists for
 * later use.
 */
require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/includes/class-boldgrid-backup-premium.php';
