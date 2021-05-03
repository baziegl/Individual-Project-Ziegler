<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.0.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/public
 * @author     BoldGrid <pdt@boldgrid.com>
 */
class Crio_Premium_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/crio-premium-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Crio_Premium_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Crio_Premium_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_register_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/crio-premium-public.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		$post    = get_post();
		$post_id = $post ? $post->ID : 0;
		$data    = array(
			'hasHeaderTemplate' => apply_filters( 'crio_premium_get_page_header', get_the_ID() ),
			'headerBackground'  => wp_get_attachment_image_src( get_post_meta( $post_id, 'crio-premium-page-header-background', true ), 'full' ),
		);

		if ( $post && ( is_page() || is_single() ) ) {
			$data['post'] = array(
				'ID'    => $post_id,
				'title' => $post->post_title,
				'type'  => $post->post_type,
			);
		} elseif ( $post ) {
			$data['post'] = array(
				'ID'    => $post_id,
				'title' => $post->post_title,
				'type'  => 'other',
			);
		}

		$post = get_post();
		if ( $post && $post_id ) {
			$data['postMeta'] = get_post_meta( $post_id );
		}

		wp_localize_script( $this->plugin_name, 'CrioPremium', $data );

		wp_enqueue_script( $this->plugin_name );

	}

}
