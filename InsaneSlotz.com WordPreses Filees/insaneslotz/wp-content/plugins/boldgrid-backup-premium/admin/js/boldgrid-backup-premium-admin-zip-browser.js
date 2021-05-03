/**
 * Browser.
 *
 * @summary JS for all admin backup pages.
 *
 * @since 1.5.3
 */

/* global ajaxurl,jQuery,boldgrid_backup_premium_zip_browser */

var BoldGrid = BoldGrid || {};

BoldGrid.PremiumZipBrowser = function( $ ) {
	var self = this;

	self.onClickRestore = function() {
		var $a = $( this ),
			$tr = $a.closest( 'tr' ),
			$fileTr = $tr.prev(),
			data = {
				action: 'boldgrid_backup_restore_single_file',
				filename: $( '#filename' ).val(),
				security: $( '#_wpnonce' ).val(),
				file: $fileTr.attr( 'data-dir' )
			},
			$restoring = $(
				'<span class="restoring"><span class="spinner inline"></span> ' +
					boldgrid_backup_premium_zip_browser.restoring +
					'...</span>'
			);

		$a.after( $restoring ).remove();

		$.post( ajaxurl, data, function( response ) {
			if ( response.data !== undefined ) {
				$restoring.html( response.data );
			} else {
				$restoring.html( boldgrid_backup_premium_zip_browser.unknownError );
			}
		} ).error( function() {
			$restoring.html( boldgrid_backup_premium_zip_browser.unknownError );
		} );
	};

	/**
	 * Init.
	 */
	$( function() {
		$( 'body' ).on( 'click', '.file-actions a.restore', self.onClickRestore );
	} );
};

BoldGrid.PremiumZipBrowser( jQuery );
