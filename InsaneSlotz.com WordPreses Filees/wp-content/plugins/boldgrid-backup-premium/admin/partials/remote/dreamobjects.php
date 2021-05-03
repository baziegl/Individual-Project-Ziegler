<?php
/**
 * DreamObjects Settings page.
 *
 * The file handles the rendering of the settings page.
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/partials/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 *
 * @uses string $key       Access Key ID.
 * @uses string $secret    Secret Access Key.
 * @uses string $bucket_id Bucket ID.
 */

// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

defined( 'WPINC' ) || die;

?>

<form method="post">

	<h1><?php echo BOLDGRID_BACKUP_TITLE . ' - ' . __( 'DreamObjects Settings', 'boldgrid-backup' ); ?></h1>

	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Access Key ID', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="key" value="<?php echo esc_attr( $key ); ?>" required /></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Secret Access Key', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="secret" value="<?php echo esc_attr( $secret ); ?>" required /></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Host', 'boldgrid-backup' ); ?></th>
			<td>
				<em><?php esc_html_e( 'Be sure to use the proper protocol, such as https://', 'boldgrid-backup' ); ?></em>
				<input placeholder="https://" type="text" name="host" value="<?php echo esc_url( $host ); ?>" minlength="9" maxlength="150" required />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Bucket ID', 'boldgrid-backup' ); ?></th>
			<td>
				<em><?php esc_html_e( 'This Bucket ID will be created if it does not already exist.', 'boldgrid-backup' ); ?></em>
				<input type="text" name="bucket_id" value="<?php echo esc_attr( $bucket_id ); ?>" minlength="3" maxlength="63" required />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Retention (Number of backup archives to retain)', 'boldgrid-backup' ); ?></th>
			<td><input type="number" name="retention_count" value="<?php echo esc_attr( $retention_count ); ?>" min="1" required /></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Nickname (If you would like to refer to this account as something other than DreamObjects)', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="nickname" value="<?php echo esc_attr( $nickname ); ?>" maxlength="63" /></td>
		</tr>
	</table>

	<?php wp_nonce_field( 'save-provider-settings_' . $this->provider->get_key() ); ?>

	<input class="button button-primary" type="submit" name="save" value="<?php echo __( 'Save changes', 'boldgrid-backup' ); ?>" />
	<input class="button" type="submit" name="delete" value="<?php echo __( 'Delete settings', 'boldgrid-backup' ); ?>" />

</form>
