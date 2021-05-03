<?php

/**
 * File: class=crio-premium-header-templates-customizer-controls.php
 *
 * Adds the Header Template Customizer Controls
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers
 */

/**
 * Class: Crio_Premium_Page_Headers_Customizer_Controls
 *
 * This Class Handles the creation and rendering of customizer controls
 * for the header templates.
 */
class Crio_Premium_Page_Headers_Customizer_Controls {

	/**
	 * Header Templates Base
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Base
	 */
	public $base;

	/**
	 * Support URL
	 *
	 * URL for the Page Headers Support article;
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Base
	 */
	public $support_url;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 *
	 * @param Crio_Premium_Page_Headers_Base $page_headers_base Page Headers Base object.
	 */
	public function __construct( $base ) {
		$this->base        = $base;
		$this->support_url = 'https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/page-headers/?source=customize-page-headers';
	}

	/**
	 * Load Customizer Specific Scripts
	 *
	 * @since 1.1.0
	 */
	public function load_customizer_scripts() {
		wp_register_script(
			'crio-premium-customizer',
			plugin_dir_url( WP_PLUGIN_DIR . '/crio-premium/admin/js/crio-premium-customizer.js' ) . 'crio-premium-customizer.js',
			array( 'jquery', 'customize-controls' ),
			CRIO_PREMIUM_VERSION,
			true
		);

		wp_localize_script(
			'crio-premium-customizer',
			'CrioPremiumUrls',
			$this->get_control_urls()
		);

		wp_enqueue_script( 'crio-premium-customizer' );
	}

