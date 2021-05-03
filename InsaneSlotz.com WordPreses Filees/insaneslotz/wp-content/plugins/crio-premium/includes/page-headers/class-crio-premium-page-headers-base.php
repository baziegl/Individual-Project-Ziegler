<?php

/**
 * File: class=crio-premium-page-headers-base.php
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
 * Class: Crio_Premium_Page_Headers_Base
 *
 * This is the base class for adding the Custom Header Templates feature
 * to Crio Premium.
 */
class Crio_Premium_Page_Headers_Base {

	/**
	 * Customizer Controls
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Customizer_Controls
	 */
	public $customizer_controls;

	/**
	 * Templates
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_Templates
	 */
	public $templates;

	/**
	 * Constructor
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->templates           = new Crio_Premium_Page_Headers_Templates( $this );
		$this->customizer_controls = new Crio_Premium_Page_Headers_Customizer_Controls( $this );

		$this->define_hooks();
	}



	/**
	 * Defines all hooks needed for the Header Templates
	 *
	 * @since 1.1.0
	 *
	 * @access private
	 */
	private function define_hooks() {
		/*
		 * Header Template Hooks
		 */

		// Check for PPB installed and active.
		add_action( 'admin_notices', array( $this, 'ppb_nag' ) );

		// Register Header Template Custom Post Type and Save Actions
		add_action( 'init', array( $this->templates, 'register_post_type' ) );
		add_action( 'save_post_crio_page_header', array( $this->templates, 'actions_on_save' ), 10, 3 );

		// Register Post meta, Meta Boxes, and Meta Save
		add_action( 'init', array( $this->templates->meta, 'register_post_meta' ) );
		add_action( 'add_meta_boxes', array( $this->templates->meta, 'add_template_metabox' ), 15, 1 );

		// Register the post meta 'save actions' for overriding the chosen template.
		add_action( 'save_post', array( $this->templates->meta, 'save_override_meta' ) );

		// Actions and Filters for getting, and printing the header template.
		add_action(
			'template_redirect', function() {
				add_action( 'get_header', array( $this->templates, 'get_header' ) );
			}
		);

		// Resets header template choice when deleting header templates.
		add_action( 'wp_trash_post', array( $this->templates, 'actions_on_delete' ) );

		// Removes template redirect after using it once.
		add_action(
			'crio_premium_remove_redirect', function() {
				remove_action( 'get_header', array( $this->templates, 'get_header' ) );
			}
		);

		add_filter( 'crio_premium_get_page_header', array( $this->templates, 'get_for_post' ), 10, 1 );

		// Actions and Filters for handling template editor
		add_action( 'admin_enqueue_scripts', array( $this->templates->editor, 'load_scripts' ), 10, 1 );
		add_action( 'after_wp_tiny_mce', array( $this->templates->editor, 'load_mce_script' ), 10, 1 );
		add_filter( 'boldgrid_editor_after_editor_styles', array( $this->templates->editor->styles, 'add_mce_css' ), 10, 1 );
		add_filter( 'boldgrid_mce_inline_styles', array( $this->templates->editor->styles, 'add_inline_css' ) );

		// Actions for managing custom menu locations within header templates.
		add_filter( 'boldgrid_theme_framework_config', array( $this->templates->navs, 'add_menus' ), 16, 1 );
		add_filter( 'boldgrid_custom_menu_locations', array( $this->templates->navs, 'get_nav_locations' ), 10, 1 );
		add_action( 'wp_ajax_crio_premium_register_menu_location', array( $this->templates->navs, 'admin_ajax_register_menu_location' ) );

		// Admin ajax call to update site logo theme mod from within a header template.
		add_action( 'wp_ajax_crio_premium_update_site_logo', array( $this->templates, 'update_site_logo' ) );

		add_action( 'admin_notices', array( $this, 'activate_page_headers' ) );
		add_action( 'wp_ajax_crio_premium_activate_page_headers', array( $this, 'wp_ajax_activate_page_headers' ) );
		add_action( 'admin_notices', array( $this, 'help_notice' ) );

		add_action( 'admin_notices', array( $this->templates->samples, 'install_samples_notice' ) );
		add_action( 'wp_ajax_crio_premium_install_sample_templates', array( $this->templates->samples, 'wp_ajax_install_sample_templates' ) );

		/*
		 * Customizer Hooks
		 */

		// Adds customizer controls to the BGTFW Configuration
		add_filter( 'boldgrid_theme_framework_config', array( $this->customizer_controls, 'add_customizer_controls' ), 16, 1 );
		add_action( 'customize_controls_enqueue_scripts', array( $this->customizer_controls, 'load_customizer_scripts' ) );

		/*
		 * Misc Hooks
		 */

		// Whether or not page headers are enabled for a given page or post.
		add_filter( 'crio_premium_page_headers_enabled', array( $this, 'page_headers_enabled' ), 10, 1 );
	}

	/**
	 * Are Page Headers Enabled.
	 *
	 * @since 1.1.0
	 *
	 * @return bool This will return true if Template Headers are enabled.
	 */
	public function page_headers_enabled() {

		$post_id = get_the_ID();

		$header_id = apply_filters( 'crio_premium_get_page_header', get_the_ID() );

		if ( false === $header_id ) {
			return false;
		}

		return get_theme_mod( 'bgtfw_page_headers_global_enabled', true );
	}

