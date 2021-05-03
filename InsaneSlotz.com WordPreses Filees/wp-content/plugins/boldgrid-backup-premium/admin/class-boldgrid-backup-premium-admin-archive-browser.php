<?php
/**
 * Archive Browser class.
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
 * Amazon S3 class.
 *
 * @since 1.0.0
 */
class Boldgrid_Backup_Premium_Admin_Archive_Browser {

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
	 * Enqueue scripts on the archive details page.
	 *
	 * @since 1.5.3
	 */
	public function enqueue_archive_details() {
		wp_register_script(
			'boldgrid-backup-premium-admin-zip-browser',
			plugin_dir_url( __FILE__ ) . 'js/boldgrid-backup-premium-admin-zip-browser.js',
			array( 'jquery' ),
			BOLDGRID_BACKUP_PREMIUM_VERSION
		);
		$translations = array(
			'restoring'    => __( 'Restoring', 'boldgrid-backup' ),
			'unknownError' => __( 'An unknown error occurred when attempting to restore this file.', 'boldgrid-backup' ),
		);
		wp_localize_script( 'boldgrid-backup-premium-admin-zip-browser', 'boldgrid_backup_premium_zip_browser', $translations );
		wp_enqueue_script( 'boldgrid-backup-premium-admin-zip-browser' );
	}

	/**
	 * Provide a list of premium features for one file in an archive.
	 *
	 * For example, allow the user to restore one file from an archive.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $response Response.
	 * @param  string $file     File.
	 * @return string
	 */
	public function wp_ajax_file_actions( $response, $file ) {
		$response = sprintf( '
			<a class="restore">%1$s</a><span class="dashicons dashicons-editor-help" data-id="restore-file"></span> | <a class="all-versions" href="admin.php?page=boldgrid-backup-historical&file=%3$s">%2$s</a>
			<p class="help" data-id="restore-file">%4$s</p>',
			__( 'Restore this version', 'boldgrid-backup' ),
			__( 'Find other versions to restore', 'boldgrid-backup' ),
			$file,
			__( 'When you choose to restore a single file from backup, a copy of the file is made just before it is overwritten. You can restore this file on the <strong>Find other versions to restore</strong> page.', 'boldgrid-backup' )
		);

		return $response;
	}

	/**
	 * Restore one file (via an ajax call).
	 *
	 * @since 1.5.3
	 */
	public function wp_ajax_restore_file() {
		$error = __( 'An error occurred while attempting to restore this file:', 'boldgrid-backup' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( $error . ' ' . __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! check_ajax_referer( 'boldgrid_backup_remote_storage_upload', 'security', false ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$filename = ! empty( $_POST['filename'] ) ? $_POST['filename'] : false; // phpcs:ignore
		$filepath = $this->core->backup_dir->get_path_to( $filename );
		$file = ! empty( $_POST['file'] ) ? $_POST['file'] : false; // phpcs:ignore
		if ( ! $this->core->archive->is_archive( $filepath ) || empty( $file ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid file / filepath.', 'boldgrid-backup' ) );
		}

		$this->premium_core->historical->save( $file );

		$zip    = new Boldgrid_Backup_Admin_Compressor_Pcl_Zip( $this->core );
		$status = $zip->extract_one( $filepath, $file );

		if ( ! $status ) {
			$error_message = ! empty( $zip->test_errors ) ? implode( '<br />', $zip->test_errors ) : __( 'Unknown error', 'boldgrid-backup' );
			wp_send_json_error( $error_message );
		}

		$this->premium_core->history->add( sprintf(
			// Translators: 1: Filename, 2: File path.
			__( 'A copy of %1$s has been restored from this archive file: %2$s.', 'boldgrid-bacup' ),
			$file,
			$filepath
		) );

		wp_send_json_success( __( '&#10003; Restored', 'boldgrid-backup' ) );
	}
}
