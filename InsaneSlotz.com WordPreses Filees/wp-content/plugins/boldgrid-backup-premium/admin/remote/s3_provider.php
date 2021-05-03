<?php // phpcs:ignore
/**
 * Generic S3 class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.2.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Generic S3 class.
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Provider extends Boldgrid_Backup_Premium_Admin_Remote_Provider {
	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_S3_Client.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Client
	 * @access private
	 */
	private $client;

	/**
	 * Our transient class.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Transient
	 * @access private
	 */
	private $transient;

	/**
	 * Our uploader.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Uploader
	 */
	private $uploader;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Client
	 * @access protected
	 */
	protected $bucket;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() { // phpcs:ignore
		parent::__construct();
	}

	/**
	 * Enforce retention.
	 *
	 * @since 1.2.0
	 */
	public function enforce_retention() {
		$retention_count = $this->get_setting( 'retention_count' );

		$bucket = $this->get_bucket();

		$bucket->enforce_retention( $retention_count, $this->title );
	}

	/**
	 * Get our bucket.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket
	 */
	public function get_bucket() {
		if ( is_null( $this->bucket ) ) {
			$client = $this->get_client();

			$bucket_id = $this->get_setting( 'bucket_id' );

			if ( ! empty( $client ) && ! empty( $bucket_id ) ) {
				$this->bucket = new Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket( $client, $bucket_id );
			}
		}

		return $this->bucket;
	}

	/**
	 * Get our client.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Premium_Admin_Remote_S3_Client
	 */
	public function get_client() {
		if ( is_null( $this->client ) ) {
			$settings = $this->remote_settings->get_settings();

			// Only try to initialize the client if we have the needed settings.
			if ( $this->remote_settings->has_setting_keys( [ 'key', 'secret', 'host' ] ) ) {
				$this->client = new Boldgrid_Backup_Premium_Admin_Remote_S3_Client( [
					'key'      => $settings['key'],
					'secret'   => $settings['secret'],
					'endpoint' => $settings['host'],
				]);

				$this->client->set_provider( $this );
			}
		}

		return $this->client;
	}

	/**
	 * Get details
	 *
	 * @since 1.2.0
	 *
	 * @param  bool $try_cache Whether or not to use last_login to validate the Dreamobjects
	 *                         account. Please see param definition in $this->is_setup().
	 * @return array
	 */
	public function get_details( $try_cache = false ) {
		$client = $this->get_client();

		$is_setup = ! empty( $client ) && $client->is_valid();

		$enabled = $this->get_setting( 'enabled' );

		$details = array(
			'title'     => $this->title,
			'key'       => $this->key,
			'configure' => 'admin.php?page=boldgrid-backup-' . $this->key,
			'is_setup'  => $is_setup,
			'enabled'   => $enabled && $is_setup,
		);

		return $details;
	}

	/**
	 * Get our transient class.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Premium_Admin_Remote_S3_Transient
	 */
	public function get_transient() {
		if ( is_null( $this->transient ) ) {
			$this->transient = new Boldgrid_Backup_Premium_Admin_Remote_S3_Transient( $this );
		}

		return $this->transient;
	}

	/**
	 * Get our uploader.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Premium_Admin_Remote_S3_Uploader
	 */
	public function get_uploader() {
		return $this->uploader;
	}

	/**
	 * Determine whether or not this provider has a bucket.
	 *
	 * @since 1.2.1
	 *
	 * @return bool
	 */
	public function has_bucket() {
		$bucket = $this->get_bucket();

		return ! empty( $bucket );
	}

	/**
	 * Determine whether or not this provider has a client.
	 *
	 * @since 1.2.1
	 *
	 * @return bool
	 */
	public function has_client() {
		$client = $this->get_client();

		return ! empty( $client );
	}

	/**
	 * Upload a file.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $filepath Path to file to upload.
	 * @return bool   True on success.
	 */
	public function upload( $filepath ) {
		$bucket = $this->get_bucket();

		$this->uploader = new Boldgrid_Backup_Premium_Admin_Remote_S3_Uploader();

		$success = $this->uploader->upload( $bucket, $filepath );

		if ( $success ) {
			$this->enforce_retention();

			/**
			 * File uploaded to remote storage location.
			 *
			 * @since 1.2.0
			 *
			 * @param string DreamObjects
			 * @param string $filepath
			 */
			do_action( 'boldgrid_backup_remote_uploaded', $this->title, $filepath );
		}

		return $success;
	}
}
