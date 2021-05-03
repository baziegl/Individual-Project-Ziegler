<?php
/**
 * File: class-boldgrid-backup-premium-admin-themes.php
 *
 * @link  https://www.boldgrid.com
 * @since 1.4.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Admin_Premium_Themes.
 *
 * This is a generic class designed to help manage how this plugin behaves within the scope of.
 * "WordPress Dashboard > Themes > *".
 *
 * @since 1.4.0
 */
class Boldgrid_Backup_Premium_Admin_Themes {
	/**
	 * Core.
	 *
	 * @since 1.4.0
	 * @var Boldgrid_Backup_Admin_Core
	 */
	public $core;

	/**
	 * Themes.
	 *
	 * @since 1.4.0
	 * @var \Boldgrid\Library\Library\Theme\Themes
	 */
	public $themes;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Auto Update Settings.
	 *
	 * @var array
	 */
	public $auto_update_settings;

	/**
	 * Data.
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {
		$this->core     = apply_filters( 'boldgrid_backup_get_core', null );
		$this->themes   = new \Boldgrid\Library\Library\Theme\Themes();
		$this->settings = get_site_option( 'boldgrid_backup_settings', array() );
		if ( array_key_exists( 'auto_update', $this->settings ) ) {
			$this->auto_update_settings = $this->settings['auto_update'];
		}
	}

	/**
	 * Admin Enqueue Scripts.
	 *
	 * @since 1.4.0
	 *
	 * @param string $hook Hook String passed to callback.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'themes.php' !== $hook ) {
			return;
		}

		$handle = 'boldgrid-backup-premium-admin-timely-updates';
		wp_register_script(
			$handle,
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/boldgrid-backup-premium-admin-timely-updates.js',
			array( 'jquery' ),
			BOLDGRID_BACKUP_PREMIUM_VERSION,
			true
		);

		$this->add_update_message();

		$translation = $this->data;

		wp_localize_script( $handle, 'BgbckTheme', $translation );

		wp_enqueue_script( $handle );
	}

	/**
	 * Filters auto update markup on themes page.
	 *
	 * In WP5.5+ the core UI already includes a message indicating when
	 * auto updates will occur. Therefore we must filter that markup to include
	 * the correct amount of time based on Timely Auto Update settings.
	 *
	 * Because of the fact that the $template string is a JS template, not actual HTML, this replacement needs to be
	 * done via regex rather than using PHP's DOMDocument. While it is possible that this pattern could change in future
	 * WordPress updates, it is not likely. In the event that a change is made to this template by WordPress, then the pattern
	 * will need to be updated as well.
	 *
	 * @since 1.14.3
	 *
	 * @param string $template The unfiltered javascript template for themes page.
	 *
	 * @return string
	 */
	public function filter_update_message( $template ) {
		$time_pattern = '/(<span class="auto-update-time">\\n\\t\\t\\t\\t<# } else { #>\\n\\t\\t\\t\\t\\t<span class="auto-update-time hidden">\\n\\t\\t\\t\\t<# } #>\\n\\t\\t\\t\\t<br \/>)(.*\s)(\d+.*)(<\/span>)/';
		$time_replace = '\1<# if ( BgbckTheme.theme_update_strings[data.id] ) { #>\2{{ BgbckTheme.theme_update_strings[data.id] }}\4<# } else { #>\2\3\4<# } #>';

		$filtered_template = preg_replace( $time_pattern, $time_replace, $template );
		return $filtered_template;
	}

	/**
	 * Get update schedule strings.
	 *
	 * Converts the unix timestamp of each theme update.
	 * into a 'xx {days|months|years}' string for use when building
	 * the array of auto update strings to be inserted into the Themes template
	 * in WP 5.5 + . Since WP uses a JS template and React to create the themes page, we cannot
	 * pass the necessary data directly to an action hook or filter. Instead, we have to make the data
	 * available to javascript. This is used to create that, and it is localized in $this::localize_script
	 *
	 * @since 1.5.2
	 *
	 * @return array An associative array of stylesheet => string.
	 */
	public function update_schedule_strings() {
		$update_schedule_strings = array();
		foreach ( $this->themes->get() as $theme ) {
			$theme->setUpdateData();
			$time_till_update = $theme->updateData->timeTillUpdate(); // phpcs:ignore WordPress.NamingConventions
			if ( is_int( $time_till_update ) && 1 <= $time_till_update ) {
				$update_schedule_strings[ $theme->stylesheet ] = human_time_diff( $time_till_update );
			}
		}
		return $update_schedule_strings;
	}

