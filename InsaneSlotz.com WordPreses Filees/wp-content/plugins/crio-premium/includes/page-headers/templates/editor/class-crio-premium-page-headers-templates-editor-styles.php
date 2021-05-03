<?php

/**
 * File: class=crio-premium-page-headers-templates-editor-styles.php
 *
 * Handles changes that override PPB TinyMCE Editor functionality.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers/Templates/Editor
 */

/**
 * Class: Crio_Premium_Page_Headers_Editor_Styles
 *
 * Handles changes that override PPB TinyMCE Editor functionality.
 */
class Crio_Premium_Page_Headers_Templates_Editor_Styles {
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
		$this->base = $base;
	}

	/**
	 * Adds CSS stylesheets to mce editor.
	 *
	 * @since 1.1.0
	 */
	public function add_mce_css( $mce_css ) {
		global $wp_filesystem;
		global $boldgrid_theme_framework;
		$configs = $boldgrid_theme_framework->get_configs();

		$mce_css[] = $configs['framework']['css_dir'] . 'smartmenus/sm-core-css.css';

		$mce_css[] = $configs['framework']['css_dir'] . 'hamburgers/hamburgers.min.css';

		$mce_css[] = plugin_dir_url( WP_PLUGIN_DIR . '/crio-premium/admin/css/hover.css' ) . 'hover.css';

		return $mce_css;
	}

	/**
	 * Get Hover inline CSS
	 *
	 * @since 1.1.0
	 *
	 * @param array $configs Config Array.
	 *
	 * @return string Hover CSS String.
	 */
	public function get_hovor_css( $configs ) {
		$boldgrid_styles = new BoldGrid_Framework_Styles( $configs );
		$inline_css      = $boldgrid_styles->get_css_vars();
		$menus           = get_registered_nav_menus();
		foreach ( $menus as $location => $description ) {
			$inline_css .= $boldgrid_styles->hover_generate( $location );
			$inline_css .= $boldgrid_styles->active_link_generate( $location );
			$inline_css .= $boldgrid_styles->menu_css( $location );
		}
		return $inline_css;
	}

	/**
	 * Get Sticky Header inline CSS
	 *
	 * @since 1.1.0
	 *
	 * @param array $configs Config Array.
	 *
	 * @return string Hover CSS String.
	 */
	public function get_sticky_css( $configs ) {
		$boldgrid_sticky_header = new Boldgrid_Framework_Sticky_Header( $configs );
		return $boldgrid_sticky_header->get_styles();
	}

	/**
	 * Get Directional CSS
	 *
	 * Directional controls have their css added as an inline style
	 * for each control on the front end. These must be added to the MCE
	 * inline css as well.
	 *
	 * @since 1.1.0
	 *
	 * @param array $configs Config Array
	 *
	 * @return string Directional CSS String.
	 */
	public function get_directional_css( $configs ) {
		$generic = new Boldgrid_Framework_Customizer_Generic( $configs );

		$inline_css = '';
		foreach ( $configs['customizer']['controls'] as $control ) {
			$name = ! empty( $control['choices']['name'] ) ? $control['choices']['name'] : null;

			if ( 'boldgrid_controls' === $name ) {
				$style_id      = $control['settings'] . '-bgcontrol';
				$theme_mod_val = get_theme_mod( $control['settings'] );

				// If theme mod is set, use it to create styles.
				if ( $theme_mod_val && ! empty( $theme_mod_val['media'] ) ) {
					$css = ! empty( $theme_mod_val['css'] ) ? $theme_mod_val['css'] : false;

					// If theme mod is not set, try to generate styles from default settings.
				} else {
					$css = $generic->get_default_styles( $control );
				}

				// Enqueue any css if applicable.
				if ( $css ) {
					$inline_css .= wp_specialchars_decode( $css, $quote_style = ENT_QUOTES );
				}
			}
		}

		return $inline_css;
	}

	/**
	 * Get Root CSS
	 *
	 * In order to accurately reflect body background
	 * information, we must add the body's background styles
	 * to the root styles for this template.
	 *
	 * @since 1.1.0
	 *
	 * @param array $configs Config Array
	 *
	 * @return string Directional CSS String.
	 */
	public function get_root_css( $config ) {
		$boldgrid_background_type = get_theme_mod( 'boldgrid_background_type' );
		$root_css                 = '#content_ifr html {';
		if ( 'pattern' === $boldgrid_background_type ) {
			$root_css       .= wp_specialchars_decode(
				'background-image: ' . get_theme_mod( 'boldgrid_background_pattern' ) . ';background-size: auto; background-repeat: repeat; background-attachment: scroll;',
				$quote_style = ENT_QUOTES
			);
		}
		$root_css .= '}';
		return '';
	}

	/**
	 * Get Hamburger Css
	 *
	 * @since 1.1.0
	 *
	 * @param $configs.
	 */
	public function get_hamburger_css( $configs ) {
		$styles = new BoldGrid_Framework_Styles( $configs );
		return $styles->hamburgers_css();
	}

	/**
	 * Add Inline CSS
	 *
	 * Adds Inline CSS to the MCE Editor via the 'boldgrid_mce_inline_styles' filter hook.
	 * This filter hook is added in Crio_Premium_Page_Headers_Base::add_hooks();
	 *
	 * @param string $inline_css A string of css passed in this filter.
	 */
	public function add_inline_css( $inline_css = '' ) {
		global $boldgrid_theme_framework;
		$configs     = $boldgrid_theme_framework->get_configs();
		$inline_css .= $this->get_hovor_css( $configs );
		$inline_css .= $this->get_hamburger_css( $configs );
		$inline_css .= $this->get_sticky_css( $configs );
		$inline_css .= $this->get_directional_css( $configs );
		$inline_css .= $this->get_root_css( $configs );
		return $inline_css;
	}
}
