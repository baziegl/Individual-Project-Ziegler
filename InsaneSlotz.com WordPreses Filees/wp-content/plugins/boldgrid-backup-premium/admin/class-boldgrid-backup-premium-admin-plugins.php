<?php
/**
 * File: class-boldgrid-backup-premium-admin-plugins.php
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
 * Class: Boldgrid_Backup_Premium_Admin_Plugins.
 *
 * This is a generic class designed to help manage how this plugin behaves within the scope of.
 * "WordPress Dashboard > Plugins > *".
 *
 * @since 1.4.0
 */
class Boldgrid_Backup_Premium_Admin_Plugins {
	/**
	 * Add Auto Update Message.
	 *
	 * @since 1.4.0
	 */
	public function add_update_message() {
		$core                 = apply_filters( 'boldgrid_backup_get_core', null );
		$settings             = $core->settings->get_settings();
		$plugins              = new \Boldgrid\Library\Library\Plugin\Plugins();
		$this->active_plugins = $plugins->getAllPlugins();
		foreach ( $this->active_plugins as $plugin ) {
			add_action( 'in_plugin_update_message-' . $plugin->getFile(), array( $this, 'print_update_message' ), 10, 2 );
		}
	}

	/**
	 * Filters Auto Update Message
	 *
	 * In WordPress 5.5, the Auto Update message displays how many hours
	 * till the next update. We must filter that, and apply our message when
	 * Timely Auto Updates is enabled.
	 *
	 * @since 1.5.2
	 *
	 * @param string $html Original HTML Markup.
	 * @param string $plugin_file Path to main plugin file.
	 * @param array  $plugin_data An array of plugin data.
	 */
	public function filter_update_message( $html, $plugin_file, $plugin_data ) {
		$core                 = apply_filters( 'boldgrid_backup_get_core', null );
		$auto_update_settings = $core->settings->get_setting( 'auto_update' );
		/*
		 * If a plugin is installed for the first time before a setting is created for it in Total Upkeep, it can
		 * generate an undefined index notice, so this has been changed to check if it is empty rather than just check
		 * if it is true or false
		 */
		$auto_updates_enabled = empty( $auto_update_settings['plugins'][ $plugin_file ] ) ? '0' : '1';

		$plugin = \Boldgrid\Library\Library\Plugin\Factory::create( $plugin_file );

		// If the plugin has an update available modify the auto-update-time class.
		if ( $plugin->hasUpdate() ) {

			$plugin->setUpdateData();
			$time_till_update = $plugin->updateData->timeTillUpdate(); //phpcs:ignore WordPress.NamingConventions.ValidVariableName

			$update_schedule_string = '';

			/*
			 * If the plugin is listed in the auto_update_plugins option then auto updates are enabled for that plugin.
			 * We have to test that $time_till_update is an integer because the timeTillUpdate() can return false in some cases.
			 * If the $time_till_update is greater than 0, then display the human readable time difference,
			 * otherwise display the standard wp_get_auto_update_message().
			*/
			if ( is_int( $time_till_update ) && 0 < $time_till_update ) {
				$update_schedule_string = sprintf(
					'%s %s.',
					esc_html__( 'Automatic update scheduled in', 'boldgrid-backup' ),
					human_time_diff( $time_till_update )
				);

				/*
				 * This pattern must be able to apply both to the "auto-update-time" and the "auto-update-time hidden" divs or else it will not work when the
				 * auto updates are not enabled for that plugin on page load, but then enabled after page load.
				 */
				$html = preg_replace( '/(<div class="auto-update-time.*">)(.*)(<\/div>)/', '$1' . $update_schedule_string . '$3', $html );
			}
		}

		return $html;
	}

