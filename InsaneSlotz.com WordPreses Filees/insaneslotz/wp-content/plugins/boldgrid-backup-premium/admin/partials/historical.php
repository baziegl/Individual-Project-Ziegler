<?php
/**
 * This file renders the historical file page.
 *
 * @since 1.5.3
 *
 * @param string $file
 * @param array  $versions_clean
 *
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/admin/partials
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

$glossary = sprintf( '
	<ul>
		<li><strong>%1$s</strong> - %2$s</li>
		<li><strong>%3$s</strong> - %4$s</li>
		<li><strong>%5$s</strong> - %6$s</li>
	</ul>',
	$this->lang['current_file'],
	$this->lang['current_file_description'],
	$this->lang['historical_file'],
	$this->lang['historical_file_description'],
	$this->lang['archive_file'],
	$this->lang['archive_file_description']
);

wp_nonce_field( 'boldgrid_backup_remote_storage_upload' );

?>

<div class="wrap">

	<input type="hidden" name="file" value="<?php echo esc_attr( $file ); ?>" />

	<h1><?php echo __( 'Historical Versions', 'boldgrid-backup' ); ?></h1>

	<?php
	$nav = include BOLDGRID_BACKUP_PATH . '/admin/partials/boldgrid-backup-admin-nav.php';
	echo $nav;
	?>

	<?php echo $glossary; ?>

	<hr />

	<h2>File: <?php echo $file; ?></h2>

	<div id="versions_container">
		<span class="spinner inline"></span>
<?php
	// Translators: 1: Filename.
	printf( __( 'Searching for all versions of %1$s...', 'boldgrid-backup' ), esc_html( $file ) );
?>
	</div>

</div>
