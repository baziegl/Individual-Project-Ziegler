<?php
/**
 * The customizer-specific functionality of the plugin.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.0.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/customizer
 */

/**
 * The customizer-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/customizer
 * @author     BoldGrid <pdt@boldgrid.com>
 */
class Crio_Premium_Customizer {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string  $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string  $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Remove the Crio Premium upsell sections from WP Customizer.
	 *
	 * @since 1.0.0
	 *
	 * @param Object $wp_customize WP_Customize instance.
	 */
	public function remove_upsell_section( $wp_customize ) {
		$wp_customize->remove_section( 'bgtfw-upsell' );
	}


	/**
	 * Remove the customizer notices added to dynamic menu sections.
	 *
	 * @since 1.0.0
	 *
	 * @param Object $wp_customize WP_Customize instance.
	 */
	public function remove_menu_notices( $wp_customize ) {
		$menus = get_registered_nav_menus();

		foreach ( $menus as $location => $description ) {

			// Remove hamburger menu style upgrade notices.
			$wp_customize->get_section( "bgtfw_menu_hamburgers_$location" )->notice = '';

			// Remove menu item hover style upgrade notices.
			$wp_customize->get_section( "bgtfw_menu_items_hover_item_$location" )->notice = '';
		}
	}

	/**
	 * Adds configuration overrides to BGTFW.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Array of configrations for BGTFW.
	 *
	 * @return array $config Array of configrations for BGTFW.
	 */
	public function add_configs( $config ) {

		$this->palette           = new Boldgrid_Framework_Compile_Colors( $config );
		$this->active_palette    = $this->palette->get_active_palette();
		$this->formatted_palette = $this->palette->color_format( $this->active_palette );
		$this->sanitize          = new Boldgrid_Framework_Customizer_Color_Sanitize();

		$bgtfw_presets   = new Boldgrid_Framework_Customizer_Presets( $config );
		$partial_refresh = new Boldgrid_Framework_Customizer_Partial_Refresh( $config );

		// Sticky Header toggle button.
		$config['customizer']['panels']['bgtfw_sticky_header_layouts']           = array(
			'title'       => __( 'Sticky Header Layout', 'bgtfw' ),
			'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'A sticky header will remain at the top of your screen as you scroll down a page.', 'bgtfw' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/customizing-the-header-design-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>' . esc_html__( 'Help', 'bgtfw' ) . '</a></div></div>',
			'panel'       => 'bgtfw_header',
			'capability'  => 'edit_theme_options',
			'priority'    => 2,
			'icon'        => 'dashicons-cover-image',
		);
		$config['customizer']['sections']['bgtfw_sticky_header_presets']         = array(
			'title'       => __( 'Select Layout', 'bgtfw' ),
			'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Choose from a number of presets for your header layout, or choose "Custom" to create a customized header layout.', 'bgtfw' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/customizing-the-header-design-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>' . esc_html__( 'Help', 'bgtfw' ) . '</a></div></div>',
			'panel'       => 'bgtfw_sticky_header_layouts',
			'capability'  => 'edit_theme_options',
			'priority'    => 1,
			'icon'        => 'dashicons-schedule',
		);
		$config['customizer']['sections']['bgtfw_sticky_header_layout_advanced'] = array(
			'title'       => __( 'Advanced', 'bgtfw' ),
			'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Manage the layout of your site\'s sticky header.', 'bgtfw' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/customizing-the-header-design-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>' . esc_html__( 'Help', 'bgtfw' ) . '</a></div></div>',
			'panel'       => 'bgtfw_sticky_header_layouts',
			'capability'  => 'edit_theme_options',
			'priority'    => 2,
			'icon'        => 'dashicons-admin-generic',
		);
		$config['customizer']['controls']['bgtfw_fixed_header']                  = array(
			'type'        => 'switch',
			'transport'   => 'postMessage',
			'settings'    => 'bgtfw_fixed_header',
			'description' => __( 'You can change what items are displayed when your header is stuck to the top of the page in the <b>Header Layout</b> section below.', 'crio-premium' ),
			'label'       => esc_attr__( 'Sticky Header', 'crio-premium' ),
			'section'     => 'bgtfw_sticky_header_presets',
			'default'     => false,
			'priority'    => 1,
		);
		$config['customizer']['controls']['bgtfw_sticky_header_preset']          = array(
			'type'            => 'radio-image',
			'transport'       => 'postMessage',
			'settings'        => 'bgtfw_sticky_header_preset',
			'label'           => esc_html__( 'Select Layout', 'kirki' ),
			'section'         => 'bgtfw_sticky_header_presets',
			'default'         => 'default',
			'priority'        => 3,
			'choices'         => $bgtfw_presets->get_preset_choices( 'sticky_header' ),
			'active_callback' => array(
				array(
					'setting'  => 'bgtfw_fixed_header',
					'operator' => '==',
					'value'    => true,
				),
			),
		);

		$config['customizer']['controls']['bgtfw_sticky_header_preset_branding'] = array(
			'type'            => 'multicheck',
			'settings'        => 'bgtfw_sticky_header_preset_branding',
			'description'     => $bgtfw_presets->get_branding_notices(),
			'transport'       => 'postMessage',
			'label'           => esc_html__( 'Branding Display', 'bgtfw' ),
			'section'         => 'bgtfw_sticky_header_presets',
			'default'         => array( 'logo', 'title' ),
			'priority'        => 2,
			'choices'         => [
				'logo'        => esc_html__( 'Logo', 'bgtfw' ),
				'title'       => esc_html__( 'Site Title', 'bgtfw' ),
				'description' => esc_html__( 'Tagline', 'bgtfw' ),
			],
			'active_callback' => array(
				array(
					'setting'  => 'bgtfw_sticky_header_preset',
					'operator' => '!=',
					'value'    => 'default',
				),
				array(
					'setting'  => 'bgtfw_sticky_header_preset',
					'operator' => '!=',
					'value'    => 'custom',
				),
				array(
					'setting'  => 'bgtfw_fixed_header',
					'operator' => '==',
					'value'    => true,
				),
			),
		);

		// Sticky Header Layout Control.
		$config['customizer']['controls']['bgtfw_sticky_header_layout'] = [
			'settings'        => 'bgtfw_sticky_header_layout',
			'label'           => '<div class="screen-reader-text">' . __( 'Sticky Header Layout', 'bgtfw' ) . '</div>',
			'type'            => 'bgtfw-sortable-accordion',
			'transport'       => 'postMessage',
			'priority'        => 2,
			'default'         => [
				[
					'container' => 'container',
					'items'     => [
						[
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => [
								[
									'selector' => '.custom-logo-link',
									'display'  => 'show',
									'title'    => __( 'Logo', 'bgtfw' ),
								],
								[
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => __( 'Title', 'bgtfw' ),
								],
								[
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => __( 'Tagline', 'bgtfw' ),
								],
							],
						],
						[
							'type'  => 'boldgrid_menu_sticky-main',
							'key'   => 'menu',
							'align' => 'e',
						],
					],
				],
			],
			'items'           => [
				'menu'     => [
					'icon'     => 'dashicons dashicons-menu',
					'title'    => __( 'Menu', 'bgtfw' ),
					'controls' => [
						'menu-select' => [],
						'align'       => [
							'default' => 'nw',
						],
					],
				],
				'branding' => [
					'icon'     => 'dashicons dashicons-store',
					'title'    => __( 'Branding', 'bgtfw' ),
					'controls' => [
						'display' => [
							'default' => [
								[
									'selector' => '.custom-logo-link',
									'display'  => 'show',
									'title'    => __( 'Logo', 'bgtfw' ),
								],
								[
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => __( 'Title', 'bgtfw' ),
								],
								[
									'selector' => '.site-description',
									'display'  => 'show',
									'title'    => __( 'Tagline', 'bgtfw' ),
								],
							],
						],
						'align'   => [
							'default' => 'nw',
						],
					],
				],
				'sidebar'  => [
					'icon'     => 'dashicons dashicons-layout',
					'title'    => __( 'Widget Area', 'bgtfw' ),
					'controls' => [
						'sidebar-edit' => [],
					],
				],
			],
			'location'        => 'sticky-header',
			'section'         => 'bgtfw_header_advanced',
			'active_callback' => function() {
				return false;
			},
		];

		$config['customizer']['controls']['bgtfw_sticky_header_layout_custom'] = [
			'settings'  => 'bgtfw_sticky_header_layout_custom',
			'label'     => '<div class="screen-reader-text">' . __( 'Sticky Header Layout', 'bgtfw' ) . '</div>',
			'type'      => 'bgtfw-sortable-accordion',
			'transport' => 'postMessage',
			'priority'  => 2,
			'default'   => [
				[
					'container' => 'container',
					'items'     => [
						[
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => [
								[
									'selector' => '.custom-logo-link',
									'display'  => 'show',
									'title'    => __( 'Logo', 'bgtfw' ),
								],
								[
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => __( 'Title', 'bgtfw' ),
								],
								[
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => __( 'Tagline', 'bgtfw' ),
								],
							],
						],
						[
							'type'  => 'boldgrid_menu_sticky-main',
							'key'   => 'menu',
							'align' => 'e',
						],
					],
				],
			],
			'items'     => [
				'menu'     => [
					'icon'     => 'dashicons dashicons-menu',
					'title'    => __( 'Menu', 'bgtfw' ),
					'controls' => [
						'menu-select' => [],
						'align'       => [
							'default' => 'nw',
						],
					],
				],
				'branding' => [
					'icon'     => 'dashicons dashicons-store',
					'title'    => __( 'Branding', 'bgtfw' ),
					'controls' => [
						'display' => [
							'default' => [
								[
									'selector' => '.custom-logo-link',
									'display'  => 'show',
									'title'    => __( 'Logo', 'bgtfw' ),
								],
								[
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => __( 'Title', 'bgtfw' ),
								],
								[
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => __( 'Tagline', 'bgtfw' ),
								],
							],
						],
						'align'   => [
							'default' => 'nw',
						],
					],
				],
				'sidebar'  => [
					'icon'     => 'dashicons dashicons-layout',
					'title'    => __( 'Widget Area', 'bgtfw' ),
					'controls' => [
						'sidebar-edit' => [],
					],
				],
			],
			'location'  => 'sticky-header',
			'section'   => 'bgtfw_sticky_header_layout_advanced',
		];

		// Blog Post Title Display
		$config['customizer']['controls']['bgtfw_posts_title_display'] = [
			'type'              => 'radio-buttonset',
			'settings'          => 'bgtfw_posts_title_display',
			'label'             => esc_html__( 'Display', 'bgtfw' ),
			'tooltip'           => esc_html__( 'This is a global setting. Access the editor to toggle post titles for individual posts.', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_title',
			'default'           => 'show',
			'choices'           => [
				'show' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'bgtfw' ),
				'hide' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'bgtfw' ),
			],
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'show', 'hide' ], true ) ? $value : $settings->default;
			},
			'partial_refresh'   => [
				'bgtfw_posts_title_display' => [
					'selector'        => '.single .post .featured-imgage-header',
					'render_callback' => function() {
						the_title( sprintf( '<p class="entry-title page-title ' . get_theme_mod( 'bgtfw_global_title_size' ) . '"><a ' . BoldGrid::add_class( 'posts_title', [ 'link' ], false ) . ' href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></p>' );
						echo '<div class="entry-meta">';
						boldgrid_posted_on();
						echo '</div>';
						return;
					},
				],
			],
		];

		// Start: Posts Meta Controls
		$config['customizer']['controls']['bgtfw_posts_meta_display'] = array(
			'type'              => 'radio-buttonset',
			'transport'         => 'auto',
			'settings'          => 'bgtfw_posts_meta_display',
			'label'             => esc_attr__( 'Display', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'inline-block',
			'choices'           => array(
				'inline-block' => '<span class="dashicons dashicons-minus"></span>' . __( 'Inline', 'bgtfw' ),
				'block'        => '<span class="dashicons dashicons-menu"></span>' . __( 'New Lines', 'bgtfw' ),
				'none'         => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'inherit', 'none' ], true ) ? $value : $settings->default;
			},
			'output'            => array(
				array(
					'element'  => '.single .entry-header .entry-meta',
					'property' => 'display',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_meta_position'] = array(
			'type'              => 'radio-buttonset',
			'transport'         => 'auto',
			'settings'          => 'bgtfw_posts_meta_position',
			'label'             => esc_attr__( 'Position', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'left',
			'choices'           => array(
				'left'   => '<span class="dashicons dashicons-editor-alignleft"></span>' . esc_attr__( 'Left', 'bgtfw' ),
				'center' => '<span class="dashicons dashicons-editor-aligncenter"></span>' . esc_attr__( 'Center', 'bgtfw' ),
				'right'  => '<span class="dashicons dashicons-editor-alignright"></span>' . esc_attr__( 'Right', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'left', 'center', 'right' ], true ) ? $value : $settings->default;
			},
			'output'            => array(
				array(
					'element'  => '.single .entry-header .entry-meta',
					'property' => 'text-align',
				),
			),
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_date_display'] = array(
			'type'              => 'radio-buttonset',
			'transport'         => 'auto',
			'settings'          => 'bgtfw_posts_date_display',
			'label'             => esc_attr__( 'Post Date Display', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'inherit',
			'choices'           => array(
				'inherit' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'bgtfw' ),
				'none'    => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'inherit', 'none' ], true ) ? $value : $settings->default;
			},
			'output'            => array(
				array(
					'element'  => '.single .entry-header .entry-meta .posted-on',
					'property' => 'display',
				),
			),
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_meta_format'] = array(
			'type'              => 'radio-buttonset',
			'transport'         => 'auto',
			'settings'          => 'bgtfw_posts_meta_format',
			'label'             => esc_attr__( 'Date Format', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'timeago',
			'choices'           => array(
				'timeago' => '<i class="fa fa-cc" aria-hidden="true"></i>' . esc_attr__( 'Human Readable', 'bgtfw' ),
				'date'    => '<i class="fa fa-calendar" aria-hidden="true"></i>' . esc_attr__( 'Date', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'timeago', 'date' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_display',
					'operator' => '!==',
					'value'    => 'none',
				),
			),
		);

		// Post Date Link Colors.
		$config['customizer']['controls']['bgtfw_posts_date_link_color_display'] = array(
			'type'              => 'radio-buttonset',
			'transport'         => 'postMessage',
			'settings'          => 'bgtfw_posts_date_link_color_display',
			'label'             => esc_attr__( 'Date Link Color', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'inherit',
			'choices'           => array(
				'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'bgtfw' ),
				'custom'  => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_display',
					'operator' => '!==',
					'value'    => 'none',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_date_link_color'] = array(
			'type'              => 'bgtfw-palette-selector',
			'transport'         => 'postMessage',
			'settings'          => 'bgtfw_posts_date_link_color',
			'label'             => esc_attr__( 'Link Color', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'color-1',
			'choices'           => array(
				'selectors' => [ '.single .entry-header .entry-meta .posted-on a' ],
				'colors'    => $this->formatted_palette,
				'size'      => $this->palette->get_palette_size( $this->formatted_palette ),
			),
			'sanitize_callback' => array( $this->sanitize, 'sanitize_palette_selector' ),
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_date_link_decoration'] = array(
			'settings'          => 'bgtfw_posts_date_link_decoration',
			'transport'         => 'postMessage',
			'label'             => esc_html__( 'Text Style', 'bgtfw' ),
			'type'              => 'radio-buttonset',
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'none',
			'choices'           => array(
				'none'      => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'bgtfw' ),
				'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_date_link_color_hover'] = array(
			'type'            => 'slider',
			'transport'       => 'postMessage',
			'settings'        => 'bgtfw_posts_date_link_color_hover',
			'label'           => esc_attr__( 'Hover Color Brightness', 'bgtfw' ),
			'section'         => 'bgtfw_pages_blog_posts_meta',
			'default'         => -25,
			'choices'         => array(
				'min'  => '-25',
				'max'  => '25',
				'step' => '1',
			),
			'active_callback' => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_date_decoration_hover'] = array(
			'settings'          => 'bgtfw_posts_date_link_decoration_hover',
			'transport'         => 'postMessage',
			'label'             => esc_html__( 'Hover Text Style', 'bgtfw' ),
			'type'              => 'radio-buttonset',
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'none',
			'choices'           => array(
				'none'      => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'bgtfw' ),
				'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_date_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_byline_display'] = array(
			'type'              => 'radio-buttonset',
			'transport'         => 'auto',
			'settings'          => 'bgtfw_posts_byline_display',
			'label'             => esc_attr__( 'Author Display', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'inherit',
			'choices'           => array(
				'inherit' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'bgtfw' ),
				'none'    => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'inherit', 'none' ], true ) ? $value : $settings->default;
			},
			'output'            => array(
				array(
					'element'  => '.single .entry-header .entry-meta .byline',
					'property' => 'display',
				),
			),
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
			),
		);

		// Start: Post Byline Link Controls.
		$config['customizer']['controls']['bgtfw_posts_byline_link_color_display'] = [
			'type'              => 'radio-buttonset',
			'transport'         => 'postMessage',
			'settings'          => 'bgtfw_posts_byline_link_color_display',
			'label'             => esc_attr__( 'Author Link Color', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'inherit',
			'choices'           => array(
				'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'bgtfw' ),
				'custom'  => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_display',
					'operator' => '!==',
					'value'    => 'none',
				),
			),
		];

		$config['customizer']['controls']['bgtfw_posts_byline_link_color'] = array(
			'type'              => 'bgtfw-palette-selector',
			'transport'         => 'postMessage',
			'settings'          => 'bgtfw_posts_byline_link_color',
			'label'             => esc_attr__( 'Link Color', 'bgtfw' ),
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'color-1',
			'choices'           => array(
				'selectors' => [ '.single .entry-header .entry-meta .byline .author a' ],
				'colors'    => $this->formatted_palette,
				'size'      => $this->palette->get_palette_size( $this->formatted_palette ),
			),
			'sanitize_callback' => array( $this->sanitize, 'sanitize_palette_selector' ),
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_byline_link_decoration'] = array(
			'settings'          => 'bgtfw_posts_byline_link_decoration',
			'transport'         => 'postMessage',
			'label'             => esc_html__( 'Text Style', 'bgtfw' ),
			'type'              => 'radio-buttonset',
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'none',
			'choices'           => array(
				'none'      => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'bgtfw' ),
				'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_byline_link_color_hover'] = array(
			'type'            => 'slider',
			'transport'       => 'postMessage',
			'settings'        => 'bgtfw_posts_byline_link_color_hover',
			'label'           => esc_attr__( 'Hover Color Brightness', 'bgtfw' ),
			'section'         => 'bgtfw_pages_blog_posts_meta',
			'default'         => -25,
			'choices'         => array(
				'min'  => '-25',
				'max'  => '25',
				'step' => '1',
			),
			'active_callback' => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		$config['customizer']['controls']['bgtfw_posts_byline_decoration_hover'] = array(
			'settings'          => 'bgtfw_posts_byline_link_decoration_hover',
			'transport'         => 'postMessage',
			'label'             => esc_html__( 'Hover Text Style', 'bgtfw' ),
			'type'              => 'radio-buttonset',
			'section'           => 'bgtfw_pages_blog_posts_meta',
			'default'           => 'none',
			'choices'           => array(
				'none'      => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'bgtfw' ),
				'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'bgtfw' ),
			),
			'sanitize_callback' => function( $value, $settings ) {
				return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
			},
			'active_callback'   => array(
				array(
					'setting'  => 'bgtfw_posts_meta_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_display',
					'operator' => '!==',
					'value'    => 'none',
				),
				array(
					'setting'  => 'bgtfw_posts_byline_link_color_display',
					'operator' => '!==',
					'value'    => 'inherit',
				),
			),
		);

		// Premium Hamburger Menu Styles.
		$config['customizer']['controls']['bgtfw_menu_hamburger_main']['choices'] = [
			'3DX'                => 'hamburger--3dx',
			'3DX Reverse'        => 'hamburger--3dx-r',
			'3DY'                => 'hamburger--3dy',
			'3DY Reverse'        => 'hamburger--3dy-r',
			'3DXY'               => 'hamburger--3dxy',
			'3DXY Reverse'       => 'hamburger--3dxy-r',
			'Arrow'              => 'hamburger--arrow',
			'Arrow Reverse'      => 'hamburger--arrow-r',
			'Arrow 2'            => 'hamburger--arrowalt',
			'Arrow 2 Reverse'    => 'hamburger--arrowalt-r',
			'Arrow Turn'         => 'hamburger--arrowturn',
			'Arrow Turn Reverse' => 'hamburger--arrowturn-r',
			'Slider'             => 'hamburger--slider',
			'Slider Reverse'     => 'hamburger--slider-r',
			'Spin'               => 'hamburger--spin',
			'Spin Reverse'       => 'hamburger--spin-r',
			'Spring'             => 'hamburger--spring',
			'Spring Reverse'     => 'hamburger--spring-r',
			'Stand'              => 'hamburger--stand',
			'Stand Reverse'      => 'hamburger--stand-r',
			'Squeeze'            => 'hamburger--squeeze',
			'Vortex'             => 'hamburger--vortex',
			'Vortex Reverse'     => 'hamburger--vortex-r',
		];

		/**
		 * Premium Menu Item Hover Styles.
		 *
		 * Single Color Background Transitions
		 */
		$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup1'][1] = array_merge(
			$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup1'][1],
			[
				/**
				 * Currently this pulses to default color in RGBA. Color doesn't look
				 * like it gets extracted out since it's happening in a transition.
				 *
				 * Disabling this for now.
				 *
				 * 'hvr-back-pulse' => esc_attr__( 'Back Pulse', 'bgtfw' ),
				 */
				'hvr-fade'             => esc_attr__( 'Fade', 'bgtfw' ),
				'hvr-sweep-to-right'   => esc_attr__( 'Sweep to Right', 'bgtfw' ),
				'hvr-sweep-to-left'    => esc_attr__( 'Sweep to Left', 'bgtfw' ),
				'hvr-sweep-to-bottom'  => esc_attr__( 'Sweep to Bottom', 'bgtfw' ),
				'hvr-sweep-to-top'     => esc_attr__( 'Sweep to Top', 'bgtfw' ),
				'hvr-bounce-to-right'  => esc_attr__( 'Bounce to Right', 'bgtfw' ),
				'hvr-bounce-to-left'   => esc_attr__( 'Bounce to Left', 'bgtfw' ),
				'hvr-bounce-to-bottom' => esc_attr__( 'Bounce to Bottom', 'bgtfw' ),
				'hvr-bounce-to-top'    => esc_attr__( 'Bounce to Top', 'bgtfw' ),
			]
		);

		// Two Color Background Transitions.
		$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup2'][1] = array_merge(
			$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup2'][1],
			[
				'hvr-radial-in'              => esc_attr__( 'Radial In', 'bgtfw' ),
				'hvr-radial-out'             => esc_attr__( 'Radial Out', 'bgtfw' ),
				'hvr-rectangle-in'           => esc_attr__( 'Rectangle In', 'bgtfw' ),
				'hvr-rectangle-out'          => esc_attr__( 'Rectangle Out', 'bgtfw' ),
				'hvr-shutter-in-horizontal'  => esc_attr__( 'Shutter In Horizontal', 'bgtfw' ),
				'hvr-shutter-in-vertical'    => esc_attr__( 'Shutter In Vertical', 'bgtfw' ),
				'hvr-shutter-out-horizontal' => esc_attr__( 'Shutter Out Horizontal', 'bgtfw' ),
				'hvr-shutter-out-vertical'   => esc_attr__( 'Shutter Out Vertical', 'bgtfw' ),
			]
		);

		// Border Effects.
		$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup3'][1] = array_merge(
			$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup3'][1],
			[
				'hvr-trim'        => esc_attr__( 'Trim', 'bgtfw' ),
				'hvr-ripple-out'  => esc_attr__( 'Ripple Out', 'bgtfw' ),
				'hvr-ripple-in'   => esc_attr__( 'Ripple In', 'bgtfw' ),
				'hvr-outline-out' => esc_attr__( 'Outline Out', 'bgtfw' ),
				'hvr-outline-in'  => esc_attr__( 'Outline In', 'bgtfw' ),
			]
		);

		// Overline/Underline Effects.
		$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup4'][1] = array_merge(
			$config['customizer']['controls']['bgtfw_menu_items_hover_effect_main']['choices']['optgroup4'][1],
			[
				'hvr-underline-from-left'   => esc_attr__( 'Underline From Left', 'bgtfw' ),
				'hvr-underline-from-center' => esc_attr__( 'Underline From Center', 'bgtfw' ),
				'hvr-underline-from-right'  => esc_attr__( 'Underline From Right', 'bgtfw' ),
				'hvr-underline-reveal'      => esc_attr__( 'Underline Reveal', 'bgtfw' ),
				'hvr-overline-reveal'       => esc_attr__( 'Overline Reveal', 'bgtfw' ),
				'hvr-overline-from-left'    => esc_attr__( 'Overline From Left', 'bgtfw' ),
				'hvr-overline-from-center'  => esc_attr__( 'Overline From Center', 'bgtfw' ),
				'hvr-overline-from-right'   => esc_attr__( 'Overline From Right', 'bgtfw' ),
			]
		);

		/**
		 * Customizer notification removal.
		 */

		// Header Layouts.
		unset( $config['customizer']['sections']['bgtfw_header_layout']['notice'] );
		unset( $config['customizer']['panels']['bgtfw_header_layouts']['notice'] );
		unset( $config['customizer']['sections']['bgtfw_header_presets']['notice'] );

		// Blog Posts.
		unset( $config['customizer']['panels']['bgtfw_blog_posts_panel']['notice'] );

		// Menus.
		unset( $config['customizer']['panels']['bgtfw_menus_panel']['notice'] );

		return $config;
	}

	/**
	 * Adds attribution controls to dynamic areas.
	 *
	 * @since 1.0.1
	 */
	public function add_attribution_controls() {

		// Allows the remove BoldGrid attribution control to be used.
		remove_action( 'customize_controls_print_styles', [ 'Boldgrid_Framework_Customizer_Footer', 'customize_attribution' ], 999 );
	}

	/**
	 * Handles the output of attribution links in the footer before the framework.
	 *
	 * This is hooked before the main framework to check the value, then handle the
	 * value after the framework has processed it.  This is to ensure backwards
	 * compatibility for versions between the theme and this plugin.
	 *
	 * @since 1.0.3
	 */
	public function pre_attribution() {
		$theme_mod = get_theme_mod( 'hide_boldgrid_attribution' );
		add_action(
			'customize_save_after', function() use ( $theme_mod ) {
				set_theme_mod( 'hide_boldgrid_attribution', $theme_mod );
			}, 1000
		);
	}
}
