<?php // phpcs:ignore
/**
 * Generic Provider class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.2.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Premium_Admin_Remote_Provider
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Provider {
	/**
	 * Core.
	 *
	 *  @since 1.2.0
	 *  @var Boldgrid_Backup_Admin_Core
	 *  @access protected
	 */
	protected $core;

	/**
	 * Default retention count.
	 *
	 * By default it's 5, but this can be changed.
	 *
	 * @since 1.2.0
	 * @var int $default_retention
	 * @access protected
	 */
	protected $default_retention = 5;

	/**
	 * Key.
	 *
	 * @since 1.2.0
	 * @var string
	 * @access protected
	 */
	protected $key;

	/**
	 * Remote settings.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Admin_Remote_Settings
	 * @access protected
	 */
	protected $remote_settings;

	/**
	 * Title.
	 *
	 * @since 1.2.0
	 * @var string
	 * @access protected
	 */
	protected $title;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		$this->remote_settings = new Boldgrid_Backup_Admin_Remote_Settings( $this->key );
	}

	/**
	 * Delete settings.
	 *
	 * Delete all of the settings for this provider.
	 *
	 * @since 1.2.0
	 */
	public function delete_settings() {
		$this->remote_settings->delete_settings();
	}

	/**
	 * Get the parent plugin core class.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Admin_Core
	 */
	public function get_core() {
		if ( is_null( $this->core ) ) {
			$this->core = apply_filters( 'boldgrid_backup_get_core', null );
		}

		return $this->core;
	}

	/**
	 * Get our default retention count.
	 *
	 * @since 1.2.0
	 *
	 * @return int
	 */
	public function get_default_retention() {
		return $this->default_retention;
	}

	/**
	 * Get our key.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get our nickname.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_nickname() {
		$nickname = $this->get_setting( 'nickname' );

		return ! empty( $nickname ) ? $nickname : $this->title;
	}

	/**
	 * Get our remote settings.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Admin_Remote_Settings
	 */
	public function get_remote_settings() {
		return $this->remote_settings;
	}

	/**
	 * Get one setting.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $key     The key of the setting to get.
	 * @param  mixed  $default The default value to return.
	 * @return mixed
	 */
	public function get_setting( $key, $default = false ) {
		return $this->remote_settings->get_setting( $key, $default );
	}

	/**
	 * Get our title.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Whether or not this provider has settings saved.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	public function has_settings() {
		return $this->remote_settings->has_settings();
	}
}
