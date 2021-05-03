/**
 * Plugin Editor.
 *
 * @summary JS for all admin plugin editor.
 *
 * @since 1.5.3
 */

/* global ajaxurl,jQuery,boldgrid_backup_premium_admin_plugin_editor */

var BoldGrid = BoldGrid || {};

BoldGrid.PluginEditor = function( $ ) {
	var self = this,
		$spinner,
		filepath,
		relFilepath,
		$editorNotices,
		noticeSuccess = '<div class="notice inline notice-success is-dismissible">',
		noticeError = '<div class="notice inline notice-error is-dismissible">',
		closeButton =
			'<button type="button" class="boldgrid-backup notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>';

	/**
	 * @summary Load our content into the page.
	 *
	 * @since 1.5.3
	 */
	self.loadContent = function() {
		var link = 'admin.php?page=boldgrid-backup-historical&file=' + relFilepath;

		$spinner
			.before(
				' <button class="button button-secondary save-copy">' +
					boldgrid_backup_premium_admin_plugin_editor.save_a_copy +
					'</button>'
			)
			.before(
				' <a class="button button-secondary" href="' +
					link +
					'">' +
					boldgrid_backup_premium_admin_plugin_editor.find_a_version +
					'</button>'
			);

		$editorNotices.after( boldgrid_backup_premium_admin_plugin_editor.help );
	};

	/**
	 * @summary Take action when the user clicks copy.
	 *
	 * @since 1.5.3
	 */
	self.onClickCopy = function() {
		var data = {
				action: 'boldgrid_backup_save_copy',
				file: relFilepath,
				pluginFile: filepath,
				nonce: $( '#nonce' ).val()
			},
			errorCallback,
			successCallback;

		errorCallback = function() {
			$editorNotices.append(
				noticeError +
					'<p>' +
					boldgrid_backup_premium_admin_plugin_editor.error_saving +
					'</p>' +
					closeButton +
					'</div>'
			);
			$spinner.removeClass( 'is-active' );
		};

		successCallback = function() {
			$editorNotices.append(
				noticeSuccess +
					'<p>' +
					boldgrid_backup_premium_admin_plugin_editor.success_saving +
					'</p>' +
					closeButton +
					'</div>'
			);
			$spinner.removeClass( 'is-active' );
		};

		$spinner.addClass( 'is-active' );

		$.post( ajaxurl, data, function( response ) {
			var success = response.success !== undefined && true === response.success;

			if ( success ) {
				successCallback();
			} else {
				errorCallback();
			}
		} ).error( errorCallback );

		return false;
	};

	/**
	 * Init.
	 *
	 * @since 1.5.3
	 */
	$( function() {
		$spinner = $( '.spinner' );
		filepath = $( 'input[name="file"]' ).val();
		relFilepath = boldgrid_backup_premium_admin_plugin_editor.rel_plugin_path + filepath;
		$editorNotices = $( '.editor-notices' );

		self.loadContent();

		$( 'body' ).on( 'click', '.save-copy', self.onClickCopy );
		$( 'body' ).on( 'click', '.boldgrid-backup.notice-dismiss', function() {
			$( this )
				.closest( '.notice' )
				.slideUp();
		} );
	} );
};

BoldGrid.PluginEditor( jQuery );