	/**
	 * Get Control Urls
	 *
	 * Creates an array of urls to redirect to
	 * when making changes to the page / post type
	 * controls.
	 *
	 * @since 1.1.0
	 *
	 * @return array An array of urls to direct to in preview pane.
	 */
	public function get_control_urls() {
		$posts = get_posts(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		$id_of_page = false;
		foreach ( $pages as $page ) {
			if ( (int) get_option( 'woocommerce_shop_page_id' ) === (int) $page->ID
				|| (int) get_option( 'woocommerce_myaccount_page_id' ) === (int) $page->ID
				|| (int) get_option( 'woocommerce_checkout_page_id' ) === (int) $page->ID
				|| (int) get_option( 'woocommerce_cart_page_id' ) === (int) $page->ID
				|| (int) get_option( 'woocommerce_terms_page_id' ) === (int) $page->ID
				|| (int) get_option( 'page_on_front' ) === (int) $page->ID
				|| (int) get_option( 'page_for_posts' ) === (int) $page->ID ) {
				continue;
			}

			$id_of_page = $page->ID;

			break;
		}

		$shop = get_option( 'woocommerce_shop_page_id' );

		$control_urls = array(
			'bgtfw_page_headers_posts_template'  => $posts ? get_permalink( $posts[0] ) : get_home_url(),
			'bgtfw_page_headers_blog_template'   => get_post_type_archive_link( 'post' ),
			'bgtfw_page_headers_search_template' => get_home_url() . '/this-page-does-not-exist/',
			'bgtfw_page_headers_pages_template'  => $id_of_page ? get_permalink( $id_of_page ) : get_home_url(),
			'bgtfw_page_headers_home_template'   => get_home_url(),
		);

		if ( function_exists( 'is_woocommerce' ) ) {
			$control_urls['bgtfw_page_headers_woo_template'] = $shop ? get_permalink( $shop ) : get_home_url();
		}

		return $control_urls;
	}

	/**
	 * Add Customizer Controls
	 *
	 * @since 1.1.0
	 */
	public function add_customizer_controls( $config ) {
		$config['customizer']['sections']['bgtfw_page_headers'] = array(
			'title'       => __( 'Page Headers', 'crio-premium' ),
			'panel'       => 'bgtfw_header',
			'description' => '<div class="bgtfw-description"><p>' . __(
				'This section helps you to configure the Page Headers used on your website.
				Here you can select which page header appears in various places.',
				'crio'
			) . '</p><div class="help"><a href="' . $this->support_url . '" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
			'capability'  => 'edit_theme_options',
			'priority'    => 1,
			'icon'        => 'dashicons-table-row-before',
		);

		$config['customizer']['controls']['bgtfw_page_headers_global_enabled'] = array(
			'type'      => 'switch',
			'transport' => 'auto',
			'settings'  => 'bgtfw_page_headers_global_enabled',
			'label'     => __( 'Enable Header Templates', 'crio-premium' ),
			'section'   => 'bgtfw_page_headers',
			'priority'  => 1,
			'default'   => true,
			'choices'   => array(
				'on'  => __( 'Enabled', 'crio-premium' ),
				'off' => __( 'Disabled', 'crio-premium' ),
			),
		);

		$config['customizer']['controls']['bgtfw_page_headers_default_template'] = $this->get_template_selector( 'global', 'Global Page Header', 2 );

		$config['customizer']['controls']['bgtfw_page_headers_default_template']['description'] = (
			'<p>' . esc_html__(
				'By default, the Global Page Header will be set to use customizer settings.
				This will cause the standard customizer menu and page headers will be used instead of the new page header templates.',
				'crio-premium'
			) . '</p>
			<p>
				<a class="button goto_header_layout">' . esc_html__( 'Edit Customizer Header Layout', 'crio-premium' ) . '</a>
				<a class="button" href="' . admin_url( 'edit.php?post_type=crio_page_header' ) . '">' . esc_html__( 'Edit Page Header Templates', 'crio-premium' ) . '</a>
			</p>'
		);

		$config['customizer']['controls']['bgtfw_page_headers_info'] = array(
			'type'            => 'generic',
			'settings'        => 'bgtfw_page_headers_info',
			'label'           => __( 'Post and Page Header Templates', 'crio-premium' ),
			'description'     => __( 'All post and page types will use the Global Page Header by default. Here, you can select a specific header to use for different post and page types.' ),
			'section'         => 'bgtfw_page_headers',
			'default'         => '',
			'choices'         => array(),
			'priority'        => 3,
			'active_callback' => function() {
				$global_headers_enabled = get_theme_mod( 'bgtfw_page_headers_global_enabled' );
				return $this->available_templates_exist() && $global_headers_enabled;
			},
		);

		$config['customizer']['controls']['bgtfw_no_page_headers_set'] = array(
			'type'            => 'generic',
			'settings'        => 'bgtfw_no_page_headers_set',
			'label'           => __( 'No Page Headers have been Created', 'crio-premium' ),
			'description'     => '<a class="button" href="' . admin_url( 'edit.php?post_type=crio_page_header' ) . '">' . esc_html__( 'Click Here to Create a New Page Header', 'crio-premium' ) . '</a>',
			'section'         => 'bgtfw_page_headers',
			'default'         => '',
			'choices'         => array(),
			'priority'        => 3,
			'active_callback' => function() {
				$global_headers_enabled = get_theme_mod( 'bgtfw_page_headers_global_enabled' );
				return ! $this->available_templates_exist() && $global_headers_enabled;
			},
		);

		if ( get_option( 'fresh_site' ) ) {
			$config['customizer']['controls']['bgtfw_no_page_headers_set']['description'] = 'For a better user experience, we recommend publishing the starter content before creating new header templates. <a class="button customizer_install_samples" href="' . admin_url( 'edit.php?post_type=crio_page_header' ) . '">' . esc_html__( 'Click Here to Create a New Page Header', 'crio-premium' ) . '</a>';
		}

		$post_page_types = array(
			'pages'  => __( 'Pages', 'crio-premium' ),
			'posts'  => __( 'Posts', 'crio-premium' ),
			'home'   => __( 'Home Page', 'crio-premium' ),
			'blog'   => __( 'Blog page', 'crio-premium' ),
			'search' => __( '404, Search & Archives', 'crio-premium' ),
		);

		if ( function_exists( 'is_woocommerce' ) ) {
			$post_page_types['woo'] = __( 'WooCommerce Shop pages', 'crio-premium' );
		}

		$config = $this->template_section_controls( $config, $post_page_types );

		return $config;
	}

	/**
	 * Available Templates Exist
	 *
	 * Returns true if there are available templates,
	 * false if there are not.
	 *
	 * @since 1.1.0
	 *
	 * @return bool Whether templates exist.
	 */
	public function available_templates_exist() {
		if ( array() === $this->base->templates->get_available( true ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Add Template Section Controls.
	 *
	 * @since 1.1.0
	 *
	 * @param array $config   BGTFW Config Array.
	 * @param array $sections Array of sections to create controls for.
	 *
	 * @return array BGTFW Config Array.
	 */
	public function template_section_controls( $config, $sections ) {
		// This is the priority of the first Template section control.
		$priority = 4;

		foreach ( $sections as $slug => $label ) {
			$config['customizer']['controls'][ 'bgtfw_page_headers_' . $slug . '_template' ] = $this->get_template_selector( $slug, $label, $priority );
			$priority++;
		}

		return $config;
	}

	/**
	 * Get Template Toggle Control
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug     Section Slug.
	 * @param string $label    Section Label.
	 * @param int    $priority Control Priority.
	 */
	public function get_toggle_control( $slug, $label, $priority ) {
		return array(
			'type'            => 'toggle',
			'settings'        => 'bgtfw_page_headers_' . $slug . '_enabled',
			'label'           => $label,
			'section'         => 'bgtfw_page_headers',
			'default'         => '1',
			'priority'        => $priority,
			'active_callback' => function() {
				$global_headers_enabled = get_theme_mod( 'bgtfw_page_headers_global_enabled' );
				return $this->available_templates_exist() && $global_headers_enabled;
			},
		);
	}

	/**
	 * Get Template Selector Control
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug     Section Slug.
	 * @param string $label    Section Label.
	 * @param int    $priority Control Priority.
	 *
	 * @return array
	 */
	public function get_template_selector( $slug, $label, $priority ) {
		$template_selectors = array(
			'type'            => 'select',
			'settings'        => 'bgtfw_page_headers_' . $slug . '_template',
			'label'           => $label,
			'priority'        => $priority,
			'choices'         => $this->get_template_choices(),
			'default'         => 'global',
			'section'         => 'bgtfw_page_headers',
			'active_callback' => function() {
				$global_headers_enabled = get_theme_mod( 'bgtfw_page_headers_global_enabled' );
				return $this->available_templates_exist() && $global_headers_enabled;
			},
		);

		if ( 'global' === $slug ) {
			unset( $template_selectors['choices']['optgroup1'][1]['global'] );
			$template_selectors['default'] = 'none';
		}

		return $template_selectors;
	}

	/**
	 * Get Template Choices
	 *
	 * @since 1.1.0
	 *
	 * @return array An Array of Available Templates.
	 */
	public function get_template_choices() {
		$available_templates = $this->base->templates->get_available( true );
		foreach ( $available_templates as $template_id => $template_title ) {
			if ( ! $template_title ) {
				$available_templates[ $template_id ] = '( Untitled Draft )';
			}
		}

		$choices = array(
			'optgroup1' => array(
				esc_attr__( 'Default Options', 'crio-premium' ),
				array(
					'none'   => esc_attr__( 'Use Customizer Settings', 'crio-premium' ),
					'global' => esc_attr__( 'Use Global Page Header', 'crio-premium' ),
				),
			),
			'optgroup2' => array(
				esc_attr__( 'Available Page Headers', 'crio-premium' ),
				$available_templates,
			),
		);

		return $choices;
	}
}
