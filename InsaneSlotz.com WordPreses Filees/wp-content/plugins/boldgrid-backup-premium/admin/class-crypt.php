<?php
/**
 * File: class-crypt.php
 *
 * @link  https://www.boldgrid.com
 * @since 1.3.0
 *
 * @package    Boldgrid
 * @subpackage Boldgrid\Backup\Premium\Admin
 * @copyright  BoldGrid
 * @author     BoldGrid <support@boldgrid.com>
 */

namespace Boldgrid\Backup\Premium\Admin;

/**
 * Class: Crypt
 *
 * @since 1.3.0
 */
class Crypt {
	/**
	 * The BoldGrid Backup core class object.
	 *
	 * @since  1.3.0
	 * @access private
	 * @var    \Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * The Boldgrid Backup Premium core object.
	 *
	 * @since  1.3.0
	 * @access private
	 * @var    \Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Allowed modes for encryption operations.
	 *
	 * Crypt mode: "e" (encrypt) or "d" (decrypt).
	 *
	 * @since  1.3.0
	 * @access private
	 * @var    array
	 */
	private $allowed_modes = [
		'e', // Encrypt.
		'd', // Decrypt.
	];

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 *
	 * @param \Boldgrid_Backup_Admin_Core         $core         \Boldgrid_Backup_Admin_Core object.
	 * @param \Boldgrid_Backup_Premium_Admin_Core $premium_core \Boldgrid_Backup_Premium_Admin_Core object.
	 */
	public function __construct( \Boldgrid_Backup_Admin_Core $core, \Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;
	}

	/**
	 * Post database dump action hook.
	 *
	 * Encrypt file contents, if configured in the settings.
	 *
	 * @since 1.3.0
	 *
	 * @see \Boldgrid_Backup_Admin_Settings::get_settings()
	 * @see \Boldgrid_Backup_Admin_Config::get_is_premium()
	 * @see self::crypt_file()
	 *
	 * @param  string $filepath Filepath.
	 * @return bool
	 */
	public function post_dump( $filepath ) {
		$success  = false;
		$settings = $this->core->settings->get_settings();

		if ( ! empty( $settings['encrypt_db'] ) ) {
			$success = $this->crypt_file( $filepath, 'e' );
		}

		if ( $success ) {
			/**
			 * Allow the filtering of our $info before generating a backup.
			 *
			 * @since 1.5.1
			 *
			 * @param array $info Archive information.
			 */
			add_filter(
				'boldgrid_backup_pre_archive_info',
				function( $info ) {
					$info['encrypt_db']  = true;
					$info['encrypt_sig'] = $this->get_token_hash();

					return $info;
				}
			);
		}

		return $success;
	}

	/**
	 * Filter hook for "boldgrid_backup_post_get_dump_file".
	 *
	 * Decrypts file contents/data, if encrypted.
	 *
	 * @since 1.3.0
	 *
	 * @see self::crypt_data()
	 *
	 * @param  string $file_contents Possibly encrypted MySQL dump file contents.
	 * @return string Unencrypted MySQL dump file contents.
	 */
	public function post_get_dump_file( $file_contents ) {
		if ( ! empty( $file_contents[0]['content'] ) ) {
			$data = $this->crypt_data( $file_contents[0]['content'], 'd' );
		}

		if ( ! empty( $data ) ) {
			$file_contents[0]['content'] = $data;
		}

		return $file_contents;
	}

