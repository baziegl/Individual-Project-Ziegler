<?php
/**
 * Help text for the Plugin Editor.
 *
 * @since 1.5.3
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/partials
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

return '<p>' . sprintf(
	// translators: 1: HTML opening strong tag, 2: HTML closing strong tag, 3: Plugin title.
	__(
		'The %1$s%3$s%2$s plugin offers two additional tools below, %1$sSave a copy before updating%2$s and %1$sFind a version to restore%2$s. If you want to make a backup of this file before saving any changes, click the %1$sSave a copy%2$s button. If you want to find or restore any copies previously saved or included in a backup, click %1$sFind a version%2$s.',
		'boldgrid-backup'
	),
	'<strong>',
	'</strong>',
	BOLDGRID_BACKUP_PREMIUM_TITLE
) . '</p>';
