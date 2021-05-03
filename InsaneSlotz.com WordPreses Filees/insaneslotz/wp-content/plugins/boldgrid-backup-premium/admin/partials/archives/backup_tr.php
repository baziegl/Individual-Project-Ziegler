<?php // phpcs:ignore
/**
 * Create a single <tr> for a backup on the backups page.
 *
 * @since 1.5.4
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/partials/archives
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

return sprintf( '
		<tr data-timestamp="%4$s">
			<td>
				%3$s
			</td>
			<td>
				<strong>%1$s</strong>: %2$s
			</td>
			<td colspan="2">
				<a href="" data-nonce="%6$s" data-key="%5$s" class="button button-primary amazon-s3-download">
					Download to server
				</a>
			</td>
		</tr>',
	/* 1 */ __( 'Backup', 'boldgrid-backup' ),
	/* 2 */ date( 'n/j/Y g:i A', $this->core->utility->time( $backup['Headers']['Metadata']['last_modified'] ) ),
	/* 3 */ __( 'Amazon S3', 'boldgrid-backup' ),
	/* 4 */ $backup['Headers']['Metadata']['last_modified'],
	/* 5 */ esc_attr( $backup['Key'] ),
	/* 6 */ $nonce
);
