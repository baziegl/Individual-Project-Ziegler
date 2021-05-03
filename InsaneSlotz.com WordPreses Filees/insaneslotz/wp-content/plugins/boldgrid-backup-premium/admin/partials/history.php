<?php
/**
 * This file renders the history page.
 *
 * @since 1.5.3
 *
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/admin/partials
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

// phpcs:disable Squiz.PHP.NonExecutableCode

defined( 'WPINC' ) ?: die;

ob_start();

printf( '<h2>%1$s</h2>', __( 'History', 'boldgrid-backup' ) ); // phpcs:ignore

echo '<p>' .
	sprintf(
		// translators: 1: Plugin title encapsulated with HTML strong tags.
		esc_html__(
			'%1$s keeps a running history of changes to your site (such as plugin updates, backups created, etc). This page shows your history log.',
			'boldgrid-backup'
		),
		'<strong>' . esc_html( BOLDGRID_BACKUP_PREMIUM_TITLE ) . '</strong>'
	) . '</p>';
?>

<table class="wp-list-table striped fixed widefat">

	<thead>
		<tr>
			<th style="width:150px;">Date</th>
			<th class="column-date">User</th>
			<th>Action</th>
		</tr>
	<thead>

	<tbody>

<?php

$history = array_reverse( $history );

foreach ( $history as $item ) {
	$time      = time();
	$time      = $this->core->utility->time( $time );
	$timestamp = $this->core->utility->time( $item['timestamp'] );

	$user = is_numeric( $item['user_id'] ) ? get_userdata( $item['user_id'] ) : null;

	printf( '
		<tr>
			<td>
				<strong>%1$s</strong><br />
				<em>%2$s ago</em>
			</td>
			<td>%3$s</td>
			<td>%4$s</td>
		</tr>',
		date( 'Y-m-d h:i:s a', $timestamp ),
		human_time_diff( $timestamp, $time ),
		is_object( $user ) && 'WP_User' === get_class( $user ) ? $user->display_name : $item['user_id'], // phpcs:ignore
		esc_html( $item['message'] )
	);
}

?>

	</tbody>

</table>

<?php

if ( empty( $history ) ) {
	printf( '<p>%1$s</p>', __( 'No history to display.', 'boldgrid-backup' ) ); // phpcs:ignore
}

?>

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