	/**
	 * Add Auto Update Message.
	 *
	 * @since 1.4.0
	 *
	 * @global string $wp_version WordPress Version Number.
	 */
	public function add_update_message() {
		global $wp_version;
		$data = array();

		if ( empty( $this->auto_update_settings['timely-updates-enabled'] ) ) {
			return;
		}

		if ( version_compare( $wp_version, '5.4.99', 'gt' ) ) {
			$data['theme_update_strings'] = $this->update_schedule_strings();
		}
		$themes = new \Boldgrid\Library\Library\Theme\Themes();
		foreach ( $themes->get() as $theme ) {
			// If the theme does not have an update, continue.
			if ( false === $theme->hasUpdate ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName
				continue;
			}
			// If the auto updates are disabled for this theme, print the disabled message or else print the enabled message.
			if ( empty( $this->auto_update_settings['themes'][ $theme->stylesheet ] ) ) {
				$data[ $theme->stylesheet ] = array(
					'auto_update' => false,
					'message'     => $this->get_message( $theme, false ),
				);
			} else {
				$data[ $theme->stylesheet ] = array(
					'auto_update' => true,
					'message'     => $this->get_message( $theme, true ),
				);
			}
		}

		$this->data = $data;
	}

	/**
	 * Localize Script.
	 *
	 * @since 1.4.0
	 *
	 * @param array $data Data to be passed to javascript.
	 */
	public function localize_script( $data ) {
		wp_localize_script(
			'boldgrid-backup-premium-admin-timely-updates',
			'BgbckTheme',
			$data
		);
	}

	/**
	 * When Updates Occur.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function when_updates_occur() {
		$time_till_next_run = human_time_diff( wp_next_scheduled( 'wp_update_themes' ) );

		return $time_till_next_run;
	}

	/**
	 * Get Message.
	 *
	 * @since 1.4.0
	 *
	 * @param \Boldgrid\Library\Library\Theme\Theme $theme Theme Object.
	 * @param bool                                  $auto_updates_enabled Whether or not the auto updates are enabled.
	 */
	public function get_message( \Boldgrid\Library\Library\Theme\Theme $theme, $auto_updates_enabled ) {
		$theme->setUpdateData();
		$new_version          = $theme->updateData->version; //phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$default_days_setting = 0;
		$days_setting         = ! empty( $this->auto_update_settings['days'] ) ? $this->auto_update_settings['days'] : $default_days_setting;
		$update_pending       = ( $days_setting - $theme->updateData->days <= 0 ); //phpcs:ignore WordPress.NamingConventions.ValidVariableName

		/*
		* If the API was unreachable or any other error occured trying to get the data, the updateData will be filled
		* with empty data. If this is the case, the downloaded property will be equal to '000000', and we return without
		* adding anything to the update message.
		*/
		if ( '000000' === $theme->updateData->downloaded ) { //phpcs:ignore WordPress.NamingConventions.ValidVariableName
			return '';
		}

		$message = sprintf(
			// translators: 1. Opening Div Tags 2. Opening Strong Tag 3.Number of days since release. 4. Closing Strong Tag 5. Line Break Tag.
			esc_html__( '%1$sThis Update was released %2$s%3$s%4$s days ago. ', 'boldgrid-backup' ),
			/*1*/'<div style="padding-bottom:5px">',
			/*2*/'<strong>',
			/*3*/$theme->updateData->days, //phpcs:ignore WordPress.NamingConventions.ValidVariableName
			/*4*/'</strong>',
			/*5*/'<br/>'
		);

		if ( $auto_updates_enabled && $update_pending ) {
			$message .= sprintf(
				'%s %s. ',
				esc_html__( 'Total Upkeep will update this theme in', 'boldgrid_backup' ),
				$this->when_updates_occur()
			);
		} elseif ( $auto_updates_enabled && false === $update_pending ) {
			$theme->setUpdateData();
			$time_till_update = $theme->updateData->timeTillUpdate(); //phpcs:ignore WordPress.NamingConventions.ValidVariableName

			$message .= wp_kses(
				sprintf(
					// translators: 1 - An opening strong tag, 2 - number of days before a theme auto update, 3 a closing strong tag.
					__( 'Total Upkeep will Automatically update this theme in %1$s%2$s%3$s', 'boldgrid-backup' ),
					'<strong>',
					human_time_diff( $time_till_update ),
					'</strong>'
				),
				array( 'strong' => array() )
			);
		} else {
			$message .= esc_html__( 'Total Upkeep is not configured to automatically update this theme.', 'boldgrid_backup' );
		}

		$message .= '</div>';
		return $message;
	}
}
