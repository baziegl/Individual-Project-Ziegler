<?php // phpcs:ignore
/**
 * File: google_drive_page.php
 *
 * @link  https://www.boldgrid.com
 * @since 1.1.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

// phpcs:disable WordPress.VIP

/**
 * Build the settings page for Google Drive.
 *
 * @since 1.1.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Page {
	/**
	 * The core class object.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Default folder name.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $default_folder_name;

	/**
	 * Default folder type.
	 *
	 * A full explanation of this property can be found in
	 * Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Folder::get_backup_id
	 *
	 * @since 1.4.0
	 * @access private
	 * @var string
	 */
	private $default_folder_type = 'name';

	/**
	 * Default nickname.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $default_nickname = 'Google Drive';

	/**
	 * Default retention count.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var int
	 */
	private $default_retention_count = 5;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param Boldgrid_Backup_Admin_Core         $core         Core class object.
	 * @param Boldgrid_Backup_Premium_Admin_Core $premium_core Premium Core class object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core, Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core                = $core;
		$this->premium_core        = $premium_core;
		$this->default_folder_name = esc_html( site_url() );
	}

	/**
	 * Add submenu page.
	 *
	 * @since 1.1.0
	 */
	public function add_submenu_page() {
		$capability = 'administrator';

		add_submenu_page(
			null,
			__( 'Google Drive Settings', 'boldgrid-backup' ),
			__( 'Google Drive Settings', 'boldgrid-backup' ),
			$capability,
			'boldgrid-backup-google-drive',
			array(
				$this,
				'page',
			)
		);
	}

	/**
	 * Get the default folder name.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_default_folder_name() {
		return $this->default_folder_name;
	}

	/**
	 * Get the default retention count.
	 *
	 * @since 1.1.0
	 *
	 * @return int
	 */
	public function get_default_retention_count() {
		return $this->default_retention_count;
	}

	/**
	 * Get our nickname.
	 *
	 * If we don't have a nickname set, it will be our default nickname, 'Google Drive'.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_nickname() {
		return $this->premium_core->google_drive->settings->get_setting( 'nickname', $this->default_nickname );
	}

	/**
	 * Determine whether or not the request to save / delete settings is authorized.
	 *
	 * This logic in this method originally lived in self::maybe_save(), but was moved here when the
	 * self::maybe_delete() method was added and the code needed to be reused.
	 *
	 * @since 1.3.2
	 *
	 * @return bool True if user is authorized.
	 */
	private function is_authorized() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			do_action( 'boldgrid_backup_notice', __( 'Unauthorized request.', 'boldgrid-backup' ), 'notice error is-dismissible' );
			return false;
		}

		if ( ! check_ajax_referer( 'bgbkup-gd-settings', 'gd_auth', false ) ) {
			do_action( 'boldgrid_backup_notice', __( 'Unauthorized request, expired nonce.', 'boldgrid-backup' ), 'notice error is-dismissible' );
			return false;
		}

		return true;
	}

	/**
	 * Render our settings page.
	 *
	 * @since 1.1.0
	 */
	public function page() {
		wp_enqueue_style( 'boldgrid-backup-admin-hide-all' );

		$this->maybe_save();
		$this->maybe_delete();

		// Vars needed for the google_drive.php page.
		$folder_type     = $this->premium_core->google_drive->settings->get_setting( 'folder_type', 'name' );
		$folder_name     = $this->premium_core->google_drive->settings->get_setting( 'folder_name', $this->default_folder_name );
		$retention_count = $this->premium_core->google_drive->settings->get_setting( 'retention_count', 5 );
		$nickname        = $this->get_nickname();

		include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/remote/google_drive.php';
	}

	/**
	 * If need be, delete the user's Google Drive settings.
	 *
	 * This means they will need to reauthorize.
	 *
	 * @since 1.3.2
	 *
	 * @return bool True on success.
	 */
	private function maybe_delete() {
		// If we're not deleting settings, abort.
		if ( empty( $_POST['delete_settings'] ) ) { // phpcs:ignore
			return false;
		}

		if ( ! $this->is_authorized() ) {
			return false;
		}

		$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ . ' Deleting Google Drive settings.' );
		$this->premium_core->google_drive->settings->delete_settings();

		do_action( 'boldgrid_backup_notice', __( 'Settings deleted.', 'boldgrid-backup' ), 'notice updated is-dismissible' );

		return true;
	}

	/**
	 * Process the user's request to update their Google Drive settings.
	 *
	 * @since 1.1.0
	 *
	 * @return bool Whether or not we saved settings.
	 */
	public function maybe_save() {
		if ( empty( $_POST['save_settings'] ) ) { // phpcs:ignore
			return false;
		}

		if ( ! $this->is_authorized() ) {
			return false;
		}

		// Nonce verification done in the is_authorized() call above.
		$folder_name     = ! empty( $_POST['folder_name'] ) ? sanitize_text_field( $_POST['folder_name'] ) : $this->default_folder_name; // phpcs:ignore
		$folder_type     = ! empty( $_POST['folder_type'] ) ? sanitize_text_field( $_POST['folder_type'] ) : $this->default_folder_type; // phpcs:ignore
		$retention_count = ! empty( $_POST['retention_count'] ) ? intval( $_POST['retention_count'] ) : $this->default_retention_count; // phpcs:ignore
		$nickname        = ! empty( $_POST['nickname'] ) ? sanitize_text_field( $_POST['nickname'] ) : $this->default_nickname; // phpcs:ignore

		$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ . ' Updating Google Drive settings.' );
		$this->premium_core->google_drive->settings->save_setting( 'folder_name', $folder_name );
		$this->premium_core->google_drive->settings->save_setting( 'folder_type', $folder_type );
		$this->premium_core->google_drive->settings->save_setting( 'retention_count', $retention_count );
		$this->premium_core->google_drive->settings->save_setting( 'nickname', $nickname );

		do_action( 'boldgrid_backup_notice', __( 'Settings saved.', 'boldgrid-backup' ), 'notice updated is-dismissible' );

		return true;
	}
}
