<?php
/**
 * Amazon S3 Settings page.
 *
 * The file handles the rendering of the settings page.
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/partials/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 *
 * @param string $key       Access Key ID.
 * @param string $secret    Secret Access Key.
 * @param string $bucket_id Bucket ID.
 */

// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

defined( 'WPINC' ) || die;

?>

<form method="post">

	<h1><?php echo BOLDGRID_BACKUP_TITLE . ' - ' . __( 'Amazon S3 Settings', 'boldgrid-backup' ); ?></h1>

	<table class="form-table">
		<tr>
			<th><?php echo __( 'Access Key ID', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="key" value="<?php echo $key; ?>" required /></td>
		</tr>
		<tr>
			<th><?php echo __( 'Secret Access Key', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="secret" value="<?php echo $secret; ?>" required /></td>
		</tr>
		<tr>
			<th><?php echo __( 'Bucket ID', 'boldgrid-backup' ); ?></th>
			<td>
				<em><?php echo __( 'This Bucket ID will be created if it does not already exist.', 'boldgrid-backup' ); ?></em>
				<input type="text" name="bucket_id" value="<?php echo $bucket_id; ?>" minlength="3" maxlength="63" required />
			</td>
		</tr>
		<tr>
			<th><?php echo __( 'Retention (Number of backup archives to retain)', 'boldgrid-backup' ); ?></th>
			<td><input type="number" name="retention_count" value="<?php echo $retention_count; ?>" min="1" required /></td>
		</tr>
		<tr>
			<th><?php echo __( 'Nickname (If you would like to refer to this account as something other than Amazon S3)', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="nickname" value="<?php echo esc_attr( $nickname ); ?>" maxlength="63" /></td>
		</tr>
	</table>

	<input class="button button-primary" type="submit" name="submit" value="<?php echo __( 'Save changes', 'boldgrid-backup' ); ?>" />
	<input class="button" type="submit" name="submit" value="<?php echo __( 'Delete settings', 'boldgrid-backup' ); ?>" />

</form>
