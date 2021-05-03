<?php // phpcs:ignore
/**
 * Google Drive Settings page.
 *
 * The file handles the rendering of the settings page.
 *
 * @since 1.1.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/partials/remote
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 *
 * @param string $folder_name
 * @param int    $retention_count
 * @param string $nickname
 *
 * phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
 */

defined( 'WPINC' ) || die;

// Determine whether "name" or "id" folder type needs to be selected.
$id_selected   = 'id' === $folder_type ? 'selected' : '';
$name_selected = 'name' === $folder_type ? 'selected' : '';

// Used with wp_kses.
$allowed_html = array(
	'strong' => array(),
	'em'     => array(),
	'br'     => array(),
);

?>

<form method="post">
	<?php wp_nonce_field( 'bgbkup-gd-settings', 'gd_auth' ); ?>

	<h1><?php echo BOLDGRID_BACKUP_TITLE . ' - ' . __( 'Google Drive Settings', 'boldgrid-backup' ); ?></h1>

	<table class="form-table">
		<tr>
			<td colspan="2">
				<strong><?php esc_html_e( 'Storage Location' ); ?> <span class="dashicons dashicons-editor-help" data-id="folder_type"></span></strong>

				<!-- Help text for "Storage Location". -->
				<div class="help" data-id="folder_type">
					<p>
						<?php esc_html_e( 'Where should we upload your backups to within Google Drive?', 'boldgrid-backup' ); ?>
					</p>

					<p>
						<?php
						echo wp_kses(
							__( 'If you choose <strong>Folder Name</strong>, your backups will be stored on Google Drive in:<br />
							<em>/Total Upkeep/<strong>Folder Name</strong>/backup.zip</em><br />
							This folder will be created if it doesn\'t exist.', 'boldgrid-backup' ),
							$allowed_html
						);
						?>
					</p>

					<p>
						<?php
						echo wp_kses(
							__( 'Choose <strong>Folder Id</strong> if you have a specific folder you want to upload to.
							For example, in your browser go to the Google Drive folder.
							If the url is <em>https://drive.google.com/drive/u/0/folders/<strong>abc123</strong></em>, then your backup id is <strong>abc123</strong>.
							If this is a shared folder, your user must have permission to delete the file, otherwise retention / deleting backups will not work.', 'boldgrid-backup' ),
							$allowed_html
						);
						?>
					</p>
				</div>
			</td>
		</tr>
		<tr>
			<td style="display: inline-block; width: 33%;">
				<select name="folder_type">
					<option value="id" <?php echo $id_selected; ?>>
						<?php esc_html_e( 'Folder ID', 'boldgrid-backup' ); ?>
					</option>
					<option value="name" <?php echo $name_selected; ?>>
						<?php esc_html_e( 'Folder Name', 'boldgrid-backup' ); ?>
					</option>
				</select>
			</td>
			<td style="display:inline-block; width:calc(67% - 15px); padding-left:15px;">
				<input type="text" name="folder_name" value="<?php echo esc_attr( $folder_name ); ?>" min="1" required />
			</td>
		</tr>
		<tr>
			<th><?php echo __( 'Retention (Number of backup archives to retain)', 'boldgrid-backup' ); ?></th>
			<td><input type="number" name="retention_count" value="<?php echo esc_attr( $retention_count ); ?>" min="1" required /></td>
		</tr>
		<tr>
			<th><?php echo __( 'Nickname (If you would like to refer to this account as something other than Google Drive)', 'boldgrid-backup' ); ?></th>
			<td><input type="text" name="nickname" value="<?php echo esc_attr( $nickname ); ?>" maxlength="63" /></td>
		</tr>
	</table>

	<input class="button button-primary" type="submit" name="save_settings" value="<?php echo __( 'Save changes', 'boldgrid-backup' ); ?>" />
	<input class="button" type="submit" name="delete_settings" value="<?php echo __( 'Delete settings', 'boldgrid-backup' ); ?>" />
</form>
