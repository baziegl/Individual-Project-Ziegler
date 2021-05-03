/**
 * File: boldgrid-backup-premium-admin-settings.js
 *
 * @summary JS for the admin settings page.
 *
 * @since 1.3.0
 */

/* global ajaxurl,jQuery,settingsData */

var BoldGrid = BoldGrid || {};

BoldGrid.BGBPSettings = function( $ ) {
	var self = this;

	/**
	 * Add settings markup to the Backup Security section.
	 *
	 * @since 1.3.0
	 */
	self.addSettingsMarkup = function() {
		var markup =
			'<tr><th>' +
			settingsData.lang.encryptionToken +
			'</th><td><input id="crypt-token" name="crypt_token" type="password" value="' +
			settingsData.cryptToken +
			'" /> <button class="button" id="crypt-token-reveal">' +
			settingsData.lang.revealToken +
			'</button> <input type="hidden" name="delete_crypt_token" value="0" />' +
			'<button class="button" id="delete-crypt-token">' +
			settingsData.lang.deleteToken +
			'</button><br /><br /><div class="notice notice-info inline"><p>' +
			settingsData.lang.addTokenText +
			'</p></div></td></tr>';

		$( 'div#section_security' )
			.find( '.form-table tr:last' )
			.after( markup );

		self.toggleToken( $( 'input[name="encrypt_db"]:checked' ) );

		$( 'p.help[data-id="backup_security"]' ).append( settingsData.lang.addHelpText );
	};

	/**
	 * Toggle the disable attribute for the encyption token input field.
	 *
	 * @since 1.3.0
	 */
	self.toggleToken = function( $this ) {
		var $tokenInputs = $( 'input#crypt-token' )
			.parent()
			.children();

		$tokenInputs.prop( 'disabled', '0' === $this.val() );
	};

	/**
	 * Reveal the encyption token.
	 *
	 * @since 1.3.0
	 */
	self.revealToken = function() {
		var $this = $( this ),
			markup =
				'<input id="crypt-token" name="crypt_token" type="text" value="' +
				settingsData.cryptToken +
				'" /> <button class="button" id="token-copy-button"' +
				' data-clipboard-text="' +
				settingsData.cryptToken +
				'">' +
				settingsData.lang.copyText +
				'</button>';

		$this
			.parent()
			.find( 'input#crypt-token' )
			.remove();

		$this.replaceWith( markup );

		new ClipboardJS( '#token-copy-button' );
	};

	/**
	 * @summary Update the download link copy button after clicking, and then reset after 3 seconds.
	 *
	 * @since 1.3.0
	 */
	self.updateCopyText = function( e ) {
		var $this = $( this ),
			oldHtml = $this.html();

		e.preventDefault();

		$this.prop( 'disabled', true );
		$this.html( settingsData.lang.copiedText );

		setTimeout( function() {
			$this.html( oldHtml );
			$this.prop( 'disabled', false );
		}, 3000 );
	};

	/**
	 * Delete the encyption token.
	 *
	 * @since 1.3.0
	 */
	self.deleteToken = function( e ) {
		var confirmResponse,
			$this = $( this ),
			$parent = $this.parent();

		confirmResponse = confirm( settingsData.lang.deleteConfirmText );

		if ( ! confirmResponse ) {
			return false;
		}

		e.preventDefault();

		$parent.find( 'input[name=delete_crypt_token]' ).val( '1' );
		$parent.find( '#crypt-token-reveal' ).remove();
		$parent.find( '#crypt-token' ).remove();
		$parent.find( '#token-copy-button' ).remove();
		$this.html( 'Click "Save Changes" to complete deletion' ).prop( 'disabled', true );
	};

	/**
	 * Init.
	 *
	 * @since 1.3.0
	 */
	$( function() {
		self.addSettingsMarkup();

		$body = $( 'body' );

		$body.on( 'click', 'input[name="encrypt_db"]:checked', function() {
			self.toggleToken( $( this ) );
		} );
		$body.on( 'click', 'button#crypt-token-reveal', self.revealToken );
		$body.on( 'click', 'button#token-copy-button', self.updateCopyText );
		$body.on( 'click', 'button#delete-crypt-token', self.deleteToken );
	} );
};

BoldGrid.BGBPSettings( jQuery );
