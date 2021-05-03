<?php // phpcs:ignore
/**
 * Amazon S3 Bucket class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.0.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Amazon S3 Bucket class.
 *
 * @since 1.0.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3_Bucket {
	/**
	 * Errors.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $errors = array();

	/**
	 * The core class object.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
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
	 * Create a bucket.
	 *
	 * If the bucket already exists and this user owns it, we'll return true.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $bucket_id Bucket id.
	 * @return bool
	 */
	public function create( $bucket_id ) {
		// Validate bucket name.
		$valid_name = $this->premium_core->amazon_s3->client->isBucketDnsCompatible( $bucket_id );
		if ( ! $valid_name ) {
			$this->premium_core->amazon_s3->errors[] = __( 'Invalid Bucket ID. Bucket name must be between 3 and 63 characters long, must not end with a dash or period, and must not use any special characters.', 'boldgrid-backup' );
			return false;
		}

		try {
			$this->premium_core->amazon_s3->client->createBucket( array(
				'Bucket' => $bucket_id,
			));
		} catch ( Aws\S3\Exception\BucketAlreadyOwnedByYouException $e ) {
			return true;
		} catch ( Aws\S3\Exception\BucketAlreadyExistsException $e ) {
			$this->premium_core->amazon_s3->errors[] = sprintf(
				// Translators: 1: Bucket id.
				__( 'Bucket ID %1$s already exist. Please try another Bucket ID.', 'boldgrid-backup' ),
				'<strong>' . esc_html( $bucket_id ) . '</strong>'
			);
			return false;
		} catch ( Exception $e ) {
			$this->premium_core->amazon_s3->errors[] = __( 'Unknown error when attempting to create bucket:', 'boldgrid-backup' ) . ' - ' . $e->getMessage();
			return false;
		}

		return true;
	}

	/**
	 * Delete bucket transients.
	 *
	 * @since 1.5.4
	 */
	public function delete_transients() {
		$bucket_id = $this->premium_core->amazon_s3->bucket_id;

		$transient = sprintf( 'boldgrid_backup_s3_bucket_w_headers_%1$s', $bucket_id );
		delete_transient( $transient );

		$transient = sprintf( 'boldgrid_backup_s3_bucket_%1$s', $bucket_id );
		delete_transient( $transient );
	}

	/**
	 * Get the contents of a bucket.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $bucket_id Bucket id.
	 * @param  bool   $include_headers Include headers.
	 * @param  bool   $use_transient   Whether or not to first try to get our
	 *                                 bucket from the transient. In some situations
	 *                                 we need fresh data, and we can pass true
	 *                                 to get fresh data from Amazon.
	 * @return array  https://pastebin.com/uVkx8t5A
	 */
	public function get( $bucket_id = null, $include_headers = false, $use_transient = true ) {

		// Shorten for readability.
		$s3 = $this->premium_core->amazon_s3;

		$s3->set_client();
		if ( empty( $s3->client ) ) {
			return array();
		}

		if ( ! empty( $bucket_id ) ) {
			$s3->set_bucket_id( $bucket_id );
		}

		if ( ! $s3->client->isBucketDnsCompatible( $s3->bucket_id ) ) {
			return array();
		}

		$transient_name = sprintf(
			'boldgrid_backup_s3_bucket_%1$s%2$s',
			$include_headers ? 'w_headers_' : '',
			$s3->bucket_id
		);

		// Save resources and try to get bucket contents from transient.
		if ( $use_transient ) {
			$bucket_contents = get_transient( $transient_name );
			if ( false !== $bucket_contents ) {
				return $bucket_contents;
			}
		}

		$bucket_contents = array();

		// If the bucket does not exist, return an empty bucket.
		try {
			$iterator = $s3->client->getIterator( 'ListObjects', array(
				'Bucket' => $s3->bucket_id,
			) );

			foreach ( $iterator as $object ) {
				if ( ! $this->core->archive->is_site_archive( $object['Key'] ) ) {
					continue;
				}

				if ( $include_headers ) {
					$object['Headers'] = $s3->get_headers( $object['Key'] );
				}

				$bucket_contents[] = $object;
			}
		} catch ( Aws\S3\Exception\NoSuchBucketException $e ) {
			return array();
		}

		set_transient( $transient_name, $bucket_contents, 5 * MINUTE_IN_SECONDS );

		return $bucket_contents;
	}

	/**
	 * Get an item from the bucket.
	 *
	 * @since 1.5.4
	 *
	 * @param  string $key Key.
	 * @return mixed Array on success, false on failure.
	 */
	public function get_item( $key ) {
		$bucket_contents = $this->get();

		foreach ( $bucket_contents as $item ) {
			if ( $item['Key'] === $key ) {
				return $item;
			}
		}

		return false;
	}

	/**
	 * Validate a local backup matches the remove backup.
	 *
	 * @since 1.5.4
	 *
	 * @param string $key Key.
	 */
	public function validate_backup( $key ) {
		$item = $this->get_item( $key );

		$local_path = $this->core->backup_dir->get_path_to( $key );
		$local      = $this->core->wp_filesystem->dirlist( $local_path );

		$remote_size = ! empty( $item['Size'] ) ? intval( $item['Size'] ) : null;
		$local_size  = ! empty( $local[ $key ]['size'] ) ? intval( $local[ $key ]['size'] ) : null;

		if ( empty( $remote_size ) || empty( $local_size ) ) {
			return false;
		}

		$same_size = $remote_size === $local_size;
		if ( ! $same_size ) {
			$this->errors[] = sprintf(
				// Translators: 1: Local file size, 2: Remote file size.
				__( 'Downloaded filesize (%1$s) does not match remote filesize (%2$s).', 'boldgrid-backup' ),
				$local_size,
				$remote_size
			);
		}

		return $same_size;
	}
}
