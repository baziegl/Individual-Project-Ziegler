<?php
/**
 * File: class-Boldgrid-backup-timely-auto-updates.php
 *
 * Adds Timely Auto Updates feature to settings.
 *
 * @since      1.4.0
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/includes
 * @copyright  BoldGrid
 * @version    $Id$
 * @link       https://www.boldgrid.com
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Class: Boldgrid_Backup_Premium_Timely_Auto_Updates.
 *
 * This is used to generate the controls for the Settings page.
 *
 * @since      1.5.0
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/includes
 * @author     BoldGrid.com <wpb@boldgrid.com>
 */
class Boldgrid_Backup_Premium_Timely_Auto_Updates {
	/**
	 * No Auto Updates Set.
	 *
	 * @since 1.4.0
	 *
	 * @param array $auto_update_settings Auto Update Settings.
	 * @return string
	 */
	public function no_auto_updates_set( $auto_update_settings ) {
		foreach ( $auto_update_settings['plugins'] as $plugin ) {
			if ( '1' === $plugin ) {
				return false;
			}
		}

		foreach ( $auto_update_settings['themes'] as $theme ) {
			if ( '1' === $theme ) {
				return false;
			}
		}
		$auto_updates = new Boldgrid_Backup_Admin_Auto_Updates();
		$auto_updates->auto_update_core();
		$results   = array();
		$results[] = apply_filters( 'allow_dev_auto_core_updates', false );
		$results[] = apply_filters( 'allow_major_auto_core_updates', false );
		$results[] = apply_filters( 'allow_minor_auto_core_updates', false );
		$results[] = apply_filters( 'auto_update_translation', false, wp_get_translation_updates() );

		foreach ( $results as $result ) {
			if ( true === $result ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get When To Make Auto Update Markup.
	 *
	 * @since 1.4.0
	 *
	 * @param array $auto_update_settings Auto Update Settings from DB.
	 * @return string
	 */
	public function get_markup( $auto_update_settings ) {
		// $default_days_setting is used for a placeholder in days input field if the value is not already set.
		$default_days_setting   = 7;
		$timely_updates_enabled = isset( $auto_update_settings['timely-updates-enabled'] ) ? (bool) $auto_update_settings['timely-updates-enabled'] : false;
		$timely_updates_days    = isset( $auto_update_settings['days'] ) ? $auto_update_settings['days'] : $default_days_setting;

		$when_updates_markup = '
		<table class="form-table div-table-body auto-update-settings">
			<tbody class="div-table-body">
					<p>' . esc_html__( 'Its often that when new software is released, there are bugs. Users who update right away end up finding those bugs first. It\'s a good idea to delay updating until the developers have had time to work out the kinks. ', 'boldgrid-backup' ) . '</p>
						<tr>
							<th>' . esc_html__( 'When To Perform Updates', 'boldgrid-backup' ) .
									'<span class="dashicons dashicons-editor-help" data-id="timely-updates-defer"></span>
							</th>
							<td colspan=2>
								<input id="timely-updates-disabled" type="radio" name="auto_update[timely-updates-enabled]" value="0"';

		if ( false === $timely_updates_enabled ) {
			$when_updates_markup .= ' checked';
		}

		$when_updates_markup .= '/>' . esc_html__( 'Perform updates immediately when they are released.', 'boldgrid-backup' ) . '
						</td>
					</tr>
					<tr class="table-help hide-help" data-id="timely-updates-defer">
					<td colspan=4>
						<p>' . esc_html__( 'Select the number of days you wish to wait after an update is released before updating plugins or themes.', 'boldgrid-backup' ) . '</p>
					</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input id="timely-updates-enabled" type="radio" name="auto_update[timely-updates-enabled]" value="1"';

		if ( true === $timely_updates_enabled ) {
			$when_updates_markup .= ' checked';
		}

		$when_updates_markup .= '/>' . esc_html__( 'Perform updates ', 'boldgrid-backup' ) .
							'	<input id="timely-updates-days-hidden" type="hidden" name="auto_update[days]" value="' . $timely_updates_days . '">
								<input id="timely-updates-days" type="number" name="auto_update[days]" min="0" max="99" value="' . $timely_updates_days . '" />';
		$when_updates_markup .= '<span>&nbsp;' . esc_html__( 'Days since update was released' ) . '</span>
							</td>
						</tr>

					</tbody>
				</table>';

		if ( $this->no_auto_updates_set( $auto_update_settings ) ) {
			$when_updates_markup .= '<div class="notice notice-warning inline">
			<p><strong>Timely Auto Updates are enabled, but you have not selected any themes or plugins to Auto Update.</strong></p>
			</div>';
		}

		$when_updates_markup .= '</div></div>';

		return $when_updates_markup;
	}
}

