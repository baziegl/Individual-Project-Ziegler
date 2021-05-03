<?php // phpcs:ignore
/**
 * Google Drive Archive class.
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

/**
 * Google Drive Archive class.
 *
 * @since 1.1.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Archive {
	/**
	 * The last error message received, if any.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	public $last_error;

	/**
	 * The core class object.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * An archive filename.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $filename;

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
	 * @param string                             $filename     An archive filename.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core, Boldgrid_Backup_Premium_Admin_Core $premium_core, $filename ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;
		$this->filename     = $filename;
		$this->last_error   = __( 'Unknown Error.', 'boldgrid-backup' );
	}

	/**
	 * Download a backup file.
	 *
	 * Had many issues following example code in the 1st @link. This method modifies that example
	 * code based on official documentation in the 2nd @link. For example, we're manually adding the
	 * Authorization header rather than having the google-api-php-client handle it.
	 *
	 * @since 1.1.0
	 *
	 * @link https://github.com/googleapis/google-api-php-client/blob/master/examples/large-file-download.php
	 * @link https://developers.google.com/drive/api/v3/manage-downloads
	 *
	 * @return bool True if file was downloaded successfully.
	 */
	public function download() {
		$log = $this->premium_core->google_drive->logs->get_download_log();
		$log->add_separator();
		$log->add( 'Downloading ' . $this->filename );

		$client = $this->premium_core->google_drive->client->init();
		if ( false === $client ) {
			$this->last_error = $this->premium_core->google_drive->client->last_error;
			$log->add( $this->last_error );
			return false;
		}

		$file = $this->premium_core->google_drive->folder->get_file( $this->filename );
		if ( ! $file instanceof Google_Service_Drive_DriveFile || empty( $file->id ) || empty( $file->size ) ) {
			$this->last_error = __( 'Unable to find backup file on Google Drive.', 'boldgrid-backup' );
			$log->add( $this->last_error );
			return false;
		}

		$file_size        = intval( $file->size );
		$local_filepath   = $this->core->backup_dir->get_path_to( $this->filename );
		$http             = $client->authorize();
		$fp               = fopen( $local_filepath, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$chunk_size_bytes = 1 * 1024 * 1024; // Download in 1 MB chunks
		$chunk_start      = 0;
		$time_start       = microtime( true );

		while ( $chunk_start < $file_size ) {
			$chunk_end = $chunk_start + $chunk_size_bytes;

			try {
				$response = $http->get(
					sprintf( 'https://www.googleapis.com/drive/v3/files/%s', $file->id ),
					array(
						'query'   => array(
							'alt' => 'media',
						),
						'headers' => array(
							'Range' => sprintf( 'bytes=%s-%s', $chunk_start, $chunk_end ),
						),
					)
				);
			} catch ( Exception $e ) {
				$this->core->archive->delete( $local_filepath );
				$this->last_error = esc_html( $e->getMessage() );
				$log->add( $e->getMessage() );
				return false;
			}

			$chunk_start = $chunk_end + 1;

			/*
			 * Example api code used getBody()->getContents() instead of getBody. The difference
			 * between the two approaches is that getContents returns the remaining contents, so
			 * that a second call returns nothing unless you seek the position of the stream with
			 * rewind or seek .
			 *
			 * @link https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6
			 */
			fwrite( $fp, $response->getBody( true ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		$time_end = microtime( true );
		$duration = $time_end - $time_start;
		$success  = fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		if ( $success ) {
			$log->add( 'Download completed successfully.' );
			$size = $this->core->wp_filesystem->size( $local_filepath );
			$rate = $size / $duration;
			$log->add( size_format( $size, 2 ) . ' downloaded in ' . human_time_diff( $time_start, $time_end ) . '(' . size_format( $rate, 2 ) . '/s)' );

			$this->core->remote->post_download( $local_filepath );
		} else {
			$log->add( 'Download failed.' );
		}

		return $success;
	}

	/**
	 * Determine whether or not a backup is uploaded.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	public function is_uploaded() {
		$file = $this->premium_core->google_drive->folder->get_file( $this->filename );

		if ( empty( $file ) ) {
			$this->last_error = $this->premium_core->google_drive->folder->last_error;
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Upload an archive.
	 *
	 * @since 1.1.0
	 */
	public function upload() {
		// Add info to the upload log.
		$log = $this->premium_core->google_drive->logs->get_upload_log();
		$log->add_separator();
		$log->add( 'Filename: ' . $this->filename );

		// Add info to the connect log.
		$this->premium_core->google_drive->logs->get_connect_log()->add_separator();
		$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ );

		$backup_folder_id = $this->premium_core->google_drive->folder->get_backup_id();
		if ( false === $backup_folder_id ) {
			$this->last_error = $this->premium_core->google_drive->folder->last_error;
			$log->add( $this->last_error );
			return false;
		}

		// Setup our client and service, needed to upload.
		$client = $this->premium_core->google_drive->client;
		$client->init();

		$service = $client->get_service();

		// Init our archive so we can get the timestamp and filepath later below.
		$this->core->archive->init_by_filename( $this->filename );

		$archive_size = $this->core->wp_filesystem->size( $this->core->archive->filepath );
		$log->add( $this->core->archive->filepath . ' / ' . $archive_size . ' (' . size_format( $archive_size, 2 ) . ')' );

		// Make sure our backup file exists.
		if ( ! $this->core->wp_filesystem->exists( $this->core->archive->filepath ) ) {
			$this->last_error = sprintf(
				// translators: 1 The filepath to a backup file.
				__( 'Archive does not exist: $1$s', 'boldgrid-backup' ),
				$this->core->archive->filepath
			);
			$log->add( $this->last_error );
			return false;
		}

		/*
		 * Insert file into folder.
		 *
		 * @link https://developers.google.com/drive/api/v3/folder
		 */
		$file = new Google_Service_Drive_DriveFile( array(
			'name'        => $this->core->archive->filename,
			'parents'     => array( $backup_folder_id ),
			'createdTime' => date( 'c', $this->core->archive->timestamp ),
			'properties'  => array(
				'createdTime' => $this->core->archive->timestamp,
			),
		));

		$chunk_size_bytes = 1 * 1024 * 1024;
		// Call the API with the media upload, defer so it doesn't immediately return.
		$client->client->setDefer( true );

		/*
		 * The supportsAllDrives parameter will be valid until June 1, 2020. After June 1, 2020, all
		 * applications will be assumed to support shared drives.
		 *
		 * @link https://developers.google.com/drive/api/v3/enable-shareddrives
		 */
		$request = $service->files->create( $file, array( 'supportsAllDrives' => true ) );

		// Create a media file upload to represent our upload process.
		$media = new Google_Http_MediaFileUpload(
			$client->client,
			$request,
			'application/zip',
			null,
			true,
			$chunk_size_bytes
		);
		$media->setFileSize( $archive_size );

		// Upload the various chunks. $status will be false until the process is complete.
		$status = false;

		// Make sure we can open our backup file before we try to upload it.
		$handle = fopen( $this->core->archive->filepath, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $handle ) {
			$this->last_error = sprintf(
				// translators: 1 The filepath to a backup file.
				__( 'Unable to open archive: $1$s', 'boldgrid-backup' ),
				$this->core->archive->filepath
			);
			$log->add( $this->last_error );
			return false;
		}

		$log->add( 'Beginning to upload file...' );
		$start_time = microtime( true );

		while ( ! $status && ! feof( $handle ) ) {
			/*
			 * Read until you get $chunk_size_bytes from TESTFILE. fread will never return more than
			 * 8192 bytes if the stream is read buffered and it does not represent a plain file. An
			 * example of a read buffered file is when reading from a URL
			 */
			$chunk = $this->read_big_chunk( $handle, $chunk_size_bytes );

			try {
				$status = $media->nextChunk( $chunk );
			} catch ( Google_Service_Exception $e ) {
				$this->last_error = __( 'Unable to upload file to Google Drive', 'boldgrid-backup' ) . ': ' . $e->getCode();
				$log->add( $this->last_error );
			}
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		// Calculate how long the upload took and the bytes per second average.
		$end_time     = microtime( true );
		$upload_speed = $archive_size / ( $end_time - $start_time );
		$log->add( 'File upload complete! Uploaded ' . size_format( $archive_size, 2 ) . ' in ' .
		human_time_diff( $start_time, $end_time ) . ' (' . size_format( $upload_speed, 2 ) . '/s)' );

		/*
		 * The final value of $status will be the data from the API for the object that's been uploaded.
		 *
		 * Example $status on success: https://pastebin.com/SZxwHwNC
		 */
		$result = false;
		if ( false !== $status ) {
			$result = $status;

			$log->add( 'Enforcing retention...' );
			$this->premium_core->google_drive->folder->enforce_retention( $backup_folder_id );
			$log->add( 'Retention enforcement complete.' );
		}

		$success = false !== $result;
		$log->add( __METHOD__ . ' Method completed. Success? ' . ( $success ? 'Yes' : 'No' ) );

		return $success;
	}

	/**
	 * Read a big chunk.
	 *
	 * @since 1.1.0
	 * @access private
	 *
	 * @link https://github.com/googleapis/google-api-php-client/blob/f88a98dbaac0207e177419a15214ab4fcf30c47a/examples/large-file-upload.php#L133
	 *
	 * @param  resource $handle     File handle.
	 * @param  int      $chunk_size Chunk size.
	 * @return string
	 */
	private function read_big_chunk( $handle, $chunk_size ) {
		$byte_count  = 0;
		$giant_chunk = '';

		while ( ! feof( $handle ) ) {
			/*
			 * fread will never return more than 8192 bytes if the stream is read buffered and it
			 * does not represent a plain file
			 */
			$chunk        = fread( $handle, 8192 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			$byte_count  += strlen( $chunk );
			$giant_chunk .= $chunk;
			if ( $byte_count >= $chunk_size ) {
				return $giant_chunk;
			}
		}

		return $giant_chunk;
	}
}
