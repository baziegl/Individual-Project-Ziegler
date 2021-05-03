<?php
/**
 * Plugin Editor class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.5.3
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Plugin Editor class.
 *
 * @since 1.5.3
 */
class Boldgrid_Backup_Premium_Admin_Plugin_Editor {
	/**
	 * The core class object.
	 *
	 * @since 1.5.3
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.5.3
	 * @var   Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.5.3
	 *
	 * @param Boldgrid_Backup_Admin_Core         $core         Boldgrid_Backup_Admin_Core object.
	 * @param Boldgrid_Backup_Premium_Admin_Core $premium_core Boldgrid_Backup_Premium_Admin_Core object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core, Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.5.3
	 *
	 * @param string $hook Hook name.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'plugin-editor.php' !== $hook ) {
			return;
		}

		$rel_plugin_path = str_replace( ABSPATH, '', dirname( BOLDGRID_BACKUP_PREMIUM_PATH ) ) . DIRECTORY_SEPARATOR;

		wp_register_script(
			'boldgrid-backup-premium-admin-plugin-editor',
			plugin_dir_url( __FILE__ ) . 'js/boldgrid-backup-premium-admin-plugin-editor.js',
			array( 'jquery' ),
			BOLDGRID_BACKUP_PREMIUM_VERSION
		);

		$lang = array(
			'rel_plugin_path' => $rel_plugin_path,
			'help'            => include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/plugin-editor.php',
			'error_saving'    => __( 'Error. Unable to create a copy of this file.', 'boldgrid-backup' ),
			'success_saving'  => __( 'Success. A copy of this file was made (or already existed).', 'boldgrid-backup' ),
			'save_a_copy'     => __( 'Save a copy before updating', 'boldgrid-backup' ),
			'find_a_version'  => __( 'Find a version to restore', 'boldgrid-backup' ),
		);
		wp_localize_script( 'boldgrid-backup-premium-admin-plugin-editor', 'boldgrid_backup_premium_admin_plugin_editor', $lang );
		wp_enqueue_script( 'boldgrid-backup-premium-admin-plugin-editor' );
	}

	/**
	 * Save a copy of a file.
	 *
	 * @since 1.5.3
	 */
	public function wp_ajax_save_copy() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		$plugin_file = ! empty( $_POST['pluginFile'] ) ? $_POST['pluginFile'] : false; // phpcs:ignore
		$file = ! empty( $_POST['file'] ) ? $_POST['file'] : false; // phpcs:ignore
		if ( empty( $file ) || empty( $plugin_file ) ) {
			wp_send_json_error( __( 'Invalid file / plugin file.', 'boldgrid-backup' ) );
		}

		// This check is similar to WordPress' check when saving a file.
		if ( ! check_ajax_referer( 'edit-plugin_' . $plugin_file, 'nonce', false ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$saved = $this->premium_core->historical->save( $file );

		if ( $saved ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}
}
