<?php
/**
 * Customizer Presets Configs.
 *
 * @package Boldgrid_Theme_Framework
 * @subpackage Boldgrid_Theme_Framework\Configs
 *
 * @since 2.0.0
 *
 * @return array Presets to use in the WordPress Customizer.
 */

return array(
	'header'        => array(
		'lbrm'   => array(
			'label'  => __( 'Branding + Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
						array(
							'type'  => 'boldgrid_menu_main',
							'key'   => 'menu',
							'align' => 'e',
							'uid'   => 'h48',
						),
					),
				),
			),
		),
		'lbcmrs' => array(
			'label'  => __( 'Branding + Menu + Social Icons', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
						array(
							'type'  => 'boldgrid_menu_main',
							'key'   => 'menu',
							'align' => 'c',
							'uid'   => 'h48',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_social',
							'align' => 'e',
							'uid'   => 'h110',
						),
					),
				),
			),
		),
		'lmrb'   => array(
			'label'  => __( 'Menu + Branding', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'  => 'boldgrid_menu_main',
							'key'   => 'menu',
							'align' => 'w',
							'uid'   => 'h48',
						),
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'e',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
					),
				),
			),
		),
		'lbrslm' => array(
			'label'  => __( 'Branding and Menu + Social', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_social',
							'align' => 'e',
							'uid'   => 'h110',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_main',
							'align' => 'w',
							'uid'   => 'h105',
						),
					),
				),
			),
		),
		'lbrscm' => array(
			'label'  => __( 'Branding + Social Icons w/ Center Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_social',
							'align' => 'e',
							'uid'   => 'h110',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_main',
							'align' => 'c',
							'uid'   => 'h105',
						),
					),
				),
			),
		),
		'lbrsrm' => array(
			'label'  => __( 'Branding + Social Icons and Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_social',
							'align' => 'e',
							'uid'   => 'h110',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_main',
							'align' => 'e',
							'uid'   => 'h105',
						),
					),
				),
			),
		),
		'cbcm'   => array(
			'label'  => __( 'Centered Branding above Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'c',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_main',
							'align' => 'c',
							'uid'   => 'h105',
						),
					),
				),
			),
		),
		'cmcb'   => array(
			'label'  => __( 'Centered Menu above Branding', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_main',
							'align' => 'c',
							'uid'   => 'h105',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'c',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 'h47',
						),
					),
				),
			),
		),
		'lshsbm' => array(
			'label'  => __( 'Centered Menu above Branding', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_social',
							'align' => 'w',
							'uid'   => 'h264',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'     => 'branding',
							'type'    => 'boldgrid_site_identity',
							'display' => array(
								array(
									'selector' => '.custom-logo-link',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'align'   => 'w',
							'uid'     => 'h47',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_main',
							'align' => 'w',
							'uid'   => 'h263',
						),
					),
				),
			),
		),
	),
	'sticky_header' => array(
		'lbrm'   => array(
			'label'  => __( 'Branding + Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
						array(
							'type'  => 'boldgrid_menu_sticky-main',
							'key'   => 'menu',
							'align' => 'e',
							'uid'   => 's48',
						),
					),
				),
			),
		),
		'lbcmrs' => array(
			'label'  => __( 'Branding + Menu + Social Icons', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
						array(
							'type'  => 'boldgrid_menu_sticky-main',
							'key'   => 'menu',
							'align' => 'c',
							'uid'   => 's48',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-social',
							'align' => 'e',
							'uid'   => 's110',
						),
					),
				),
			),
		),
		'lmrb'   => array(
			'label'  => __( 'Menu + Branding', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'  => 'boldgrid_menu_sticky-main',
							'key'   => 'menu',
							'align' => 'w',
							'uid'   => 's48',
						),
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'e',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
					),
				),
			),
		),
		'lbrslm' => array(
			'label'  => __( 'Branding and Menu + Social', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-social',
							'align' => 'e',
							'uid'   => 's110',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-main',
							'align' => 'w',
							'uid'   => 's105',
						),
					),
				),
			),
		),
		'lbrscm' => array(
			'label'  => __( 'Branding + Social Icons w/ Center Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-social',
							'align' => 'e',
							'uid'   => 's110',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-main',
							'align' => 'c',
							'uid'   => 's105',
						),
					),
				),
			),
		),
		'lbrsrm' => array(
			'label'  => __( 'Branding + Social Icons and Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'w',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-social',
							'align' => 'e',
							'uid'   => 's110',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-main',
							'align' => 'e',
							'uid'   => 's105',
						),
					),
				),
			),
		),
		'cbcm'   => array(
			'label'  => __( 'Centered Branding above Menu', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'c',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-main',
							'align' => 'c',
							'uid'   => 's105',
						),
					),
				),
			),
		),
		'cmcb'   => array(
			'label'  => __( 'Centered Menu above Branding', 'crio' ),
			'config' => array(
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'key'   => 'menu',
							'type'  => 'boldgrid_menu_sticky-main',
							'align' => 'c',
							'uid'   => 's105',
						),
					),
				),
				array(
					'container' => 'container',
					'items'     => array(
						array(
							'type'    => 'boldgrid_site_identity',
							'key'     => 'branding',
							'align'   => 'c',
							'display' => array(
								array(
									'selector' => '.custom-logo',
									'display'  => 'show',
									'title'    => 'Logo',
								),
								array(
									'selector' => '.site-title',
									'display'  => 'show',
									'title'    => 'Title',
								),
								array(
									'selector' => '.site-description',
									'display'  => 'hide',
									'title'    => 'Tagline',
								),
							),
							'uid'     => 's47',
						),
					),
				),
			),
		),
	),
);
