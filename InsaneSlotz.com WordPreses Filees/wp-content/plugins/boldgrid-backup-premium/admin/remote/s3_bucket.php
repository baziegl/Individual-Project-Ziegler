<?php // phpcs:ignore
/**
 * S3 Bucket class.
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
 * S3 Bucket class.
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket {
	/**
	 * An array of backups.
	 *
	 * @since 1.2.0
	 * @var array
	 * @access private
	 */
	private $backups;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_S3_Client.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Client
	 * @access private
	 */
	private $client;

	/**
	 * An array of error messages.
	 *
	 * @since 1.2.0
	 * @var array
	 * @access private
	 */
	private $errors = [];

	/**
	 * Our bucket id.
	 *
	 * @since 1.2.0
	 * @var string
	 * @access private
	 */
	private $id;

	/**
	 * An array of objects in a bucket.
	 *
	 * @since 1.2.0
	 * @var array
	 * @access private
	 */
	private $objects;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param Boldgrid_Backup_Premium_Admin_Remote_S3_Client $client Our S3 client.
	 * @param string                                         $id     Our bucket id.
	 */
	public function __construct( Boldgrid_Backup_Premium_Admin_Remote_S3_Client $client, $id ) {
		$this->client = $client;

		$this->id = $id;
	}

	/**
	 * Get our bucket id.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get an array of our backups within the bucket.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_backups() {
		return $this->backups;
	}

	/**
	 * Get an array of our backups, ordered by lastmodified desc.
	 *
	 * The first items in the array will have the largest lastmodified values, IE the newest files.
	 *
	 * This method is mainly used when enforcing retention.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_backups_desc() {
		$this->set_backups();

		// Sort by timestamp desc.
		usort( $this->backups, function( $a, $b ) {
			return $a['Metadata']['lastmodified'] < $b['Metadata']['lastmodified'] ? 1 : -1;
		} );

		return $this->backups;
	}

	/**
	 * Get our bucket's client.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Premium_Admin_Remote_S3_Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Get our errors.
	 *
	 * @since 1.2.0
	 *
	 * @return mixed An array if we have errors, null if we don't.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Get an object's headers.
	 *
	 * @since 1.2.0
	 *
	 * @var    string $key The object key.
	 * @return Guzzle\Service\Resource\Model Raw response from s3 server.
	 */
	public function get_object_headers( $key ) {
		$params = [
			'Bucket' => $this->id,
			'Key'    => $key,
		];

		return $this->client->get_client()->headObject( $params );
	}

	/**
	 * Get one set of data from the headers.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $object_key The object key to get the headers for.
	 * @param  string $header_key The key of the header to retrieve.
	 * @return mixed
	 */
	public function get_object_header( $object_key, $header_key ) {
		$headers = $this->get_object_headers( $object_key );

		return $headers->get( $header_key );
	}

	/**
	 * Get an object by key.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $key The key of the object to get.
	 * @return array The object.
	 */
	public function get_object( $key ) {
		$my_object = [];

		foreach ( $this->objects as $object ) {
			if ( $object['Key'] === $key ) {
				$my_object = $object;
				break;
			}
		}

		return $my_object;
	}

	/**
	 * Get this bucket's objects.
	 *
	 * Be sure to set them first.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_objects() {
		return $this->objects;
	}

	/**
	 * Whether or not this bucket has errors.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Whether or not a bucket has an object by key.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $key The object key to search for.
	 * @return bool
	 */
	public function has_object_key( $key ) {
		$has_object_key = false;

		$this->set_objects();

		foreach ( $this->objects as $object ) {
			if ( $object['Key'] === $key ) {
				$has_object_key = true;
			}
		}

		return $has_object_key;
	}

	/**
	 * Create a bucket if it does not already exist.
	 *
	 * @since 1.2.0
	 */
	public function maybe_create() {
		$buckets = new Boldgrid_Backup_Premium_Admin_Remote_S3_Buckets( $this->client );

		if ( ! $buckets->has_bucket( $this->id ) ) {
			$this->create();
		}
	}

	/**
	 * Enforce retention.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $retention_count The number of backups to keep.
	 * @param string $service_name    The name of the service.
	 */
	public function enforce_retention( $retention_count, $service_name ) {
		$found = 0;

		foreach ( $this->get_backups_desc() as $backup ) {
			$found++;

			if ( $found <= $retention_count ) {
				continue;
			}

			$this->client->get_client()->deleteObject( [
				'Bucket' => $this->id,
				'Key'    => $backup['Key'],
			] );

			/**
			 * Remote file deleted due to remote retention settings.
			 *
			 * @since 1.2.0
			 */
			do_action(
				'boldgrid_backup_remote_retention_deleted',
				$service_name,
				// Translators: 1: Bucket id, 2: Key.
				sprintf( __( 'Bucket: %1$s, Key: %2$s', 'boldgrid-backup' ), $this->id, $backup['Key'] )
			);
		}

		// Clear the objects transient. Enforce retention is the only place objects are deleted.
		$this->get_client()->get_provider()->get_transient()->delete_objects( $this->id );
	}

	/**
	 * Create a bucket.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True if the bucket is created or the user previously created the bucket.
	 */
	public function create() {
		// Validate bucket name.
		if ( ! $this->client->get_client()->isBucketDnsCompatible( $this->id ) ) {
			$this->errors[] = __( 'Invalid Bucket ID. Bucket name must be between 3 and 63 characters long, must not end with a dash or period, and must not use any special characters.', 'boldgrid-backup' );
			return false;
		}

		// Clear the buckets transient.
		if ( $this->client->has_provider() ) {
			$this->client->get_provider()->get_transient()->delete_buckets();
		}

		try {
			$this->client->get_client()->createBucket( [
				'Bucket' => $this->id,
			] );
		} catch ( Aws\S3\Exception\BucketAlreadyOwnedByYouException $e ) {
			return true;
		} catch ( Aws\S3\Exception\BucketAlreadyExistsException $e ) {
			$this->errors[] = sprintf(
				// Translators: 1: Bucket id.
				__( 'Bucket ID %1$s already exist. Please try another Bucket ID.', 'boldgrid-backup' ),
				'<strong>' . esc_html( $this->id ) . '</strong>'
			);

			return false;
		} catch ( Exception $e ) {
			$this->errors[] = __( 'Unknown error when attempting to create bucket.', 'boldgrid-backup' );

			return false;
		}

		return true;
	}

	/**
	 * Create a unique bucket id.
	 *
	 * When you delete a bucket, Amazon gives you the following message:
	 * Amazon S3 buckets are unique. If you delete this bucket, you may lose the
	 * bucket name to another AWS user.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function create_unique_bucket() {
		$url = parse_url( get_site_url() ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$bucket_parts = [
			'boldgrid-backup',
			$url['host'],
		];

		$bucket_id = implode( '-', $bucket_parts );

		return $bucket_id;
	}

	/**
	 * Download a file by key.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $key  The object key to download.
	 * @param  string $path The filepath to save the file to locally.
	 * @return bool         True if the file was downloaded successfully.
	 */
	public function download_key( $key, $path ) {
		$core = apply_filters( 'boldgrid_backup_get_core', null );

		$this->set_objects( true );
		$object = $this->get_object( $key );

		/*
		 * Make sure the object exists before trying to download it.
		 *
		 * The set_objects( true ) call above bypasses transient data and gets fresh data. If the backup
		 * is not found by chance, the objects transient will be cleared, avoid future issues with this
		 * backup.
		 */
		if ( empty( $object ) ) {
			$this->errors[] = __( 'Backup does not exist on the host.', 'boldgrid-backup' );

			return false;
		}

		// Example $result: https://pastebin.com/thyw9jrh.
		$result = $this->client->get_client()->getObject( array(
			'Bucket' => $this->id,
			'Key'    => $key,
			'SaveAs' => $path,
		));

		if ( empty( $result['ContentLength'] ) ) {
			$this->errors[] = __( 'File was empty, no ContentLength.', 'boldgrid-backup' );

			return false;
		}

		// Make sure the size of the backup we downloaded matches what we should have downloaded.
		$filesize_match = ! empty( $object['Size'] ) && (int) $object['Size'] === (int) $result['ContentLength'];

		if ( $filesize_match ) {
			/*
			 * Change the timestamp of the backup file.
			 *
			 * Normally you have to make a separate call to get Headers / Metadata, however it's included
			 * in our $result.
			 */
			$last_modified = ! empty( $result['Metadata']['lastmodified'] ) ? $result['Metadata']['lastmodified'] : null;
			if ( ! empty( $last_modified ) ) {
				$core->wp_filesystem->touch( $path, $last_modified );
			}

			$core->remote->post_download( $path );

			return true;
		} else {
			$this->errors[] = esc_html( sprintf(
				// translators: 1 the filesize of the backup we downloaded, 2 the filesize we expected to download.
				__( 'Expected to download %1$s bytes, only downloaded %2$s.', 'boldgrid-backup' ),
				$object['Size'],
				$core->wp_filesystem->size( $path )
			) );

			// Delete the file we just downloaded, it's not valid.
			$core->wp_filesystem->delete( $path );

			return false;
		}
	}

	/**
	 * Get an array of our backups within the bucket.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function set_backups() {
		// First, try to get backups from transient.
		if ( is_null( $this->backups ) ) {
			if ( $from_transient = $this->client->get_provider()->get_transient()->get_backups( $this->id ) ) { // phpcs:ignore
				$this->backups = $from_transient;
			}
		}

		/*
		 * If we could not get backups from transient, get fresh and save transient.
		 *
		 * Transient is cleared whenever the objects transient is cleared.
		 */
		if ( is_null( $this->backups ) ) {
			$this->backups = [];

			// The list of backups is built from our list of objects, so build objects first.
			$this->set_objects();

			foreach ( $this->objects as &$object ) {
				if ( ! isset( $object['Metadata'] ) ) {
					$object['Metadata'] = $this->get_object_header( $object['Key'], 'Metadata' );

					// There could be any number of files in the bucket. Only include backup files.
					if ( ! empty( $object['Metadata']['isboldgridbackup'] ) ) {
						$this->backups[] = $object;
					}
				}
			}

			$this->client->get_provider()->get_transient()->set_backups( $this->backups, $this->id );
		}
	}

	/**
	 * Initilize our bucket's objects.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $force Pass as true to wipe existing objects and get fresh.
	 */
	public function set_objects( $force = false ) {
		$success = false;

		// Validate our bucket id before continuing.
		if ( empty( $this->id ) ) {
			$this->errors[] = __( 'S3 Bucket error: Attempting to set objects on an empty bucket id.', 'boldgrid-backup' );
			return $success;
		}

		// First, try to get objects from transient.
		if ( is_null( $this->objects ) ) {
			if ( $from_transient = $this->client->get_provider()->get_transient()->get_objects( $this->id ) ) { // phpcs:ignore
				$this->objects = $from_transient;
			}
		}

		if ( $force ) {
			$this->objects = null;
		}

		/*
		 * If we could not get objects from transient, get them fresh and set transient.
		 *
		 * Transient is cleared whenever an object is uploaded or deleted.
		 */
		if ( is_null( $this->objects ) ) {
			$this->objects = [];

			$objects = $this->client->get_client()->getIterator(
				'ListObjects',
				[
					'Bucket' => $this->id,
				]
			);

			try {
				foreach ( $objects as $object ) {
					$this->objects[] = $object;
				}

				$this->client->get_provider()->get_transient()->set_objects( $this->objects, $this->id );
			} catch ( Exception $e ) {
				$error = method_exists( $e, 'getStatusCode' ) && method_exists( $e, 'getAwsErrorCode' )
					? $e->getStatusCode() . ' ' . $e->getAwsErrorCode()
					: __( 'Unknown error', 'boldgrid-backup' );

				$this->errors[] = wp_kses(
					sprintf(
						// Translators: 1 This s3 bucket id, 2 the error message, 3 an opening em tag, 4 its closing em tag, 5 an opening strong tag, 6 its closing strong tag.
						__( '%5$sError%6$s: Unable to retrieve a list of backups from %3$sS3 bucket%4$s %5$s%1$s%6$s. %2$s', 'boldgrid-backup' ),
						$this->id,
						$error,
						'<em>',
						'</em>',
						'<strong>',
						'</strong>'
					),
					[
						'em'     => [],
						'strong' => [],
					]
				);

				return false;
			}
		}

		$success = is_array( $this->objects );

		return $success;
	}
}
