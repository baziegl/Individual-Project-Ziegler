<?php

/**
 * File: class=crio-premium-page-headers-templates-editor.php
 *
 * Handles changes that override PPB TinyMCE Editor functionality.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers/Templates
 */

/**
 * Class: Crio_Premium_Page_Headers_Editor
 *
 * Handles changes that override PPB TinyMCE Editor functionality.
 */
class Crio_Premium_Page_Headers_Templates_Editor {
	/**
	 * Page Headers Base
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_base
	 */
	public $page_haders_base;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 *
	 * @param Crio_Premium_Page_Headers_Base $page_headers_base Page Headers Base object.
	 */
	public function __construct( $base ) {
		$this->base   = $base;
		$this->styles = new Crio_Premium_Page_Headers_Templates_Editor_Styles( $base );
	}

	/**
	 * Load Scripts
	 *
	 * @since 1.1.0
	 *
	 * @param array $mce_settings
	 */
	public function load_scripts( $mce_settings ) {
		global $boldgrid_theme_framework;
		global $pagenow;
		$configs          = $boldgrid_theme_framework->get_configs();
		$script_url       = plugin_dir_url( WP_PLUGIN_DIR . '/crio-premium/admin/js/crio-premium-editor.js' ) . 'crio-premium-editor.js';
		$boldgrid_scripts = new Boldgrid_Framework_Scripts( $configs );

		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
			return;
		}

		$screen = get_current_screen();

		// Ensure this is only enqueued if we are on a crio_page_header custom post type.
		if ( $screen && 'crio_page_header' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'bgtfw-smartmenus',
			$configs['framework']['js_dir'] . 'smartmenus/jquery.smartmenus.min.js',
			array( 'jquery' ),
			'1.4',
			true
		);

		wp_register_script(
			'boldgrid-front-end-scripts',
			$boldgrid_scripts->get_webpack_url( $configs['framework']['js_dir'], 'front-end.min.js' ),
			array( 'jquery' ),
			$configs['version'],
			true
		);

		wp_register_script(
			'float-labels',
			$configs['framework']['js_dir'] . 'float-labels.js/float-labels.min.js',
			array(),
			$configs['version'],
			true
		);

		wp_register_script(
			'crio-premium-editor',
			$script_url,
			array( 'jquery' ),
			CRIO_PREMIUM_VERSION,
			true
		);

		wp_enqueue_script( 'boldgrid-front-end-scripts' );
		wp_enqueue_script( 'float-labels' );

		wp_enqueue_script(
			'bgtfw-modernizr',
			$configs['framework']['js_dir'] . 'modernizr.min.js',
			array( 'boldgrid-front-end-scripts' ),
			$configs['version'],
			true
		);

		wp_localize_script(
			'boldgrid-front-end-scripts',
			'highlightRequiredFields',
			array( get_option( 'woocommerce_checkout_highlight_required_fields', 'yes' ) )
		);

		wp_localize_script(
			'boldgrid-front-end-scripts',
			$boldgrid_scripts->get_asset_path(),
			array( $configs['framework']['root_uri'] )
		);

		ob_start();
		get_template_part( 'templates/header/header', $configs['template']['header'] );
		$header = ob_get_contents();
		ob_end_clean();

		$post_meta =

		wp_localize_script(
			'crio-premium-editor',
			'CrioPremiumData',
			array(
				'headerMarkup'      => $header,
				'includeSiteHeader' => get_post_meta( get_the_ID(), 'crio-premium-include-site-header', true ),
				'mergePageHeader'   => get_post_meta( get_the_ID(), 'crio-premium-merge-site-header', true ),
				'background'        => array(
					'image'      => get_theme_mod( 'background_image' ),
					'size'       => get_theme_mod( 'boldgrid_background_image_size' ),
					'repeat'     => get_theme_mod( 'background_repeat' ),
					'attachment' => get_theme_mod( 'boldgrid_background_attachment' ),
					'type'       => get_theme_mod( 'boldgrid_background_type' ),
				),
			)
		);

		wp_localize_script(
			'crio-premium-editor',
			'BGTFW = BGTFW || {}; BGTFW.assets = BGTFW.assets || {}; BGTFW.assets.path',
			array( $configs['framework']['root_uri'] )
		);

		wp_enqueue_script( 'crio-premium-editor' );
	}

	/**
	 * Load MCE Script
	 *
	 * @since 1.1.0
	 */
	public function load_mce_script() {
		global $boldgrid_theme_framework;
		global $wp_styles;

		$screen = get_current_screen();

		// Ensure this is only enqueued if we are on a crio_page_header custom post type.
		if ( $screen && 'crio_page_header' !== $screen->post_type ) {
			return;
		}

		$configs     = $boldgrid_theme_framework->get_configs();
		$script_urls = array(
			'crio-premium-front-end' => $configs['framework']['js_dir'] . 'front-end.min.js',
			'crio-premium-editor'    => plugin_dir_url( WP_PLUGIN_DIR . '/crio-premium/admin/js/crio-premium-editor.js' ) . 'crio-premium-editor-mce.js',
		);

		foreach ( $script_urls as $script => $url ) {
			// phpcs:disable
			printf(
				'<script type="text/javascript" id="%s" src="%s"></script>',
				$script,
				$url
			);
			// phpcs:enable
		}
	}
}
