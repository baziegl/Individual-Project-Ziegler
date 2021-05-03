<?php
/**
 * The admin-specific core functionality of the plugin
 *
 * @link  https://www.boldgrid.com
 * @since 1.0.0
 *
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/admin
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Premium_Admin_Core
 *
 * @since 1.0.0
 */
class Boldgrid_Backup_Premium_Admin_Core {
	/**
	 * Amazon S3 class.
	 *
	 * @since  1.0.0
	 * @var    Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3
	 */
	public $amazon_s3;

	/**
	 * An instance of the Boldgrid_Backup_Premium_Admin_Archive_Browser class.
	 *
	 * @since  1.5.3
	 * @var    Boldgrid_Backup_Premium_Admin_Archive_Browser
	 */
	public $archive_browser;

	/**
	 * Our DreamObjects class.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_Dreamobjects
	 */
	public $dreamobjects;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core
	 *
	 * @since 1.1.0
	 * @var Boldgrid_Backup_Premium_Admin_Core
	 */
	public $google_drive;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Historical.
	 *
	 * @since  1.5.3
	 * @var    Boldgrid_Backup_Premium_Admin_Historical
	 */
	public $historical;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_History.
	 *
	 * @since  1.5.3
	 * @var    Boldgrid_Backup_Premium_Admin_History
	 */
	public $history;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Plugin_Editor.
	 *
	 * @since  1.5.3
	 * @var    Boldgrid_Backup_Premium_Admin_Plugin_Editor
	 */
	public $plugin_editor;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Recent.
	 *
	 * @since  1.5.4
	 * @var    Boldgrid_Backup_Premium_Admin_Recent
	 */
	public $recent;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Support.
	 *
	 * @since  1.1.0
	 * @var    Boldgrid_Backup_Premium_Admin_Support
	 */
	public $support;

	/**
	 * An instance of Boldgrid\Backup\Premium\Admin\Crypt.
	 *
	 * @since  1.3.0
	 * @var    Boldgrid\Backup\Premium\Admin\Crypt
	 */
	public $crypt;

	/**
	 * An instance of Boldgrid\Backup\Premium\Admin\Settings.
	 *
	 * @since  1.3.0
	 * @var    Boldgrid\Backup\Premium\Admin\Settings
	 */
	public $settings;

	/**
	 * Configuration array.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 * @staticvar
	 */
	private static $configs;

	/**
	 * The core class object.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Boldgrid_Backup_Admin_Core object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core ) {
		$this->core            = $core;
		$this->archive_browser = new Boldgrid_Backup_Premium_Admin_Archive_Browser( $this->core, $this );
		$this->amazon_s3       = new Boldgrid_Backup_Premium_Admin_Remote_Amazon_S3( $this->core, $this );
		$this->historical      = new Boldgrid_Backup_Premium_Admin_Historical( $this->core, $this );
		$this->plugin_editor   = new Boldgrid_Backup_Premium_Admin_Plugin_Editor( $this->core, $this );
		$this->history         = new Boldgrid_Backup_Premium_Admin_History( $this->core, $this );
		$this->recent          = new Boldgrid_Backup_Premium_Admin_Recent( $this->core, $this );
		$this->support         = new Boldgrid_Backup_Premium_Admin_Support( $this->core, $this );
		$this->crypt           = new \Boldgrid\Backup\Premium\Admin\Crypt( $this->core, $this );
		$this->settings        = new \Boldgrid\Backup\Premium\Admin\Settings( $this->core, $this );
		$this->google_drive    = new Boldgrid_Backup_Premium_Admin_Remote_Google_Drive( $this->core, $this );
		$this->dreamobjects    = new Boldgrid_Backup_Premium_Admin_Remote_Dreamobjects();

		$this->prepare_plugin_update();
	}

	/**
	 * Prepare the plugin update class.
	 *
	 * @since 1.0.0
	 *
	 * @see self::get_configs()
	 */
	public function prepare_plugin_update() {
		$is_cron  = ( defined( 'DOING_CRON' ) && DOING_CRON );
		$is_wpcli = ( defined( 'WP_CLI' ) && WP_CLI );

		if ( $is_cron || $is_wpcli || is_admin() ) {
			require_once BOLDGRID_BACKUP_PREMIUM_PATH .
				'/admin/class-boldgrid-backup-premium-admin-update.php';

			$plugin_update = new Boldgrid_Backup_Premium_Admin_Update( self::get_configs() );

			add_action( 'init', array(
				$plugin_update,
				'add_hooks',
			) );
		}
	}

	/**
	 * Get configuration settings.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 *
	 * @return array An array of configuration settings.
	 */
	public static function get_configs() {
		// If the configuration array was already created, then return it.
		if ( ! empty( self::$configs ) ) {
			return self::$configs;
		}

		// Set the config directory.
		$config_dir = BOLDGRID_BACKUP_PREMIUM_PATH . '/includes/config';

		// Set the config file paths.
		$global_config_path = $config_dir . '/config.plugin.php';
		$local_config_path  = $config_dir . '/config.local.php';

		// Initialize $global_configs array.
		$global_configs = array();

		// If a global config file exists, read the global configuration settings.
		if ( file_exists( $global_config_path ) ) {
			$global_configs = require $global_config_path;
		}

		// Initialize $local_configs array.
		$local_configs = array();

		// If a local configuration file exists, then read the settings.
		if ( file_exists( $local_config_path ) ) {
			$local_configs = require $local_config_path;
		}

		// If an api key hash stored in the database, then set it as the global api_key.
		$api_key_from_database = get_option( 'boldgrid_api_key' );

		if ( ! empty( $api_key_from_database ) ) {
			$global_configs['api_key'] = $api_key_from_database;
		}

		// Get the WordPress site url and set it in the global configs array.
		$global_configs['site_url'] = get_site_url();

		// Merge global and local configuration settings.
		if ( ! empty( $local_configs ) ) {
			$configs = array_replace_recursive( $global_configs, $local_configs );
		} else {
			$configs = $global_configs;
		}

		// Set the configuration array in the class property.
		self::$configs = $configs;

		// Return the configuration array.
		return $configs;
	}
}
