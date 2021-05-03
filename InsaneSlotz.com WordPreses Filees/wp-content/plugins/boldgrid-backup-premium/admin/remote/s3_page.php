<?php // phpcs:ignore
/**
 * S3_Page class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.2.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * S3_Page class.
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Page {
	/**
	 * Errors.
	 *
	 * @since 1.2.0
	 * @var array
	 * @access private
	 */
	private $errors = array();

	/**
	 * The provider this page is for.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Provider
	 * @access private
	 */
	private $provider;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider
	 */
	public function __construct( Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider ) {
		$this->provider = $provider;
	}

	/**
	 * Add menu items.
	 *
	 * @since 1.2.0
	 */
	public function add_submenu_page() {
		$capability = 'administrator';

		$title = sprintf(
			// Translators: 1 the name of the s3 provider, such as DreamObjects.
			__( '%1$s Settings', 'boldgrid-backup' ),
			$this->provider->get_title()
		);

		add_submenu_page(
			null,
			$title,
			$title,
			$capability,
			'boldgrid-backup-' . $this->provider->get_key(),
			[
				$this,
				'submenu_page',
			]
		);
	}

	/**
	 * Ensure the host has a protocol.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function sanitize_host( $host ) {
		if ( false === strpos( $host, '://' ) ) {
			$host = 'https://' . $host;
		}

		return stripslashes( $host );
	}

	/**
	 * Whether or not the page was submitted with a valid nonce.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	public function is_valid_nonce() {
		$nonce = ! empty( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : null; // phpcs:ignore

		return wp_verify_nonce( $nonce, 'save-provider-settings_' . $this->provider->get_key() );
	}

	/**
	 * Generate the submenu page for our Provider's Settings page.
	 *
	 * @since 1.2.0
	 */
	public function submenu_page() {
		wp_enqueue_style( 'boldgrid-backup-admin-hide-all' );

		$this->submenu_page_save();
		if ( ! empty( $this->errors ) ) {
			do_action( 'boldgrid_backup_notice', implode( '<br /><br />', $this->errors ) );
		}

		/*
		 * Determine the values to use in the form.
		 *
		 * If we're posting data, use that, otherwise use the values already in the settings.
		 */
		if ( ! empty( $_POST['save'] ) && $this->is_valid_nonce() ) { // phpcs:ignore
			// phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
			$key             = sanitize_text_field( $_POST['key'] );
			$secret          = sanitize_text_field( $_POST['secret'] );
			$bucket_id       = sanitize_text_field( $_POST['bucket_id'] );
			$retention_count = (int) $_POST['retention_count'];
			$nickname        = sanitize_text_field( $_POST['nickname'] );
			$host            = esc_url( $this->sanitize_host( $_POST['host'] ) );
			// phpcs:enable
		} else {
			$settings = $this->provider->get_remote_settings()->get_settings();

			$key             = ! empty( $settings['key'] ) ? $settings['key'] : null;
			$secret          = ! empty( $settings['secret'] ) ? $settings['secret'] : null;
			$bucket_id       = ! empty( $settings['bucket_id'] ) ? $settings['bucket_id'] : Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket::create_unique_bucket();
			$retention_count = ! empty( $settings['retention_count'] ) ? $settings['retention_count'] : $this->provider->get_default_retention();
			$nickname        = ! empty( $settings['nickname'] ) ? $settings['nickname'] : '';
			$host            = ! empty( $settings['host'] ) ? $settings['host'] : null;
		}

		// @todo Next implementation of a generic s3 provider, may need to rework the settings page.
		include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/remote/' . sanitize_file_name( $this->provider->get_key() ) . '.php';
	}

	/**
	 * Process the user's request to update their Provider's settings.
	 *
	 * @since 1.2.0
	 */
	public function submenu_page_save() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		if ( empty( $_POST ) ) { // phpcs:ignore
			return false;
		}

		if ( ! $this->is_valid_nonce() ) {
			$this->errors[] = __( 'Access denied: invalid nonce.', 'boldgrid-backup' );

			return false;
		}

		$this->provider->get_transient()->delete_all();

		$provider_settings = [];

		if ( ! $this->provider->has_settings() ) {
			$provider_settings = [];
		}

		// If the user has requested to delete all their settings, do that now and return.
		if ( ! empty( $_POST['delete'] ) ) { // phpcs:ignore
			$this->provider->delete_settings();

			do_action( 'boldgrid_backup_notice', __( 'Settings saved.', 'boldgrid-backup' ), 'notice updated is-dismissible' );

			return;
		}

		$key             = ! empty( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : null; // phpcs:ignore
		$secret          = ! empty( $_POST['secret'] ) ? sanitize_text_field( $_POST['secret'] ) : null; // phpcs:ignore
		$bucket_id       = ! empty( $_POST['bucket_id'] ) ? sanitize_text_field( $_POST['bucket_id'] ) : Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket::create_unique_bucket(); // phpcs:ignore
		$retention_count = ! empty( $_POST['retention_count'] ) && is_numeric( $_POST['retention_count'] ) ? (int) $_POST['retention_count'] : $this->provider->get_default_retention(); // phpcs:ignore
		$nickname        = ! empty( $_POST['nickname'] )  ? sanitize_text_field( stripslashes( $_POST['nickname'] ) ) : null; // phpcs:ignore
		$host            = ! empty( $_POST['host'] )  ? esc_url( $this->sanitize_host( $_POST['host'] ) ) : null; // phpcs:ignore

		echo $this->provider->get_core()->elements['long_checking_creds']; // phpcs:ignore
		if ( ob_get_level() > 0 ) {
			ob_flush();
		}
		flush();

		$client_args = [
			'key'      => $key,
			'secret'   => $secret,
			'endpoint' => $host,
		];

		$client = new Boldgrid_Backup_Premium_Admin_Remote_S3_Client( $client_args );

		if ( $client->is_valid() ) {
			$provider_settings = [
				'key'             => $key,
				'secret'          => $secret,
				'retention_count' => $retention_count,
				'nickname'        => $nickname,
				'host'            => $host,
				'bucket_id'       => $bucket_id,
			];

			// Create the bucket if it does not already exist.
			$bucket = new Boldgrid_Backup_Premium_Admin_Remote_S3_Bucket( $client, $bucket_id );
			$bucket->maybe_create();
		} else {
			$this->errors[] = __( 'Invalid Access Key Id and / or Secret Access Key.', 'boldgrid-backup' );
		}

		if ( empty( $this->errors ) ) {
			$this->provider->get_remote_settings()->save_settings( $provider_settings );
			do_action( 'boldgrid_backup_notice', __( 'Settings saved.', 'boldgrid-backup' ), 'notice updated is-dismissible' );
		}
	}
}
