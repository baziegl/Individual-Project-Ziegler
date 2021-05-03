<?php
/**
 * This file renders one <tr> of the "all versions of this file" table.
 *
 * @since 1.5.3
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/partials/historical
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

// Reset values and force them to be created below.
$last_modified_span   = '';
$last_modified_utc    = '';
$archive_created_span = '';

$this->core->time->init( $version['lastmodunix'] );
$last_modified_span = $this->core->time->get_span( 'M j @ h:i a' );
$last_modified_utc  = $this->core->time->utc_time;

switch ( $version['type'] ) {
	case 'current':
		$type        = $this->lang['current_file'];
		$row_actions = '';
		break;
	case 'historical':
		$type        = $this->lang['historical_file'];
		$row_actions = sprintf( '<a href="" class="restore-historical" data-file-version="%2$s">%1$s</a>', __( 'Restore previous version', 'boldgrid-backup' ), $version['name'] );
		break;
	case 'in_archives':
		$type        = $this->lang['archive_file'];
		$row_actions = sprintf(
			'<a href="" class="restore">%1$s</a> | <a href="%2$s">%3$s</a>',
			__( 'Restore from archive', 'boldgrid-backup' ),
			$this->core->archive_details->get_url( basename( $version['archive_filepath'] ) ),
			__( 'View details', 'boldgrid-backup' )
		);

		$this->core->time->init( $version['created'] );
		$archive_created_span = $this->core->time->get_span( 'M j @ h:i a' );

		break;
	default:
		$type        = __( 'Unknown', 'boldgrid-backup' );
		$row_actions = '';
}



$version_td = '<td></td>';
$tr_class   = '';

if ( $last_modified_utc !== $last_modified ) {
	$version_number++;
	$last_modified = $last_modified_utc;
	$tr_class      = 'top';

	$version_td = sprintf( '<td><strong>%1$s</strong>.</td>', $version_number );
}

return sprintf( '
				<tr data-filename="%5$s" class="%8$s">
					%6$s
					<td>
						<strong>%1$s</strong>
						%7$s
						<div class="row-actions">%4$s</div>
					</td>
					<td>%2$s</td>
					<td>%3$s</td>
				</tr>',
	// 1. Type.
	$type,
	// 2. Last modified -- date( 'M j, Y h:i:s a', $version['lastmodunix'] ).
	sprintf(
		'<strong>%1$s %3$s</strong> <em>(%2$s)</em>',
		human_time_diff( $last_modified_utc, time() ),
		$last_modified_span,
		__( 'ago', 'boldgrid-backup' )
	),
	// 3. Size.
	Boldgrid_Backup_Admin_Utility::bytes_to_human( $version['size'] ),
	// 4. Row actions (restore/etc).
	$row_actions,
	// 5. Path to zip file.
	! empty( $version['archive_filepath'] ) ? basename( $version['archive_filepath'] ) : '',
	// 6. The version number.
	$version_td,
	// 7. Created.
	'in_archives' === $version['type'] ? sprintf(
		'<br />%4$s %1$s %3$s <em>(%2$s)</em>',
		human_time_diff( $version['created'], time() ),
		$archive_created_span,
		__( 'ago', 'boldgrid-backup' ),
	__( 'Archive created', 'boldgrid-backukp' ) ) : '',
	// 8. Class applied to the tr.
	$tr_class
);
