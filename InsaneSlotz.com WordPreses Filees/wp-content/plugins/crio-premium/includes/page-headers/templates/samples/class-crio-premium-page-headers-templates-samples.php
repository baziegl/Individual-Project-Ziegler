<?php

/**
 * File: class=crio-premium-page-headers-templates-samples.php
 *
 * Adds the Sample Page Headers.
 *
 * @link       https://www.boldgrid.com/
 * @since      1.1.0
 *
 * @package    Crio_Premium
 * @subpackage Crio_Premium/includes/Page_Headers
 */

/**
 * Class: Crio_Premium_Page_Headers_Templates_Samples
 *
 * This is the class for managing the Sample Custom Header Templates.
 */
class Crio_Premium_Page_Headers_Templates_Samples {

	/**
	 * Page Headers Base
	 *
	 * @since 1.1.0
	 * @var Crio_Premium_Page_Headers_base
	 */
	public $base;

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
	 * Install Samples Notice
	 *
	 * Shows a notice allowing users to install sample header templates.
	 *
	 * @since 1.1.0
	 */
	public function install_samples_notice() {
		$sample_templates_installed = get_option( 'crio_premium_sample_templates_installed', false );

		if ( $sample_templates_installed ) {
			return;
		}

		if ( function_exists( 'get_current_screen' ) && is_plugin_active( 'post-and-page-builder/post-and-page-builder.php' ) ) {
			$screen = get_current_screen();
			if ( $screen && isset( $screen->post_type ) && 'crio_page_header' === $screen->post_type ) {
				?>
				<div data-dismissible="crio-premium-install-samples" class="install_sample_templates notice notice-info is-dismissible">
				<p>
					<strong>
						<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
							<?php esc_html_e( 'Crio Pro comes with some sample templates to help get you started. Would you like to install the Page Header Templates?', 'crio-premium' ); ?>
						</span>
						<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
							<span class="hidden install_samples_nonce"><?php echo wp_create_nonce( 'crio_premium_install_sample_templates' ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?></span>
							<a class="button install_stamples_yes" href="">
								<?php esc_html_e( 'Yes', 'crio-premium' ); ?>
							</a>
							<a class="button install_stamples_no" href="">
								<?php esc_html_e( 'No', 'crio-premium' ); ?>
							</a>
						</span>
					</strong>
				</p>
				</div>
				<?php
			}
		}
	}
	/**
	 * WP Ajax Install Sample Templates
	 *
	 * AP Ajax callback to install sample templates.
	 *
	 * @since 1.1.0
	 */
	public function wp_ajax_install_sample_templates() {
		$verified = false;
		if ( isset( $_POST ) && isset( $_POST['installSamplesNonce'] ) ) {
			$verified = wp_verify_nonce(
				$_POST['installSamplesNonce'],
				'crio_premium_install_sample_templates'
			);
		}

		if ( ! $verified ) {
			return false;
		}

		$templates_installed_successfully = $this->install_sample_templates();

		wp_send_json( array( 'sampleTemplatesInstalled' => $templates_installed_successfully ) );
	}

	/**
	 * Install Sample Templates
	 *
	 * @since 1.1.0
	 */
	public function install_sample_templates() {
		$samples          = array();
		$samples_config   = include __DIR__ . '/configs/sample.templates.config.php';
		$num_of_templates = count( $this->base->templates->get_available() );
		foreach ( $samples_config as $sample_config ) {
			$sample = new Crio_Premium_Page_Headers_Templates_Sample( $this->base, $sample_config );
		}
		$num_of_samples = count( $samples_config );
		if ( count( $this->base->templates->get_available() ) - $num_of_templates === $num_of_samples ) {
			update_option( 'crio_premium_sample_templates_installed', true );
			return true;
		} else {
			update_option( 'crio_premium_sample_templates_installed', false );
			return false;
		}
	}
}
