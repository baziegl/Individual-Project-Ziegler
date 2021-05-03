<?php // phpcs:ignore
/**
 * Google Drive Client class.
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
 * Google Drive Client class.
 *
 * @since 1.1.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Google_Drive_Client {
	/**
	 * Our Google Drive client.
	 *
	 * @since 1.1.0
	 * @var Google_Client
	 */
	public $client;

	/**
	 * The last error message, if any, received.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	public $last_error;

	/**
	 * Access token key.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $access_token_key = 'access_token';

	/**
	 * Code key.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var string
	 */
	private $code_key = 'code';

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
	 * An instance of Google_Service_Drive.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var Google_Service_Drive
	 */
	private $service;

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
	 * Init our client.
	 *
	 * @since 1.1.0
	 *
	 * @link https://github.com/googleapis/google-api-php-client/blob/master/examples/large-file-upload.php
	 *
	 * @return mixed Google_Client on success, false on failure.
	 */
	public function init() {
		if ( ! empty( $this->client ) ) {
			return $this->client;
		}

		$configs = $this->premium_core->get_configs();

		$this->client = new Google_Client();
		$this->client->setAuthConfig( $configs['google_drive_config'] );

		/*
		 * Refreshing an access token (offline access)
		 *
		 * @link https://developers.google.com/identity/protocols/OAuth2WebServer#offline
		 *
		 * Access tokens periodically expire. You can refresh an access token without prompting the
		 * user for permission (including when the user is not present) if you requested offline
		 * access to the scopes associated with the token.
		 */
		$this->client->setAccessType( 'offline' );

		$this->client->setIncludeGrantedScopes( true );

		// View and manage Google Drive files and folders that you have opened or created with this app.
		$this->client->addScope( Google_Service_Drive::DRIVE_FILE );

		/*
		 * Required to get a refresh token.
		 *
		 * @link https://stackoverflow.com/questions/8942340/get-refresh-token-google-api
		 */
		$this->client->setApprovalPrompt( 'force' );

		if ( empty( $_GET['code'] ) ) { // phpcs:ignore
			$code         = $this->get_code();
			$access_token = $this->get_access_token();
		} else {
			$code = $_GET['code']; // phpcs:ignore
			$this->update_code( $code );

			// Exchange an authorization code for an access token.
			$access_token = $this->client->authenticate( $code );
			$this->update_access_token( $access_token );

			if ( ! empty( $access_token['error'] ) ) {
				$message = __( 'Unable to authorize Google Drive:', 'boldgrid-backup' ) . ' ' . esc_html( $access_token['error'] ) . ' - ' . esc_html( $access_token['error_description'] );
				$this->core->notice->add_user_notice( $message, $this->core->notice->lang['dis_error'] );
			} else {
				$message = __( 'Google Drive successfully authorized!', 'boldgrid-backup' );
				$this->core->notice->add_user_notice( $message, $this->core->notice->lang['dis_success'] );
			}
			$this->premium_core->google_drive->logs->get_connect_log()->add( $message );

			wp_redirect( admin_url( 'admin.php?page=boldgrid-backup-settings&section=section_storage' ) ); // phpcs:ignore WordPress.VIP
			exit;
		}

		// Catch any possible exceptions thrown by the Google Drive classes.
		try {
			$this->client->setAccessToken( $access_token );

			$this->maybe_refresh_token();

			if ( $this->client->isAccessTokenExpired() ) {
				$this->last_error = __( 'Unable to connect to Google Drive. Access token expired.', 'boldgrid-backup' );
				$this->premium_core->google_drive->logs->get_connect_log()->add( $this->last_error );
				return false;
			}
		} catch ( InvalidArgumentException $e ) {
			// Translators: 1: Error message.
			$this->last_error = sprintf( __( 'Unable to connect to Google Drive. %1$s.', 'boldgrid-backup' ), $e->getMessage() );
			$this->premium_core->google_drive->logs->get_connect_log()->add( $this->last_error );
			return false;
		}

		return $this->client;
	}

	/**
	 * Maybe refresh our access token.
	 *
	 * Since 1.1.0
	 *
	 * @return bool Whether or not the access token was updated.
	 */
	public function maybe_refresh_token() {
		$refreshed = false;

		if ( $this->client->isAccessTokenExpired() ) {
			$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ . ' Access token is expired.' );

			$refresh_token = $this->get_refresh_token();

			if ( ! empty( $refresh_token ) ) {
				$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ . ' Fetching access token with refresh token...' );
				$access_token = $this->client->fetchAccessTokenWithRefreshToken( $refresh_token );
				$refreshed    = $this->update_access_token( $access_token );
			} else {
				$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ . ' Unable to fetch access token. Missing refresh token.' );
			}
		}

		return $refreshed;
	}

	/**
	 * Set our client's defer status.
	 *
	 * Declare whether making API calls should make the call immediately, or return a request which
	 * can be called with ->execute();
	 *
	 * This is a wrapper method, made primarily to give the developer the following comments:
	 *
	 * On a case by case basis, you may need to set the defer to false so that calls are executed
	 * right away. This was discovered because calls to "get files" were returning different results
	 * when called in the same manner. For example, sometimes we would get a
	 * Google_Service_Drive_FileList object in return, and other times we would get a
	 * GuzzleHttp\Psr7\Request object instead. Most likely this was caused by the calls being made
	 * immediately before the calls to "get files", which probably changed the defer type. So, if
	 * you experience an issue as described above, try setting the defer type to false.
	 *
	 * @since 1.1.0
	 *
	 * @link https://github.com/googleapis/google-api-php-client/blob/v2.2.2/src/Google/Service/Resource.php#L222-L230
	 * @link https://github.com/googleapis/google-api-php-client/blob/v2.2.2/src/Google/Client.php#L906-L915
	 *
	 * @param bool $defer True if calls should not be executed right away.
	 */
	public function set_defer( $defer ) {
		$this->client->setDefer( $defer );
	}

	/**
	 * Get our access token.
	 *
	 * @since 1.1.0
	 *
	 * @return array Example: https://pastebin.com/ur1Jh9YM
	 */
	public function get_access_token() {
		$access_token = $this->premium_core->google_drive->settings->get_setting( $this->access_token_key, array() );

		if ( empty( $access_token ) ) {
			$this->premium_core->google_drive->logs->get_connect_log()->add( __METHOD__ . ' No access token.' );
		}

		return $access_token;
	}

	/**
	 * Get our authentication code.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->premium_core->google_drive->settings->get_setting( $this->code_key );
	}

	/**
	 * Get our refresh token.
	 *
	 * Access tokens have limited lifetimes. If your application needs access to a Google API beyond
	 * the lifetime of a single access token, it can obtain a refresh token. A refresh token allows
	 * your application to obtain new access tokens. Save refresh tokens in secure long-term storage
	 * and continue to use them as long as they remain valid. Limits apply to the number of refresh
	 * tokens that are issued per client-user combination, and per user across all clients, and these
	 * limits are different. If your application requests enough refresh tokens to go over one of the
	 * limits, older refresh tokens stop working.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_refresh_token() {
		$refresh_token = '';

		$option_value = $this->get_access_token();

		if ( ! empty( $option_value['refresh_token'] ) ) {
			$refresh_token = $option_value['refresh_token'];
		} else {
			$this->premium_core->google_drive->logs->get_connection_log()->add( 'No refresh token found.' );
		}

		return $refresh_token;
	}

	/**
	 * Determine whether or not we have an access token.
	 *
	 * @since SINCEVERSION
	 *
	 * @return bool
	 */
	public function has_access_token() {
		$access_token = $this->get_access_token();

		return ! empty( $access_token );
	}

	/**
	 * Get our service object.
	 *
	 * @since 1.1.0
	 *
	 * @return Google_Service_Drive object.
	 */
	public function get_service() {
		if ( ! is_null( $this->service ) ) {
			return $this->service;
		}

		$this->init();

		$this->service = new Google_Service_Drive( $this->client );

		return $this->service;
	}

	/**
	 * Save our access token.
	 *
	 * @since 1.1.0
	 *
	 * @param  array $access_token An array of access token info.
	 * @return bool
	 */
	public function update_access_token( array $access_token ) {
		if ( empty( $access_token['access_token'] ) || empty( $access_token['refresh_token'] ) ) {
			return false;
		}

		if ( isset( $access_token['error'] ) ) {
			return false;
		}

		return $this->premium_core->google_drive->settings->save_setting( $this->access_token_key, $access_token );
	}

	/**
	 * Update our authentication code.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $code Authentication code.
	 * @return bool
	 */
	public function update_code( $code ) {
		return $this->premium_core->google_drive->settings->save_setting( $this->code_key, $code );
	}
}
