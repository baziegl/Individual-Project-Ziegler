<?php

/**
 * File: class=crio-premium-page-headers-navs.php
 *
 * Handles the registration of Menu locations within page header templates.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers/Templates
 */

/**
 * Class: Crio_Premium_Page_Headers_Templates_Navs
 *
 * Adds extra post meta and meta boxes to page header templates.
 */
class Crio_Premium_Page_Headers_Templates_Navs {
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
	 * Register Header Navs
	 *
	 * @since 1.1.0
	 */
	public function register_header_navs() {
		global $boldgrid_theme_framework;

		ob_start();
		foreach ( array_keys( get_registered_nav_menus() ) as $registered_nav ) {
			if ( in_array( $registered_nav, $this->get_nav_locations(), true ) ) {
				do_action( 'boldgrid_menu_' . $registered_nav );
			}
		}
		ob_end_clean();

		$saved_menus      = array();
		$all_page_headers = $this->base->templates->get_available();

		foreach ( array_keys( $all_page_headers ) as $page_header ) {
			$header_menus = get_post_meta( $page_header, 'crio-premium-menus', true );
			if ( is_array( $header_menus ) && ! empty( $header_menus ) ) {
				$saved_menus = array_merge( $saved_menus, $header_menus );
			}
		}

		$registered_menus = array_keys( get_nav_menu_locations() );

		$unregistered_menus = array();

		foreach ( array_diff( $saved_menus, $registered_menus ) as $menu ) {
			$unregistered_menus[] = array( $menu, $this->get_description( $menu ) );
		}
	}

	/**
	 * Get Nav Locations
	 *
	 * Get an array of nav locations.
	 * This is for use by the 'boldgrid_custom_menu_locations' filter
	 * to add dynamic menu locations to the bgtfw.
	 *
	 * @since 1.1.0
	 *
	 * @param array $locations an array of Menu Location IDs
	 *
	 * @return array Filtered array of menu location IDs
	 */
	public function get_nav_locations( $locations = array() ) {
		$all_page_headers          = $this->base->templates->get_available();
		$new_locations             = array();
		$registered_menu_locations = get_option( 'crio_premium_menu_locations', array() );

		foreach ( $registered_menu_locations as $menu_location ) {
			$new_locations[] = $menu_location['id'];
		}
		$new_locations = array_diff( $new_locations, $locations );

		return array_merge( $locations, $new_locations );
	}

	/**
	 * Add Menus
	 *
	 * This function is used to add the menus to the
	 * BGTFW configs array using the 'boldgrid_theme_framework_config'
	 * filter.
	 *
	 * @since 1.1.0
	 *
	 * @param array $config Configuration array.
	 *
	 * @return array Configuration array with new menus added.
	 */
	public function add_menus( $config ) {
		$updated_config = $config;

		$locations = $this->get_nav_locations();

		$unregistered_menus = array();

		foreach ( $locations as $menu ) {
			$unregistered_menus[] = array( $menu, $this->get_description( $menu ) );
		}

		foreach ( $unregistered_menus as $menu ) {
			$updated_config['menu']['locations'][ $menu[0] ] = $menu[1];
			$updated_config['menu']['prototype'][ $menu[0] ] = array(
				'theme_location' => $menu[0],
				'container'      => false,
				'menu_id'        => $menu[0],
				'menu_class'     => 'sm sm-clean ' . $menu[0],
			);
		}

		return $updated_config;
	}

	/**
	 * Saves Menu Locations on post save action.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post The Post this save was made for.
	 */
	public function update_menu_locations( $post ) {
		$content = $post->post_content;
		if ( empty( $content ) ) {
			return;
		}
		$dom = new DOMDocument();
		$dom->loadHTML( $content );
		$finder    = new DomXPath( $dom );
		$classname = 'boldgrid-component-menu';
		$nodes     = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]" );

