<?php // phpcs:ignore
/**
 * Generic S3 Hooks class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.2.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Premium_Admin_Remote_S3_Hooks
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Hooks {
	/**
	 * The provider this page is for.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Provider
	 * @access private
	 */
	private $provider;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider
	 */
	public function __construct( Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider ) {
		$this->provider = $provider;
	}

	/**
	 * Upload a backup via an ajax request.
	 *
	 * This is done via the archive details of a single archive.
	 *
	 * @since 1.2.0
	 */
	public function ajax_upload() {
		$core = $this->provider->get_core();

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! $core->archive_details->validate_nonce() ) {
			wp_send_json_error( __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		// Get our filepath based on filename.
		$filename = ! empty( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : false; // phpcs:ignore
		$filepath = $core->backup_dir->get_path_to( $filename );

		if ( empty( $filename ) || ! $core->wp_filesystem->exists( $filepath ) ) {
			wp_send_json_error( __( 'Invalid archive filename.', 'boldgrid-backup' ) );
		}

		$success = $this->provider->upload( $filepath );

		if ( $success ) {
			wp_send_json_success();
		} else {
			$error = $this->provider->get_uploader()->has_error()
				? implode( '<br />', $this->provider->get_uploader()->get_errors() )
				: esc_html__( 'Unknown error.', 'boldgrid-backup' );

			wp_send_json_error( $error );
		}
	}

	/**
	 * Hook into the filter to add all of our provider's backups to the full list of backups.
	 *
	 * @since 1.2.0
	 */
	public function filter_get_all() {
		$core = $this->provider->get_core();

		$bucket = $this->provider->get_bucket();

		if ( empty( $bucket ) ) {
			return;
		}

		$bucket->set_objects();

		if ( $bucket->has_errors() ) {
			$core->notice->boldgrid_backup_notice(
				implode( '<br />', $bucket->get_errors() ),
				'notice notice-error is-dismissible'
			);

			return;
		}

		$bucket->set_backups();

		foreach ( $bucket->get_backups() as $object ) {
			$backup = array(
				'filename'      => $object['Key'],
				'last_modified' => ! empty( $object['Metadata']['lastmodified'] ) ? $object['Metadata']['lastmodified'] : strtotime( $object['LastModified'] ),
				'size'          => $object['Size'],
				'locations'     => array(
					array(
						'title'            => $this->provider->get_nickname(),
						'on_remote_server' => true,
						'title_attr'       => $this->provider->get_title(),
					),
				),
			);

			$core->archives_all->add( $backup );
		}
	}

	/**
	 * Determine if provider is setup.
	 *
	 * @since 1.2.0
	 */
	public function is_setup_ajax() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! check_ajax_referer( 'boldgrid_backup_settings', 'security', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		// Settings and location needed within the storage-location.php file.
		$settings = $this->provider->get_core()->settings->get_settings();
		$location = $this->provider->get_details();
		$tr       = include BOLDGRID_BACKUP_PATH . '/admin/partials/settings/storage-location.php';

		$client = $this->provider->get_client();

		! empty( $client ) && $client->is_valid() ? wp_send_json_success( $tr ) : wp_send_json_error( $tr );
	}

	/**
	 * Actions to take after a backup file has been generated.
	 *
	 * @since 1.2.0
	 *
	 * @param array $info An array of info about our backup.
	 */
	public function post_archive_files( array $info ) {
		$core = $this->provider->get_core();

		/*
		 * We only want to add this to the jobs queue if we're in the middle of an automatic backup.
		 * If the user simply clicked on "Backup site now", we don't want to automatically send the
		 * backup to Google, there's a button for that.
		 */
		if ( ! $core->doing_cron ) {
			return;
		}

		$enabled = $this->provider->get_setting( 'enabled' );

		if ( ! $enabled || $info['dryrun'] || ! $info['save'] ) {
			return;
		}

		$args = array(
			'filepath'     => $info['filepath'],
			'action'       => 'boldgrid_backup_' . $this->provider->get_key() . '_upload_post_archive',
			'action_data'  => $info['filepath'],
			'action_title' => sprintf(
				// Translators: 1 the name of our provider, such as DreamObjects.
				__( 'Upload backup file to %1$s', 'boldgrid-backup' ),
				$this->provider->get_title()
			),
		);

		$core->jobs->add( $args );
	}

	/**
	 * Register our Provider as a storage location.
	 *
	 * When you go to the settings page and see a list of storage providers, each of those storage providers needs to
	 * hook into the "boldgrid_backup_register_storage_location" filter and add themselves.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $storage_locations An array of storage locations.
	 * @return array                    An updated array of storage locations.
	 */
	public function register_storage_location( array $storage_locations ) {
		$storage_locations[] = $this->provider->get_details();

		return $storage_locations;
	}

	/**
	 * Add the one click upload from an archive's details page.
	 *
	 * @since 1.2.0
	 *
	 * @param string $filepath Path to our archive.
	 */
	public function single_archive_remote_option( $filepath ) {
		$filename = basename( $filepath );

		// Determine if the remote service is setup.
		$is_setup = $this->provider->has_client() && $this->provider->get_client()->is_valid();

		// Determine if a backup file exists on the remote server.
		$uploaded = $this->provider->has_bucket() && $this->provider->get_bucket()->has_object_key( $filename );

		$storage = array(
			'id'           => $this->provider->get_key(),
			'title'        => $this->provider->get_nickname(),
			'title_attr'   => $this->provider->get_title(),
			'uploaded'     => $uploaded,
			'allow_upload' => $is_setup,
			'is_setup'     => $is_setup,
		);

		$this->provider->get_core()->archive_details->remote_storage_li[] = $storage;
	}

	/**
	 * Upload a file.
	 *
	 * The jobs queue will call this method to upload a file.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $filepath File path.
	 * @return bool
	 */
	public function upload_post_archiving( $filepath ) {
		return $this->provider->upload( $filepath );
	}

	/**
	 * Download a backup file.
	 *
	 * @since 1.2.0
	 */
	public function wp_ajax_download() {
		$core = $this->provider->get_core();

		// translators: 1: A generic error from our provider.
		$error = __( 'Unable to download backup from %1$s: %2$s', 'bolgrid-bakcup' );

		// Validation, user role.
		if ( ! current_user_can( 'update_plugins' ) ) {
			$message = sprintf( $error, $this->provider->get_title(), __( 'Permission denied.', 'boldgrid-backup' ) );
			$core->notice->add_user_notice( $message, 'notice notice-error' );
			wp_send_json_error();
		}

		// Validation, nonce.
		if ( ! $core->archive_details->validate_nonce() ) {
			$message = sprintf( $error, $this->provider->get_title(), __( 'Invalid nonce.', 'boldgrid-backup' ) );
			$core->notice->add_user_notice( $message, 'notice notice-error' );
			wp_send_json_error();
		}

		// Validation, $_POST data.
		$filename = ! empty( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : false; // phpcs:ignore
		if ( empty( $filename ) ) {
			$message = sprintf( $error, $this->provider->get_title(), __( 'Invalid filename.', 'boldgrid-backup' ) );
			$core->notice->add_user_notice( $message, 'notice notice-error' );
			wp_send_json_error();
		}

		$bucket = $this->provider->get_bucket();

		$path = $core->backup_dir->get_path_to( $filename );

		$success = $bucket->download_key( $filename, $path );

		if ( $success ) {
			$notice = '<h2>' . wp_kses(
				sprintf(
					// Translators: 1 The name of our provider title, such as DreamObjects.
					BOLDGRID_BACKUP_PREMIUM_TITLE . ' - ' . __( '%1$s Download', 'boldgrid-backup' ),
					$this->provider->get_title()
				),
				[]
			) . '</h2>';

			$notice .= '<p>' . wp_kses(
				sprintf(
					// Translators: 1 an opening strong tag, 2 its closing strong tag, 3 the filename of the backup just downloaded, 4 the name of our provider (such as DreamObjects).
					__( 'Backup file %1$s%3$s%2$s successfully downloaded from %4$s.', 'boldgrid-backup' ),
					'<strong>',
					'</strong>',
					$filename,
					$this->provider->get_title()
				),
				[ 'strong' => [] ]
			) . '</p>';

			$core->notice->add_user_notice( $notice, 'notice notice-success' );

			wp_send_json_success();
		} else {
			$bucket_errors = $bucket->has_errors()
				? implode( '<br />', $bucket->get_errors() )
				: __( 'Unknown error', 'boldgrid-backup' );

			$message = sprintf( $error, $this->provider->get_title(), $bucket_errors );

			$core->notice->add_user_notice( $message, 'notice notice-error' );

			wp_send_json_error();
		}
	}
}
