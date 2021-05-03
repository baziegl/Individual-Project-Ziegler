<?php

/**
 * File: class=crio-premium-page-headers-templates.php
 *
 * Adds the Page Headers feature to Crio.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers
 */

/**
 * Class: Crio_Premium_Page_Headers_Templates
 *
 * This is the class for managing the Custom Header Template Post Type.
 */
class Crio_Premium_Page_Headers_Templates {

	/**
	 * Page Headers Base
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_base
	 */
	public $base;

	/**
	 * Available Header Templates
	 *
	 * @since 1.1.0
	 * @var array
	 */
	public $available;

	/**
	 * Page Headers Meta
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Templates_Meta
	 */
	public $meta;

	/**
	 * Page Headers Template Preivewer
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Templates_Previewer
	 */
	public $previewer;

	/**
	 * Page Headers Template Navs
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Templates_Previewer
	 */
	public $navs;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 *
	 * @param Crio_Premium_Page_Headers_Base $page_headers_base Page Headers Base object.
	 */
	public function __construct( $base ) {
		$this->base      = $base;
		$this->available = $this->get_available();
		$this->meta      = new Crio_Premium_Page_Headers_Templates_Meta( $this->base );
		$this->editor    = new Crio_Premium_Page_Headers_Templates_Editor( $this->base );
		$this->navs      = new Crio_Premium_Page_Headers_Templates_Navs( $this->base );
		$this->samples   = new Crio_Premium_Page_Headers_Templates_Samples( $this->base );
	}

	/**
	 * Query Available Templates
	 *
	 * @since 1.1.0
	 *
	 * @return array An array of available templates.
	 */
	public function get_available() {
		$posts               = get_posts(
			array(
				'post_type'   => 'crio_page_header',
				'post_status' => array( 'publish' ),
				'numberposts' => -1,
			)
		);
		$available_templates = array();

		foreach ( $posts as $post ) {
			$available_templates[ $post->ID ] = $post->post_title;
		}
		return $available_templates;
	}

