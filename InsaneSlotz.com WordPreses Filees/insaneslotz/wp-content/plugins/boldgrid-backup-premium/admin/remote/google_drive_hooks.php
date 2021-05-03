<?php // phpcs:ignore
/**
 * Google Drive Hooks class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.1.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Hooks
 *
 * @since 1.1.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Hooks {
	/**
	 * The core class object.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var   Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param Boldgrid_Backup_Admin_Core         $core         Boldgrid_Backup_Admin_Core object.
	 * @param Boldgrid_Backup_Premium_Admin_Core $premium_core Boldgrid_Backup_Premium_Admin_Core object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core, Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;
	}

	/**
	 * Upload a backup via an ajax request.
	 *
	 * This is done via the archive details of a single archive.
	 *
	 * @since 1.1.0
	 */
	public function ajax_upload() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! $this->core->archive_details->validate_nonce() ) {
			wp_send_json_error( __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$filename = ! empty( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : false; // phpcs:ignore
		$filepath = $this->core->backup_dir->get_path_to( $filename );
		if ( empty( $filename ) || ! $this->core->wp_filesystem->exists( $filepath ) ) {
			wp_send_json_error( __( 'Invalid archive filename.', 'boldgrid-backup' ) );
		}

		$archive = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Archive( $this->core, $this->premium_core, $filename );
		$success = $archive->upload();

		if ( $success ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( $archive->last_error );
		}
	}

	/**
	 * Check for an auth code and scope coming in via the query params.
	 *
	 * This method is called duruing admin_init, early enough so that we can do a redirect within
	 * the init() method if need be.
	 *
	 * @since 1.1.0
	 */
	public function check_for_auth() {
		if ( ! empty( $_GET['code'] ) && ! empty( $_GET['scope'] ) ) { // phpcs:ignore
			$this->premium_core->google_drive->client->init();
		}
	}

	/**
	 * Hook into the filter to add all Google Drive backups to the full list of backups.
	 *
	 * @since 1.1.0
	 */
	public function filter_get_all() {
		$files = $this->premium_core->google_drive->folder->get_files();
		$files = ! empty( $files['files'] ) ? $files['files'] : array();

		foreach ( $files as $file ) {
			$backup = array(
				'filename'      => $file['name'],
				'last_modified' => ! empty( $file['properties']['createdTime'] ) ? $file['properties']['createdTime'] : strtotime( $file['createdTime'] ),
				'size'          => $file['size'],
				'locations'     => array(
					array(
						'title'            => $this->premium_core->google_drive->page->get_nickname(),
						'on_remote_server' => true,
						'title_attr'       => $this->premium_core->google_drive->get_title(),
					),
				),
			);

			$this->core->archives_all->add( $backup );
		}
	}

	/**
	 * Determine if Google Drive is setup.
	 *
	 * @since 1.1.0
	 */
	public function is_setup_ajax() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! check_ajax_referer( 'boldgrid_backup_settings', 'security', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		// Settings and location needed within the storage-location.php file.
		$settings = $this->core->settings->get_settings();
		$location = $this->premium_core->google_drive->get_details();
		$tr       = include BOLDGRID_BACKUP_PATH . '/admin/partials/settings/storage-location.php';

		$this->core->ftp->is_setup() ? wp_send_json_success( $tr ) : wp_send_json_error( $tr );
	}

	/**
	 * Actions to take after a backup file has been generated.
	 *
	 * @since 1.1.0
	 *
	 * @param array $info An array of info about our backup.
	 */
	public function post_archive_files( array $info ) {
		/*
		 * We only want to add this to the jobs queue if we're in the middle of an automatic backup.
		 * If the user simply clicked on "Backup site now", we don't want to automatically send the
		 * backup to Google, there's a button for that.
		 */
		if ( ! $this->core->doing_cron ) {
			return;
		}

		if ( ! $this->premium_core->google_drive->settings->get_setting( 'enabled', false ) || $info['dryrun'] || ! $info['save'] ) {
			return;
		}

		$args = array(
			'filepath'     => $info['filepath'],
			'action'       => 'boldgrid_backup_google_drive_upload_post_archive',
			'action_data'  => $info['filepath'],
			'action_title' => __( 'Upload backup file to Google Drive', 'boldgrid-backup' ),
		);

		$this->core->jobs->add( $args );
	}

	/**
	 * Register Google Drive as a storage location.
	 *
	 * When you go to the settings page and see a list of storage providers, each of those storage providers needs to
	 * hook into the "boldgrid_backup_register_storage_location" filter and add themselves.
	 *
	 * @since 1.1.0
	 *
	 * @param  array $storage_locations An array of storage locations.
	 * @return array                    An updated array of storage locations.
	 */
	public function register_storage_location( array $storage_locations ) {
		$storage_locations[] = $this->premium_core->google_drive->get_details();

		return $storage_locations;
	}

	/**
	 * Add the one click upload from an archive's details page.
	 *
	 * @since 1.1.0
	 *
	 * @param string $filepath Path to our archive.
	 */
	public function single_archive_remote_option( $filepath ) {
		$filename = basename( $filepath );
		$archive  = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Archive( $this->core, $this->premium_core, $filename );
		$uploaded = $archive->is_uploaded();
		$is_setup = $this->premium_core->google_drive->is_setup();

		$storage = array(
			'id'           => $this->premium_core->google_drive->get_key(),
			'title'        => $this->premium_core->google_drive->page->get_nickname(),
			'title_attr'   => $this->premium_core->google_drive->get_title(),
			'uploaded'     => $uploaded,
			'allow_upload' => $is_setup,
			'is_setup'     => $is_setup,
		);

		$this->core->archive_details->remote_storage_li[] = $storage;
	}

	/**
	 * Upload a file.
	 *
	 * The jobs queue will call this method to upload a file.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $filepath File path.
	 * @return bool
	 */
	public function upload_post_archiving( $filepath ) {
		$filename = basename( $filepath );
		$archive  = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Archive( $this->core, $this->premium_core, $filename );
		$success  = $archive->upload();

		return $success;
	}

	/**
	 * Download a backup file.
	 *
	 * @since 1.1.0
	 */
	public function wp_ajax_download() {
		// translators: 1: A Google Drive error message.
		$error = __( 'Unable to download backup from Google Drive: %1$s', 'bolgrid-bakcup' );

		$allowed_html = array(
			'h2'     => array(),
			'p'      => array(),
			'strong' => array(),
		);

		// Validation, user role.
		if ( ! current_user_can( 'update_plugins' ) ) {
			$this->core->notice->add_user_notice( sprintf( $error, __( 'Permission denied.', 'boldgrid-backup' ) ), 'notice notice-error' );
			wp_send_json_error();
		}

		// Validation, nonce.
		if ( ! $this->core->archive_details->validate_nonce() ) {
			$this->core->notice->add_user_notice( sprintf( $error, __( 'Invalid nonce.', 'boldgrid-backup' ) ), 'notice notice-error' );
			wp_send_json_error();
		}

		// Validation, $_POST data.
		$filename = ! empty( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : false; // phpcs:ignore
		if ( empty( $filename ) ) {
			$this->core->notice->add_user_notice( sprintf( $error, __( 'Invalid filename.', 'boldgrid-backup' ) ), 'notice notice-error' );
			wp_send_json_error();
		}

		$archive = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Archive( $this->core, $this->premium_core, $filename );
		$result  = $archive->download();

		if ( $result ) {
			$this->core->notice->add_user_notice(
				wp_kses(
					sprintf(
						// translators: 1: Filename, 2: Premium plugin title.
						__(
							'<h2>%2$s - Google Drive Download</h2><p>Backup file <strong>%1$s</strong> successfully downloaded from Google Drive.</p>',
							'boldgrid-backup'
						),
						$filename,
						BOLDGRID_BACKUP_PREMIUM_TITLE
					), $allowed_html
				),
				'notice notice-success'
			);
			wp_send_json_success();
		} else {
			$this->core->notice->add_user_notice( sprintf( $error, $archive->last_error ), 'notice notice-error' );
			wp_send_json_error();
		}
	}
}
