<?php

/**
 * File: class=crio-premium-page-headers-templates-sample.php
 *
 * Class that handles a single sample template.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers
 */

/**
 * Class: Crio_Premium_Page_Headers_Templates_Sample
 *
 * This is the class for installing a single sample template.
 */
class Crio_Premium_Page_Headers_Templates_Sample {

	/**
	 * Page Headers Base
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_base
	 */
	public $base;

	/**
	 * Sample Parameters
	 *
	 * @since 1.1.0
	 * @var array
	 */
	public $sample_params;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 *
	 * @param Crio_Premium_Page_Headers_Base $page_headers_base Page Headers Base object.
	 */
	public function __construct( $base, $sample_params ) {
		$this->base          = $base;
		$this->sample_params = $sample_params;
		$this->postarr       = $sample_params['postarr'];
		$this->menus         = isset( $this->sample_params['menus'] ) ? $this->generate_menus() : array();
		$this->prepare_template_content();

		$this->ID = wp_insert_post( $this->postarr );
	}

	/**
	 * Prepare Template Content
	 *
	 * This method replaces menu placeholders with the
	 * generated menu shortcodes created by $this->generate_menus().
	 *
	 * return string the content.
	 */
	public function prepare_template_content() {
		$index   = 0;
		$content = $this->sample_params['template_content'];
		foreach ( $this->menus as $menu_shortcode ) {
			$string_to_replace = '{{menu-' . $index . '}}';
			$content           = str_replace( $string_to_replace, $menu_shortcode, $content );
			$index++;
		}
		$this->postarr['post_content'] = $content;
	}

	/**
	 * Generates Menus
	 *
	 * Generates markup for menus in this sample.
	 *
	 * @since 1.1.0
	 */
	public function generate_menus() {
		$menus = array();
		foreach ( $this->sample_params['menus'] as $menu ) {
			$shortcode        = '[boldgrid_component type="wp_boldgrid_component_menu"';
			$menu_location    = $this->get_menu_location( $menu['type'] );
			$menu_location_id = $this->get_location_id( $menu_location );
			$menu_id          = $this->get_menu_id( $menu['type'] );
			$opts             = array(
				'widget-boldgrid_component_menu[][bgc_menu_location]'    => $menu_location,
				'widget-boldgrid_component_menu[][bgc_menu_location_id]' => $menu_location_id,
				'widget-boldgrid_component_menu[][bgc_menu]'             => $menu_id,
				'widget-boldgrid_component_menu[][bgc_menu_align]'       => $menu['align'],
			);
			$opts             = rawurlencode( wp_json_encode( $opts ) );
			$shortcode       .= ' opts="' . $opts . '"]';
			$menus[]          = $shortcode;
		}

		return $menus;
	}

	/**
	 * Get Menu Id
	 *
	 * This retrieves the desired WordPress menu ID
	 * for this type of menu.
	 *
	 * @since 1.1.0
	 *
	 * @param string $menu_type Type of Menu.
	 *
	 * @return int Menu ID.
	 */
	public function get_menu_id( $menu_type ) {
		$nav_location_name = explode( '_', $menu_type )[0];
		$nav_locations     = get_nav_menu_locations();
		foreach ( $nav_locations as $nav_location => $menu_id ) {
			if ( $nav_location === $nav_location_name ) {
				return $menu_id;
			}
		}
		return 0;
	}

	/**
	 * Get Menu Location
	 *
	 * Creates a menu location name from configs
	 *
	 * @since 1.1.0
	 *
	 * @param string $menu_type Type of Menu.
	 *
	 * @return string Menu Location Name
	 */
	public function get_menu_location( $menu_type ) {
		$template_name = $this->sample_params['template_name'];
		$menu_type     = ucwords( str_replace( '_', ' ', $menu_type ) );

		return $template_name . ' ' . $menu_type;
	}

	/**
	 * Get Menu Location Id
	 *
	 * Converts Menu Location Name to a unique ID.
	 *
	 * @since 1.1.0
	 *
	 * @param string $menu_location Menu Location Name.
	 *
	 * @return string Menu Location ID
	 */
	public function get_location_id( $menu_location ) {
		$menu_location_id = strtolower( str_replace( ' ', '-', $menu_location ) );
		return $menu_location_id . '_001';
	}
}