	/**
	 * Filter archive attribute value.
	 *
	 * Allows operations to be performed on attribute changes and alter the value depending on results.
	 *
	 * @since 1.3.1
	 *
	 * @see \Boldgrid_Backup_Admin_Core::get_dump_file()
	 * @see self::archive_crypt_file()
	 *
	 * @param  mixed  $value     New value.
	 * @param  mixed  $old_value Old value.
	 * @param  mixed  $key       Key name.
	 * @param  string $filepath  Archive filepath.
	 * @return string            Filtered new value.
	 */
	public function filter_update_attribute( $value, $old_value, $key, $filepath ) {
		// Handle changing of "encrypt_db".
		if ( 'encrypt_db' === $key && $value != $old_value ) { // phpcs:ignore
			$dump_file = basename( $this->core->get_dump_file( $filepath ) );
			$mode      = empty( $value ) ? 'd' : 'e';
			$success   = $this->archive_crypt_file( $filepath, $dump_file, $mode );
			$value     = (bool) ( $success ? $value : $old_value );
		}

		return $value;
	}

	/**
	 * Get the BoldGrid Backup Settings.
	 *
	 * Checks the openssl settings and save defaults if not present.
	 *
	 * @since 1.3.0
	 *
	 * @see \Boldgrid_Backup_Admin_Settings::get_settings()
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = $this->core->settings->get_settings();

		$settings['updated']           = time();
		$settings['openssl']['cipher'] = ! empty( $settings['openssl']['cipher'] ) &&
			openssl_cipher_iv_length( $settings['openssl']['cipher'] ) ?
			$settings['openssl']['cipher'] : 'AES-256-CBC';
		$settings['openssl']['iv']     = ! empty( $settings['openssl']['iv'] ) ?
			$settings['openssl']['iv'] :
			bin2hex( openssl_random_pseudo_bytes( openssl_cipher_iv_length( $settings['openssl']['cipher'] ) ) );
		$settings['openssl']['key']    = ! empty( $settings['openssl']['key'] ) ?
			$settings['openssl']['key'] : hash( 'sha256', openssl_random_pseudo_bytes( 16 ) );

		update_site_option( 'boldgrid_backup_settings', $settings );

		return $settings;
	}

	/**
	 * Get the crypt token hash.
	 *
	 * A crypt token hash is used to compare the encryption settings signature.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function get_token_hash() {
		return md5( $this->get_crypt_token() );
	}

	/**
	 * Get the crypt token.
	 *
	 * A crypt token is a represeantation of the encryption settings for a user to copy and paste.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function get_crypt_token() {
		return bin2hex( gzcompress( wp_json_encode( $this->get_settings()['openssl'] ), 9 ) );
	}

	/**
	 * Decode the encryption settings using a crypt token.
	 *
	 * A crypt token is a represeantation of the encryption settings for a user to copy and paste.
	 *
	 * @since 1.3.0
	 *
	 * @param  string $crypt_token A crypt token string.
	 * @return array Decoded encryption settings.
	 */
	public function decode_crypt_token( $crypt_token ) {
		$settings = [];

		if ( ! empty( $crypt_token ) && 0 === strlen( $crypt_token ) % 2 ) {
			$setting = json_decode( @gzuncompress( hex2bin( $crypt_token ) ), true ); // phpcs:ignore
		}

		if ( ! empty( $setting['cipher'] ) && ! empty( $setting['iv'] ) && ! empty( $setting['key'] ) ) {
			$settings['openssl'] = $setting;
		}

		return $settings;
	}

	/**
	 * Is the specified file encrypted with our settings.
	 *
	 * @since 1.3.0
	 *
	 * @param  string $filepath Filepath.
	 * @return bool
	 */
	public function is_file_encrypted( $filepath ) {
		$data = $this->core->wp_filesystem->get_contents( $filepath );

		if ( empty( $data ) ) {
			return false;
		}

		return false !== $this->crypt_data( $data, 'd' );
	}

