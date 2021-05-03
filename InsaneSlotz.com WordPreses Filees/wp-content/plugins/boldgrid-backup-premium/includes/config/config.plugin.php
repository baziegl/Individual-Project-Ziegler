<?php
/**
 * Plugin configuration file
 *
 * @link http://www.boldgrid.com
 * @since 1.0.0
 *
 * @package Boldgrid_Backup_Premium
 */

// Prevent direct calls.
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$asset_server = 'https://api.boldgrid.com';

return array(
	'ajax_calls'              => array(
		'get_plugin_version' => '/api/open/get-plugin-version',
		'get_asset'          => '/api/open/get-asset',
		'google_drive_auth'  => '/api/google-drive/auth',
	),
	'asset_server'            => $asset_server,
	'plugin_name'             => 'boldgrid-backup-premium',
	'plugin_key_code'         => 'backup-premium',
	'main_file_path'          => BOLDGRID_BACKUP_PREMIUM_PATH . '/boldgrid-backup-premium.php',
	'plugin_transient_name'   => 'boldgrid_backup_premium_version_data',
	'required_parent_version' => '1.7.3-rc.1',

	/*
	 * Google Drive Config.
	 *
	 * Google Drive SDK - client secret - how secret is it?
	 * @link https://stackoverflow.com/questions/18828662/google-drive-sdk-client-secret-how-secret-is-it?rq=1
	 * @link https://developers.google.com/identity/protocols/OAuth2InstalledApp
	 * Installed apps are distributed to individual devices, and it is assumed that these apps
	 * cannot keep secrets. They can access Google APIs while the user is present at the app or when
	 * the app is running in the background.
	 */
	'google_drive_config'     => array(
		'web' => array(
			'client_id'                   => '1005887436912-d9tjeg1fte7t73b8iru0ja34479u0ech.apps.googleusercontent.com',
			'project_id'                  => 'boldgrid-backup',
			'auth_uri'                    => 'https://accounts.google.com/o/oauth2/auth',
			'token_uri'                   => 'https://oauth2.googleapis.com/token',
			'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
			'client_secret'               => 'rvVGNZ5c45o6kEdd4hxlbV8a',
			'redirect_uris'               => array(
				$asset_server . '/api/google-drive/auth-complete',
			),
		),
	),
);
