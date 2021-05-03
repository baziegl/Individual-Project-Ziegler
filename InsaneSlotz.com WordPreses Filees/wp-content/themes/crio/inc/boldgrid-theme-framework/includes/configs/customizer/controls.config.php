<?php
/**
 * Customizer Controls Configs.
 *
 * @package Boldgrid_Theme_Framework
 * @subpackage Boldgrid_Theme_Framework\Configs
 *
 * @since 2.0.0
 *
 * @return array Controls to create in the WordPress Customizer.
 */

global $boldgrid_theme_framework;
$bgtfw_configs = $boldgrid_theme_framework->get_configs();

// Check that get_page_templates() method is available in the customizer.
if ( ! function_exists( 'get_page_templates' ) ) {
	require_once ABSPATH . 'wp-admin/includes/theme.php';
}

$bgtfw_palette           = new Boldgrid_Framework_Compile_Colors( $bgtfw_configs );
$bgtfw_active_palette    = $bgtfw_palette->get_active_palette();
$bgtfw_formatted_palette = $bgtfw_palette->color_format( $bgtfw_active_palette );
$bgtfw_color_sanitize    = new Boldgrid_Framework_Customizer_Color_Sanitize();
$bgtfw_typography        = new Boldgrid_Framework_Customizer_Typography( $bgtfw_configs );
$bgtfw_generic           = new Boldgrid_Framework_Customizer_Generic( $bgtfw_configs );
$bgtfw_presets           = new Boldgrid_Framework_Customizer_Presets( $bgtfw_configs );
$bgtfw_partial_refresh   = new Boldgrid_Framework_Customizer_Partial_Refresh( $bgtfw_configs );


