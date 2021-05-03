/**
 * Amazon S3.
 *
 * @summary JS for Amazon S3.
 *
 * @since 1.5.4
 */

/* global ajaxurl,jQuery,boldgrid_backup_premium_admin_amazon_s3 */

var BoldGrid = BoldGrid || {};

BoldGrid.AmazonS3 = function( $ ) {
	var self = this;

	/**
	 * @summary Download an Amazon S3 file.
	 *
	 * @since 1.5.4
	 */
	self.download = function() {
		var $a = $( this ),
			data = {
				nonce: $a.attr( 'data-nonce' ),
				key: $a.attr( 'data-key' ),
				action: 'boldgrid_backup_amazon_s3_download'
			},
			spinner = '<span class="spinner inline"></span>';

		$a.after( spinner + ' ' + boldgrid_backup_premium_admin_amazon_s3.downloading + '...' ).remove();

		$.post( ajaxurl, data, function( response ) {
			location.reload();
		} ).error( function() {
			location.reload();
		} );

		return false;
	};

	/**
	 * @summary Init.
	 *
	 * @since 1.5.4
	 */
	$( function() {
		$( 'body' ).on( 'click', '.amazon-s3-download', self.download );
	} );
};

BoldGrid.AmazonS3( jQuery );
