<?php
/**
 * Fired during plugin activation
 *
 * @link https://www.boldgrid.com
 * @since 1.0.0
 *
 * @package Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/includes
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/includes
 * @author BoldGrid.com <wpb@boldgrid.com>
 */
class Boldgrid_Backup_Premium_Activator {
	/**
	 * Plugin activation.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		/**
		 * Clear license data.
		 *
		 * @since 1.0.0
		 */
		do_action( 'Boldgrid\Library\License\clearTransient' ); // phpcs:ignore
	}
}
