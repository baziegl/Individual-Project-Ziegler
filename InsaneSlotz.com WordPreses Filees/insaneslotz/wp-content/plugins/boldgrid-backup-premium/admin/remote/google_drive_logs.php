<?php // phpcs:ignore
/**
 * File: google_drive_logs.php
 *
 * @link  https://www.boldgrid.com
 * @since SINCEVERSION
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Google Drive Logs.
 *
 * @since SINCEVERSION
 */
class Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Logs {
	/**
	 * Google Drive Connection Log.
	 *
	 * A log used for various connection related issues. Initially added to help troubleshoot an issue
	 * with a user needing to continually reauthenticate with Google Drive.
	 *
	 * @since SINCEVERSION
	 * @access private
	 * @var Boldgrid_Backup_Admin_Log
	 */
	private $connect_log;

	/**
	 * The core class object.
	 *
	 * @since SINCEVERSION
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Google Drive Download Log.
	 *
	 * A log used to store info about downloads.
	 *
	 * @since 1.5.8
	 * @access private
	 * @var Boldgrid_Backup_Admin_Log
	 */
	private $download_log;

	/**
	 * Google Drive Upload Log.
	 *
	 * A log used to store info about uploads.
	 *
	 * @since SINCEVERSION
	 * @access private
	 * @var Boldgrid_Backup_Admin_Log
	 */
	private $upload_log;

	/**
	 * Constructor.
	 *
	 * @since SINCEVERSION
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Boldgrid_Backup_Admin_Core object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core ) {
		$this->core = $core;
	}

	/**
	 * Get our Connect Log.
	 *
	 * @since SINCEVERSION
	 *
	 * @return Boldgrid_Backup_Admin_Log
	 */
	public function get_connect_log() {
		if ( is_null( $this->connect_log ) ) {
			$this->connect_log = new Boldgrid_Backup_Admin_Log( $this->core );
			$this->connect_log->init( 'google-drive-connect.log' );
		}

		return $this->connect_log;
	}

	/**
	 * Get our Download Log.
	 *
	 * @since 1.5.8
	 *
	 * @return Boldgrid_Backup_Admin_Log
	 */
	public function get_download_log() {
		if ( is_null( $this->download_log ) ) {
			$this->download_log = new Boldgrid_Backup_Admin_Log( $this->core );
			$this->download_log->init( 'google-drive-download.log' );
		}

		return $this->download_log;
	}

	/**
	 * Get our Upload Log.
	 *
	 * @since SINCEVERSION
	 *
	 * @return Boldgrid_Backup_Admin_Log
	 */
	public function get_upload_log() {
		if ( is_null( $this->upload_log ) ) {
			$this->upload_log = new Boldgrid_Backup_Admin_Log( $this->core );
			$this->upload_log->init( 'google-drive-upload.log' );
		}

		return $this->upload_log;
	}
}