	/**
	 * Registers Page Headers Custom Post Type
	 *
	 * @since 1.1.0
	 */
	public function register_post_type() {

		set_user_setting( 'editor', 'tinymce' );

		register_post_type(
			'crio_page_header',
			array(
				'labels'       => array(
					'name'           => __( 'Page Headers', 'crio-premium' ),
					'singlular_name' => __( 'Page Header', 'crio-premium' ),
					'add_new_item'   => __( 'Add New Page Header', 'crio-premium' ),
					'edit_item'      => __( 'Edit Header', 'crio-premium' ),
					'view_items'     => __( 'Page Headers', 'crio-premium' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'supports'     => array( 'editor', 'title' ),
			)
		);
	}

	/**
	 * Set Default Editor
	 *
	 * @since 1.1.0
	 */
	public function set_default_editor() {
		$option                     = Boldgrid_Editor_Option::get( 'default_editor' );
		$option                     = empty( $option ) ? array() : $option;
		$option['crio_page_header'] = 'bgppb';
		Boldgrid_Editor_Option::update( 'default_editor', $option );
	}

	/**
	 * Print Header Template to screen
	 *
	 * @since 1.1.0
	 */
	public function get_header() {
		global $boldgrid_theme_framework;
		$bgtfw_configs = $boldgrid_theme_framework->get_configs();
		if ( ! apply_filters( 'crio_premium_page_headers_enabled', false ) ) {
			return;
		}
		$current_template = apply_filters( 'crio_premium_get_page_header', get_the_ID() );

		if ( get_the_ID() === $current_template ) {
			return;
		}

		$template_meta = get_post_meta( $current_template );

		$primary_header_included = isset( $template_meta['crio-premium-include-site-header'] ) ? $template_meta['crio-premium-include-site-header'][0] : '1';

		$merge_page_header = isset( $template_meta['crio-premium-merge-site-header'] ) ? $template_meta['crio-premium-merge-site-header'][0] : '1';

		/*
		 * If the page header is included in this site header,
		 * then we need to capture the header as it is rendered using
		 * get_template_part(). This is captured and added to the $content string
		 */
		if ( ! empty( $primary_header_included ) ) {
			ob_start();
			get_template_part( 'templates/header/header', $bgtfw_configs['template']['header'] );
			$content = ob_get_contents();
			ob_end_clean();
			if ( ! empty( $merge_page_header ) ) {
				$this->merge_page_header();
				$content = str_replace( 'class="header', 'class="header merged-header', $content );
			} else {
				$content = str_replace( 'class="header', 'class="header included-site-header', $content );
			}
		} else {
			$content = '<header id="masthead" class="template-header template-' . $current_template . '">';
		}

		// This is where we add the actual post pontent from the theme template.
		$content .= apply_filters( 'the_content', get_post_field( 'post_content', $current_template ) );

		/*
		 * The get_template_part() already prints the </header> tag,
		 * so we don't need to print that if the primary header
		 * is included.
		 */
		if ( empty( $primary_header_included ) ) {
			$content .= '</header>';
		}

		/*
		 * BGTFW prints the opening <div> tag for the #content element as part of the output of the page-title section, since that page title is
		 * printed within that section. In order to account for that, we have to manually print that tag here. However, if the page header does
		 * not contain a page title element, we need to let the default one display.
		 */
		$has_page_title = get_post_meta( $current_template, 'crio-premium-template-has-page-title', true );
		if ( $has_page_title ) {
			$content .= '</div><div id="content" ' . BoldGrid::add_class( 'site_content', array( 'site-content' ), false ) . ' role="document">';
		} else {
			$content .= '</div>';
		}

		$background_override     = get_post_meta( get_the_ID(), 'crio-premium-page-header-background', true );
		$background_override_src = wp_get_attachment_image_src( $background_override, 'full' );

		/*
		 * These conditionals are designed to handle background image overrides for three scenarios in this order:
		 * 1. If the background-image for the section is already set in the inline styles.
		 * 2. If the background-image is NOT already set in the inline styles, but there are other inline styles existing for the .boldgrid-section element.
		 * 3. If the background-image is NOT already set in the inline styles, and there are NO other inline styles existing for the .boldgrid-section element.
		 */
		if ( isset( $background_override_src[0] ) && 1 === preg_match( "/background-image: .*url\('\S+'\)/", $content ) ) {
			$content = preg_replace( "/background-image: (.*)url\('(\S+)'\)/", "background-image: $1url('$background_override_src[0]')", $content, 1 );
		} elseif ( isset( $background_override_src[0] ) && 1 === preg_match( '/<div[^>]*class="[^">]*boldgrid-section[^">]*"[^">]*style="[^">]*"[^">]*>/', $content ) ) {
			$content = preg_replace( '/(<div[^>]*class="[^">]*boldgrid-section[^">]*"[^">]*)(style=")([^">]*"[^">]*>)/', "$1style=\"background-image: url('$background_override_src[0]');background-position: center;background-size: cover; $3", $content, 1 );
		} elseif ( isset( $background_override_src[0] ) && 1 === preg_match( '/<div[^>]*class="[^">]*boldgrid-section[^">]*"[^">]*[^">]*>/', $content ) ) {
			$content = preg_replace( '/(<div[^>]*class="[^">]*boldgrid-section[^">]*"[^">]*[^">]*)(>)/', "$1 style=\"background-image: url('$background_override_src[0]');background-position: center;background-size: cover;$3\">", $content, 1 );
		}

		echo $content; // phpcs:ignore WordPress.XSS.EscapeOutput
	}

	/**
	 * Get the template for a page / post when in the editor.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post WP_Post Object
	 *
	 * @return int Template ID.
	 */
	public function edit_post_template( $post ) {

		$global_template_id = (int) get_theme_mod( 'bgtfw_page_headers_global_template' );

		$theme_mod = '';
		if ( get_option( 'page_on_front' ) === $post->ID ) {
			$theme_mod = 'bgtfw_page_headers_home_template';
		} elseif ( get_option( 'page_for_posts' ) === $post->ID ) {
			$theme_mod = 'bgtfw_page_headers_blog_template';
		} elseif ( $this->is_woo( $post->ID ) ) {
			$theme_mod = 'bgtfw_page_headers_woo_template';
		} elseif ( 'page' === $post->post_type ) {
			$theme_mod = 'bgtfw_page_headers_pages_template';
		} else {
			$theme_mod = 'bgtfw_page_headers_posts_template';
		}

		$template = get_theme_mod( $theme_mod );

		if ( 'global' === $template && 'none' === get_theme_mod( 'bgtfw_page_headers_global_template' ) ) {
			return false;
		}

		if ( 'none' === $template ) {
			return false;
		}

		$template = 'global' === $template ? $global_template_id : (int) $template;

		return $template;
	}

	/**
	 * Get The template for this post
	 *
	 * Determines the correct template for a given post ID
	 *
	 * @since 1.1.0
	 *
	 * @param int  $post_id
	 * @param bool $allow_override Allow this to reflect post / page overrides.
	 *
	 * @return int Template ID.
	 */
	public function get_for_post( $post_id, $allow_override = true ) {
		/*
		 * Returning false in this method during template rendering
		 * causes the default customizer header to be displayed.
		 */
		if ( ! get_theme_mod( 'bgtfw_page_headers_global_enabled', true ) ) {
			return false;
		}

		$global_template_id = (int) get_theme_mod( 'bgtfw_page_headers_global_template' );

		$theme_mod = '';

		if ( is_front_page() && is_home() ) {
			// Default Homepage ( Latest Posts ).
			$theme_mod      = 'bgtfw_page_headers_home_template';
			$allow_override = false;
		} elseif ( is_front_page() ) {
			// Static Homepage.
			$theme_mod = 'bgtfw_page_headers_home_template';
		} elseif ( is_home() ) {
			// Blog Page.
			$theme_mod = 'bgtfw_page_headers_blog_template';
		} elseif ( $this->is_woo( $post_id ) ) {
			$theme_mod = 'bgtfw_page_headers_woo_template';
		} elseif ( is_page() ) {
			// General Pages.
			$theme_mod = 'bgtfw_page_headers_pages_template';
		} elseif ( is_single() ) {
			// General Posts.
			$theme_mod = 'bgtfw_page_headers_posts_template';
		} elseif ( is_404() || is_search() || is_archive() ) {
			// Search / 404 Pages.
			$theme_mod = 'bgtfw_page_headers_search_template';
		}

		$template = get_theme_mod( $theme_mod );

		/*
		 * We need to only allow overriding the template if the "allow_override" flag is set to true. This is because
		 * Crio_Premium_Page_Headers_Templates_Meta::override_header_callback needs to be able to get the result of this
		 * call without the override.
		 */
		if ( true === $allow_override && 'post' === get_post_meta( $post_id, 'crio-premium-page-header-override', true ) ) {
			$template = get_post_meta( $post_id, 'crio-premium-page-header-select', true );
		}

		/*
		 * When this method returns false when rendering the template, the customizer template is shown instead
		 * of the page header. Therefore, if the template is set to global and the global is set to 'none',
		 * we return false.
		 */
		if ( 'global' === $template && 'none' === get_theme_mod( 'bgtfw_page_headers_global_template' ) ) {
			return false;
		}
		if ( 'none' === $template ) {
			return false;
		}

		$template_id = 'global' === $template ? $global_template_id : (int) $template;

		return $template_id;
	}

	/**
	 * Is this a Woocommerce page.
	 *
	 * Determines if a given post_id is a woocommerce
	 * page, product, category, etc.
	 *
	 * @since 1.1.0
	 *
	 * @param int $post_id ID of the post to check.
	 *
	 * @return bool Whether or not it is a woocommerce page.
	 */
	public function is_woo( $post_id ) {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			// WooCommerce is not Enabled
			return false;
		}

		/*
		 * If this is called from within the loop of the shop page,
		 * the $post_id value will be the first post listed, not the actual
		 * page's ID. Therefore we need to set the $post_id to the shop_id
		 */
		if ( is_shop() || is_product_category() ) {
			$post_id = get_option( 'woocommerce_shop_page_id' );
		}

		$post = get_post( $post_id );

		if ( $post && 'product' === $post->post_type ) {
			return true;
		}

		$option_keys = array(
			'woocommerce_shop_page_id',
			'woocommerce_terms_page_id',
			'woocommerce_cart_page_id',
			'woocommerce_checkout_page_id',
			'woocommerce_pay_page_id',
			'woocommerce_thanks_page_id',
			'woocommerce_myaccount_page_id',
			'woocommerce_edit_address_page_id',
			'woocommerce_view_order_page_id',
			'woocommerce_change_password_page_id',
			'woocommerce_logout_page_id',
			'woocommerce_lost_password_page_id',
		);

		$shop_page_ids = array();

		foreach ( $option_keys as $option_key ) {
			$id = get_option( $option_key );
			if ( $id ) {
				$shop_page_ids[] = (int) $id;
			}
		}

		if ( in_array( (int) $post_id, $shop_page_ids, true ) ) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Merge Page Headers
	 *
	 * @since 1.1.0
	 */
	public function merge_page_header() {
		Boldgrid_Framework_Customizer_Generic::add_inline_style(
			'sticky-header-image-opacity',
			'#masthead { position: absolute; left: 0; right:0; background-color: rgba(0,0,0,0) !important}'
		);
	}

	/**
	 * Actions On Save
	 *
	 * These are functions to be run anytime a template is saved or updated.
	 *
	 * @since 1.1.0
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 */
	public function actions_on_save( $post_ID, $post, $update ) {
		$this->navs->update_menu_locations( $post );
		$this->meta->save_metadata( $post );

		// Redirect to the go-to-customizer link if set.
		if ( isset( $_POST['go-to-customizer'] ) ) { //phpcs ignore: WordPress.CSRF.NonceVerification.NoNonceVerification
			if ( wp_redirect( $_POST['go-to-customizer'] ) ) {
				exit; //phpcs ignore: WordPress.CSRF.NonceVerification.NoNonceVerification
			}
		}

		// Redirect to the go-to-customizer link if set.
		if ( isset( $_POST['go-to-menu-assignment'] ) ) { //phpcs ignore: WordPress.CSRF.NonceVerification.NoNonceVerification
			if ( wp_redirect( $_POST['go-to-menu-assignment'] ) ) {
				exit; //phpcs ignore: WordPress.CSRF.NonceVerification.NoNonceVerification
			}
		}
	}

	/**
	 * Actions on Delete
	 *
	 * This is run when a post is sent to the trash using the
	 * 'trash_posts' action hook. This is run for all posts, so
	 * be sure to filter for the crio_page_header post types.
	 *
	 * @since 1.1.0
	 *
	 * @param int $post_id Post ID number.
	 */
	public function actions_on_delete( $post_id ) {
		$post = get_post( $post_id );
		if ( 'crio_page_header' !== $post->post_type ) {
			return;
		}
		$options = array(
			'bgtfw_page_headers_posts_template',
			'bgtfw_page_headers_latest_template',
			'bgtfw_page_headers_blog_template',
			'bgtfw_page_headers_search_template',
			'bgtfw_page_headers_woo_template',
			'bgtfw_page_headers_pages_template',
			'bgtfw_page_headers_home_template',
		);

		/*
		 * If any of the options are set to use this template,
		 * we need to change them to use global instead.
		 */
		foreach ( $options as $option ) {
			if ( (int) get_theme_mod( $option ) === $post_id ) {
				set_theme_mod( $option, 'global' );
			}
		}

		/*
		 * If the global template is the one being deleted, this needs to revert to
		 * 'none' so that it uses the customizer settings.
		 */
		if ( (int) get_theme_mod( 'bgtfw_page_headers_global_template' ) === $post_id ) {
			set_theme_mod( 'bgtfw_page_headers_global_template', 'none' );
		}
	}

	/**
	 * Update Site Logo Ajax callback.
	 *
	 * @since 1.1.0
	 */
	public function update_site_logo() {
		$verified = false;
		if ( isset( $_POST ) && isset( $_POST['nonce'] ) ) {
			$verified = wp_verify_nonce(
				$_POST['nonce'],
				'crio_premium_update_site_logo'
			);
		}

		if ( ! $verified ) {
			return false;
		}

		$logo_id = isset( $_POST['logoId'] ) ? $_POST['logoId'] : '';
		set_theme_mod( 'custom_logo', $logo_id );

		$custom_logo = get_theme_mod( 'custom_logo' );

		if ( (string) $logo_id === (string) $custom_logo ) {
			wp_send_json( array( 'logoIdUpdated' => true ) );
		} else {
			wp_send_json( array( 'logoIdUpdated' => false ) );
		}
	}
}
