<?php
/**
 * File: class-settings.php
 *
 * @link  https://www.boldgrid.com
 * @since 1.3.0
 *
 * @package    Boldgrid
 * @subpackage Boldgrid\Backup\Premium\Admin
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Backup\Premium\Admin;

/**
 * Class: Settings
 *
 * @since 1.3.0
 */
class Settings {
	/**
	 * The BoldGrid Backup core class object.
	 *
	 * @since  1.3.0
	 * @access private
	 * @var    \Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * The Boldgrid Backup Premium core object.
	 *
	 * @since  1.3.0
	 * @access private
	 * @var    \Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 *
	 * @param \Boldgrid_Backup_Admin_Core         $core         \Boldgrid_Backup_Admin_Core object.
	 * @param \Boldgrid_Backup_Premium_Admin_Core $premium_core \Boldgrid_Backup_Premium_Admin_Core object.
	 */
	public function __construct( \Boldgrid_Backup_Admin_Core $core, \Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;
	}

	/**
	 * Enqueue scripts for admin pages.
	 *
	 * @since 1.3.0
	 */
	public function admin_enqueue_scripts() {
		if ( isset( $_GET['page'] ) && 'boldgrid-backup-settings' === $_GET['page'] ) { // phpcs:ignore
			wp_register_script(
				'boldgrid-backup-premium-admin-settings',
				plugin_dir_url( __FILE__ ) . 'js/boldgrid-backup-premium-admin-settings.js',
				[ 'jquery' ],
				BOLDGRID_BACKUP_PREMIUM_VERSION,
				false
			);

			wp_localize_script(
				'boldgrid-backup-premium-admin-settings',
				'settingsData',
				[
					'cryptToken' => ( new Crypt( $this->core, $this->premium_core ) )->get_crypt_token(),
					'lang'       => [
						'encryptionToken'   => __( 'Encryption Token', 'boldgrid-backup' ),
						'revealToken'       => __( 'Show', 'boldgrid-backup' ),
						'copyText'          => __( 'Copy', 'boldgrid-backup' ),
						'copiedText'        => __( 'Copied!', 'boldgrid-backup' ),
						'deleteToken'       => __( 'Delete', 'boldgrid-backup' ),
						'deleteConfirmText' => __(
							'If you delete the encryption token, then you will not be able to access encrpyted file stored in previously-created backup archives.',
							'boldgrid-backup'
						),
						'addHelpText'       => sprintf(
							// translators: 1: HTML break tag, 2: HTML strong open tag, 3: HTML strong closing tag.
							esc_html__(
								'%1$s%1$s%2$sEncryption Token%3$s%1$sThe token is used for encryption and should be saved in a secure place (such as a password safe or keyring) and can be used to decrypt files in your encrypted backup archives.',
								'boldgrid-backup'
							),
							'<br />',
							'<strong>',
							'</strong>'
						),
						'addTokenText'      => sprintf(
							// translators: 1: HTML break tag.
							esc_html__(
								'Save this token in a secure place, such as a password safe or keyring.  It is used to decrypt encrypted files stored in backup archives.%1$sIf you change or delete this token, then you will not be able to restore encrypted contents from backup archives.',
								'boldgrid-backup'
							),
							'<br />'
						),
					],
				]
			);

			wp_enqueue_script( 'boldgrid-backup-premium-admin-settings' );
			wp_enqueue_script( 'clipboard' );
		}
	}

	/**
	 * Filter the boldgrid_backup_settings site option before saving to the database.
	 *
	 * @since 1.3.0
	 *
	 * @param  array $settings BoldGrid Backup settings.
	 * @return array
	 */
	public function filter_settings( $settings ) {
		$settings = (array) $settings;

		// The there is a crypt_token, then update the encryption settings.
		if ( ! empty( $_POST['crypt_token'] ) ) { // phpcs:ignore
			$crypt_settings = ( new Crypt( $this->core, $this->premium_core ) )
				->decode_crypt_token( sanitize_key( $_POST['crypt_token'] ) ); // phpcs:ignore

			if ( ! empty( $crypt_settings ) && ( empty( $settings['openssl'] ) || $settings['openssl'] !== $crypt_settings ) ) {
				$settings = array_merge( $settings, $crypt_settings );
			}
		} elseif ( ! empty( $_POST['delete_crypt_token'] ) ) { // phpcs:ignore
			// Delete the encryption settings.  They will regenerate if needed.
			unset( $settings['openssl'] );
		}

		return $settings;
	}

	/**
	 * Filter plugin links.
	 *
	 * @since 1.3.0
	 *
	 * @link https://developer.wordpress.org/reference/hooks/plugin_action_links/
	 *
	 * @param array  $actions     An array of plugin action links. By default this can include 'activate',
	 *                            'deactivate', and 'delete'. With Multisite active this can also include
	 *                            'network_active' and 'network_only' items.
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string $context     The plugin context. By default this can include 'all', 'active', 'inactive',
	 *                            'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 */
	public function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		$row_actions = [
			'settings' => '<a href="' . esc_url( $this->core->settings->get_settings_url() ) . '">' .
				esc_html__( 'Settings', 'boldgrid-backup' ) . '</a>',
		];

		if ( ! $this->core->config->get_is_premium() ) {
			$row_actions[] = '<a href="' . esc_url( $this->core->go_pro->get_premium_url( 'bgbkup-plugin-actions' ) ) .
				'" target="_blank">' . esc_html__( 'Get Premium', 'boldgrid-backup' ) . '</a>';
		}

		$actions = array_merge( $row_actions, $actions );

		return $actions;
	}

	/**
	 * Is Timely Updates Enabled.
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	public function is_timely_updates( $arg ) {
		$settings = $this->core->settings->get_settings();
		return ! empty( $settings['auto_update']['timely-updates-enabled'] );
	}
}