	/**
	 * Prints Update Message.
	 *
	 * @since 1.4.0
	 *
	 * @param array $data Data sent to callback.
	 * @param array $response Response sent to callback.
	 *
	 * @global string $wp_version WordPress Version Number.
	 */
	public function print_update_message( $data, $response ) {
		global $wp_version;
		$core   = apply_filters( 'boldgrid_backup_get_core', null );
		$plugin = apply_filters( 'boldgrid_backup_get_plugin', $this->active_plugins, $data['slug'] );

		$settings     = get_site_option( 'boldgrid_backup_settings', array() );
		$default_days = 0;

		$is_wp55 = version_compare( $wp_version, '5.4.99', 'gt' );

		// If 'auto_update' is not set, then the user has neither enabled or disabled the feature yet, So add a link to settings.
		if ( ! isset( $settings['auto_update'] ) ) {
			printf(
				'&nbsp;<a href="%s">%s</a>',
				esc_url( $core->settings->get_settings_url( 'section_auto_updates' ) ),
				esc_html__( 'View Update Settings', 'boldgrid-backup' )
			);
			return;
		}
		// In cases where there is a WP_Error returned when the plugin is checked for updateData, return.
		if ( is_wp_error( $plugin ) ) {
			return;
		}

		$plugin->setUpdateData( true );

		$version     = $plugin->updateData->version; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$days        = $plugin->updateData->days; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$third_party = $plugin->updateData->thirdParty; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$api_called  = $plugin->updateData->apiFetchTime; // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		$auto_update_settings   = $settings['auto_update'];
		$plugin_updates_enabled = false;
		if ( isset( $auto_update_settings['plugins'][ $plugin->getFile() ] ) ) {
			$plugin_updates_enabled = (bool) $auto_update_settings['plugins'][ $plugin->getFile() ];
		} else {
			$plugin_updates_enabled = (bool) $auto_update_settings['plugins']['default'];
		}

		$days_setting    = ! empty( $auto_update_settings['days'] ) ? $auto_update_settings['days'] : $default_days;
		$updates_enabled = ! empty( $auto_update_settings['timely-updates-enabled'] ) ? $auto_update_settings['timely-updates-enabled'] : '0';

		// If the auto updates are disabled, don't print anything.
		if ( '0' === $updates_enabled ) {
			return;
		}

		// if the update is old enough, then the plugin would have an 'update_pending', otherwise it is 'deferred'.
		$plugin_updates_status = ( $days_setting - $days <= 0 ) ? 'update_pending' : 'deferred';

		$plugin_updates_status = ( true === $third_party ) ? 'third_party' : $plugin_updates_status;

		// if auto updates are disabled for this plugin, change status to 'disabled', otherwise it stays 'update_pending' or 'deferred'.
		$plugin_updates_status = ( true === $plugin_updates_enabled ) ? $plugin_updates_status : 'disabled';

		$markup = '';

		switch ( $plugin_updates_status ) {
			case 'disabled':
				// Advise user that updates for this plugin are disabled, and provide link to settings.
				$markup = sprintf(
					'<br/><span class="bg-auto-update dashicons dashicons-warning"></span>%s',
					esc_html__( 'Automatic updates for this plugin are not enabled.', 'boldgrid-backup' )
				);
				break;
			case 'deferred':
				// Advise user in how many days updates will occur.
				$markup = sprintf(
					'<br/><span class="bg-auto-update dashicons dashicons-yes"></span>%s <strong>%s</strong> %s <strong>%s</strong> %s',
					esc_html__( 'Version', 'boldgrid-backup' ),
					esc_html( $version ),
					esc_html__( 'was released', 'boldgrid-backup' ),
					esc_html( $days ),
					esc_html__( 'days ago.', 'boldgrid-backup' ),
					esc_html( $days_setting - $days )
				);
				// In wp5.5 and newer, the below message is handled by a different filter, but in wp < 5.5 it needs to be added here.
				if ( ! $is_wp55 ) {
					$markup .= wp_kses(
						sprintf(
							// translators: 1 an opening strong tag, 2 its closing strong tag, 3 the number of days until an auto update.
							__( 'Total Upkeep will Automatically update this plugin in %1$s%3$s%2$s days.', 'boldgrid-backup' ),
							'<strong>',
							'</strong>',
							( $days_setting - $days )
						),
						array( 'strong' => array() )
					);
				}
				break;
			case 'update_pending':
				// If the update is pending, then determine what time the next plugin update cron even runs, do the math, and advise user approximately when it will update.
				$markup = sprintf(
					'<br/><span class="bg-auto-update dashicons dashicons-yes"></span>%s <strong>%s</strong> %s <strong>%s</strong> %s.',
					esc_html__( 'Version', 'boldgrid-backup' ),
					esc_html( $version ),
					esc_html__( 'was released', 'boldgrid-backup' ),
					esc_html( $days ),
					esc_html__( 'days ago', 'boldgrid-backup' )
				);
				// In wp5.5 and newer, the below message is handled by a different filter, but in wp < 5.5 it needs to be added here.
				if ( ! $is_wp55 ) {
					$markup .= sprintf(
						' %s %s.',
						esc_html__( 'Total Upkeep will update this plugin within the next ', 'boldrid-backup' ),
						esc_html( $this->when_updates_occur() )
					);
				}

				break;
			case 'third_party':
				// If the update is pending, then determine what time the next plugin update cron even runs, do the math, and advise user when it will update.
				$markup = sprintf(
					'<br/><span class="bg-auto-update dashicons dashicons-yes"></span>%s <strong>%s</strong> %s.',
					esc_html__( 'Version', 'boldgrid-backup' ),
					esc_html( $version ),
					esc_html__( 'is now available', 'boldgrid-backup' )
				);
				// In wp5.5 and newer, the below message is handled by a different filter, but in wp < 5.5 it needs to be added here.
				if ( ! $is_wp55 ) {
					$markup .= sprintf(
						' %s %s.',
						esc_html__( 'Total Upkeep will update this plugin within the next ', 'boldrid-backup' ),
						esc_html( $this->when_updates_occur() )
					);
				}
				break;
			default:
				break;
		}

		if ( $markup ) {
			$full_markup = $markup . sprintf(
				// translators: 1 Update Settings URL, 2 View Update Settings Text.
				'&nbsp;<a href="%1$s">%2$s</a>',
				/* 1 */esc_url( $core->settings->get_settings_url( 'section_auto_updates' ) ),
				/* 2 */esc_html__( 'View Update Settings', 'boldgrid-backup' )
			);
			echo $full_markup; // phpcs:ignore WordPress.XSS.EscapeOutput
		}

		return $markup;
	}

	/**
	 * When Updates Occur.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function when_updates_occur() {
		$next_run = new DateTime();
		$next_run->setTimestamp( wp_next_scheduled( 'wp_update_plugins' ) );
		$time_till_next_run = $next_run->diff( new DateTime() );
		switch ( $time_till_next_run->h ) {
			case 0:
				return $time_till_next_run->i . ' minutes';
			case 1:
				return 'one hour';
			default:
				return $time_till_next_run->h . ' hours';
		}
	}
}
