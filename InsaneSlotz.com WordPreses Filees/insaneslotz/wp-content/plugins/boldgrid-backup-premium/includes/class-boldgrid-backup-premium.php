<?php
/**
 * Premium Core plugin class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.0.0
 *
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/includes
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Premium.
 *
 * This is used to define internationalization and admin-specific hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/includes
 * @author     BoldGrid.com <wpb@boldgrid.com>
 */
class Boldgrid_Backup_Premium {
	/**
	 * The core class object.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Boldgrid_Backup_Premium_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area of the site.
	 *
	 * @since 1.0.0
	 *
	 * @param Boldgrid_Backup_Admin_Core $core Core object.
	 */
	public function __construct( $core ) {
		$this->plugin_name = 'boldgrid-backup-premium';
		$this->version     = ( defined( 'BOLDGRID_BACKUP_PREMIUM_VERSION' ) ? BOLDGRID_BACKUP_PREMIUM_VERSION : '' );
		$this->core        = $core;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin.
	 *
	 * - Boldgrid_Backup_Premium_Loader. Orchestrates the hooks of the plugin.
	 * - Boldgrid_Backup_Premium_i18n. Defines internationalization functionality.
	 * - Boldgrid_Backup_Premium_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/vendor/autoload.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/includes/class-boldgrid-backup-premium-loader.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/includes/class-boldgrid-backup-premium-i18n.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-core.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-archive-browser.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-historical.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-plugin-editor.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-history.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-recent.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-support.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-crypt.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-settings.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/provider.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_provider.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_hooks.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_page.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/amazon_s3.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/amazon_s3_backups_page.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/amazon_s3_bucket.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive_hooks.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive_archive.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive_folder.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive_client.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive_page.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/google_drive_logs.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/dreamobjects.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_client.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_uploader.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_bucket.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_buckets.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/remote/s3_transient.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-plugins.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-admin-themes.php';
		require_once BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/class-boldgrid-backup-premium-timely-auto-updates.php';

		$this->loader = new Boldgrid_Backup_Premium_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Boldgrid_Backup_Premium_i18n class in order to set the domain and to register the hook.
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Boldgrid_Backup_Premium_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$premium_core = new Boldgrid_Backup_Premium_Admin_Core( $this->core );

		if ( ! $premium_core->support->has_hook_support() ) {
			return false;
		}

		/*
		 * Load amazon S3.
		 *
		 * Commented filters are required for any 3rd party storage provider to register themselves. The additional
		 * filters are miscellaneous and are specific to our Amazon S3 implementation.
		 */
		// When viewing details of an individual backup:
		// # Allow one click upload.
		$this->loader->add_action( 'boldgrid_backup_single_archive_remote_options', $premium_core->amazon_s3, 'single_archive_remote_option' );
		// # Process the one click upload.
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_upload_amazon_s3', $premium_core->amazon_s3, 'ajax_upload' );
		// After a backup file has been created, add remote provider to jobs queue.
		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $premium_core->amazon_s3, 'post_archive_files' );
		// This is the filter executed by the jobs queue.
		$this->loader->add_filter( 'boldgrid_backup_amazon_s3_upload_post_archive', $premium_core->amazon_s3, 'upload_post_archiving' );
		// On the settings page:
		// # List this storage provider.
		$this->loader->add_filter( 'boldgrid_backup_register_storage_location', $premium_core->amazon_s3, 'register_storage_location' );
		// # Refresh the configure / configured notice.
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_is_setup_amazon_s3', $premium_core->amazon_s3, 'is_setup_ajax' );
		$this->loader->add_action( 'admin_menu', $premium_core->amazon_s3, 'add_menu_items' );
		$this->loader->add_filter( 'boldgrid_backup_backup_locations', $premium_core->amazon_s3->backups_page, 'backup_locations', 10, 2 );
		$this->loader->add_filter( 'admin_enqueue_scripts', $premium_core->amazon_s3->backups_page, 'admin_enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_download_amazon_s3', $premium_core->amazon_s3->backups_page, 'wp_ajax_download' );
		$this->loader->add_action( 'boldgrid_backup_get_all', $premium_core->amazon_s3, 'filter_get_all' );