	/**
	 * Encrypt or decrypt file contents.
	 *
	 * @since 1.3.0
	 *
	 * @see \WP_Filesystem::get_contents()
	 * @see self::crypt_data()
	 * @see \WP_Filesystem::put_contents()
	 *
	 * @param  string $filepath Filepath.
	 * @param  string $mode     Crypt mode: "e" (encrypt) or "d" (decrypt).
	 * @return bool
	 */
	public function crypt_file( $filepath, $mode ) {
		$success = false;

		if ( ! in_array( $mode, $this->allowed_modes, true ) ) {
			return false;
		}

		$data = $this->core->wp_filesystem->get_contents( $filepath );

		if ( ! empty( $data ) ) {
			$data = $this->crypt_data( $data, $mode );
		}

		if ( false !== $data ) {
			$success = (bool) $this->core->wp_filesystem->put_contents( $filepath, $data );
		}

		return $success;
	}

	/**
	 * Encrypt or decrypt data, depending on mode argument.
	 *
	 * @since 1.3.0
	 *
	 * @param  string $data Data.
	 * @param  string $mode Crypt mode: "e" (encrypt) or "d" (decrypt).
	 * @return string|false
	 */
	public function crypt_data( $data, $mode ) {
		if ( ! in_array( $mode, $this->allowed_modes, true ) || empty( $data ) ) {
			return false;
		}

		$function = 'e' === $mode ? 'openssl_encrypt' : 'openssl_decrypt';
		$settings = $this->get_settings();
		$data     = $function(
			$data,
			$settings['openssl']['cipher'],
			$settings['openssl']['key'],
			0,
			hex2bin( $settings['openssl']['iv'] )
		);

		return $data;
	}

	/**
	 * Encrypt or decrypt a file in a backup archive.
	 *
	 * @since 1.3.0
	 *
	 * @see \Boldgrid_Backup_Admin_Archive::init()
	 * @see self::crypt_data()
	 * @see \Boldgrid_Backup_Admin_Backup_Dir::get()
	 *
	 * @uses \Boldgrid_Backup_Admin_Archive::log
	 *
	 * @param  string $filepath  Archive filepath.
	 * @param  string $filename  Filename.
	 * @param  string $mode      Crypt mode: "e" (encrypt) or "d" (decrypt).
	 * @return bool
	 */
	public function archive_crypt_file( $filepath, $filename, $mode ) {
		if ( empty( $filepath ) || empty( $filename ) || ! in_array( $mode, $this->allowed_modes, true ) ||
			! $this->core->wp_filesystem->is_writable( $filepath ) ) {
				return false;
		}

		if ( ! class_exists( '\PclZip' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		}

		$this->core->archive->init( $filepath );

		$archive = new \PclZip( $filepath );
		if ( 0 === $archive ) {
			return false;
		}

		$list = $archive->listContent();
		if ( empty( $list ) ) {
			return false;
		}

		$file_index = false;

		foreach ( $list as $index => $filedata ) {
			if ( $filename === $filedata['filename'] ) {
				$file_index = $index;
				break;
			}
		}

		if ( false === $file_index ) {
			return false;
		}

		$file_contents = $archive->extractByIndex( $file_index, PCLZIP_OPT_EXTRACT_AS_STRING );

		if ( empty( $file_contents[0]['content'] ) ) {
			return false;
		}

		$data = $this->crypt_data( $file_contents[0]['content'], $mode );

		if ( empty( $data ) ) {
			return false;
		}

		$crypt_file_path = $this->core->backup_dir->get() . '/' . $filename;

		$written = $this->core->wp_filesystem->put_contents( $crypt_file_path, $data );
		if ( ! $written ) {
			return false;
		}

		$log = $this->core->archive->log;

		// Ensure the act updating a file does not change the backup file's timestamp.
		if ( ! empty( $log['lastmodunix'] ) ) {
			$this->core->wp_filesystem->touch( $crypt_file_path, $log['lastmodunix'] );
		}

		$status = $archive->deleteByIndex( $file_index );
		if ( 0 === $status ) {
			return false;
		}

		$status = $archive->add( $crypt_file_path, PCLZIP_OPT_REMOVE_ALL_PATH );
		if ( 0 === $status ) {
			return false;
		}

		return true;
	}
}