		$menus_in_template = array();
		foreach ( $nodes as $node ) {
			$menu_attrs = json_decode(
				trim(
					urldecode(
						str_replace(
							array( ']', 'opts=' ),
							'',
							shortcode_parse_atts( trim( $node->nodeValue ) )['1'] // phpcs:ignore WordPress.NamingConventions.ValidVariableName
						)
					),
					'"'
				),
				true
			);

			$menus_in_template[] = $menu_attrs['widget-boldgrid_component_menu[][bgc_menu_location_id]'];
		}

		$registered_menu_locations = get_option( 'crio_premium_menu_locations', array() );
		$registered_menus_template = array();

		foreach ( $menus_in_template as $menu_in_template ) {
			if ( ! isset( $registered_menu_locations[ $menu_in_template ] ) ) {
				$registered_menu_locations[ $menu_in_template ] = array(
					'id'          => $menu_in_template,
					'name'        => ucwords( str_replace( '-', ' ', explode( '_', $menu_in_template )[0] ) ),
					'template_id' => $post->ID,
				);
			}
		}

		// We have to loop through the registered menu locations to isolate the ones that are for this specific post.
		foreach ( $registered_menu_locations as $menu_location ) {
			if ( isset( $menu_location['template_id'] ) && (int) $menu_location['template_id'] === $post->ID ) {
				$registered_menus_template[ $menu_location['id'] ] = $menu_location;
			}
		}

		// We then have to loop through the menu locations for this post, and unset any locations that are no longer in the post.
		foreach ( array_keys( $registered_menus_template ) as $menu_id ) {
			if ( ! in_array( $menu_id, $menus_in_template, true ) ) {
				unset( $registered_menu_locations[ $menu_id ] );
			}
		}

		// Unset any menu locations that are for templates no longer existing.
		foreach ( $registered_menu_locations as $menu_location ) {
			if ( ! array_key_exists( $menu_location['template_id'], $this->base->templates->get_available() ) ) {
				unset( $registered_menu_locations[ $menu_location['id'] ] );
			}
		}

		update_option( 'crio_premium_menu_locations', $registered_menu_locations );
	}

	/**
	 * Get Description
	 *
	 * Converts the ID into a human friendly
	 * menu name.
	 *
	 * @since 1.1.0
	 *
	 * @param string $menu_id Menu ID.
	 *
	 * @return string Human friendly Menu Name.
	 */
	public function get_description( $menu_id ) {
		$name = str_replace( '-', ' ', $menu_id );
		$name = preg_replace( '/_\d+/', '', $name );
		return ucwords( $name );
	}

	/**
	 * Admin Ajax function for 'register_menu_locations' action.
	 *
	 * This is used by the 'register_menu_locations' admin-ajax action
	 * to update the 'crio_premium_menu_locations' options array.
	 * This does not actually register the menu locations with WordPress,
	 * that is done by the bgtfw.
	 *
	 * @since 1.1.0
	 *
	 * @return bool Returns false if fails.
	 */
	public function admin_ajax_register_menu_location() {
		$verified = false;
		if ( isset( $_POST ) && isset( $_POST['nonce'] ) ) {
			$verified = wp_verify_nonce(
				$_POST['nonce'],
				'crio_premium_register_menu_location'
			);
		}

		if ( ! $verified ) {
			return false;
		}

		$location_id = $_POST['location_id'];

		$registered_menu_locations = get_option( 'crio_premium_menu_locations', array() );

		$registered_menu_locations[ $location_id ] = array(
			'id'          => $location_id,
			'name'        => $_POST['location_name'],
			'template_id' => $_POST['template_id'],
		);

		update_option( 'crio_premium_menu_locations', $registered_menu_locations );

		$return = array(
			'registered' => true,
			'testData'   => 'success',
			'locationId' => $_POST['location_id'],
			'locations'  => $registered_menu_locations,
		);

		wp_send_json( $return );
	}
}
