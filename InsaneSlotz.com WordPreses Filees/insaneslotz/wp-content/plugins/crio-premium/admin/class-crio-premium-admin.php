<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.0.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/admin
 * @author     BoldGrid <pdt@boldgrid.com>
 */
class Crio_Premium_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Crio_Premium_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Crio_Premium_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$screen = get_current_screen();
		if ( $screen && isset( $screen->post_type ) && 'crio_page_header' === $screen->post_type ) {
			wp_enqueue_style( $this->plugin_name . '-editor', plugin_dir_url( __FILE__ ) . 'css/crio-premium-editor.css', array(), $this->version, 'all' );
		}

		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/crio-premium-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Adds Editor Styles
	 *
	 * @since 1.1.0
	 *
	 * @param string $stylesheets
	 *
	 * @return string
	 */
	public function add_editor_styles( $stylesheets ) {
		if ( ! $stylesheets ) {
			return plugins_url( $this->plugin_name . '/public/css/crio-premium-public.css', $this->plugin_name );
		} else {
			return $stylesheets .= ', ' . plugins_url( $this->plugin_name . '/public/css/crio-premium-public.css', $this->plugin_name );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Crio_Premium_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Crio_Premium_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/crio-premium-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add global setting as a page title option.
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $options   Default title options.
	 * @param  WP_post $post      Current Post object.
	 * @param  string  $post_meta Saved post meta
	 * @return array              Merged array options.
	 */
	public function update_page_title_options( $options, $post, $post_meta ) {
		global $boldgrid_theme_framework;

		$configs = $boldgrid_theme_framework->get_configs();
		$title   = new Boldgrid_Framework_Title( $configs );

		unset( $options['global'] );

		$premium_options = [
			'global' => [
				'name'      => __( 'Use Global Setting', 'crio-premium' ),
				'value'     => 'global',
				'checked'   => 'global' === $post_meta || '' === $post_meta,
				'post_text' => 'show' === $title->get_global( $post->post_type ) ? __( 'Show', 'crio-premium' ) : __( 'Hide', 'crio-premium' ),
			],
			'show'   => [
				'name'      => __( 'Show', 'bgtfw' ),
				'value'     => '1',
				'checked'   => '1' === $post_meta,
				'post_text' => $configs['title']['meta_box'][ $post->post_type ]['show_post_text'],
			],
		];

		return $premium_options + $options;
	}

	/**
	 * Add Crio Page
	 *
	 * This adds the Crio Page to the admin menu
	 * as a top level item, so organize additional
	 * feature options.
	 *
	 * @since 1.1.0
	 */
	public function add_crio_page() {
		require_once get_parent_theme_file_path( 'inc/class-boldgrid-crio-welcome.php' );
		$crio_welcome = new BoldGrid_Crio_Welcome();

		if ( isset( $GLOBALS['menu'] ) ) {
			$menus    = $GLOBALS['menu'];
			$priority = array_filter(
				$menus, function( $item ) {
					return 'themes.php' === $item[2];
				}
			);
			$priority = ! empty( $priority ) && 1 === count( $priority ) ? key( $priority ) - 1 : null;
		} else {
			$priority = null;
		}

		add_menu_page(
			__( 'Crio', 'crio-premium' ),
			'Crio',
			'manage_options',
			'crio_premium',
			array( $crio_welcome, 'page_welcome' ),
			plugins_url( 'crio-premium/admin/img/crio_white.png' ),
			$priority
		);

		add_submenu_page(
			'crio_premium',
			'Page Headers',
			'Page Headers',
			'manage_options',
			'edit.php?post_type=crio_page_header'
		);

		add_submenu_page(
			'crio_premium',
			'All Headers',
			'All Headers',
			'manage_options',
			'edit.php?post_type=crio_page_header'
		);

		add_submenu_page(
			'crio_premium',
			'Add New',
			'Add New',
			'manage_options',
			'post-new.php?post_type=crio_page_header'
		);
	}

	public function go_to_help() {
		wp_redirect( 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/custom-header-templates/' );
		exit();
	}
}
