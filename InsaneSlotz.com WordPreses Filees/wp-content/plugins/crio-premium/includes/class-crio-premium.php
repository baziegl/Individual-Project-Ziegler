<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.0.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes
 */


use Boldgrid\Library\Library;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes
 * @author     BoldGrid <pdt@boldgrid.com>
 */
class Crio_Premium {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Crio_Premium_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Custom Header Features Class.
	 *
	 * @since 1.1.0
	 * @access protected
	 * @var Crio_Premium_Custom_Header
	 */
	protected $custom_header_features;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CRIO_PREMIUM_VERSION' ) ) {
			$this->version = CRIO_PREMIUM_VERSION;
		} else {
			$this->version = '1.0.3';
		}

		if ( ! defined( 'CRIO_FREE_VERSION' ) ) {
			$theme = wp_get_theme();
			define( 'CRIO_FREE_VERSION', $theme->version );
		}

		$this->plugin_name = 'crio-premium';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_customizer_hooks();
	}

	/**
	 * Validate connect key from central before kickoff.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Is valid to run?
	 */
	public function validate() {
		$v     = false;
		$theme = wp_get_theme();

		if ( 'Crio' === $theme->name || 'crio' === $theme->template || 'prime' === $theme->template ) {
			$license = new Library\License();
			$v       = $license->getValid() && $license->isPremium( 'crio' );
		}

		return $v;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Crio_Premium_Loader. Orchestrates the hooks of the plugin.
	 * - Crio_Premium_I18n. Defines internationalization functionality.
	 * - Crio_Premium_Admin. Defines all hooks for the admin area.
	 * - Crio_Premium_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-crio-premium-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-crio-premium-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-crio-premium-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-crio-premium-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'customizer/class-crio-premium-customizer.php';

		/**
		 * The class responsible for updating the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-crio-premium-updater.php';

		/*
		 * The class responsible for Custom Header Templates.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/class-crio-premium-page-headers-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/class-crio-premium-page-headers-customizer-controls.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/class-crio-premium-page-headers-templates.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/class-crio-premium-page-headers-templates-navs.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/class-crio-premium-page-headers-templates-meta.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/samples/class-crio-premium-page-headers-templates-samples.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/samples/class-crio-premium-page-headers-templates-sample.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/editor/class-crio-premium-page-headers-templates-editor.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/page-headers/templates/editor/class-crio-premium-page-headers-templates-editor-styles.php';

		$this->loader = new Crio_Premium_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Crio_Premium_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Crio_Premium_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Crio_Premium_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter( 'bgtfw_page_title_options', $plugin_admin, 'update_page_title_options', 10, 3 );
		$this->loader->add_filter( 'mce_css', $plugin_admin, 'add_editor_styles', 10, 1 );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_crio_page' );

		$updater = new Crio_Premium_Updater();
		$updater->add_hooks();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Crio_Premium_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the customizer functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_customizer_hooks() {
		$customizer = new Crio_Premium_Customizer( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'boldgrid_theme_framework_config', $customizer, 'add_configs', 15 );
		$this->loader->add_action( 'customize_register', $customizer, 'remove_upsell_section', 15 );
		$this->loader->add_action( 'customize_register', $customizer, 'remove_menu_notices', 80 );
		$this->loader->add_action( 'customize_controls_print_styles', $customizer, 'add_attribution_controls' );
		$this->loader->add_action( 'customize_save_after', $customizer, 'pre_attribution', 998 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		if ( $this->validate() ) {
			$this->loader->run();
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Crio_Premium_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