return array(
	'custom_theme_js' => array(
		'type'        => 'code',
		'settings'    => 'custom_theme_js',
		'label'       => __( 'JS code', 'crio' ),
		'help'        => __( 'This adds live JavaScript to your website.', 'crio' ),
		'description' => __( 'Add custom javascript for this theme.', 'crio' ),
		'section'     => 'custom_css',
		'default'     => "// jQuery('body');",
		'priority'    => 10,
		'choices'     => array(
			'language' => 'javascript',
			'theme'    => 'base16-dark',
			'height'   => 100,
		),
	),
	'boldgrid_background_type' => [
		'type'        => 'radio-buttonset',
		'transport'   => 'postMessage',
		'settings'    => 'boldgrid_background_type',
		'section'     => 'background_image',
		'default'     => 'image',
		'priority'    => 0,
		'choices'     => [
			'image'   => '<span class="dashicons dashicons-format-image"></span>' . esc_html__( 'Image', 'crio' ),
			'pattern' => '<span class="dashicons dashicons-art"></span>' . esc_html__( 'Pattern & Color', 'crio' ),
		],
	],
	'boldgrid_background_image_size' => [
		'type' => 'radio',
		'label' => __( 'Background Image Size', 'crio' ),
		'section' => 'background_image',
		'settings' => 'boldgrid_background_image_size',
		'transport' => 'refresh',
		'default'     => 'cover',
		'priority' => 15,
		'choices' => [
			'cover' => __( 'Cover Page', 'crio' ),
			'contain' => __( 'Scaled to Fit', 'crio' ),
			'100% auto' => __( 'Full Width', 'crio' ),
			'auto 100%' => __( 'Full Height', 'crio' ),
			'inherit' => __( 'Default', 'crio' ),
			'auto' => __( 'Do Not Resize', 'crio' ),
		],
	],
	'bgtfw_background_description' => array(
		'type'        => 'custom',
		'settings'    => 'bgtfw_background_description',
		'section'     => 'background_image',
		'default'     => '<div class="bgtfw-description"><p>' . esc_html__( 'Change the appearance of your site\'s background.', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/changing-your-site-background-in-boldgrid-crio/?source=customize-background" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
		'priority'    => 1,
	),
	'boldgrid_background_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'boldgrid_background_color',
		'label' => esc_attr__( 'Color', 'crio' ),
		'description' => esc_attr__( 'Choose a color from your palette to use.', 'crio' ),
		'tooltip' => 'testing what a tool tip looks like',
		'section'     => 'background_image',
		'priority' => 2,
		'default'     => 'color-neutral',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),

	/*** Start Background Image Overlay ***/
	'bgtfw_background_overlay' => array(
		'type'        => 'switch',
		'settings'    => 'bgtfw_background_overlay',
		'transport'   => 'postMessage',
		'label'       => __( 'Image Overlay', 'crio' ),
		'description' => esc_attr__( 'Add an overlay to give your text readability over an image.', 'crio' ),
		'section'     => 'background_image',
		'default'     => false,
		'priority'    => 10,
		'choices'     => array(
			'on'  => esc_attr__( 'Enable', 'crio' ),
			'off' => esc_attr__( 'Disable', 'crio' ),
		),
	),
	'bgtfw_background_overlay_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_background_overlay_color',
		'label'       => esc_attr__( 'Overlay Color', 'crio' ),
		'section'     => 'background_image',
		'priority'    => 10,
		'default'     => 'color-1',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_background_overlay_type' => array(
		'type'        => 'select',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_background_overlay_type',
		'label'       => esc_attr__( 'Overlay Blend Mode', 'crio' ),
		'section'     => 'background_image',
		'priority'    => 10,
		'default'     => 'overlay',
		'choices' => [
			'multiply' => __( 'Multiply', 'crio' ),
			'screen' => __( 'Screen', 'crio' ),
			'overlay' => __( 'Overlay', 'crio' ),
			'darken' => __( 'Darken', 'crio' ),
			'lighten' => __( 'Lighten', 'crio' ),
			'color-dodge' => __( 'Color Dodge', 'crio' ),
			'color-burn' => __( 'Color Burn', 'crio' ),
			'hard-light' => __( 'Hard Light', 'crio' ),
			'soft-light' => __( 'Soft Light', 'crio' ),
			'difference' => __( 'Difference', 'crio' ),
			'exclusion' => __( 'Exclusion', 'crio' ),
			'hue' => __( 'Hue', 'crio' ),
			'saturation' => __( 'Saturation', 'crio' ),
			'color' => __( 'Color', 'crio' ),
			'luminosity' => __( 'Luminosity', 'crio' ),
		],
	),
	'bgtfw_background_overlay_alpha' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_background_overlay_alpha',
		'label'       => esc_attr__( 'Overlay Opacity', 'crio' ),
		'section'     => 'background_image',
		'priority'    => 10,
		'default'     => '0.70',
		'choices'     => array(
			'min'  => '0',
			'max'  => '1',
			'step' => '.01',
		),
	),
	/*** End Background Image Overlay ***/

	/*** Start Header Generic Controls ***/
	'bgtfw_header_margin' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_header_margin_section',
		'settings'    => 'bgtfw_header_margin',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Margin',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.site-header' ),
					'sliders' => array(
						array( 'name' => 'top', 'label' => 'Top', 'cssProperty' => 'margin-top' ),
						array( 'name' => 'bottom', 'label' => 'Bottom', 'cssProperty' => 'margin-bottom' ),
					),
				),
			),
		),
	),
	'bgtfw_header_padding' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_header_padding_section',
		'settings'    => 'bgtfw_header_padding',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Padding',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.site-header header' ),
				),
			),
		),
	),
	'bgtfw_header_border' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_header_border_section',
		'settings'    => 'bgtfw_header_border',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Border',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.site-header header' ),
				),
			),
		),
	),
	'bgtfw_header_border_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_header_border_color',
		'label'       => esc_attr__( 'Border Color', 'crio' ),
		'section'     => 'boldgrid_header_border_section',
		'priority'    => 20,
		'default'     => 'color-1',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_header_shadow' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_header_shadow_section',
		'settings'    => 'bgtfw_header_shadow',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BoxShadow',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.site-header header' ),
				),
			),
		),
	),
	'bgtfw_header_radius' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_header_radius_section',
		'settings'    => 'bgtfw_header_radius',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BorderRadius',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.site-header header', '.wp-custom-header' ),
				),
			),
		),
	),
	/*** End Header Generic Controls ***/

	/*** Start Footer Generic Controls ***/
	'bgtfw_footer_margin' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_footer_margin_section',
		'settings'    => 'bgtfw_footer_margin',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Margin',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#colophon.site-footer' ),
					'sliders' => array(
						array( 'name' => 'top', 'label' => 'Top', 'cssProperty' => 'margin-top' ),
						array( 'name' => 'bottom', 'label' => 'Bottom', 'cssProperty' => 'margin-bottom' ),
					),
				),
			),
		),
	),
	'bgtfw_footer_padding' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_footer_padding_section',
		'settings'    => 'bgtfw_footer_padding',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Padding',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#colophon.site-footer' ),
				),
			),
		),
	),
	'bgtfw_footer_border' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_footer_border_section',
		'settings'    => 'bgtfw_footer_border',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Border',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#colophon.site-footer' ),
				),
			),
		),
	),
	'bgtfw_footer_border_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_footer_border_color',
		'label'       => esc_attr__( 'Border Color', 'crio' ),
		'section'     => 'boldgrid_footer_border_section',
		'priority'    => 20,
		'default'     => 'color-1',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_footer_shadow' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_footer_shadow_section',
		'settings'    => 'bgtfw_footer_shadow',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BoxShadow',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#colophon.site-footer' ),
				),
			),
		),
	),
	'bgtfw_footer_radius' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'boldgrid_footer_radius_section',
		'settings'    => 'bgtfw_footer_radius',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BorderRadius',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#colophon.site-footer' ),
				),
			),
		),
	),
	/*** End Footer Generic Controls ***/

	'bgtfw_pages_container' => array(
		'settings' => 'bgtfw_pages_container',
		'transport'   => 'refresh',
		'label'       => esc_html__( 'Container', 'crio' ),
		'type'        => 'radio-buttonset',
		'priority'    => 35,
		'default'   => 'container',
		'choices'     => array(
			'container' => '<span class="icon-layout-container"></span>' . esc_attr__( 'Contained', 'crio' ),
			'' => '<span class="icon-layout-full-screen"></span>' . esc_attr__( 'Full Width', 'crio' ),
		),
		'section' => 'bgtfw_layout_page_container',
		'sanitize_callback' => function( $value, $settings ) {
			return 'container' === $value || '' === $value ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.page .site-content',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'site-content $',
			),
		),
	),

	'bgtfw_woocommerce_container' => array(
		'settings' => 'bgtfw_woocommerce_container',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Container', 'crio' ),
		'type'        => 'radio-buttonset',
		'priority'    => 35,
		'default'   => 'container',
		'choices'     => array(
			'container' => '<span class="icon-layout-container"></span>' . esc_attr__( 'Contained', 'crio' ),
			'' => '<span class="icon-layout-full-screen"></span>' . esc_attr__( 'Full Width', 'crio' ),
		),
		'section' => 'bgtfw_layout_woocommerce_container',
		'sanitize_callback' => function( $value, $settings ) {
			return 'container' === $value || 'full-width' === $value ? $value : '';
		},
		'js_vars' => array(
			array(
				'element' => '.woocommerce .site-content, .woocommerce-page .site-content',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'site-content $',
			),
			array(
				'element' => '.woocommerce .main-wrapper, .woocommerce-page .main-wrapper',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'main-wrapper $',
			),
			array(
				'element' => '.woocommerce-page .main > .container, .woocommerce-page .main > .full-width',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => '$',
			),
		),
	),
	'bgtfw_layout_page' => array(
		'type'        => 'radio',
		'settings'    => 'bgtfw_layout_page',
		'label'       => __( 'Sidebar', 'crio' ),
		'section'     => 'bgtfw_layout_page_sidebar',
		'default'     => 'no-sidebar',
		'priority'    => 10,
		'choices'     => array(),
		'sanitize_callback' => 'esc_attr',
	),
	'bgtfw_woocommerce_products_per_page' => array(
		'type'              => 'kirki-generic',
		'settings'          => 'bgtfw_woocommerce_products_per_page',
		'label'             => __( 'Products Per Page', 'crio' ),
		'description'       => __( 'How many products should be shown per page?', 'crio' ),
		'section'           => 'woocommerce_product_catalog',
		'default'           => 10,
		'priority'          => 10,
		'sanitize_callback' => 'esc_attr',
		'choices'           => array(
			'type' => 'number',
		),
	),

	// Start: Page Title Controls.
	'bgtfw_pages_title_display' => array(
		'type' => 'radio-buttonset',
		'settings' => 'bgtfw_pages_title_display',
		'label' => esc_html__( 'Display', 'crio' ),
		'tooltip' => esc_html__( 'This is a global setting. Access the editor to toggle page titles for individual posts.', 'crio' ),
		'section' => 'bgtfw_layout_page_title',
		'default' => 'show',
		'choices' => array(
			'show' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'hide' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'show', 'hide' ], true ) ? $value : $settings->default;
		},
		'partial_refresh' => array(
			'bgtfw_pages_title_display' => array(
				'selector' => '.page .page .featured-imgage-header, .blog .page-header .featured-imgage-header, .archive .page-header .featured-imgage-header',
				'render_callback' => function() {
					if ( ! is_front_page() && is_home() ) {
						printf(
							'<p class="page-title %1$s"><a %2$s href="%3$s" rel="bookmark">%4$s</a></p>',
							esc_attr( get_theme_mod( 'bgtfw_global_title_size' ) ),
							BoldGrid::add_class( 'pages_title', [ 'link' ], false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							esc_url( get_permalink( get_option( 'page_for_posts', true ) ) ),
							wp_kses_post( get_the_title( get_option( 'page_for_posts', true ) ) )
						);
					} else if ( is_archive() || is_author() ) {
						$queried_obj_id = get_queried_object_id();
						$archive_url = is_author() ? get_author_posts_url( $queried_obj_id ) : get_term_link( $queried_obj_id );
						printf(
							'<p class="page-title %1$s"><a %2$s href="%3$s" rel="bookmark">%4$s</a></p>',
							esc_attr( get_theme_mod( 'bgtfw_global_title_size' ) ),
							BoldGrid::add_class( 'pages_title', [ 'link' ], false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							esc_url( $archive_url ),
							wp_kses_post( get_the_archive_title() )
						);
						the_archive_description( '<div class="taxonomy-description">', '</div>' );
					} else {
						the_title( sprintf( '<p class="entry-title page-title ' . esc_attr( get_theme_mod( 'bgtfw_global_title_size' ) ) . '"><a ' . BoldGrid::add_class( 'pages_title', [ 'link' ], false ) . ' href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></p>' );
					}
					return;
				},
			),
		),
	),

	// Start: Post Tag Controls.
	'bgtfw_posts_tags_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_posts_tags_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'tooltip' => __( 'Toggle the display of your tags on the blog roll and archive pages.', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'block',
		'choices' => array(
			'block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.single .entry-footer .tags-links',
				'property' => 'display',
			),
		),
	),

	// Start: Posts Tags Links Color Controls.
	'bgtfw_posts_tags_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_tags_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_posts_tags_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_posts_tags_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.single .entry-footer .tags-links a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_tags_link_decoration' => array(
		'settings'    => 'bgtfw_posts_tags_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_tags_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_posts_tags_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_tags_decoration_hover' => array(
		'settings'    => 'bgtfw_posts_tags_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	// Start Tag Icons.
	'bgtfw_posts_tags_icon_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_posts_tags_icon_display',
		'label' => esc_attr__( 'Icon Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'inline-block',
		'choices' => array(
			'inline-block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inline-block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.single .entry-footer .tags-links .fa',
				'property' => 'display',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_posts_tag_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_tag_icon',
		'label' => esc_attr__( 'Single Tag Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'hashtag',
		'js_vars' => array(
			array(
				'element' => '.single .tags-links.singular .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_tags_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_posts_tags_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_tags_icon',
		'label' => esc_attr__( 'Multiple Tags Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_tags_links',
		'default' => 'hashtag',
		'js_vars' => array(
			array(
				'element' => '.single .tags-links.multiple .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_tags_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),

	'bgtfw_posts_cats_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_posts_cats_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'block',
		'choices' => array(
			'block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.single .entry-footer .cat-links',
				'property' => 'display',
			),
		),
	),

	// Start: Posts Category Links Color Controls.
	'bgtfw_posts_cats_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_cats_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_posts_cats_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_posts_cats_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.single .entry-footer .cat-links a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_cats_link_decoration' => array(
		'settings'    => 'bgtfw_posts_cats_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_cats_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_posts_cats_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_cats_decoration_hover' => array(
		'settings'    => 'bgtfw_posts_cats_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	// Start: Category Icons.
	'bgtfw_posts_cats_icon_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_posts_cats_icon_display',
		'label' => esc_attr__( 'Icon Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'inline-block',
		'choices' => array(
			'inline-block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inline-block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.single .entry-footer .cat-links .fa',
				'property' => 'display',
			),
		),
	),
	'bgtfw_posts_cat_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_cat_icon',
		'label' => esc_attr__( 'Single Category Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'folder',
		'js_vars' => array(
			array(
				'element' => '.single .cat-links.singular .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_cats_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_posts_cats_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_cats_icon',
		'label' => esc_attr__( 'Multiple Categories Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_cat_links',
		'default' => 'folder-open',
		'js_vars' => array(
			array(
				'element' => '.single .cat-links.multiple .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_cats_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),

	'bgtfw_posts_navigation_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_posts_navigation_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_navigation_links',
		'default' => 'flex',
		'choices' => array(
			'flex' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'flex', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.single .post-navigation',
				'property' => 'display',
			),
		),
	),

	// Start: Posts Navigation Link Color Controls.
	'bgtfw_posts_navigation_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_posts_navigation_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_navigation_links',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_navigation_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_posts_navigation_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_posts_navigation_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_navigation_links',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.single .post-navigation a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_navigation_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_navigation_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_navigation_link_decoration' => array(
		'settings'    => 'bgtfw_posts_navigation_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_posts_navigation_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_navigation_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_navigation_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_navigation_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_posts_navigation_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_navigation_links',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_navigation_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_navigation_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_posts_navigation_decoration_hover' => array(
		'settings'    => 'bgtfw_posts_navigation_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_posts_navigation_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_posts_navigation_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_posts_navigation_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	'bgtfw_headings_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_headings_color',
		'label'       => esc_attr__( 'Color', 'crio' ),
		'section'     => 'headings_typography',
		'priority'    => 10,
		'default'     => '',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_headings_typography' => array(
		'type'     => 'typography',
		'settings'  => 'bgtfw_headings_typography',
		'transport'   => 'auto',
		'settings'    => 'bgtfw_headings_typography',
		'label'       => esc_attr__( 'Headings Typography', 'crio' ),
		'section'     => 'headings_typography',
		'default'     => array(
			'font-family'    => 'Roboto',
			'variant'        => 'regular',
			'line-height'    => '1.5',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none',
		),
		'priority'    => 20,
		'output'      => $bgtfw_typography->get_output_values( $bgtfw_configs ),
	),
	'bgtfw_headings_font_size' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_headings_font_size',
		'label'       => esc_attr__( 'Font Size', 'crio' ),
		'section'     => 'headings_typography',
		'default'     => '14',
		'choices'     => array(
			'min'  => '6',
			'max'  => '42',
			'step' => '1',
		),
		'priority'    => 30,
	),
	'bgtfw_tagline_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_tagline_color',
		'label'       => esc_attr__( 'Color', 'crio' ),
		'section'     => 'bgtfw_tagline',
		'priority'    => 10,
		'default'     => '',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_tagline_typography' => array(
		'type'        => 'typography',
		'transport'   => 'auto',
		'settings'    => 'bgtfw_tagline_typography',
		'label'       => esc_attr__( 'Typography', 'crio' ),
		'section'     => 'bgtfw_tagline',
		'default'     => array(
			'font-family'    => 'Roboto',
			'variant'        => 'regular',
			'font-size'      => '42px',
			'line-height'    => '1.5',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none',
			'text-align'     => 'left',
		),
		'priority'    => 20,
		'output'      => array(
			array(
				'element' => '.site-branding .site-description, .bgc-tagline',
			),
		),
	),
	'bgtfw_header_preset_branding' => array(
		'type'        => 'multicheck',
		'settings'    => 'bgtfw_header_preset_branding',
		'description' => $bgtfw_presets->get_branding_notices(),
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Branding Display', 'crio' ),
		'section'     => 'bgtfw_header_presets',
		'default'     => array( 'logo' ),
		'priority'    => 1,
		'choices'     => [
			'logo'        => esc_html__( 'Logo', 'crio' ),
			'title'       => esc_html__( 'Site Title', 'crio' ),
			'description' => esc_html__( 'Tagline', 'crio' ),
		],
		'active_callback' => array(
			array(
				'setting'  => 'bgtfw_header_preset',
				'operator' => '!=',
				'value'    => 'default',
			),
			array(
				'setting'  => 'bgtfw_header_preset',
				'operator' => '!=',
				'value'    => 'custom',
			),
		),
	),
	'bgtfw_header_preset' => array(
		'type'        => 'radio-image',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_header_preset',
		'label'       => esc_html__( 'Header Layout', 'crio' ),
		'section'     => 'bgtfw_header_presets',
		'default'     => 'default',
		'priority'    => 2,
		'choices'     => $bgtfw_presets->get_preset_choices( 'header' ),
	),
	'bgtfw_header_width' => array(
		'type'        => 'slider',
		'settings'    => 'bgtfw_header_width',
		'transport'   => 'auto',
		'label'       => esc_attr__( 'Header Width', 'crio' ),
		'section'     => 'bgtfw_header_layout_advanced',
		'default'     => 400,
		'choices'     => array(
			'min'  => '0',
			'max'  => '600',
			'step' => '1',
		),
		'active_callback' => array(
			array(
				'setting'  => 'bgtfw_header_layout_position',
				'operator' => '!=',
				'value'    => 'header-top',
			),
		),
		'output' => array(
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => '.flexbox .header-left .site-header, .flexbox .header-right .site-header',
				'property' => 'flex',
				'value_pattern' => '0 0 $px',
			),
			array(
				'media_query' => '@media only screen and (max-width : 968px)',
				'element'  => '.flexbox .header-left .site-content, .flexbox .header-right .site-content',
				'property' => 'flex',
				'value_pattern' => '1 0 calc(100% - $px)',
			),
			array(
				'media_query' => '@media only screen and (min-width: 992px)',
				'element'  => '.flexbox .header-left.has-sidebar .main, .flexbox .header-right.has-sidebar .main',
				'property' => 'width',
				'value_pattern' => 'calc((100% * (2/3)) - $px + 1em)',
			),
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => ' .flexbox .header-left.header-fixed .site-footer, .flexbox .header-right.header-fixed .site-footer',
				'property' => 'width',
				'value_pattern' => 'calc(100% - $px)',
			),
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => '.flexbox .header-left .site-content, .flexbox .header-left.header-fixed .site-footer, .flexbox .header-right .site-content, .flexbox .header-right.header-fixed .site-footer',
				'property' => 'width',
				'value_pattern' => 'calc(100% - $px)',
			),
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => '.flexbox .header-right.header-fixed .site-header, .flexbox .header-left.header-fixed .site-header, .header-right .wp-custom-header, .header-left .wp-custom-header, .header-right .site-header, .header-left .site-header, .header-left #masthead, .header-right #masthead',
				'property' => 'width',
				'value_pattern' => '$px',
			),
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => '.header-left #navi-wrap, .header-right #navi-wrap',
				'property' => 'max-width',
				'value_pattern' => '$px',
			),
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => '.flexbox .header-right.header-fixed .site-footer, .flexbox .header-right.header-fixed .site-content',
				'property' => 'margin-right',
				'value_pattern' => '$px',
			),
			array(
				'media_query' => '@media only screen and (min-width : 768px)',
				'element'  => '.flexbox .header-left.header-fixed .site-footer, .flexbox .header-left.header-fixed .site-content',
				'property' => 'margin-left',
				'value_pattern' => '$px',
			),
		),
	),
	'bgtfw_header_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_header_color',
		'label' => esc_attr__( 'Background Color', 'crio' ),
		'section'     => 'header_image',
		'priority' => 1,
		'default'     => '',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_site_title_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_site_title_color',
		'label' => esc_attr__( 'Color', 'crio' ),
		'section'     => 'bgtfw_site_title',
		'priority' => 10,
		'default'     => '',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_site_title_typography' => array(
		'type'        => 'typography',
		'transport'   => 'auto',
		'settings'    => 'bgtfw_site_title_typography',
		'label'       => esc_attr__( 'Typography', 'crio' ),
		'section'     => 'bgtfw_site_title',
		'default'     => array(
			'font-family'    => 'Roboto',
			'variant'        => 'regular',
			'font-size'      => '42px',
			'line-height'    => '1.5',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none',
			'text-align'     => 'left',
		),
		'priority'    => 20,
		'output'      => array(
			array(
				'element' => '.' . get_theme_mod( 'boldgrid_palette_class', 'palette-primary' ) . '.site-header .site-title > a, .' . get_theme_mod( 'boldgrid_palette_class', 'palette-primary' ) . ' .site-header .site-title > a,.' . get_theme_mod( 'boldgrid_palette_class', 'palette-primary' ) . ' .site-header .site-title > a:hover, .bgc-site-title, .bgc-site-title:hover',
			),
		),
	),
	'bgtfw_body_typography' => array(
		'type'        => 'typography',
		'transport'   => 'auto',
		'settings'    => 'bgtfw_body_typography',
		'label'       => esc_attr__( 'Typography', 'crio' ),
		'section'     => 'body_typography',
		'default'     => array(
			'font-family'    => 'Roboto',
			'variant'        => '100',
			'font-size'      => '18px',
			'line-height'    => '1.4',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'none',
		),
		'priority'    => 10,
		'output'      => array(
			array(
				'element' => '.widget, .site-content, .attribution-theme-mods-wrapper, .gutenberg .edit-post-visual-editor, .mce-content-body, .template-header',
			),
		),
	),

	/* Start Global Page Title Control */
	'bgtfw_global_title_background_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_global_title_background_color',
		'label' => esc_attr__( 'Background Color', 'crio' ),
		'tooltip' => esc_attr__( 'Choose a color from your palette to use.', 'crio' ),
		'section'     => 'bgtfw_global_page_titles',
		'priority' => 1,
		'default'     => '',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_global_title_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_global_title_color',
		'label' => esc_attr__( 'Title Color', 'crio' ),
		'section'     => 'bgtfw_global_page_titles',
		'default'     => '',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_global_title_size' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_global_title_size',
		'label' => esc_attr__( 'Title Font Size', 'crio' ),
		'section'     => 'bgtfw_global_page_titles',
		'default'     => 'h2',
		'choices'     => array(
			'h1'   => esc_attr__( 'H1', 'crio' ),
			'h2' => esc_attr__( 'H2', 'crio' ),
			'h3'  => esc_attr__( 'H3', 'crio' ),
			'h4'   => esc_attr__( 'H4', 'crio' ),
			'h5' => esc_attr__( 'H5', 'crio' ),
			'h6'  => esc_attr__( 'H6', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.page-header .entry-title, .page-header .page-title',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'entry-title $',
			),
		),
	),
	'bgtfw_global_title_alignment' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'auto',
		'settings'    => 'bgtfw_global_title_alignment',
		'label' => esc_attr__( 'Text Position', 'crio' ),
		'section'     => 'bgtfw_global_page_titles',
		'default'     => 'left',
		'choices'     => array(
			'left'   => '<span class="dashicons dashicons-editor-alignleft"></span>' . esc_attr__( 'Left', 'crio' ),
			'center' => '<span class="dashicons dashicons-editor-aligncenter"></span>' . esc_attr__( 'Center', 'crio' ),
			'right'  => '<span class="dashicons dashicons-editor-alignright"></span>' . esc_attr__( 'Right', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'left', 'center', 'right' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.page-header',
				'property' => 'text-align',
			),
		),
	),
	'bgtfw_global_title_position' => array(
		'type' => 'radio-buttonset',
		'transport' => 'refresh',
		'settings' => 'bgtfw_global_title_position',
		'label' => esc_attr__( 'Position', 'crio' ),
		'tooltip' => __( 'Change where your page titles appear on your site.', 'crio' ),
		'section' => 'bgtfw_global_page_titles',
		'default' => 'above',
		'choices' => array(
			'above' => '<span class="dashicons dashicons-arrow-up-alt"></span>' . __( 'Above Content', 'crio' ),
			'content' => '<span class="dashicons dashicons-format-aside"></span>' . __( 'In Content', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'above', 'content' ], true ) ? $value : $settings->default;
		},
	),
	'bgtfw_global_title_background_container' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_global_title_background_container',
		'label' => esc_attr__( 'Background Container', 'crio' ),
		'tooltip' => __( 'Change where your page titles appear on your site.', 'crio' ),
		'section' => 'bgtfw_global_page_titles',
		'default'   => 'full-width',
		'choices'     => array(
			'container' => '<span class="icon-layout-container"></span>' . esc_attr__( 'Contained', 'crio' ),
			'full-width' => '<span class="icon-layout-full-screen"></span>' . esc_attr__( 'Full Width', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return 'container' === $value || 'full-width' === $value ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.page-title-above .page-header-wrapper',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'page-header-wrapper $',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_global_title_position',
				'operator' => '!==',
				'value'    => 'content',
			),
		),
	),
	'bgtfw_global_title_content_container' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_global_title_content_container',
		'label' => esc_attr__( 'Content Container', 'crio' ),
		'tooltip' => __( 'Set the page title content to be displayed in a container or full width of the page.', 'crio' ),
		'section' => 'bgtfw_global_page_titles',
		'default'   => 'container',
		'choices'     => array(
			'container' => '<span class="icon-layout-container"></span>' . esc_attr__( 'Contained', 'crio' ),
			'' => '<span class="icon-layout-full-screen"></span>' . esc_attr__( 'Full Width', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return 'container' === $value || '' === $value ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.page-title-above .page-header .featured-imgage-header',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'featured-imgage-header $',
			),
		),
		'active_callback'  => array(
			array(
				'setting'  => 'bgtfw_global_title_position',
				'operator' => '!==',
				'value'    => 'content',
			),
			array(
				'setting'  => 'bgtfw_global_title_background_container',
				'operator' => '!==',
				'value'    => 'container',
			),
		),
	),

	/* Start Link Design */
	'bgtfw_body_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_body_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section'    => 'bgtfw_body_link_design',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => Boldgrid_Framework_Links::$default_link_selectors,
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_body_link_decoration' => array(
		'settings'    => 'bgtfw_body_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section'     => 'bgtfw_body_link_design',
		'default' => 'underline',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
	),
	'bgtfw_body_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_body_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section'     => 'bgtfw_body_link_design',
		'default'     => 0,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
	),
	'bgtfw_body_link_decoration_hover' => array(
		'settings'    => 'bgtfw_body_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section'     => 'bgtfw_body_link_design',
		'default' => 'underline',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
	),
	/* End Link Design */

	/* Start: Scroll To Top Settings. */
	'bgtfw_scroll_to_top_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_scroll_to_top_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'tooltip' => __( 'Toggle the display of the scroll to top button on your site.', 'crio' ),
		'section' => 'bgtfw_scroll_to_top',
		'default' => 'show',
		'choices' => array(
			'show' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'hide' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'show', 'hide' ], true ) ? $value : $settings->default;
		},
	),
	/* End: Scroll To Top Settings. */

	'boldgrid_contact_details_setting' => array(
		'type'        => 'repeater',
		'label'       => esc_attr__( 'Contact Details', 'crio' ),
		'section'     => 'boldgrid_footer_panel',
		'priority'    => 10,
		'row_label' => array(
			'field' => 'contact_block',
			'type' => 'field',
			'value' => esc_attr__( 'Contact Block', 'crio' ),
		),
		'settings'    => 'boldgrid_contact_details_setting',
		'default'     => array(
			array(
				'contact_block' => '&copy; ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ),
			),
			array(
				'contact_block' => esc_attr( '202 Grid Blvd. Agloe, NY 12776' ),
			),
			array(
				'contact_block' => esc_attr( '777-765-4321' ),
			),
			array(
				'contact_block' => esc_attr( 'info@example.com' ),
			),
		),
		'fields' => array(
			'contact_block' => array(
				'type'        => 'text',
				'label'       => esc_attr__( 'Text', 'crio' ),
				'description' => esc_attr__( 'Enter the text to display in your contact details', 'crio' ),
				'default'     => '',
			),
		),
	),

	'bgtfw_video_background_all' => array(
		'type'        => 'switch',
		'settings'    => 'bgtfw_video_background_all',
		'transport'   => 'refresh',
		'label'       => '',
		'description' => esc_attr__( 'By default, the header video will only display on the home page. If you want the video to display on all pages, disable this option.', 'crio' ),
		'section'     => 'header_image',
		'default'     => true,
		'priority'    => 11,
		'choices'     => array(
			'on'  => esc_attr__( 'Home Only', 'crio' ),
			'off' => esc_attr__( 'All Pages', 'crio' ),
		),
	),

	// Header overlay begin.
	'bgtfw_header_overlay' => array(
		'type'        => 'switch',
		'settings'    => 'bgtfw_header_overlay',
		'transport'   => 'postMessage',
		'label'       => __( 'Header Overlay', 'crio' ),
		'description' => esc_attr__( 'Add an overlay to give your text readability over an image or video.', 'crio' ),
		'section'     => 'header_image',
		'default'     => false,
		'priority'    => 20,
		'choices'     => array(
			'on'  => esc_attr__( 'Enable', 'crio' ),
			'off' => esc_attr__( 'Disable', 'crio' ),
		),
	),
	'bgtfw_header_overlay_color' => array(
		'type'              => 'bgtfw-palette-selector',
		'transport'         => 'postMessage',
		'settings'          => 'bgtfw_header_overlay_color',
		'label'             => esc_attr__( 'Overlay Color', 'crio' ),
		'section'           => 'header_image',
		'priority'          => 25,
		'default'           => 'color-1',
		'choices'           => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_header_overlay_alpha' => array(
		'type'      => 'slider',
		'transport' => 'postMessage',
		'settings'  => 'bgtfw_header_overlay_alpha',
		'label'     => esc_attr__( 'Overlay Opacity', 'crio' ),
		'section'   => 'header_image',
		'priority'  => 30,
		'default'   => '0.70',
		'choices'   => array(
			'min'  => '0',
			'max'  => '1',
			'step' => '.01',
		),
	),
	// Header overlay end.
	'bgtfw_footer_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_footer_color',
		'label' => esc_attr__( 'Background Color', 'crio' ),
		'description' => esc_attr__( 'Choose a color from your palette to use.', 'crio' ),
		'section'     => 'bgtfw_footer_colors',
		'priority' => 10,
		'default'     => '',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_footer_links' => array(
		'type' => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_footer_links',
		'label' => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_footer_colors',
		'priority' => 30,
		'default' => '',
		'choices' => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_pages_blog_blog_page_layout_columns' => array(
		'label'       => __( 'Columns', 'crio' ),
		'tooltip' => __( 'Select the number of columns you wish to display on your blog page.', 'crio' ),
		'type'        => 'number',
		'settings'    => 'bgtfw_pages_blog_blog_page_layout_columns',
		'priority'    => 1,
		'default'     => 1,
		'transport'   => 'postMessage',
		'choices'     => array(
			'min'  => 1,
			'max'  => 6,
			'step' => 1,
		),
		'section'     => 'bgtfw_pages_blog_blog_page_post_content',
		'sanitize_callback' => function( $value, $setting ) {
			return is_int( $value ) && 6 <= absint( $value ) ? absint( $value ) : $setting->default;
		},
	),
	'bgtfw_blog_page_container' => array(
		'settings' => 'bgtfw_blog_page_container',
		'transport'   => 'refresh',
		'label'       => esc_html__( 'Container', 'crio' ),
		'type'        => 'radio-buttonset',
		'priority'    => 35,
		'default'   => 'container',
		'choices'     => array(
			'container' => '<span class="icon-layout-container"></span>' . esc_attr__( 'Contained', 'crio' ),
			'' => '<span class="icon-layout-full-screen"></span>' . esc_attr__( 'Full Width', 'crio' ),
		),
		'section' => 'bgtfw_pages_blog_blog_page_post_content',
		'sanitize_callback' => function( $value, $settings ) {
			return 'container' === $value || '' === $value ? $value : $settings->default;
		},
	),
	'bgtfw_pages_blog_blog_page_layout_posts_per_page' => array(
		'label'       => __( 'Blog Posts Per Page', 'crio' ),
		'tooltip' => __( 'Set how many posts display per page for your blog, categories, archives, and search pages.', 'crio' ),
		'type'        => 'number',
		'settings'    => 'posts_per_page',
		'option_type' => 'option',
		'default'     => 10,
		'transport'   => 'refresh',
		'choices'     => array(
			'min'  => 1,
			'max'  => 300,
			'step' => 1,
		),
		'section' => 'bgtfw_pages_blog_blog_page_post_content',
	),
	'bgtfw_pages_blog_blog_page_layout_content' => array(
		'type'        => 'radio-buttonset',
		'settings' => 'bgtfw_pages_blog_blog_page_layout_content',
		'transport' => 'refresh',
		'label'       => esc_html__( 'Post Content Display', 'crio' ),
		'tooltip' => __( 'Choose between automatically generated post excerpts or displaying your posts\' full content.', 'crio' ),
		'default'   => 'excerpt',
		'choices'     => array(
			'excerpt' => esc_attr__( 'Post Excerpt', 'crio' ),
			'content' => esc_attr__( 'Full Content', 'crio' ),
		),
		'section' => 'bgtfw_pages_blog_blog_page_post_content',
		'sanitize_callback' => function( $value, $settings ) {
			return 'excerpt' === $value || 'content' === $value ? $value : $settings->default;
		},
	),
	'bgtfw_pages_blog_blog_page_layout_excerpt_length' => array(
		'label'       => __( 'Excerpt Length', 'crio' ),
		'tooltip' => __( 'Set the length of excerpts used for your blog, categories, archives, and search pages.', 'crio' ),
		'type'        => 'number',
		'settings'    => 'bgtfw_pages_blog_blog_page_layout_excerpt_length',
		'default'     => 55,
		'transport'   => 'refresh',
		'choices'     => array(
			'min'  => 1,
			'max'  => 300,
			'step' => 1,
		),
		'section' => 'bgtfw_pages_blog_blog_page_post_content',
		'active_callback' => array(
			array(
				'setting'  => 'bgtfw_pages_blog_blog_page_layout_content',
				'operator' => '===',
				'value'    => 'excerpt',
			),
		),
	),
	'bgtfw_blog_posts_container' => array(
		'settings' => 'bgtfw_blog_posts_container',
		'transport'   => 'refresh',
		'label'       => esc_html__( 'Container', 'crio' ),
		'tooltip' => __( 'Choose if you would like your content wrapped in a container or cover the full width of the page.', 'crio' ),
		'type'        => 'radio-buttonset',
		'priority'    => 40,
		'default'   => 'container',
		'choices'     => array(
			'container' => '<span class="icon-layout-container"></span>' . esc_attr__( 'Contained', 'crio' ),
			'' => '<span class="icon-layout-full-screen"></span>' . esc_attr__( 'Full Width', 'crio' ),
		),
		'section' => 'bgtfw_pages_blog_posts_container',
		'sanitize_callback' => function( $value, $settings ) {
			return 'container' === $value || '' === $value ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.single-post .main-wrapper',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'main-wrapper $',
			),
		),
	),
	'bgtfw_layout_blog' => array(
		'settings' => 'bgtfw_layout_blog',
		'label'       => esc_html__( 'Sidebar Display', 'crio' ),
		'type'        => 'radio',
		'priority'    => 10,
		'default'   => 'no-sidebar',
		'choices'     => array(),
		'section'     => 'bgtfw_pages_blog_posts_sidebar',
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_blog_blog_page_settings' => array(
		'settings' => 'bgtfw_blog_blog_page_settings',
		'label'       => esc_html__( 'Homepage Sidebar Display', 'crio' ),
		'type'        => 'radio',
		'priority'    => 10,
		'default'   => 'no-sidebar',
		'choices'     => array(),
		'section'     => 'bgtfw_blog_blog_page_settings',
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_blog_blog_page_sidebar' => array(
		'settings' => 'bgtfw_blog_blog_page_sidebar',
		'label'       => esc_html__( 'Homepage Sidebar Display', 'crio' ),
		'type'        => 'radio',
		'priority'    => 30,
		'default'   => '',
		'choices'     => array(),
		'section'     => 'static_front_page',
		'active_callback' => function() {
			return get_option( 'show_on_front', 'posts' ) === 'posts' ? true : false;
		},
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_blog_blog_page_sidebar2' => array(
		'setting' => 'bgtfw_blog_blog_page_sidebar2',
		'settings'    => 'bgtfw_blog_blog_page_sidebar',
		'label'       => esc_html__( 'Sidebar Options', 'crio' ),
		'type'        => 'radio',
		'priority'    => 10,
		'default'   => '',
		'choices'     => array(),
		'section'     => 'bgtfw_blog_blog_page_panel_sidebar',
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_layout_blog_layout' => array(
		'settings' => 'bgtfw_layout_blog_layout',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Homepage Blog Layout', 'crio' ),
		'type'        => 'radio',
		'priority'    => 40,
		'default'   => 'layout-1',
		'choices'     => array(
			'layout-1' => esc_attr__( 'Layout 1', 'crio' ),
			'layout-2' => esc_attr__( 'Layout 2', 'crio' ),
			'layout-3' => esc_attr__( 'Layout 3', 'crio' ),
			'layout-4' => esc_attr__( 'Layout 4', 'crio' ),
			'layout-5' => esc_attr__( 'Layout 5', 'crio' ),
			'layout-6' => esc_attr__( 'Layout 6', 'crio' ),
		),
		'section' => 'static_front_page',
		'active_callback' => function() {
			return get_option( 'show_on_front', 'posts' ) === 'posts' ? true : false;
		},
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_layout_blog_layout' => array(
		'settings' => 'bgtfw_layout_blog_layout',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Layout', 'crio' ),
		'type'        => 'radio',
		'priority'    => 40,
		'default' => 'layout-1',
		'choices'     => array(
			'layout-1' => esc_attr__( 'Layout 1', 'crio' ),
			'layout-2' => esc_attr__( 'Layout 2', 'crio' ),
			'layout-3' => esc_attr__( 'Layout 3', 'crio' ),
			'layout-4' => esc_attr__( 'Layout 4', 'crio' ),
			'layout-5' => esc_attr__( 'Layout 5', 'crio' ),
			'layout-6' => esc_attr__( 'Layout 6', 'crio' ),
		),
		'section' => 'bgtfw_layout_blog',
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_footer_layout' => [
		'settings' => 'bgtfw_footer_layout',
		'transport' => 'auto',
		'label' => __( 'Footer Layout', 'crio' ),
		'type' => 'bgtfw-sortable-accordion',
		'default' => [
			[
				'container' => 'container',
				'items' => [
					[
						'type' => 'boldgrid_display_attribution_links',
						'key' => 'attribution',
						'align' => 'w',
					],
					[
						'type' => 'boldgrid_menu_social',
						'key' => 'menu',
						'align' => 'e',
					],
				],
			],
		],
		'items' => [
			'menu' => [
				'icon' => 'dashicons dashicons-menu',
				'title' => __( 'Menu', 'crio' ),
				'controls' => [
					'menu-select' => [],
					'align' => [
						'default' => 'nw',
					],
				],
			],
			'branding' => [
				'icon' => 'dashicons dashicons-store',
				'title' => __( 'Branding', 'crio' ),
				'controls' => [
					'align' => [
						'default' => 'nw',
					],
					'display' => [
						'default' => [
							[
								'selector' => '.custom-logo-link',
								'display' => 'show',
								'title' => __( 'Logo', 'crio' ),
							],
							[
								'selector' => '.site-title',
								'display' => 'show',
								'title' => __( 'Title', 'crio' ),
							],
							[
								'selector' => '.site-description',
								'display' => 'show',
								'title' => __( 'Tagline', 'crio' ),
							],
						],
					],
				],
			],
			'sidebar' => [
				'icon' => 'dashicons dashicons-layout',
				'title' => __( 'Widget Area', 'crio' ),
				'controls' => [
					'sidebar-edit' => [],
				],
			],
			'attribution' => [
				'icon' => 'dashicons dashicons-admin-links',
				'title' => __( 'Attribution Links', 'crio' ),
				'controls' => [
					'attribution' => [],
					'align' => [
						'default' => 'w',
					],
				],
			],
		],
		'location' => 'footer',
		'section' => 'boldgrid_footer_panel',
		'partial_refresh' => [
			'bgtfw_footer_layout' => [
				'selector' => '.bgtfw-footer',
				'render_callback' => [ 'BoldGrid', 'dynamic_footer' ],
			],
		],
	],
	'bgtfw_header_layout_col_width' => array(
		'settings' => 'bgtfw_header_layout_col_width',
		'transport' => 'refresh',
		'label' => __( 'Header Column Widths', 'crio' ),
		'priority' => 8,
		'section' => 'bgtfw_header_layout',
		'type' => 'kirki-generic',
		'default' => $bgtfw_generic->get_column_defaults(),
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'ColWidth',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control'    => array(
					'selectors' => array( '.site-header header row' ),
					'sliders' => $bgtfw_generic->get_header_columns(),
					'description' => __(
						'Headers have a maximum of 12 columns per row. If the total columns used by the items in a row exceed 12, they will be rolled over to a new row.',
						'crio'
					),
				),
				'slider' => array(
					'col' => array(
						'min'   => 1,
						'max'   => 12,
						'step'  => 1,
						'value' => 6,
					),
				),
			),
		),
	),
	'bgtfw_header_layout_position' => array(
		'settings' => 'bgtfw_header_layout_position',
		'transport' => 'postMessage',
		'label' => __( 'Header Position', 'crio' ),
		'type' => 'radio-buttonset',
		'priority' => 5,
		'default' => 'header-top',
		'choices' => array(
			'header-left' => '<span class="icon-advanced-layout-left"></span>' . esc_html__( 'Left', 'crio' ),
			'header-top' => '<span class="icon-advanced-layout-top"></span>' . esc_html__( 'Top', 'crio' ),
			'header-right' => '<span class="icon-advanced-layout-right"></span>' . esc_html__( 'Right', 'crio' ),
		),
		'section' => 'bgtfw_header_layout_advanced',
		'sanitize_callback' => 'sanitize_html_class',
	),
	'bgtfw_header_layout' => [
		'settings' => 'bgtfw_header_layout',
		'label' => '<div class="screen-reader-text">' . __( 'Standard Header Layout', 'crio' ) . '</div>',
		'type' => 'bgtfw-sortable-accordion',
		'default' => [
			[
				'container' => 'container',
				'items' => [
					[
						'type' => 'boldgrid_site_identity',
						'key' => 'branding',
						'align' => 'w',
						'uid' => 'h47',
						'display' => [
							[
								'selector' => '.custom-logo-link',
								'display' => 'show',
								'title' => __( 'Logo', 'crio' ),
							],
							[
								'selector' => '.site-title',
								'display' => 'show',
								'title' => __( 'Title', 'crio' ),
							],
							[
								'selector' => '.site-description',
								'display' => 'show',
								'title' => __( 'Tagline', 'crio' ),
							],
						],
					],
					[
						'type' => 'boldgrid_menu_main',
						'key' => 'menu',
						'align' => 'e',
						'uid' => 'h48',
					],
				],
			],
		],
		'items' => [
			'menu' => [
				'icon' => 'dashicons dashicons-menu',
				'title' => __( 'Menu', 'crio' ),
				'controls' => [
					'menu-select' => [],
					'align' => [
						'default' => 'nw',
					],
				],
			],
			'branding' => [
				'icon' => 'dashicons dashicons-store',
				'title' => __( 'Branding', 'crio' ),
				'controls' => [
					'display' => [
						'default' => [
							[
								'selector' => '.custom-logo-link',
								'display' => 'show',
								'title' => __( 'Logo', 'crio' ),
							],
							[
								'selector' => '.site-title',
								'display' => 'show',
								'title' => __( 'Title', 'crio' ),
							],
							[
								'selector' => '.site-description',
								'display' => 'show',
								'title' => __( 'Tagline', 'crio' ),
							],
						],
					],
					'align' => [
						'default' => 'nw',
					],
				],
			],
			'sidebar' => [
				'icon' => 'dashicons dashicons-layout',
				'title' => __( 'Widget Area', 'crio' ),
				'controls' => [
					'sidebar-edit' => [],
				],
			],
		],
		'location' => 'header',
		'section' => 'bgtfw_header_layout',
		'transport' => 'postMessage',
	],

	'bgtfw_header_layout_custom' => [
		'settings' => 'bgtfw_header_layout_custom',
		'transport' => 'postMessage',
		'label' => '<div class="screen-reader-text">' . __( 'Custom Header Layout', 'crio' ) . '</div>',
		'type' => 'bgtfw-sortable-accordion',
		'default' => $bgtfw_presets->get_custom_layout( 'header' ),
		'items' => [
			'menu' => [
				'icon' => 'dashicons dashicons-menu',
				'title' => __( 'Menu', 'crio' ),
				'controls' => [
					'menu-select' => [],
					'align' => [
						'default' => 'nw',
					],
				],
			],
			'branding' => [
				'icon' => 'dashicons dashicons-store',
				'title' => __( 'Branding', 'crio' ),
				'controls' => [
					'display' => [
						'default' => [
							[
								'selector' => '.custom-logo-link',
								'display' => 'show',
								'title' => __( 'Logo', 'crio' ),
							],
							[
								'selector' => '.site-title',
								'display' => 'show',
								'title' => __( 'Title', 'crio' ),
							],
							[
								'selector' => '.site-description',
								'display' => 'show',
								'title' => __( 'Tagline', 'crio' ),
							],
						],
					],
					'align' => [
						'default' => 'nw',
					],
				],
			],
			'sidebar' => [
				'icon' => 'dashicons dashicons-layout',
				'title' => __( 'Widget Area', 'crio' ),
				'controls' => [
					'sidebar-edit' => [],
				],
			],
		],
		'location' => 'header',
		'section' => 'bgtfw_header_layout_advanced',
	],

	/*** Start: Dynamic Menu Controls ***/
	'bgtfw_menu_hamburger_main_toggle' => array(
		'type' => 'switch',
		'settings' => 'bgtfw_menu_hamburger_main_toggle',
		'transport' => 'postMessage',
		'label' => esc_html__( 'Enable Hamburger Menu', 'crio' ),
		'section' => 'bgtfw_menu_hamburgers_main',
		'default' => true,
	),
	'bgtfw_menu_hamburger_main_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_hamburger_main_color',
		'label'       => esc_attr__( 'Primary Color', 'crio' ),
		'section'     => 'bgtfw_menu_hamburgers_main',
		'default'     => 'color-1',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_menu_hamburger_main' => array(
		'settings' => 'bgtfw_menu_hamburger_main',
		'transport' => 'postMessage',
		'label' => __( 'Hamburger Style', 'crio' ),
		'type' => 'bgtfw-menu-hamburgers',
		'default' => 'hamburger--collapse',
		'section' => 'bgtfw_menu_hamburgers_main',
		'sanitize_callback' => 'sanitize_html_class',
	),

	/* Start: Main Menu Background Controls */
	'bgtfw_menu_background_main' => array(
		'type'            => 'bgtfw-palette-selector',
		'transport'       => 'postMessage',
		'settings'        => 'bgtfw_menu_background_main',
		'label'           => esc_attr__( 'Background Color', 'crio' ),
		'section'         => 'bgtfw_menu_background_main',
		'default'         => 'transparent',
		'choices'         => array(
			'colors'      => $bgtfw_formatted_palette,
			'size'        => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette, true ),
			'transparent' => true,
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),

	/* Start: Main Menu Spacing Controls */
	'bgtfw_menu_margin_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_margin_main',
		'settings'    => 'bgtfw_menu_margin_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Margin',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu' ),
					'sliders' => array(
						array( 'name' => 'top', 'label' => 'Top', 'cssProperty' => 'margin-top' ),
						array( 'name' => 'right', 'label' => 'Right', 'cssProperty' => 'margin-right' ),
						array( 'name' => 'bottom', 'label' => 'Bottom', 'cssProperty' => 'margin-bottom' ),
						array( 'name' => 'left', 'label' => 'Left', 'cssProperty' => 'margin-left' ),
					),
				),
				'slider' => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'em' => array(
						'min' => 0,
						'max' => 5,
					),
				),
			),
		),
	),
	'bgtfw_menu_padding_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_padding_main',
		'settings'    => 'bgtfw_menu_padding_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Padding',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu' ),
					'sliders' => array(
						array( 'name' => 'top', 'label' => 'Top', 'cssProperty' => 'padding-top' ),
						array( 'name' => 'right', 'label' => 'Right', 'cssProperty' => 'padding-right' ),
						array( 'name' => 'bottom', 'label' => 'Bottom', 'cssProperty' => 'padding-bottom' ),
						array( 'name' => 'left', 'label' => 'Left', 'cssProperty' => 'padding-left' ),
					),
				),
				'slider' => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
					'em' => array(
						'min' => 0,
						'max' => 5,
					),
				),
			),
		),
	),
	/* End: Main Menu Spacing Controls */

	'bgtfw_menu_visibility_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_visibility_main',
		'settings'    => 'bgtfw_menu_visibility_main',
		'label'       => '',
		'default'     => [],
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'DeviceVisibility',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu' ),
				),
			),
		),
	),

	/* Start: Main Menu Border */
	'bgtfw_menu_border_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_border_main',
		'settings'    => 'bgtfw_menu_border_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Border',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu' ),
				),
			),
		),
	),
	'bgtfw_menu_border_color_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_border_color_main',
		'label'       => esc_attr__( 'Border Color', 'crio' ),
		'section'     => 'bgtfw_menu_border_main',
		'default'     => 'color-3',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_menu_border_radius_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_border_main',
		'settings'    => 'bgtfw_menu_border_radius_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BorderRadius',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu' ),
				),
			),
		),
	),

	/* End: Main Menu Border */
	'bgtfw_menu_items_border_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_items_border_main',
		'settings'    => 'bgtfw_menu_items_border_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Border',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu > li:not(.current-menu-item)' ),
				),
			),
		),
	),
	'bgtfw_menu_items_border_color_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_items_border_color_main',
		'label'       => esc_attr__( 'Primary Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_border_main',
		'default'     => 'color-3',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_menu_items_border_radius_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_items_border_main',
		'settings'    => 'bgtfw_menu_items_border_radius_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BorderRadius',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu > li:not(.current-menu-item)' ),
				),
			),
		),
	),
	'bgtfw_menu_items_spacing_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_items_spacing_main',
		'settings'    => 'bgtfw_menu_items_spacing_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Margin',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu > li' ),
					'sliders' => array(
						array( 'name' => 'top', 'label' => 'Top', 'cssProperty' => 'margin-top' ),
						array( 'name' => 'right', 'label' => 'Right', 'cssProperty' => 'margin-right' ),
						array( 'name' => 'bottom', 'label' => 'Bottom', 'cssProperty' => 'margin-bottom' ),
						array( 'name' => 'left', 'label' => 'Left', 'cssProperty' => 'margin-left' ),
					),
				),
			),
		),
	),
	'bgtfw_menu_items_hover_color_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_items_hover_color_main',
		'label'       => esc_attr__( 'Primary Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_hover_item_main',
		'default'     => 'color-4',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_menu_items_hover_background_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_items_hover_background_main',
		'label'       => esc_attr__( 'Secondary Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_hover_item_main',
		'default'     => 'color-3',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_menu_items_hover_effect_main' => array(
		'type'        => 'select',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_items_hover_effect_main',
		'label'       => esc_attr__( 'Hover Effect', 'crio' ),
		'section'     => 'bgtfw_menu_items_hover_item_main',
		'default'     => 'hvr-underline-reveal',
		'sanitize_callback' => 'esc_attr',
		'choices'     => array(

			/** No Effects */
			'' => esc_attr__( 'No Hover Effects', 'crio' ),

			/** Background Transitions */
			'optgroup1' => array(
				esc_attr__( 'Single Color Transitions', 'crio' ),
				array(
					/**
					 * Currently this pulses to default color in RGBA. Color doesn't look
					 * like it gets extracted out since it's happening in a transition.
					 *
					 * Disabling this for now.
					 *
					 * 'hvr-back-pulse' => esc_attr__( 'Back Pulse', 'bgtfw' ),
					 */
					'hvr-fade' => esc_attr__( 'Fade', 'crio' ),
					'hvr-sweep-to-right' => esc_attr__( 'Sweep to Right', 'crio' ),
					'hvr-sweep-to-left' => esc_attr__( 'Sweep to Left', 'crio' ),
				),
			),

			/** Two Color Background Transitions */
			'optgroup2' => array(
				esc_attr__( 'Two Color Transitions', 'crio' ),
				array(
					'hvr-rectangle-in' => esc_attr__( 'Rectangle In', 'crio' ),
					'hvr-rectangle-out' => esc_attr__( 'Rectangle Out', 'crio' ),
					'hvr-shutter-in-horizontal' => esc_attr__( 'Shutter In Horizontal', 'crio' ),
					'hvr-shutter-out-horizontal' => esc_attr__( 'Shutter Out Horizontal', 'crio' ),
				),
			),

			/** Border Effects */
			'optgroup3' => array(
				esc_attr__( 'Border Effects', 'crio' ),
				array(
					'hvr-trim' => esc_attr__( 'Trim', 'crio' ),
					'hvr-ripple-in' => esc_attr__( 'Ripple In', 'crio' ),
					'hvr-outline-out' => esc_attr__( 'Outline Out', 'crio' ),
				),
			),
			'optgroup4' => array(
				esc_attr__( 'Overline/Underline Effects', 'crio' ),
				array(
					'hvr-underline-from-center' => esc_attr__( 'Underline From Center', 'crio' ),
					'hvr-underline-reveal' => esc_attr__( 'Underline Reveal', 'crio' ),
					'hvr-overline-reveal' => esc_attr__( 'Overline Reveal', 'crio' ),
					'hvr-overline-from-center' => esc_attr__( 'Overline From Center', 'crio' ),
				),
			),
		),
	),

	'bgtfw_menu_items_link_color_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_menu_items_link_color_main',
		'label' => esc_attr__( 'Link Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_link_color_main',
		'priority' => 1,
		'default'     => 'color-1',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),

	'bgtfw_menu_items_active_link_color_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_menu_items_active_link_color_main',
		'label' => esc_attr__( 'Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_active_link_color_main',
		'priority' => 1,
		'default'  => 'color-4',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),

	'bgtfw_menu_items_active_link_background_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_menu_items_active_link_background_main',
		'label' => esc_attr__( 'Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_active_link_background_main',
		'priority' => 1,
		'default'  => 'transparent',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette, true ),
			'transparent' => true,
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),

	'bgtfw_menu_items_active_link_border_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_items_active_link_border_main',
		'settings'    => 'bgtfw_menu_items_active_link_border_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Border',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu > li.current-menu-item' ),
				),
			),
		),
	),
	'bgtfw_menu_items_active_link_border_color_main' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_menu_items_active_link_border_color_main',
		'label'       => esc_attr__( 'Primary Color', 'crio' ),
		'section'     => 'bgtfw_menu_items_active_link_border_main',
		'default'     => 'color-3',
		'choices'     => array(
			'colors'  => $bgtfw_formatted_palette,
			'size'    => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_menu_items_active_link_border_radius_main' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_menu_items_active_link_border_main',
		'settings'    => 'bgtfw_menu_items_active_link_border_radius_main',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BorderRadius',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '#main-menu > li.current-menu-item' ),
				),
			),
		),
	),

	/** Menu Typography */
	'bgtfw_menu_typography_main' => array(
		'type'     => 'typography',
		'transport'   => 'auto',
		'settings'    => 'bgtfw_menu_typography_main',
		'label'       => esc_attr__( 'Typography', 'crio' ),
		'section'     => 'bgtfw_menu_typography_main',
		'default'     => array(
			'font-family'    => 'Roboto',
			'variant'        => 'regular',
			'font-size'      => '18px',
			'line-height'    => '1.5',
			'letter-spacing' => '0',
			'subsets'        => array( 'latin-ext' ),
			'text-transform' => 'uppercase',
		),
		'priority'    => 20,
		'output'      => array(
			array(
				'element'  => '#main-menu li a, .mce-content-body .sm-clean',
			),
		),
	),

	/*** End: Dynamic Menu Controls ***/

	'bgtfw_blog_margin' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_blog_margin_section',
		'settings'    => 'bgtfw_blog_margin',
		'label'       => '',
		'default'     => [
			[
				'media' => [ 'base' ],
				'unit' => 'em',
				'isLinked' => true,
				'values' => [
					'bottom' => 0.5,
					'top' => 0.5,
				],
			],
		],
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Margin',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.palette-primary.archive .post, .palette-primary.blog .post' ),
					'sliders' => array(
						array( 'name' => 'top', 'label' => 'Top', 'cssProperty' => 'margin-top' ),
						array( 'name' => 'bottom', 'label' => 'Bottom', 'cssProperty' => 'margin-bottom' ),
					),
				),
			),
		),
	),
	'bgtfw_blog_padding' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_blog_padding_section',
		'settings'    => 'bgtfw_blog_padding',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Padding',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.palette-primary.archive .post, .palette-primary.blog .post' ),
				),
			),
		),
	),
	'bgtfw_blog_border' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_blog_border_section',
		'settings'    => 'bgtfw_blog_border',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'Border',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.palette-primary.archive .post, .palette-primary.blog .post' ),
				),
			),
		),
	),
	'bgtfw_blog_border_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_border_color',
		'label'       => esc_attr__( 'Border Color', 'crio' ),
		'section'     => 'bgtfw_blog_border_section',
		'priority'    => 20,
		'default'     => 'color-1',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_blog_shadow' => array(
		'type'        => 'kirki-generic',
		'transport'   => 'postMessage',
		'section'     => 'bgtfw_blog_shadow_section',
		'settings'    => 'bgtfw_blog_shadow',
		'label'       => '',
		'default'     => '',
		'sanitize_callback' => array( 'Boldgrid_Framework_Customizer_Generic', 'sanitize' ),
		'choices' => array(
			'name' => 'boldgrid_controls',
			'type' => 'BoxShadow',
			'settings' => array(
				'responsive' => Boldgrid_Framework_Customizer_Generic::$device_sizes,
				'control' => array(
					'selectors' => array( '.palette-primary.archive .post, .palette-primary.blog .post' ),
				),
			),
		),
	),
	'bgtfw_blog_header_background_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_blog_header_background_color',
		'label' => esc_attr__( 'Post Header', 'crio' ),
		'description' => esc_attr__( 'Choose a color from your palette to use.', 'crio' ),
		'section'     => 'bgtfw_blog_blog_page_colors',
		'priority' => 1,
		'default'     => 'color-neutral',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_blog_post_background_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_blog_post_background_color',
		'label' => esc_attr__( 'Post Content', 'crio' ),
		'description' => esc_attr__( 'Choose a color from your palette to use.', 'crio' ),
		'section'     => 'bgtfw_blog_blog_page_colors',
		'priority' => 1,
		'default'     => 'color-neutral',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
	),
	'bgtfw_blog_post_header_title_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_title_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_titles',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-header .entry-title, .archive .post .entry-header .entry-title',
				'property' => 'display',
			),
		),
	),
	'bgtfw_blog_post_header_title_color' => array(
		'type'        => 'bgtfw-palette-selector',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_blog_post_header_title_color',
		'label' => esc_attr__( 'Color', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_titles',
		'default'     => 'color-1',
		'choices'     => array(
			'colors' => $bgtfw_formatted_palette,
			'size' => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_title_display',
				'operator' => '===',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_title_size' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_blog_post_header_title_size',
		'label' => esc_attr__( 'Font Size', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_titles',
		'default'     => 'h2',
		'choices'     => array(
			'h1'   => esc_attr__( 'H1', 'crio' ),
			'h2' => esc_attr__( 'H2', 'crio' ),
			'h3'  => esc_attr__( 'H3', 'crio' ),
			'h4'   => esc_attr__( 'H4', 'crio' ),
			'h5' => esc_attr__( 'H5', 'crio' ),
			'h6'  => esc_attr__( 'H6', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.blog .post .entry-header .entry-title, .archive .post .entry-header .entry-title',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'entry-title $',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_title_display',
				'operator' => '===',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_title_position' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'auto',
		'settings'    => 'bgtfw_blog_post_header_title_position',
		'label' => esc_attr__( 'Position', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_titles',
		'default'     => 'left',
		'choices'     => array(
			'left'   => '<span class="dashicons dashicons-editor-alignleft"></span>' . esc_attr__( 'Left', 'crio' ),
			'center' => '<span class="dashicons dashicons-editor-aligncenter"></span>' . esc_attr__( 'Center', 'crio' ),
			'right'  => '<span class="dashicons dashicons-editor-alignright"></span>' . esc_attr__( 'Right', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'left', 'center', 'right' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-header .entry-title, .archive .post .entry-header .entry-title',
				'property' => 'text-align',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_title_display',
				'operator' => '===',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_meta_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_meta_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'inline-block',
		'choices' => array(
			'inline-block' => '<span class="dashicons dashicons-minus"></span>' . __( 'Inline', 'crio' ),
			'block' => '<span class="dashicons dashicons-menu"></span>' . __( 'New Lines', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inline-block', 'block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element' => '.blog .post .entry-header .entry-meta, .archive .post .entry-header .entry-meta',
				'property' => 'display',
			),
		),
	),
	'bgtfw_blog_post_header_meta_position' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'auto',
		'settings'    => 'bgtfw_blog_post_header_meta_position',
		'label' => esc_attr__( 'Position', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default'     => 'left',
		'choices'     => array(
			'left'   => '<span class="dashicons dashicons-editor-alignleft"></span>' . esc_attr__( 'Left', 'crio' ),
			'center' => '<span class="dashicons dashicons-editor-aligncenter"></span>' . esc_attr__( 'Center', 'crio' ),
			'right'  => '<span class="dashicons dashicons-editor-alignright"></span>' . esc_attr__( 'Right', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'left', 'center', 'right' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element' => '.blog .post .entry-header .entry-meta, .archive .post .entry-header .entry-meta',
				'property' => 'text-align',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_header_date_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_date_display',
		'label' => esc_attr__( 'Post Date Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element' => '.blog .post .entry-header .entry-meta .posted-on, .archive .post .entry-header .entry-meta .posted-on',
				'property' => 'display',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_header_meta_format' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'auto',
		'settings'    => 'bgtfw_blog_post_header_meta_format',
		'label' => esc_attr__( 'Date Format', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default'     => 'date',
		'choices'     => array(
			'timeago'   => '<i class="fa fa-cc" aria-hidden="true"></i>' . esc_attr__( 'Human Readable', 'crio' ),
			'date' => '<i class="fa fa-calendar" aria-hidden="true"></i>' . esc_attr__( 'Date', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'timeago', 'date' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),

	// Start: Date Link Color.
	'bgtfw_blog_post_header_date_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_header_date_link_color_display',
		'label' => esc_attr__( 'Date Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_header_date_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_blog_post_header_date_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section'    => 'bgtfw_pages_blog_blog_page_post_meta',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.blog .post .entry-header .entry-meta .posted-on a', '.archive .post .entry-header .entry-meta .posted-on a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_date_link_decoration' => array(
		'settings'    => 'bgtfw_blog_post_header_date_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_date_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_post_header_date_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_date_decoration_hover' => array(
		'settings'    => 'bgtfw_blog_post_header_date_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_date_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	'bgtfw_blog_post_header_byline_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_byline_display',
		'label' => esc_attr__( 'Author Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element' => '.blog .post .entry-header .entry-meta .byline, .archive .post .entry-header .entry-meta .byline',
				'property' => 'display',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),

	// Start: Blog Page Author Link Color Controls.
	'bgtfw_blog_post_header_byline_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_header_byline_link_color_display',
		'label' => esc_attr__( 'Author Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_header_byline_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_blog_post_header_byline_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section'    => 'bgtfw_pages_blog_blog_page_post_meta',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.blog .post .entry-header .entry-meta .byline a', '.archive .post .entry-header .entry-meta .byline a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_byline_link_decoration' => array(
		'settings'    => 'bgtfw_blog_post_header_byline_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_byline_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_post_header_byline_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_header_byline_decoration_hover' => array(
		'settings'    => 'bgtfw_blog_post_header_byline_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section'     => 'bgtfw_pages_blog_blog_page_post_meta',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_meta_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_byline_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	// Start: Blog Page Featured Image Controls.
	'bgtfw_blog_post_header_feat_image_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_feat_image_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'tooltip' => __( 'Hide or show your featured image on your blog roll and archive pages.', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_featured_images',
		'default' => 'show',
		'choices' => array(
			'show' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'hide' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'show', 'hide' ], true ) ? $value : $settings->default;
		},
	),
	'bgtfw_blog_post_header_feat_image_position' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_feat_image_position',
		'label' => esc_attr__( 'Position', 'crio' ),
		'tooltip' => __( 'Change where your featured image appears on your blog roll or archive pages.', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_featured_images',
		'default' => 'background',
		'choices' => array(
			'background' => '<span class="dashicons dashicons-format-image"></span>' . __( 'Header Background', 'crio' ),
			'above' => '<span class="dashicons dashicons-arrow-up-alt"></span>' . __( 'Above Header', 'crio' ),
			'below' => '<span class="dashicons dashicons-arrow-down-alt"></span>' . __( 'Below Header', 'crio' ),
			'content' => '<span class="dashicons dashicons-format-aside"></span>' . __( 'In Post Content', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'background', 'above', 'below', 'content' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_display',
				'operator' => '!==',
				'value'    => 'hide',
			),
		),
	),
	'bgtfw_blog_post_header_feat_image_height' => array(
		'type' => 'slider',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_feat_image_height',
		'label' => esc_attr__( 'Height', 'crio' ),
		'tooltip' => __( 'Change the height of the featured image container on your blog page and archive pages.', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_featured_images',
		'type'        => 'slider',
		'default'     => 9,
		'choices'     => array(
			'min'  => '0',
			'max'  => '30',
			'step' => '.1',
		),
		'output' => array(
			array(
				'units' => 'em',
				'element' => '.blog .post.has-post-thumbnail .entry-header.above .featured-imgage-header, .archive .post.has-post-thumbnail .entry-header.above .featured-imgage-header, .blog .post.has-post-thumbnail .entry-header.below .featured-imgage-header, .archive .post.has-post-thumbnail .entry-header.below .featured-imgage-header',
				'property' => 'height',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_display',
				'operator' => '!==',
				'value'    => 'hide',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_position',
				'operator' => '!==',
				'value'    => 'background',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_position',
				'operator' => '!==',
				'value'    => 'content',
			),
		),
	),
	'bgtfw_blog_post_header_feat_image_width' => array(
		'type' => 'slider',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_header_feat_image_width',
		'label' => esc_attr__( 'Width', 'crio' ),
		'tooltip' => __( 'Change the width of the featured image container on your blog page and archive pages.', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_featured_images',
		'type'        => 'slider',
		'default'     => 100,
		'choices'     => array(
			'min'  => '0',
			'max'  => '100',
			'step' => '1',
		),
		'output' => array(
			array(
				'units' => '%',
				'element' => '.blog .post.has-post-thumbnail .entry-header.above .featured-imgage-header, .archive .post.has-post-thumbnail .entry-header.above .featured-imgage-header, .blog .post.has-post-thumbnail .entry-header.below .featured-imgage-header, .archive .post.has-post-thumbnail .entry-header.below .featured-imgage-header',
				'property' => 'width',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_display',
				'operator' => '!==',
				'value'    => 'hide',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_position',
				'operator' => '!==',
				'value'    => 'background',
			),
			array(
				'setting'  => 'bgtfw_blog_post_header_feat_image_position',
				'operator' => '!==',
				'value'    => 'content',
			),
		),
	),
	'bgtfw_blog_post_tags_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_tags_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'tooltip' => __( 'Toggle the display of your tags on the blog roll and archive pages.', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'block',
		'choices' => array(
			'block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-footer .tags-links, .archive .post .entry-footer .tags-links',
				'property' => 'display',
			),
		),
	),

	// Start: Blog Post Featured Image Controls.
	'bgtfw_post_header_feat_image_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_post_header_feat_image_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'tooltip' => __( 'Hide or show your featured image on your blog posts.', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_featured_images',
		'default' => 'show',
		'choices' => array(
			'show' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'hide' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'show', 'hide' ], true ) ? $value : $settings->default;
		},
	),
	'bgtfw_post_header_feat_image_position' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_post_header_feat_image_position',
		'label' => esc_attr__( 'Position', 'crio' ),
		'tooltip' => __( 'Change where your featured image appears on your blog posts.', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_featured_images',
		'default' => 'background',
		'choices' => array(
			'background' => '<span class="dashicons dashicons-format-image"></span>' . __( 'Header Background', 'crio' ),
			'below' => '<span class="dashicons dashicons-arrow-down-alt"></span>' . __( 'Below Header', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'background', 'below' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_post_header_feat_image_display',
				'operator' => '!==',
				'value'    => 'hide',
			),
		),
	),
	'bgtfw_post_header_feat_image_size' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_post_header_feat_image_size',
		'label' => esc_attr__( 'Size', 'crio' ),
		'tooltip' => __( 'Change the size of your featured images. Due to container sizes, very large images may now show the full size when left or right aligned', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_featured_images',
		'default' => 'medium',
		'choices' => array(
			'thumbnail' => __( 'Thumbnail', 'crio' ),
			'medium' => __( 'Medium', 'crio' ),
			'large' => __( 'Large', 'crio' ),
			'full' => __( 'Full', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, array( 'thumbnail', 'medium', 'large', 'full' ), true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_post_header_feat_image_display',
				'operator' => '!==',
				'value'    => 'hide',
			),
			array(
				'setting'  => 'bgtfw_post_header_feat_image_position',
				'operator' => '!==',
				'value'    => 'background',
			),
		),
	),
	'bgtfw_post_header_feat_image_align' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_post_header_feat_image_align',
		'label' => esc_attr__( 'Alignment', 'crio' ),
		'tooltip' => __( 'Change the alignment of your image.', 'crio' ),
		'section' => 'bgtfw_pages_blog_posts_featured_images',
		'default' => 'alignleft',
		'choices' => array(
			'alignnone' => __( 'None', 'crio' ),
			'alignleft' => __( 'Left', 'crio' ),
			'aligncenter' => __( 'Center', 'crio' ),
			'alignright' => __( 'Right', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, array( 'alignnone', 'alignleft', 'aligncenter', 'alignright' ), true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_post_header_feat_image_display',
				'operator' => '!==',
				'value'    => 'hide',
			),
			array(
				'setting'  => 'bgtfw_post_header_feat_image_position',
				'operator' => '!==',
				'value'    => 'background',
			),
		),
	),

	// Start: Blog Page Tag Link Colors.
	'bgtfw_blog_post_tags_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_tags_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_tags_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_blog_post_tags_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.blog .post .entry-footer .tags-links a', '.archive .post .entry-footer .tags-links a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_tags_link_decoration' => array(
		'settings'    => 'bgtfw_blog_post_tags_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_tags_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_post_tags_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_tags_decoration_hover' => array(
		'settings'    => 'bgtfw_blog_post_tags_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_tags_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	'bgtfw_blog_post_tags_icon_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_tags_icon_display',
		'label' => esc_attr__( 'Icon Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'inline-block',
		'choices' => array(
			'inline-block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inline-block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-footer .tags-links .fa, .archive .post .entry-footer .tags-links .fa',
				'property' => 'display',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_tag_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_tag_icon',
		'label' => esc_attr__( 'Single Tag Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'tag',
		'js_vars' => array(
			array(
				'element' => '.blog .post .tags-links.singular .fa, .archive .post .tags-links.singular .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_tags_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_tags_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_tags_icon',
		'label' => esc_attr__( 'Multiple Tags Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_tags_links',
		'default' => 'tags',
		'js_vars' => array(
			array(
				'element' => '.blog .post .tags-links.multiple .fa, .archive .post .tags-links.multiple .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_tags_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_tags_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_cats_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_cats_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'block',
		'choices' => array(
			'block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-footer .cat-links, .archive .post .entry-footer .cat-links',
				'property' => 'display',
			),
		),
	),

	// Start: Blog Page Category Links Color Controls.
	'bgtfw_blog_post_cats_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_cats_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_cats_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_blog_post_cats_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.blog .post .entry-footer .cat-links a', '.archive .post .entry-footer .cat-links a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_cats_link_decoration' => array(
		'settings'    => 'bgtfw_blog_post_cats_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_cats_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_post_cats_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_cats_decoration_hover' => array(
		'settings'    => 'bgtfw_blog_post_cats_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_cats_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	// Start: Blog Page Category Icons.
	'bgtfw_blog_post_cats_icon_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_cats_icon_display',
		'label' => esc_attr__( 'Icon Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'inline-block',
		'choices' => array(
			'inline-block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inline-block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-footer .cat-links .fa, .archive .post .entry-footer .cat-links .fa',
				'property' => 'display',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_cat_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_cat_icon',
		'label' => esc_attr__( 'Single Category Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'folder',
		'js_vars' => array(
			array(
				'element' => '.blog .post .cat-links.singular .fa, .archive .post .cat-links.singular .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_cats_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_cats_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_cats_icon',
		'label' => esc_attr__( 'Multiple Categories Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_cat_links',
		'default' => 'folder-open',
		'js_vars' => array(
			array(
				'element' => '.blog .post .cat-links.multiple .fa, .archive .post .cat-links.multiple .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_cats_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_cats_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_comments_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_comments_display',
		'label' => esc_attr__( 'Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'block',
		'choices' => array(
			'block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-footer .comments-link, .archive .post .entry-footer .comments-link',
				'property' => 'display',
			),
		),
	),

	// Start: Comment Link Color Controls.
	'bgtfw_blog_post_comments_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_comments_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_comments_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_blog_post_comments_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.blog .post .entry-footer .comments-link a', '.archive .post .entry-footer .comments-link a' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_comments_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_comments_link_decoration' => array(
		'settings'    => 'bgtfw_blog_post_comments_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type' => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'Normal', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_comments_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_comments_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_post_comments_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default'     => -25,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_comments_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_comments_decoration_hover' => array(
		'settings'    => 'bgtfw_blog_post_comments_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'none',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_comments_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),


	'bgtfw_blog_post_comments_icon_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'auto',
		'settings' => 'bgtfw_blog_post_comments_icon_display',
		'label' => esc_attr__( 'Icon Display', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'inline-block',
		'choices' => array(
			'inline-block' => '<span class="dashicons dashicons-visibility"></span>' . __( 'Show', 'crio' ),
			'none' => '<span class="dashicons dashicons-hidden"></span>' . __( 'Hide', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inline-block', 'none' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element'  => '.blog .post .entry-footer .comments-link .fa, .archive .post .entry-footer .comments-link .fa',
				'property' => 'display',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_comment_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_comment_icon',
		'label' => esc_attr__( 'Single Comment Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'comment',
		'js_vars' => array(
			array(
				'element' => '.blog .post .comments-link.singular .fa, .archive .post .comments-link.singular .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_comments_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),
	'bgtfw_blog_post_comments_icon' => array(
		'type' => 'fontawesome',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_comments_icon',
		'label' => esc_attr__( 'Multiple Comments Icon', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_comment_links',
		'default' => 'comments',
		'js_vars' => array(
			array(
				'element' => '.blog .post .comments-link.multiple .fa, .archive .post .comments-link.multiple .fa',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => 'fa fa-fw fa-$',
			),
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_comments_display',
				'operator' => '!==',
				'value'    => 'none',
			),
			array(
				'setting'  => 'bgtfw_blog_post_comments_icon_display',
				'operator' => '!==',
				'value'    => 'none',
			),
		),
	),

	// Start: Read More Design.
	'bgtfw_blog_post_readmore_text' => array(
		'type' => 'text',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_readmore_text',
		'section' => 'bgtfw_pages_blog_blog_page_read_more',
		'label' => esc_attr__( 'Text', 'crio' ),
		'default' => 'Continue Reading &raquo;',
		'js_vars' => array(
			array(
				'element' => '.read-more a',
				'function' => 'html',
			),
		),
	),
	'bgtfw_blog_post_readmore_type' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings'    => 'bgtfw_blog_post_readmore_type',
		'label' => esc_attr__( 'Type', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_read_more',
		'default'     => 'btn button-secondary',
		'choices'     => array(
			'btn button-primary' => '<i class="fa fa-square" aria-hidden="true"></i>' . esc_attr__( 'Primary Button', 'crio' ),
			'btn button-secondary' => '<i class="fa fa-square-o" aria-hidden="true"></i>' . esc_attr__( 'Secondary Button', 'crio' ),
			'link' => '<span class="dashicons dashicons-admin-links"></span>' . esc_attr__( 'Link', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'btn button-primary', 'btn button-secondary', 'link' ], true ) ? $value : $settings->default;
		},
		'js_vars' => array(
			array(
				'element' => '.read-more a',
				'function' => 'html',
				'attr' => 'class',
				'value_pattern' => '$',
			),
		),
	),

	/* Start Read More Link Type Design */
	'bgtfw_blog_post_readmore_link_color_display' => array(
		'type' => 'radio-buttonset',
		'transport' => 'postMessage',
		'settings' => 'bgtfw_blog_post_readmore_link_color_display',
		'label' => esc_attr__( 'Colors', 'crio' ),
		'section' => 'bgtfw_pages_blog_blog_page_read_more',
		'default' => 'inherit',
		'choices' => array(
			'inherit' => '<span class="dashicons dashicons-admin-site"></span>' . __( 'Global Color', 'crio' ),
			'custom' => '<span class="dashicons dashicons-admin-customizer"></span>' . __( 'Custom', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'inherit', 'custom' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_readmore_type',
				'operator' => '===',
				'value'    => 'link',
			),
		),
	),
	'bgtfw_blog_post_readmore_link_color' => array(
		'type'       => 'bgtfw-palette-selector',
		'transport'  => 'postMessage',
		'settings'   => 'bgtfw_blog_post_readmore_link_color',
		'label'      => esc_attr__( 'Link Color', 'crio' ),
		'section'    => 'bgtfw_pages_blog_blog_page_read_more',
		'default'    => 'color-1',
		'choices'    => array(
			'selectors' => [ '.read-more .link' ],
			'colors' => $bgtfw_formatted_palette,
			'size'   => $bgtfw_palette->get_palette_size( $bgtfw_formatted_palette ),
		),
		'sanitize_callback' => array( $bgtfw_color_sanitize, 'sanitize_palette_selector' ),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_readmore_type',
				'operator' => '===',
				'value'    => 'link',
			),
			array(
				'setting'  => 'bgtfw_blog_post_readmore_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_readmore_link_decoration' => array(
		'settings'    => 'bgtfw_blog_post_readmore_link_decoration',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section'     => 'bgtfw_pages_blog_blog_page_read_more',
		'default' => 'underline',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_readmore_type',
				'operator' => '===',
				'value'    => 'link',
			),
			array(
				'setting'  => 'bgtfw_blog_post_readmore_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_readmore_link_color_hover' => array(
		'type'        => 'slider',
		'transport'   => 'postMessage',
		'settings'    => 'bgtfw_blog_post_readmore_link_color_hover',
		'label'       => esc_attr__( 'Hover Color Brightness', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_read_more',
		'default'     => 0,
		'choices'     => array(
			'min'  => '-25',
			'max'  => '25',
			'step' => '1',
		),
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_readmore_type',
				'operator' => '===',
				'value'    => 'link',
			),
			array(
				'setting'  => 'bgtfw_blog_post_readmore_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),
	'bgtfw_blog_post_readmore_link_decoration_hover' => array(
		'settings'    => 'bgtfw_blog_post_readmore_link_decoration_hover',
		'transport'   => 'postMessage',
		'label'       => esc_html__( 'Hover Text Style', 'crio' ),
		'type'        => 'radio-buttonset',
		'section'     => 'bgtfw_pages_blog_blog_page_read_more',
		'default' => 'underline',
		'choices' => array(
			'none' => '<span class="dashicons dashicons-editor-textcolor"></span>' . __( 'None', 'crio' ),
			'underline' => '<span class="dashicons dashicons-editor-underline"></span>' . __( 'Underline', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'none', 'underline' ], true ) ? $value : $settings->default;
		},
		'active_callback'    => array(
			array(
				'setting'  => 'bgtfw_blog_post_readmore_type',
				'operator' => '===',
				'value'    => 'link',
			),
			array(
				'setting'  => 'bgtfw_blog_post_readmore_link_color_display',
				'operator' => '!==',
				'value'    => 'inherit',
			),
		),
	),

	// Read More Button Position.
	'bgtfw_blog_post_readmore_position' => array(
		'type'        => 'radio-buttonset',
		'transport' => 'auto',
		'settings'    => 'bgtfw_blog_post_readmore_position',
		'label' => esc_attr__( 'Position', 'crio' ),
		'section'     => 'bgtfw_pages_blog_blog_page_read_more',
		'default'     => 'right',
		'choices'     => array(
			'left'   => '<span class="dashicons dashicons-editor-alignleft"></span>' . esc_attr__( 'Left', 'crio' ),
			'center' => '<span class="dashicons dashicons-editor-aligncenter"></span>' . esc_attr__( 'Center', 'crio' ),
			'right'  => '<span class="dashicons dashicons-editor-alignright"></span>' . esc_attr__( 'Right', 'crio' ),
		),
		'sanitize_callback' => function( $value, $settings ) {
			return in_array( $value, [ 'left', 'center', 'right' ], true ) ? $value : $settings->default;
		},
		'output' => array(
			array(
				'element' => '.read-more',
				'property' => 'text-align',
			),
		),
	),
);
