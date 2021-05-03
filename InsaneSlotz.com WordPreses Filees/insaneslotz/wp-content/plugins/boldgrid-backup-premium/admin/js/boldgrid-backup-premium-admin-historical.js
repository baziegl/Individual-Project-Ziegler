/**
 * Historical.
 *
 * @summary JS for all admin historical pages.
 *
 * @since 1.5.3
 */

/* global ajaxurl,jQuery,boldgrid_backup_premium_admin_historical */

var BoldGrid = BoldGrid || {};

BoldGrid.Historical = function( $ ) {
	var self = this,
		reloadingTable =
			'<br /><span class="spinner inline"></span>' +
			boldgrid_backup_premium_admin_historical.reloading_table +
			'...',
		iconWarning = boldgrid_backup_premium_admin_historical.icon_warning + '';

	/**
	 * @summary Ajax and load the table of file versions.
	 *
	 * @since 1.5.3
	 */
	self.loadVersions = function() {
		var data = {
				action: 'boldgrid_backup_get_historical_versions',
				security: $( '#_wpnonce' ).val(),
				file: $( '[name="file"]' ).val()
			},
			$table = $( '#versions_container' );

		$.post( ajaxurl, data, function( response ) {
			if ( response.data !== undefined ) {
				$table.html( response.data );
			} else {
				$table.html( iconWarning + boldgrid_backup_premium_admin_historical.unknown_error_load );
			}
		} ).error( function() {
			$table.html( iconWarning + boldgrid_backup_premium_admin_historical.unknown_error_load );
		} );
	};

	/**
	 * @summary Action to take when restoring from a zip.
	 *
	 * @since 1.5.3
	 */
	self.onClickRestore = function() {
		var $a = $( this ),
			$tr = $a.closest( 'tr' ),
			data = {
				action: 'boldgrid_backup_restore_single_file',
				filename: $tr.attr( 'data-filename' ),
				security: $( '#_wpnonce' ).val(),
				file: $( '[name="file"]' ).val()
			},
			$restoring = $(
				'<span class="restoring"><span class="spinner inline"></span> ' +
					boldgrid_backup_premium_admin_historical.restoring +
					'...</span>'
			);

		$a.parent( 'div' )
			.removeClass( 'row-actions' )
			.end()
			.after( $restoring )
			.remove();

		$.post( ajaxurl, data, function( response ) {
			if ( response.data !== undefined ) {
				$restoring.html( response.data + reloadingTable );
				self.loadVersions();
			} else {
				$restoring.html(
					iconWarning + boldgrid_backup_premium_admin_historical.unknown_error_restore
				);
			}
		} ).error( function() {
			$restoring.html( iconWarning + boldgrid_backup_premium_admin_historical.unknown_error_restore );
		} );

		return false;
	};

	/**
	 * @summary Action to take when user clicks to restore a historic version.
	 *
	 * @since 1.5.3
	 */
	self.onClickRestoreHistorical = function() {
		var $a = $( this ),
			fileVersion = $a.attr( 'data-file-version' ),
			data = {
				action: 'boldgrid_backup_restore_historical',
				file_version: fileVersion,
				security: $( '#_wpnonce' ).val(),
				file: $( '[name="file"]' ).val()
			},
			$restoring = $(
				'<span class="restoring"><span class="spinner inline"></span> ' +
					boldgrid_backup_premium_admin_historical.restoring +
					'...</span>'
			);

		$a.parent( 'div' )
			.removeClass( 'row-actions' )
			.end()
			.after( $restoring )
			.remove();

		$.post( ajaxurl, data, function( response ) {
			if ( response.data !== undefined ) {
				$restoring.html( response.data + reloadingTable );
				self.loadVersions();
			} else {
				$restoring.html(
					iconWarning + boldgrid_backup_premium_admin_historical.unknown_error_restore
				);
			}
		} ).error( function() {
			$restoring.html( iconWarning + boldgrid_backup_premium_admin_historical.unknown_error_restore );
		} );

		return false;
	};

	/**
	 * Init.
	 */
	$( function() {
		$( 'body' ).on( 'click', 'a.restore', self.onClickRestore );
		$( 'body' ).on( 'click', 'a.restore-historical', self.onClickRestoreHistorical );

		self.loadVersions();
	} );
};

BoldGrid.Historical( jQuery );
