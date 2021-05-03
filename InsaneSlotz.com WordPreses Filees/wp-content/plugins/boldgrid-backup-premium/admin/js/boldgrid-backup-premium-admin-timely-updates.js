/**
 * File: boldgrid-backup-premium-admin-timely-updates.js
 *
 * @summary JS for the admin themes page.
 *
 * @since 1.4.0
 */

/* global ajaxurl,jQuery,settingsData*/
var BoldGrid = BoldGrid || {};

/**
 * Class: TimelyUpdates.
 *
 * @since 1.4.0
 */
class TimelyUpdates {
	/**
	 * Constructor
	 *
	 * @since 1.4.0
	 */
	constructor() {
		$ = jQuery;
		this.themes = window.BgbckTheme || {};
		$(document).on('ready', this._onReady());
	}

	/**
	 * _onReady
	 *
	 * @since 1.4.0
	 */
	_onReady() {
		var themeSlugs = [];
		for (var themeSlug in this.themes) {
			themeSlugs.push(themeSlug);
		}
		themeSlugs.forEach(function(themeSlug, index) {
			var updateMessage = this.themes[themeSlug].message;
			var contents = $(updateMessage);
			this.prependDiv(themeSlug, contents);
		}, this);
	}

	/**
	 * Prepend Div with provided contents.
	 *
	 * @since 1.4.0
	 *
	 * @param {string} themeSlug Theme Slug.
	 * @param {string} contents Contents of update message.
	 */
	async prependDiv(themeSlug, contents) {
		var totalCheckTime = 0,
			checkExist = setInterval(function() {
				/*
				 * Every 250ms we will check for the themes to have been rendered. For performance
				 * reasons, we will stop checking after 5 seconds.
				 */
				totalCheckTime += 250;
				if (totalCheckTime > 5000) {
					clearInterval(checkExist);
				}
				// When the themes have been rendered, add our upgrade notice.
				if ($('.theme-browser.rendered').length) {
					$(".theme[aria-describedby*='" + themeSlug + "'] .update-message p").after(contents);
					clearInterval(checkExist);
				}
			}, 250); // check every 250ms
	}
}

BoldGrid.TimelyUpdates = new TimelyUpdates();
