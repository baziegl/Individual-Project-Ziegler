<?php // phpcs:ignore
/**
 * Google Drive class.
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
 * Google Drive class.
 *
 * @since 1.1.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Google_Drive {
	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Client.
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Client
	 */
	public $client;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Folder.
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Folder
	 */
	public $folder;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Hooks.
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Hooks
	 */
	public $hooks;

	/**
	 * An instance our our Google Drive Logs.
	 *
	 * @since SINCEVERSION
	 * @var Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Logs
	 */
	public $logs;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Page.
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Page
	 */
	public $page;

	/**
	 * An instance of the Boldgrid_Backup_Admin_Remote_Settings class.
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Admin_Remote_Settings
	 */
	public $settings;

	/**
	 * The core class object.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Key.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $key = 'google_drive';

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Title.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $title = 'Google Drive';

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

		$this->hooks    = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Hooks( $core, $premium_core );
		$this->client   = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Client( $core, $premium_core );
		$this->folder   = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Folder( $core, $premium_core );
		$this->settings = new Boldgrid_Backup_Admin_Remote_Settings( 'google_drive' );
		$this->page     = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Page( $core, $premium_core );
		$this->logs     = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Logs( $core );
	}

	/**
	 * Get details
	 *
	 * @since 1.1.0
	 *
	 * @param  bool $try_cache Whether or not to use last_login to validate the Google Drive
	 *                         account. Please see param definition in $this->is_setup().
	 * @return array
	 */
	public function get_details( $try_cache = false ) {
		$this->client->init();

		$configs = $this->premium_core->get_configs();

		// Defaults.
		$configure_url = 'admin.php?page=boldgrid-backup-google-drive';
		$authorize_url = '';
		$authorized    = true;
		$enabled       = false;

		if ( ! $this->client->has_access_token() ) {
			$this->logs->get_connect_log()->add( __METHOD__ . ' Missing access token.' );
		}

		/*
		 * Config changes if the access token is expired.
		 *
		 * This method begins with a call to client->init(). In that method, the access token should
		 * be refreshed if it needs to be. If it's expired by this point, then the user will need to
		 * reauthorize.
		 */
		if ( $this->client->client->isAccessTokenExpired() ) {
			$this->logs->get_connect_log()->add( __METHOD__ . ' Access token is expired.' );

			$is_setup         = false;
			$authorized       = false;
			$authorize_params = array(
				// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions
				'site_url'   => urlencode( get_site_url() ),
				'return_url' => urlencode( admin_url( 'admin.php?page=boldgrid-backup-settings&section=section_storage' ) ),
				'key'        => urlencode( get_option( 'boldgrid_api_key' ) ),
				// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions
			);

			$authorize_url = add_query_arg( $authorize_params, $configs['asset_server'] . $configs['ajax_calls']['google_drive_auth'] );
		} else {
			$this->logs->get_connect_log()->add( __METHOD__ . ' Access token is valid.' );
			/*
			 * Determine if we're setup.
			 *
			 * Initially, this was done before the isAccessTokenExpired() call above. In the case of
			 * isAccessTokenExpired(), calling is_setup() first would mean we can't properly run the
			 * is_setup() method because we're not authorized, resulting in the following error:
			 * Error 403: Daily Limit for Unauthenticated Use Exceeded.
			 */
			$is_setup = $this->is_setup( $try_cache );
			$enabled  = $this->settings->get_setting( 'enabled', false ) && $is_setup;
		}

		$storage_location = array(
			'title'      => __( 'Google Drive', 'boldgrid-backup' ),
			'key'        => 'google_drive',
			'configure'  => $configure_url,
			'authorize'  => $authorize_url,
			'authorized' => $authorized,
			'is_setup'   => $is_setup,
			'enabled'    => $enabled,
			/*
			 * If we have an error, return it. The only class really doing any work right now is the
			 * folder class, so we'll return that error. If the is_setup() method changes, we'll need
			 * change how we detect an error.
			 */
			'error'      => $this->folder->last_error,
		);

		return $storage_location;
	}

	/**
	 * Get our key.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get our title.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Determine whether or not Google Drive is setup.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	public function is_setup() {
		$backup_folder_id = $this->folder->get_backup_id();
		$parent_folder_id = $this->folder->get_parent_id();

		return ! empty( $backup_folder_id ) && ! empty( $parent_folder_id );
	}
}
