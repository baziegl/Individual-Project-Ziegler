<?php // phpcs:ignore
/**
 * Amazon S3 Backups Page class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.0.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Amazon S3 Backups Page class.
 *
 * @since 1.0.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3_Backups_Page {
	/**
	 * An instance of Boldgrid_Backup_Admin_Core.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var   Boldgrid_Backup_Premium_Admin_Core
	 */
	private $core;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var   Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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
	 * @since 1.5.4
	 *
	 * @param string $hook Hook name.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'total-upkeep_page_boldgrid-backup' !== $hook ) {
			return;
		}

		$handle = 'boldgrid-backup-premium-admin-amazon-s3';
		wp_register_script(
			$handle,
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/boldgrid-backup-premium-admin-amazon-s3.js',
			array( 'jquery' ),
			BOLDGRID_BACKUP_PREMIUM_VERSION
		);
		$translation = array(
			'downloading' => __( 'Downloading', 'boldgrid-backup' ),
		);
		wp_localize_script( $handle, 'boldgrid_backup_premium_admin_amazon_s3', $translation );
		wp_enqueue_script( $handle );
	}

	/**
	 * If a local backup is on Amazon S3 too, update verbiage to reflect.
	 *
	 * @since 1.5.4
	 *
	 * @param  array  $locations Locations.
	 * @param  string $filepath File path.
	 * @return array
	 */
	public function backup_locations( array $locations, $filepath ) {
		if ( $this->premium_core->amazon_s3->in_bucket( null, $filepath ) ) {
			$locations[] = __( 'Amazon S3', 'boldgrid-backup' );
		}

		return $locations;
	}

	/**
	 * Handle the ajax request to download an Amazon S3 backup locally.
	 *
	 * @since 1.5.4
	 */
	public function wp_ajax_download() {
		$error = __( 'Unable to download backup from Amazon S3', 'bolgrid-bakcup' );

		// Validation, user role.
		if ( ! current_user_can( 'update_plugins' ) ) {
			$this->core->notice->add_user_notice(
				sprintf( $error . ': ' . __( 'Permission denied.', 'boldgrid-backup' ) ),
				'notice notice-error'
			);
			wp_send_json_error();
		}

		// Validation, nonce.
		if ( ! $this->core->archive_details->validate_nonce() ) {
			$this->core->notice->add_user_notice(
				sprintf( $error . ': ' . __( 'Invalid nonce.', 'boldgrid-backup' ) ),
				'notice notice-error'
			);
			wp_send_json_error();
		}

		// Validation, $_POST data.
		$key = ! empty( $_POST['filename'] ) ? $_POST['filename'] : false; // phpcs:ignore
		if ( empty( $key ) ) {
			$this->core->notice->add_user_notice(
				sprintf( $error . ': ' . __( 'Invalid key.', 'boldgrid-backup' ) ),
				'notice notice-error'
			);
			wp_send_json_error();
		}

		$result = $this->premium_core->amazon_s3->download( $key );

		if ( $result ) {
			$this->core->notice->add_user_notice(
				sprintf(
					// Translators: 1: Key, 2: Title.
					__( '<h2>%2$s</h2><p>Backup file <strong>%1$s</strong> successfully downloaded from Amazon S3.</p>', 'boldgrid-backup' ),
					/* 1 */ $key,
					/* 2 */ BOLDGRID_BACKUP_PREMIUM_TITLE . ' - ' . __( 'Amazon S3 Download', 'boldgrid-backup' )
				),
				'notice notice-success'
			);
			wp_send_json_success();
		}

		if ( ! empty( $this->premium_core->amazon_s3->errors ) ) {
			$this->core->notice->add_user_notice(
				implode( '<br />', $this->premium_core->amazon_s3->errors ),
				'notice notice-error'
			);
			wp_send_json_error();
		}

		wp_send_json_error();
	}
}
