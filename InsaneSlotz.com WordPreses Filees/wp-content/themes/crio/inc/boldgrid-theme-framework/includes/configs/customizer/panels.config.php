<?php
/**
 * Customizer Panels Configs
 *
 * @package Boldgrid_Theme_Framework
 * @subpackage Boldgrid_Theme_Framework\Configs
 *
 * @since 2.0.0
 *
 * @return array Panels to create in the WordPress Customizer.
 */

return array(
	'bgtfw_design_panel' => array(
		'title' => __( 'Design', 'crio' ),
		'priority' => 1,
		'icon' => 'dashicons-admin-appearance',
	),
	'boldgrid_typography' => array(
		'title' => __( 'Fonts', 'crio' ),
		'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Manage your site\'s typography settings.', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/working-with-fonts-in-boldgrid-crio/?source=customize-fonts" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
		'priority' => 90,
		'icon' => 'dashicons-editor-textcolor',
	),
	'bgtfw_header' => array(
		'title' => __( 'Header', 'crio' ),
		'priority' => 1,
		'panel' => 'bgtfw_design_panel',
		'icon' => 'icon-header-settings',
	),
	'bgtfw_site_content' => array(
		'title' => __( 'Site Content', 'crio' ),
		'priority' => 2,
		'panel' => 'bgtfw_design_panel',
		'icon' => 'dashicons-welcome-widgets-menus',
	),
	'bgtfw_footer' => array(
		'title' => __( 'Footer', 'crio' ),
		'priority' => 3,
		'panel' => 'bgtfw_design_panel',
		'icon' => 'icon-footer-settings',
	),
	'bgtfw_blog_panel' => array(
		'title' => __( 'Blog', 'crio' ),
		'priority' => 5,
		'panel' => 'bgtfw_design_panel',
		'icon' => 'dashicons-admin-post',
	),
	'bgtfw_blog_blog_page_panel' => array(
		'title' => __( 'Blog Page', 'crio' ),
		'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Change the display of your site\'s blog page.', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/customizing-your-blog-page-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
		'panel' => 'bgtfw_blog_panel',
		'icon' => 'dashicons-media-document',
	),
	'bgtfw_pages_panel' => array(
		'title' => __( 'Pages', 'crio' ),
		'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Change the display of your site\'s pages', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/changing-the-page-layout-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
		'priority' => 2,
		'panel' => 'bgtfw_design_panel',
		'icon' => 'dashicons-admin-page',
	),
	'bgtfw_header_layouts' => array(
		'title' => __( 'Site Header Layout', 'crio' ),
		'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Manage the layout of your site\'s header.', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/customizing-the-header-design-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>' . esc_html__( 'Help', 'crio' ) . '</a></div></div>',
		'panel' => 'bgtfw_header',
		'capability' => 'edit_theme_options',
		'priority' => 1,
		'notice' => [
			'dismissible' => false,
			'message' => esc_html__( 'Upgrade Crio to get additional display options for your header!', 'crio' ),
			'type' => 'bgtfw-features',
			'templateId' => 'bgtfw-notification',
			'featureCount' => 1,
			'featureDescription' => esc_html__( '1 premium feature available!', 'crio' ),
			'url' => esc_url( 'https://www.boldgrid.com/wordpress-themes/crio/header/?source=customize-header' ),
			'buttonText' => esc_html__( 'Learn More', 'crio' ),
		],
		'icon' => 'dashicons-schedule',
	),
	'bgtfw_menus_panel' => array(
		'title' => __( 'Menus', 'crio' ),
		'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Manage the display of menus on your site.', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/working-with-menus-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
		'priority' => 4,
		'panel' => 'bgtfw_design_panel',
		'notice' => [
			'dismissible' => false,
			'message' => esc_html__( 'Upgrade Crio to get additional menu style options!', 'crio' ),
			'type' => 'bgtfw-features',
			'templateId' => 'bgtfw-notification',
			'featureCount' => 36,
			'featureDescription' => esc_html__( '36 premium features available!', 'crio' ),
			'url' => esc_url( 'https://www.boldgrid.com/wordpress-themes/crio/menu/?source=customize-menu' ),
			'buttonText' => esc_html__( 'Learn More', 'crio' ),
		],
		'icon' => 'dashicons-menu',
	),
	'bgtfw_blog_posts_panel' => array(
		'title' => __( 'Posts', 'crio' ),
		'description' => '<div class="bgtfw-description"><p>' . esc_html__( 'Change the display of single blog posts.', 'crio' ) . '</p><div class="help"><a href="https://www.boldgrid.com/support/boldgrid-crio/customizing-your-blog-posts-in-boldgrid-crio/" target="_blank"><span class="dashicons"></span>Help</a></div></div>',
		'panel' => 'bgtfw_blog_panel',
		'notice' => [
			'dismissible' => false,
			'message' => esc_html__( 'Upgrade Crio to get additional customization options for your posts!', 'crio' ),
			'type' => 'bgtfw-features',
			'templateId' => 'bgtfw-notification',
			'featureCount' => 15,
			'featureDescription' => esc_html__( '15 premium features available!', 'crio' ),
			'url' => esc_url( 'https://www.boldgrid.com/wordpress-themes/crio/blog/?source=customize-blog' ),
			'buttonText' => esc_html__( 'Learn More', 'crio' ),
		],
		'icon' => 'dashicons-admin-post',
	),
);