		$this->loader->add_filter( 'boldgrid_backup_file_actions', $premium_core->archive_browser, 'wp_ajax_file_actions', 20, 2 );
		$this->loader->add_action( 'boldgrid_backup_enqueue_archive_details', $premium_core->archive_browser, 'enqueue_archive_details' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_restore_single_file', $premium_core->archive_browser, 'wp_ajax_restore_file' );

		$this->loader->add_filter( 'boldgrid_backup_create_dir_config', $premium_core->historical, 'create_dir_config', 10, 2 );
		$this->loader->add_action( 'admin_menu', $premium_core->historical, 'add_menu_items' );
		$this->loader->add_action( 'admin_enqueue_scripts', $premium_core->historical, 'admin_enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_restore_historical', $premium_core->historical, 'wp_ajax_restore_historical' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_get_historical_versions', $premium_core->historical, 'wp_ajax_get_historical_versions' );

		$this->loader->add_action( 'admin_enqueue_scripts', $premium_core->plugin_editor, 'admin_enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_save_copy', $premium_core->plugin_editor, 'wp_ajax_save_copy' );

		$this->loader->add_action( 'boldgrid_backup_add_history', $premium_core->history, 'add', 10, 2 );
		$this->loader->add_action( 'upgrader_process_complete', $premium_core->history, 'upgrader_process_complete', 10, 2 );
		$this->loader->add_action( 'update_option_active_plugins', $premium_core->history, 'update_option_active_plugins', 10, 3 );
		$this->loader->add_action( 'delete_plugin', $premium_core->history, 'delete_plugin' );
		$this->loader->add_action( 'switch_theme', $premium_core->history, 'switch_theme', 10, 3 );
		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $premium_core->history, 'post_archive_files' );
		$this->loader->add_action( 'boldgrid_backup_retention_deleted', $premium_core->history, 'retention_deleted' );
		$this->loader->add_action( 'boldgrid_backup_user_deleted_backup', $premium_core->history, 'user_deleted_backup', 10, 2 );
		$this->loader->add_action( 'boldgrid_backup_settings_updated', $premium_core->history, 'settings_updated' );
		$this->loader->add_action( 'boldgrid_backup_remote_uploaded', $premium_core->history, 'remote_uploaded', 10, 2 );
		$this->loader->add_action( 'boldgrid_backup_remote_retention_deleted', $premium_core->history, 'remote_retention_deleted', 10, 2 );
		$this->loader->add_filter( 'boldgrid_backup_tools_sections', $premium_core->history, 'filter_tools_section' );
		$this->loader->add_filter( 'boldgrid_backup_tools_sections', $premium_core->recent, 'filter_tools_section' );
		$this->loader->add_filter( 'plugin_action_links_boldgrid-backup-premium/boldgrid-backup-premium.php', $premium_core->settings, 'plugin_action_links', 10, 4 );

		// Encryption.
		$this->loader->add_filter( 'boldgrid_backup_post_dump', $premium_core->crypt, 'post_dump' );
		$this->loader->add_filter( 'boldgrid_backup_post_get_dump_file', $premium_core->crypt, 'post_get_dump_file' );
		$this->loader->add_action( 'admin_enqueue_scripts', $premium_core->settings, 'admin_enqueue_scripts' );
		$this->loader->add_filter( 'site_option_boldgrid_backup_settings', $premium_core->settings, 'filter_settings' );
		$this->loader->add_filter( 'pre_update_site_option_boldgrid_backup_settings', $premium_core->settings, 'filter_settings' );
		$this->loader->add_filter( 'boldgrid_backup_archive_update_attribute', $premium_core->crypt, 'filter_update_attribute', 10, 4 );
		$this->loader->add_filter( 'boldgrid_backup_crypt_file', $premium_core->crypt, 'crypt_file', 10, 2 );

		// Google Drive.
		$this->loader->add_action( 'boldgrid_backup_single_archive_remote_options', $premium_core->google_drive->hooks, 'single_archive_remote_option' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_upload_google_drive', $premium_core->google_drive->hooks, 'ajax_upload' );
		$this->loader->add_filter( 'boldgrid_backup_register_storage_location', $premium_core->google_drive->hooks, 'register_storage_location' );
		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $premium_core->google_drive->hooks, 'post_archive_files' );
		$this->loader->add_filter( 'boldgrid_backup_google_drive_upload_post_archive', $premium_core->google_drive->hooks, 'upload_post_archiving' );
		$this->loader->add_action( 'admin_menu', $premium_core->google_drive->page, 'add_submenu_page' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_is_setup_google_drive', $premium_core->google_drive->hooks, 'is_setup_ajax' );
		$this->loader->add_action( 'boldgrid_backup_get_all', $premium_core->google_drive->hooks, 'filter_get_all' );
		$this->loader->add_action( 'admin_init', $premium_core->google_drive->hooks, 'check_for_auth' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_download_google_drive', $premium_core->google_drive->hooks, 'wp_ajax_download' );

		// Dreamobjects.
		$this->loader->add_action( 'boldgrid_backup_single_archive_remote_options', $premium_core->dreamobjects->hooks, 'single_archive_remote_option' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_upload_dreamobjects', $premium_core->dreamobjects->hooks, 'ajax_upload' );
		$this->loader->add_filter( 'boldgrid_backup_register_storage_location', $premium_core->dreamobjects->hooks, 'register_storage_location' );
		$this->loader->add_action( 'boldgrid_backup_post_archive_files', $premium_core->dreamobjects->hooks, 'post_archive_files' );
		$this->loader->add_filter( 'boldgrid_backup_dreamobjects_upload_post_archive', $premium_core->dreamobjects->hooks, 'upload_post_archiving' );
		$this->loader->add_action( 'admin_menu', $premium_core->dreamobjects->page, 'add_submenu_page' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_is_setup_dreamobjects', $premium_core->dreamobjects->hooks, 'is_setup_ajax' );
		$this->loader->add_action( 'boldgrid_backup_get_all', $premium_core->dreamobjects->hooks, 'filter_get_all' );
		$this->loader->add_action( 'wp_ajax_boldgrid_backup_remote_storage_download_dreamobjects', $premium_core->dreamobjects->hooks, 'wp_ajax_download' );
		$this->loader->add_filter( 'boldgrid_backup_premium_get_dreamobjects', $premium_core->dreamobjects->hooks, 'get_dreamobjects' );

		$this->loader->add_filter( 'boldgrid_backup_is_timely_updates', $premium_core->settings, 'is_timely_updates', 10, 1 );
		// TimelyUpdates.
		$themes = new Boldgrid_Backup_Premium_Admin_Themes();
		$this->loader->add_action( 'admin_enqueue_scripts', $themes, 'admin_enqueue_scripts' );
		$this->loader->add_filter( 'theme_auto_update_setting_template', $themes, 'filter_update_message', 10, 1 );

		$plugins = new Boldgrid_Backup_Premium_Admin_Plugins();

		$this->loader->add_filter( 'boldgrid_backup_get_plugin', new \Boldgrid\Library\Library\Plugin\Plugins(), 'getBySlug', 10, 2 );
		$this->loader->add_action( 'load-plugins.php', $plugins, 'add_update_message' );
		$this->loader->add_action( 'load-update-core.php', $plugins, 'add_update_message' );
		$this->loader->add_filter( 'plugin_auto_update_setting_html', $plugins, 'filter_update_message', 10, 3 );

		$timely_auto_updates = new Boldgrid_Backup_Premium_Timely_Auto_Updates();
		$this->loader->add_filter( 'boldgrid_backup_premium_timely_auto_updates', $timely_auto_updates, 'get_markup', 10, 1 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return Boldgrid_Backup_Premium_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
