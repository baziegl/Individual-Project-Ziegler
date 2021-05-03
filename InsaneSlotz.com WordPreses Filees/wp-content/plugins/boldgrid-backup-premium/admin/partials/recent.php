<?php
/**
 * This file renders the recent page.
 *
 * @since 1.5.4
 *
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/admin/partials
 */

ob_start();

$table = sprintf('
	<table class="wp-list-table striped fixed widefat">
	<thead>
		<tr>
			<th>%1$s</th>
			<th>%2$s</th>
			<th>%3$s</th>
		</tr>
	</thead>
	<tbody>',
	/* 1 */ __( 'File', 'boldgrid-backup' ),
	/* 2 */ __( 'Last Modified', 'boldgrid-backup' ),
	/* 3 */ __( 'Actions', 'boldgrid-backup' )
);

foreach ( $this->list as $item ) {
	$relative_path = str_replace( ABSPATH, '', $item['path'] );
	$href          = 'admin.php?page=boldgrid-backup-historical&file=' . $relative_path;

	$link = sprintf( '<a href="%1$s">%2$s</a>', $href, __( 'Find versions to restore', 'boldgrid-backup' ) );

	$table .= sprintf( '
		<tr>
			<td>%1$s</td>
			<td>%2$s</td>
			<td>%3$s</td>
		</tr>',
		/* 1 */ $relative_path,
		/* 2 */ sprintf(
			'<strong>%1$s %2$s</strong> <em>(%3$s)</em>',
			human_time_diff( $item['lastmodunix'], time() ),
			__( 'ago', 'boldgrid-backup' ),
			date( 'M j @ h:i a', $this->core->utility->time( $item['lastmodunix'] ) )
		),
		/* 3 */ $link
	);
}

$table .= '</tbody></table>';

if ( ! isset( $_GET['mins'] ) ) { // phpcs:ignore
	$table = '';
} elseif ( empty( $this->list ) ) {
	$table = '<p>' . __( 'There are no files last modified within the given time frame. Please try again.', 'boldgrid-backup' ) . '</p>';
}

printf( '
	<h2>%1$s</h2>
	<p>%2$s</p>',
	__( 'Recently Modified Files', 'boldgrid-backup' ), // phpcs:ignore
	__( 'Use this tool to find a list of all files modified recently. Enter a number of minutes below and we will find all files modified within that time frame.', 'boldgrid-backup' ) // phpcs:ignore
);

printf('
	<p><form method="get">
		<input type="hidden" name="page" value="boldgrid-backup-tools" />
		<input type="hidden" name="section" value="section_recent" />
		%4$s: <input placeholder="%3$s" type="number" name="mins" value="%2$s" size="4" required min="0" style="width:60px;" />
		<input type="submit" class="button button-primary" value="%1$s" />
	</form></p>',
	/* 1 */ __( 'Search', 'boldgrid-backup' ),
	/* 2 */ ! empty( $minutes ) ? esc_attr( $minutes ) : '',
	/* 3 */ '60',
	/* 4 */ __( 'Minutes', 'boldgrid-backup' ) // phpcs:ignore
);

echo $table; // phpcs:ignore

$output = ob_get_contents();
ob_end_clean();
return $output;
