<?php // phpcs:ignore
/**
 * Amazon S3 class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.0.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

use Aws\S3\S3Client;
use Aws\S3\Model\MultipartUpload\UploadBuilder;
use Aws\Common\Exception\MultipartUploadException;
use Aws\Common\Model\MultipartUpload\AbstractTransfer;

/**
 * Amazon S3 class.
 *
 * @since 1.0.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3 {
	/**
	 * Backups page.
	 *
	 * @since 1.0.0
	 * @var   Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3_Backups_Page.
	 */
	public $backups_page;

	/**
	 * Bucket object.
	 *
	 * @since 1.0.0
	 * @var   Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3_Bucket.
	 */
	public $bucket;

	/**
	 * Bucket id.
	 *
	 * @since 1.0.0
	 * @var string $bucket_id
	 */
	public $bucket_id = null;

	/**
	 * Our S3 client.
	 *
	 * @since 1.0.0
	 * @var object $client
	 */
	public $client = null;

	/**
	 * Errors.
	 *
	 * @since 1.5.4
	 * @var   array
	 */
	public $errors = array();

	/**
	 * Nickname.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $nickname;

	/**
	 * Title.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $title = 'Amazon S3';

	/**
	 * Title attribute.
	 *
	 * Used as a title tag attribute.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	public $title_attr;

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
	 * Retention count.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var   int $retention_count
	 */
	private $retention_count = 5;

	/**
	 * Key.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $key
	 */
	private $key = null;

	/**
	 * Secret, I'm not telling.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $secret
	 */
	private $secret = null;

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

		$settings = $this->core->settings->get_settings();

		$this->key             = ! empty( $settings['remote']['amazon_s3']['key'] ) ?
			$settings['remote']['amazon_s3']['key'] : $this->key;
		$this->secret          = ! empty( $settings['remote']['amazon_s3']['secret'] ) ?
			$settings['remote']['amazon_s3']['secret'] : $this->secret;
		$this->bucket_id       = ! empty( $settings['remote']['amazon_s3']['bucket_id'] ) ?
			$settings['remote']['amazon_s3']['bucket_id'] : $this->create_unique_bucket();
		$this->retention_count = ! empty( $settings['remote']['amazon_s3']['retention_count'] ) ?
			$settings['remote']['amazon_s3']['retention_count'] : $this->retention_count;
		$this->nickname        = ! empty( $settings['remote']['amazon_s3']['nickname'] ) ?
			$settings['remote']['amazon_s3']['nickname'] : $this->nickname;

		$this->title_attr = $this->title . ': ' . $this->bucket_id;

		if ( ! empty( $this->nickname ) ) {
			$this->title = $this->nickname;
		}

		$this->backups_page = new Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3_Backups_Page( $this->core, $this->premium_core );
		$this->bucket       = new Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3_Bucket( $this->core, $this->premium_core );
	}

	/**
	 * Add menu items.
	 *
	 * @since 1.0
	 */
	public function add_menu_items() {
		$capability = 'administrator';

		add_submenu_page(
			null,
			__( 'Amazon S3 Settings', 'boldgrid-backup' ),
			__( 'Amazon S3 Settings', 'boldgrid-backup' ),
			$capability,
			'boldgrid-backup-amazon-s3',
			array(
				$this,
				'submenu_page',
			)
		);
	}

	/**
	 * Upload a backup via an ajax request.
	 *
	 * This is done via the archive details of a single archive.
	 *
	 * @since 1.0.0
	 */
	public function ajax_upload() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! $this->core->archive_details->validate_nonce() ) {
			wp_send_json_error( __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$filename = ! empty( $_POST['filename'] ) ? $_POST['filename'] : false; // phpcs:ignore
		$filepath = $this->core->backup_dir->get_path_to( $filename );
		if ( empty( $filename ) || ! $this->core->wp_filesystem->exists( $filepath ) ) {
			wp_send_json_error( __( 'Invalid archive filename.', 'boldgrid-backup' ) );
		}

		// @todo Temp code to get more details about any errors.
		add_action( 'shutdown', function() {
			$last_error = error_get_last();

			// If there's no error or this is not fatal, abort.
			if ( empty( $last_error ) || 1 !== $last_error['type'] ) {
				return;
			}

			$message = sprintf(
				'<strong>%1$s</strong>: %2$s in %3$s on line %4$s',
				__( 'Fatal error', 'boldgrid-backup' ),
				$last_error['message'],
				$last_error['file'],
				$last_error['line']
			);

			wp_send_json_error( $message );
		});

		$upload_result = $this->upload( $filepath );

		if ( true === $upload_result ) {
			wp_send_json_success( $upload_result );
		} else {
			wp_send_json_error( $upload_result );
		}
	}

	/**
	 * Create a unique bucket id.
	 *
	 * When you delete a bucket, Amazon gives you the following message:
	 * Amazon S3 buckets are unique. If you delete this bucket, you may lose the
	 * bucket name to another AWS user.
	 *
	 * @since 1.0.0
	 */
	public function create_unique_bucket() {
		$url = parse_url( get_site_url() ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$bucket_parts[] = 'boldgrid-backup';
		$bucket_parts[] = $url['host'];

		$bucket_id = implode( '-', $bucket_parts );

		return $bucket_id;
	}

	/**
	 * Download a backup from Amazon S3 and put it in backups folder.
	 *
	 * @since 1.5.4
	 *
	 * @param  string $key Key.
	 * @param  string $bucket_id Bucket id.
	 * @return bool
	 */
	public function download( $key, $bucket_id = null ) {
		$bucket_id = is_null( $bucket_id ) ? $this->bucket_id : $bucket_id;

		if ( empty( $bucket_id ) || empty( $key ) ) {
			return false;
		}

		$this->set_client();

		$path = $this->core->backup_dir->get_path_to( $key );

		// Example $result: https://pastebin.com/thyw9jrh .
		$result = $this->client->getObject( array(
			'Bucket' => $bucket_id,
			'Key'    => $key,
			'SaveAs' => $path,
		));

		if ( empty( $result['ContentLength'] ) ) {
			return false;
		}

		// Change the timestamp of the backup file.
		$last_modified = ! empty( $result['Metadata']['last_modified'] ) ? $result['Metadata']['last_modified'] : null;
		if ( ! empty( $last_modified ) ) {
			$this->core->wp_filesystem->touch( $path, $last_modified );
		}

		// If the backup file is not valid, delete it.
		$valid = $this->bucket->validate_backup( $key );
		if ( ! $valid && ! empty( $this->bucket->errors ) ) {
			$this->errors = array_merge( $this->errors, $this->bucket->errors );
			$this->core->wp_filesystem->delete( $path );
		}

		$this->core->remote->post_download( $path );

		return $valid;
	}

	/**
	 * Enforce retention.
	 *
	 * @since 1.0.0
	 */
	public function enforce_retention() {
		if ( empty( $this->retention_count ) ) {
			return;
		}

		$bucket_contents = $this->bucket->get( $this->bucket_id, true, false );

		if ( empty( $bucket_contents ) ) {
			return;
		}

		// Remove files from bucket list that are not our backups.
		foreach ( $bucket_contents as $key => $item ) {
			if ( empty( $item['Headers']['Metadata']['is_boldgrid_backup'] ) || 'true' !== $item['Headers']['Metadata']['is_boldgrid_backup'] ) {
				unset( $bucket_contents[ $key ] );
			}
		}

		// Sort by timestamp desc.
		usort( $bucket_contents, function( $a, $b ) {
			return $a['Headers']['Metadata']['last_modified'] < $b['Headers']['Metadata']['last_modified'] ? 1 : -1;
		} );

		// Do the deleting.
		$count = 0;
		foreach ( $bucket_contents as $item ) {
			$count++;
			if ( $count <= $this->retention_count ) {
				continue;
			}

			$this->client->deleteObject( array(
				'Bucket' => $this->bucket_id,
				'Key'    => $item['Key'],
			) );

			/**
			 * Remote file deleted due to remote retention settings.
			 *
			 * @since 1.5.3
			 */
			do_action(
				'boldgrid_backup_remote_retention_deleted',
				'Amazon S3',
				// Translators: 1: Bucket id, 2: Key.
				sprintf( __( 'Bucket: %1$s, Key: %2$s', 'boldgrid-backup' ), $this->bucket_id, $item['Key'] )
			);
		}

		$this->bucket->delete_transients();
	}

	/**
	 * Add Amazon S3 backups to the list of all backups.
	 *
	 * @since 1.0.0
	 */
	public function filter_get_all() {
		$bucket_contents = $this->bucket->get( $this->bucket_id, true );

		if ( empty( $bucket_contents ) ) {
			return;
		}

		foreach ( $bucket_contents as $item ) {
			$filename     = $item['Key'];
			$is_this_site = false !== strpos( $item['Key'], get_site_option( 'boldgrid_backup_id' ) );
			$is_backup    = ! empty( $item['Headers']['Metadata']['is_boldgrid_backup'] ) && 'true' === $item['Headers']['Metadata']['is_boldgrid_backup'];

			if ( ! $is_backup || ! $is_this_site ) {
				continue;
			}

			$backup = array(
				'filename'      => $filename,
				'last_modified' => $item['Headers']['Metadata']['last_modified'],
				'size'          => $item['Size'],
				'locations'     => array(
					array(
						'title'            => $this->title,
						'title_attr'       => $this->title_attr,
						'on_remote_server' => true,
					),
				),
			);

			$this->core->archives_all->add( $backup );
		}
	}

	/**
	 * Get the contents of a bucket.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $bucket_id Bucket id.
	 * @param  bool   $include_headers Include headers.
	 * @return array  https://pastebin.com/uVkx8t5A
	 */
	public function get_bucket( $bucket_id, $include_headers = false ) {
		$this->set_client();
		$this->set_bucket_id( $bucket_id );

		$bucket_contents = array();

		$transient_name = sprintf(
			'boldgrid_backup_s3_bucket_%1$s%2$s',
			$include_headers ? 'w_headers_' : '',
			$this->bucket_id
		);

		$bucket_contents = get_transient( $transient_name );
		if ( false !== $bucket_contents ) {
			return $bucket_contents;
		}

		// If the bucket does not exist, return an empty bucket.
		try {
			$iterator = $this->client->getIterator( 'ListObjects', array(
				'Bucket' => $this->bucket_id,
			) );

			foreach ( $iterator as $object ) {
				if ( $include_headers ) {
					$object['Headers'] = $this->get_headers( $object['Key'] );
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
	 * Get settings.
	 *
	 * @since 1.0.0
	 */
	public function get_details() {
		$settings = $this->core->settings->get_settings();

		$details = array(
			'title'     => __( 'Amazon S3', 'boldgrid-backup' ),
			'key'       => 'amazon_s3',
			'configure' => 'admin.php?page=boldgrid-backup-amazon-s3',
			'is_setup'  => $this->is_setup(),
			'enabled'   => ! empty( $settings['remote']['amazon_s3']['enabled'] ) && $settings['remote']['amazon_s3']['enabled'] && $this->is_setup(),
		);

		return $details;
	}

	/**
	 * Get the headers of an object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Key.
	 */
	public function get_headers( $key ) {
		$params = array(
			'Bucket' => $this->bucket_id,
			'Key'    => $key,
		);

		$headers = $this->client->headObject( $params );

		return $headers->toArray();
	}

	/**
	 * Determine if a file exists in a bucket.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $bucket_id Bucket id.
	 * @param  string $filepath File path.
	 * @return bool
	 */
	public function in_bucket( $bucket_id, $filepath ) {
		$this->set_client();
		if ( empty( $this->client ) ) {
			return false;
		}

		$bucket_contents = $this->bucket->get( $bucket_id );
		$filename        = basename( $filepath );

		foreach ( $bucket_contents as $item ) {
			if ( $item['Key'] === $filename ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set our client.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key    Key.
	 * @param string $secret Secret.
	 */
	public function set_client( $key = null, $secret = null ) {
		$key    = empty( $key ) ? $this->key : $key;
		$secret = empty( $secret ) ? $this->secret : $secret;

		if ( empty( $key ) || empty( $secret ) ) {
			return false;
		}

		$credentials = new \Aws\Credentials\Credentials( $key, $secret );

		/*
		 * Define our region.
		 *
		 * When using v2 of the aws-sdk-php, no region was defined. As such, all backups should have
		 * defaulted to us-east-1.
		 * @link https://github.com/aws/aws-sdk-php/blob/2.8.31/src/Aws/Common/HostNameUtils.php#L26
		 *
		 * @todo Give users the option to choose.
		 */
		$region = 'us-east-1';

		$this->client = new \Aws\S3\S3Client( array(
			/*
			 * A "version" configuration value is required. Specifying a version constraint ensures
			 * that your code will not be affected by a breaking change made to the service.
			 * @link https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-version
			 */
			'version'     => '2006-03-01',
			'region'      => $region,
			'credentials' => $credentials,
		) );
	}

	/**
	 * Return data about a particular archive in Amazon S3.
	 *
	 * For example, if you're looking at a single backup, we need to know if it
	 * already exists in our Amazon S3 account.
	 *
	 * This method will return an array of information useful to the single
	 * archive page.
	 *
	 * @since  1.0.0
	 *
	 * @param string $filepath File path.
	 */
	public function single_archive_remote_option( $filepath ) {
		$allow_upload = $this->is_setup();
		$uploaded     = $allow_upload && $this->in_bucket( null, $filepath );
		$storage      = array(
			'id'           => 'amazon_s3',
			'title'        => $this->title,
			'title_attr'   => $this->title_attr,
			'uploaded'     => $uploaded,
			'allow_upload' => $allow_upload,
			'is_setup'     => $allow_upload,
		);

		$this->core->archive_details->remote_storage_li[] = $storage;
	}

	/**
	 * Generate the submenu page for our Amazon S3 Settings page.
	 *
	 * @since 1.0.0
	 */
	public function submenu_page() {
		wp_enqueue_style( 'boldgrid-backup-admin-hide-all' );

		$this->submenu_page_save();

		$settings = $this->core->settings->get_settings();

		$key             = ! empty( $settings['remote']['amazon_s3']['key'] ) ?
			$settings['remote']['amazon_s3']['key'] : null;
		$secret          = ! empty( $settings['remote']['amazon_s3']['secret'] ) ?
			$settings['remote']['amazon_s3']['secret'] : null;
		$bucket_id       = ! empty( $settings['remote']['amazon_s3']['bucket_id'] ) ?
			$settings['remote']['amazon_s3']['bucket_id'] : $this->bucket_id;
		$retention_count = ! empty( $settings['remote']['amazon_s3']['retention_count'] ) ?
			$settings['remote']['amazon_s3']['retention_count'] : $this->retention_count;
		$nickname        = ! empty( $settings['remote']['amazon_s3']['nickname'] ) ?
			$settings['remote']['amazon_s3']['nickname'] : '';

		include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/remote/amazon-s3.php';
	}

	/**
	 * Process the user's request to update their Amazon S3 settings.
	 *
	 * @since 1.0.0
	 */
	public function submenu_page_save() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		if ( empty( $_POST ) ) { // phpcs:ignore
			return false;
		}

		$this->bucket->delete_transients();

		$settings = $this->core->settings->get_settings();
		if ( ! isset( $settings['remote']['amazon_s3'] ) || ! is_array( $settings['remote']['amazon_s3'] ) ) {
			$settings['remote']['amazon_s3'] = array();
		}

		/*
		 * If the user has requested to delete all their settings, do that now
		 * and return.
		 */
		if ( __( 'Delete settings', 'boldgrid-backup' ) === $_POST['submit'] ) { // phpcs:ignore
			$settings['remote']['amazon_s3'] = array();
			update_site_option( 'boldgrid_backup_settings', $settings );

			$this->key             = null;
			$this->secret          = null;
			$this->bucket_id       = null;
			$this->retention_count = null;
			$this->nickname        = null;

			do_action( 'boldgrid_backup_notice', __( 'Settings saved.', 'boldgrid-backup' ), 'notice updated is-dismissible' );
			return;
		}

		$key    = ! empty( $_POST['key'] ) ? $_POST['key'] : null; // phpcs:ignore
		$secret = ! empty( $_POST['secret'] ) ? $_POST['secret'] : null; // phpcs:ignore
		// If no bucket_id submitted, we had a valid bucket_id created in the constructor.
		$bucket_id = ! empty( $_POST['bucket_id'] ) ? $_POST['bucket_id'] : $this->bucket_id; // phpcs:ignore
		$retention_count = ! empty( $_POST['retention_count'] ) && is_numeric( $_POST['retention_count'] ) ? $_POST['retention_count'] : $this->retention_count; // phpcs:ignore
		$nickname =  ! empty( $_POST['nickname'] )  ? stripslashes( $_POST['nickname'] ) : null; // phpcs:ignore

		echo $this->core->elements['long_checking_creds']; // phpcs:ignore
		if ( ob_get_level() > 0 ) {
			ob_flush();
		}
		flush();

		$valid_credentials = $this->is_valid_credentials( $key, $secret );

		if ( $valid_credentials ) {
			$settings['remote']['amazon_s3']['key']    = $key;
			$settings['remote']['amazon_s3']['secret'] = $secret;
			$this->key                                 = $key;
			$this->secret                              = $secret;
		} else {
			$this->errors[] = __( 'Invalid Access Key Id and / or Secret Access Key.', 'boldgrid-backup' );
		}

		if ( $this->bucket->create( $bucket_id ) ) {
			$settings['remote']['amazon_s3']['bucket_id'] = $bucket_id;
			$this->bucket_id                              = $bucket_id;
		}

		$settings['remote']['amazon_s3']['retention_count'] = $retention_count;
		$settings['remote']['amazon_s3']['nickname']        = $nickname;

		if ( ! empty( $this->errors ) ) {
			do_action( 'boldgrid_backup_notice', implode( '<br /><br />', $this->errors ) );
		} else {
			update_site_option( 'boldgrid_backup_settings', $settings );
			do_action( 'boldgrid_backup_notice', __( 'Settings saved.', 'boldgrid-backup' ), 'notice updated is-dismissible' );
		}
	}

	/**
	 * Set our bucket id.
	 *
	 * @since 1.0.0
	 *
	 * @param string $bucket_id Bucket id.
	 */
	public function set_bucket_id( $bucket_id ) {
		if ( empty( $bucket_id ) ) {
			return;
		}

		$this->bucket_id = $bucket_id;
	}

	/**
	 * Upload a backup file.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $filepath File path.
	 * @return mixed  True on success, error message on failure.
	 */
	public function upload( $filepath ) {
		$this->set_client();

		if ( ! $this->core->wp_filesystem->exists( $filepath ) ) {
			// Translators: 1: File path.
			return sprintf( __( 'Failed to upload, filepath does not exist: %1$s', 'boldgrid-backup' ), $filepath );
		}

		$key = basename( $filepath );

		/*
		 * When files are uploaded to Amazon S3, the LastModified is the time
		 * the file was uploaded, not the time the file was last modified. When
		 * we enforce retention, we'll need to know when the backup archive was
		 * created, not when it was uploaded to Amazon S3.
		 */
		$archive_data  = $this->core->archive_log->get_by_zip( $filepath );
		$last_modified = ! empty( $archive_data['lastmodunix'] ) ? $archive_data['lastmodunix'] : $this->core->wp_filesystem->mtime( $filepath );

		// Make sure our bucket is created.
		if ( ! $this->bucket->create( $this->bucket_id ) ) {
			return sprintf(
				// Translators: 1: URL.
				__( 'Unable to create bucket! Please go to your <a href="%1$s">settings page</a> to configure Amazon S3.', 'boldgrid-backup' ),
				'admin.php?page=boldgrid-backup-settings'
			);
		}

		try {
			$uploader = new \Aws\S3\MultipartUploader(
				$this->client,
				fopen( $filepath, 'rb' ), // phpcs:ignore WordPress.WP.AlternativeFunctions
				array(
					'bucket'          => $this->bucket_id,
					'key'             => $key,
					/*
					 * Before Initiate.
					 *
					 * Set our custom metadata.
					 *
					 * @link https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#createmultipartupload
					 * @link https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-multipart-upload.html
					 *
					 * @var $command Aws\Command A CreateMultipartUpload operation.
					 */
					'before_initiate' => function( $command ) use ( $last_modified ) {
						$command['Metadata'] = array(
							'is_boldgrid_backup' => 'true',
							'last_modified'      => $last_modified,
						);
					},
				)
			);
		} catch ( Exception $e ) {
			return __( 'Failed to initialize', 'boldgrid-backup' );
		}

		try {
			$uploader->upload();
			$this->enforce_retention();
			$this->bucket->delete_transients();
		} catch ( MultipartUploadException $e ) {
			$uploader->abort();
			return __( 'Failed to upload.', 'boldgrid-inspirations' );
		}

		/**
		 * File uploaded to remote storage location.
		 *
		 * @since 1.5.3
		 *
		 * @param string Amazon S3
		 * @param string $filepath
		 */
		do_action( 'boldgrid_backup_remote_uploaded', 'Amazon S3', $filepath );

		return true;
	}

	/**
	 * Upload a file.
	 *
	 * The jobs queue will call this method to upload a file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filepath File path.
	 */
	public function upload_post_archiving( $filepath ) {
		$success = $this->upload( $filepath );

		return $success;
	}

	/**
	 * Determine if Amazon S3 is setup properly.
	 *
	 * Hook into "boldgrid_backup_is_setup_amazon_s3".
	 */
	public function is_setup() {
		return $this->is_valid_credentials( $this->key, $this->secret ) && $this->client->isBucketDnsCompatible( $this->bucket_id );
	}

	/**
	 * Determine if Amazon S3 is setup properly.
	 *
	 * This method is ran within an ajax request.
	 */
	public function is_setup_ajax() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! check_ajax_referer( 'boldgrid_backup_settings', 'security', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$settings = $this->core->settings->get_settings();

		$location = $this->get_details();
		$tr       = include BOLDGRID_BACKUP_PATH . '/admin/partials/settings/storage-location.php';

		if ( $this->is_setup() ) {
			wp_send_json_success( $tr );
		} else {
			wp_send_json_error( $tr );
		}
	}

	/**
	 * Determine if credentials are valid.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key    Key.
	 * @param  string $secret Secret.
	 * @return bool
	 */
	public function is_valid_credentials( $key, $secret ) {
		if ( empty( $key ) || empty( $secret ) ) {
			return false;
		}

		$this->set_client( $key, $secret );

		try {
			$this->client->listBuckets();
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Actions to take after a backup file has been generated.
	 *
	 * @since 1.0.0
	 *
	 * @param array $info Info.
	 */
	public function post_archive_files( array $info ) {
		/*
		 * We only want to add this to the jobs queue if we're in the middle of
		 * an automatic backup. If the user simply clicked on "Backup site now",
		 * we don't want to automatically send the backup to Amazon, there's a
		 * button for that.
		 */
		if ( ! $this->core->doing_cron ) {
			return;
		}

		if ( ! $this->core->remote->is_enabled( 'amazon_s3' ) || $info['dryrun'] || ! $info['save'] ) {
			return;
		}

		$args = array(
			'filepath'     => $info['filepath'],
			'action'       => 'boldgrid_backup_amazon_s3_upload_post_archive',
			'action_data'  => $info['filepath'],
			'action_title' => __( 'Upload backup file to Amazon S3', 'boldgrid-backup' ),
		);

		$this->core->jobs->add( $args );
	}

	/**
	 * Register Amazon S3 as a storage location.
	 *
	 * When you go to the settings page and see a list of storage providers, each of those storage providers needs to
	 * hook into the "boldgrid_backup_register_storage_location" filter and add themselves.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $storage_locations Storage locations.
	 * @return array
	 */
	public function register_storage_location( array $storage_locations ) {
		$storage_locations[] = $this->get_details();

		return $storage_locations;
	}
}
