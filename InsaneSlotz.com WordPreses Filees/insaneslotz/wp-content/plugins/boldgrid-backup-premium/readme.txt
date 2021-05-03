=== Total Upkeep Premium – WordPress Backup Plugin plus Restore & Migrate by BoldGrid ===
Contributors: boldgrid, imh_brad, joemoto, rramo012, timph
Tags: backup, cloud backup, database backup, restore, wordpress backup
Requires at least: 4.4
Tested up to: 5.7
Requires PHP: 5.4
Stable tag: 1.5.8

Premium extension for the Total Upkeep plugin.

== Description ==

Premium extension for the Total Upkeep plugin.

== Requirements ==

== Installation ==

= Minimum Requirements =
* PHP 5.4 or higher

= Manually =
1. Upload the entire boldgrid-backup-premium folder to the /wp-content/plugins/ directory.
1. Activate the plugin through the Plugins menu in WordPress.

== Changelog ==

= 1.5.8 =

Release date: March 11th, 2021

* Update: Updated dependencies, including aws-php-sdk from version 2 to 3.
* Update: Adding google-drive-download.og

= 1.5.7 =

Release date: February 16th, 2021

* Bug fix: Fixed confings type in update class.

= 1.5.6 =

Release date: December 8th, 2020

* Bug fix: Various JQMIGRATE warnings fixed.

= 1.5.5 =

Release date: December 2nd, 2020

* Bug fix: Fixed invalid nonce when downloading Amazon S3 backups.

= 1.5.4 =

Release date: October 15th, 2020

* Bug fix: Fixed issue with installing plugins.

= 1.5.3 =

Release date: October 13th, 2020

* Update: Added additional logging for Google Drive.

= 1.5.2 =

Release date: August 12th, 2020

* Update: Updated Timely Auto Updates to work with the new WordPress 5.5+ Auto Update UI.

= 1.5.1 =

Release date: July 7th, 2020

* Bug fix: Auto Update Translation filter causes fatal error with JetPack active [#50](https://github.com/BoldGrid/boldgrid-backup-premium/issues/50)

= 1.5.0 =

Release date: July 7th, 2020

* New Feature: Added Timely Auto Updates.

= 1.4.0 =

Release date: May 19th, 2020

* New feature: Google drive - shared drive support.
* New feature: Google drive - upload to any folder by using a folder id.
* Bug fix: Cleaned up Amazon libraries loaded to prevent conflict with W3TC.

= 1.3.3 =

Release date: February 19th, 2020

* Bug fix: Cannot save Google Drive on settings page.

= 1.3.2 =

Release date: February 18th, 2020

* Update: Allow Google Drive settings to be reset, and which allows for reauthorization.
* Update: Show error messages when validating Google Drive.
* Bug fix: Error 403: Daily Limit for Unauthenticated Use Exceeded.

= 1.3.1 =

Release date: December 13th, 2019

* Bug fix: Fixed filtering of archive attributes.

= 1.3.0 =

Release data: November 21th, 2019

* Update: Renamed plugin from "BoldGrid Backup Premium" to "Total Upkeep Premium".
* New feature: Added database dump file encryption.

= 1.2.1 =

Release date: October 1st, 2019

* Bug fix: Fix bucket errors on archive details page.

= 1.2.0 =

Release date: October 1st, 2019

* New feature: DreamObjects support.

= 1.1.2 =

Release date: May 21th, 2019

* Bug fix: Google Drive attempting to upload archive that does not exist.

= 1.1.1 =

Release date: May 14th, 2019

* Bug fix: Themes autoupdated not showing in Tools > History.
* Bug fix: Incorrect theme version listed in history during autoupdate.

= 1.1.0 =

Release date: Feb 14th, 2019

* New feature: Google Drive support.
* Update: Composer libraries updated.

= 1.0.3 =

Release date: Dec 5th, 2018

* Bug fix: Fixed updating plugin via ajax.

= 1.0.2 =

Release date: Nov 20th, 2018

* Bug fix: Fixed ob_flush warning with S3.
* Bug fix: Fixed error: Invalid argument supplied for foreach().

= 1.0.1 =

Release Date: October 25th, 2018

* Bug fix: Fixed handling of hook parameter on non-bulk plugin upgrades.
* Bug fix: Fatal error: Uncaught Error: Call to undefined function wp_generate_password.

= 1.0.0 =

Release Date: April 11th, 2017

* Initial release.

== Upgrade Notice ==
= 1.3.0 =
BoldGrid Backup Premium has been renamed to Total Upkeep Premium.  Different name with the same great features.