	/**
	 * PPB Nag
	 *
	 * Shows a notice if PPB is not installad and active.
	 *
	 * @since 1.1.0
	 */
	public function ppb_nag() {
		$ppb_plugin_path = WP_PLUGIN_DIR . '/post-and-page-builder/post-and-page-builder.php';
		$ppb_version     = get_file_data( $ppb_plugin_path, array( 'Version' => 'Version' ), false );

		if ( is_plugin_active( 'post-and-page-builder/post-and-page-builder.php' ) && version_compare( $ppb_version['Version'], REQUIRED_PPB_VERSION, 'ge' ) ) {
			return;
		}

		if ( ! PAnD::is_admin_notice_active( 'ppb-crio-premium-activate-nag' ) && ! PAnD::is_admin_notice_active( 'ppb-crio-premium-install-nag' ) ) {
			return;
		}

		$ppb_version_invalid = version_compare( $ppb_version['Version'], REQUIRED_PPB_VERSION, 'lt' );

		if ( file_exists( $ppb_plugin_path ) && ! $ppb_version_invalid ) {
			$url  = admin_url( 'plugins.php?action=activate&plugin=post-and-page-builder%2Fpost-and-page-builder.php&_wpnonce=' );
			$url .= wp_create_nonce( 'activate-plugin_post-and-page-builder/post-and-page-builder.php' );
			$link = sprintf(
				// translators: 1: Theme page URL.
				__( '<a href="%1$s">Activate Now</a>', 'crio-premium' ),
				$url
			);
			?>
			<div data-dismissible="ppb-crio-premium-activate-nag" class="crio-premium-ppb-nag notice notice-error">
				<p><?php printf( __( '<b>Post and Page Builder</b> needs to be active in order to use the new <b>Page Header Templates</b> feature! %1$s', 'crio-premium' ), $link ); // phpcs:ignore ?></p>
			</div>
			<?php
		} elseif ( file_exists( $ppb_plugin_path ) && $ppb_version_invalid ) {
			?>
			<div data-dismissible="ppb-version-nag" class="crio-premium-ppb-nag notice notice-error">
				<p><b><?php esc_html_e( 'Post And Page Builder needs to be updated to the newest version to use the new Page Header Templates feature!', 'crio-premium' ); ?></b></p>
			</div>
			<?php
		} else {
			$url  = wp_nonce_url( admin_url( 'plugin-install.php?s=post+and+page+builder&tab=search&type=term' ) );
			$link = sprintf(
				// translators: 1: Theme page URL.
				__( '<a href="%1$s">Install Now</a>', 'crio-premium' ),
				$url
			);
			?>
			<div data-dismissible="ppb-crio-premium-install-nag" class="crio-premium-ppb-nag notice notice-error">
				<p><?php printf( __( '<b>Post and Page Builder</b> needs to be installed and active in order to use the new <b>Page Header Templates</b> feature! %1$s', 'crio-premium' ), $link ); // phpcs:ignore ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Help Notice
	 *
	 * Shows a notice directing users to help docs.
	 *
	 * @since 1.1.0
	 */
	public function help_notice() {
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && isset( $screen->post_type ) && 'crio_page_header' === $screen->post_type ) {
				?>
				<div data-dismissible="crio-premium-page-header-help" class="page_headers_help notice notice-info">
				<p>
					<strong>
						<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
							With Crio and Post and Page Builder, you can create custom page headers using our drag and drop builder. Additionally, you can specify which pages or posts these headers will be used on.
						</span>
						<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
							<a class="button" href="https://www.boldgrid.com/support/boldgrid-crio-supertheme-product-guide/custom-header-templates/" target="_blank">View our support documentation for more information</a>
						</span>
					</strong>
				</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Activate Page Headers
	 *
	 * Shows a notice if page headers are disabled, and gives option to activate.
	 *
	 * @since 1.1.0
	 */
	public function activate_page_headers() {
		if ( get_theme_mod( 'bgtfw_page_headers_global_enabled', true ) ) {
			return;
		}

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && isset( $screen->post_type ) && 'crio_page_header' === $screen->post_type ) {
				?>
				<div data-dismissible="crio-premium-page-header-nag" class="page_headers_disabled notice notice-error">
					<?php wp_nonce_field( 'crio_premium_activate_page_headers' ); ?>
					<p><?php esc_html_e( 'Page Header Templates are globally disabled in the customizer. Page Headers must be enabled to use this feature.', 'crio-premium' ); ?></p>
					<p><a class="button" href=""><?php esc_html_e( 'Enable Page Headers' ); ?></a></p>
				</div>
				<?php
			}
		}
	}

	/**
	 * WP Ajax Activate Page Headers
	 *
	 * AP Ajax callback to activate page headers theme mod.
	 *
	 * @since 1.1.0
	 */
	public function wp_ajax_activate_page_headers() {
		$verified = false;
		if ( isset( $_POST ) && isset( $_POST['nonce'] ) ) {
			$verified = wp_verify_nonce(
				$_POST['nonce'],
				'crio_premium_activate_page_headers'
			);
		}

		if ( ! $verified ) {
			return false;
		}

		set_theme_mod( 'bgtfw_page_headers_global_enabled', true );

		wp_send_json( array( 'pageHeadersEnabled' => get_theme_mod( 'bgtfw_page_headers_global_enabled', true ) ) );
	}
}
