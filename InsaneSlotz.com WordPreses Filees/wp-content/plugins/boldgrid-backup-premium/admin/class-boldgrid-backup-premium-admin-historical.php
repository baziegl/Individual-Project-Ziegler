<?php
/**
 * Historical class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.5.3
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Historical class.
 *
 * @since 1.5.3
 */
class Boldgrid_Backup_Premium_Admin_Historical {
	/**
	 * The core class object.
	 *
	 * @since 1.5.3
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Our historical dir.
	 *
	 * This will be 'historical-' followed by 16 random characters.
	 *
	 * @since 1.5.3
	 * @var   string
	 */
	public $dir = null;

	/**
	 * Errors.
	 *
	 * @since 1.5.3
	 * @var   array
	 */
	public $errors = array();

	/**
	 * Regular expression search for a historical file.
	 *
	 * @since 1.5.3
	 * @var   string
	 */
	public $filename_expression = '/^([0-9]{10})[.]([0-9]{10})[.](.*)$/';

	/**
	 * An array of language strings.
	 *
	 * @since 1.5.3
	 * @var   array
	 */
	public $lang = array();

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.5.3
	 * @var   Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.5.3
	 *
	 * @param Boldgrid_Backup_Admin_Core         $core         Boldgrid_Backup_Admin_Core object.
	 * @param Boldgrid_Backup_Premium_Admin_Core $premium_core Boldgrid_Backup_Premium_Admin_Core object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core, Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;

		$this->set_lang();
	}

	/**
	 * Create a page for the historical versions.
	 *
	 * @since 1.5.3
	 */
	public function add_menu_items() {
		add_submenu_page(
			null,
			__( 'Historical', 'boldgrid-backup' ),
			__( 'Historical', 'boldgrid-backup' ),
			'administrator',
			'boldgrid-backup-historical',
			array(
				$this,
				'page',
			)
		);
	}

	/**
	 * Enqueue scripts for the historical page.
	 *
	 * @since 1.5.3
	 *
	 * @param string $hook Hook name.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'admin_page_boldgrid-backup-historical' !== $hook ) {
			return;
		}

		wp_register_script(
			'boldgrid-backup-premium-admin-historical',
			plugin_dir_url( __FILE__ ) . 'js/boldgrid-backup-premium-admin-historical.js',
			array( 'jquery' ),
			BOLDGRID_BACKUP_PREMIUM_VERSION
		);
		wp_localize_script( 'boldgrid-backup-premium-admin-historical', 'boldgrid_backup_premium_admin_historical', $this->lang );
		wp_enqueue_script( 'boldgrid-backup-premium-admin-historical' );

		wp_enqueue_style(
			'boldgrid-backup-premium-historical',
			plugin_dir_url( __FILE__ ) . 'css/boldgrid-backup-premium-admin-historical.css', array(),
			BOLDGRID_BACKUP_PREMIUM_VERSION,
			'all'
		);
	}

	/**
	 * Filter the config of files created by the backup dir class.
	 *
	 * @since 1.5.3
	 *
	 * @param  array  $files      Files.
	 * @param  string $backup_dir Backup directory path.
	 * @return array
	 */
	public function create_dir_config( $files, $backup_dir ) {
		$backup_identifier = $this->core->get_backup_identifier();

		$base_dir = 'historical-' . $backup_identifier . '-';

		$dirlist = $this->core->wp_filesystem->dirlist( $backup_dir );
		$dirlist = is_array( $dirlist ) ? $dirlist : array();

		foreach ( $dirlist as $file ) {
			if ( 'd' !== $file['type'] ) {
				continue;
			}

			preg_match( '/^' . $base_dir . '[a-zA-Z0-9]{16}$/', $file['name'], $matches );

			if ( ! empty( $matches ) ) {
				$this->dir = Boldgrid_Backup_Admin_Utility::trailingslashit( $backup_dir ) . $matches[0];
				break;
			}
		}

		if ( is_null( $this->dir ) ) {
			/*
			 * Generate a 16 character key to help make the "historical" directory name unique.
			 *
			 * Originally we used the wp_generate_password function to do this, however a report
			 * came in of a fatal error when this function was undefined:
			 * https://wordpress.org/support/topic/fatal-error-blocked-me-from-wp/
			 *
			 * There are other methods to create a random string, such as time(). However, to keep
			 * backwards compatibility, the random string MUST be 16 characters long.
			 */
			$random_key = substr( md5( time() ), -16 );

			$this->dir = Boldgrid_Backup_Admin_Utility::trailingslashit( $backup_dir ) . $base_dir . $random_key;

			$files[] = array(
				'type'  => 'dir',
				'path'  => $this->dir,
				'chmod' => 0700,
			);
		}

		return $files;
	}

	/**
	 * Determine if a historical copy of a file already exists.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file File path.
	 * @return bool
	 */
	public function exists( $file ) {
		if ( empty( $file ) ) {
			return false;
		}

		$versions = $this->get_versions( $file );
		if ( empty( $versions ) ) {
			return false;
		}

		$current_file = ABSPATH . $file;
		if ( ! $this->core->wp_filesystem->exists( $current_file ) ) {
			return false;
		}

		$this->set_dir();

		foreach ( $versions as $file_data ) {
			$historical_file = Boldgrid_Backup_Admin_Utility::trailingslashit( $this->dir ) .
				dirname( $file ) . DIRECTORY_SEPARATOR . $file_data['name'];

			if ( sha1_file( $current_file ) === sha1_file( $historical_file ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get an array of all historical versions we have of a file.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file Filename.
	 * @return array
	 */
	public function get_versions( $file ) {
		$this->set_dir();
		$parts    = pathinfo( $file );
		$versions = array();

		$dirlist = $this->core->wp_filesystem->dirlist( Boldgrid_Backup_Admin_Utility::trailingslashit( $this->dir ) . $parts['dirname'] );
		if ( empty( $dirlist ) ) {
			return $versions;
		}

		foreach ( $dirlist as $file ) {
			preg_match( $this->filename_expression, $file['name'], $matches );

			if ( empty( $matches[3] ) ) {
				continue;
			}

			if ( $matches[3] !== $parts['basename'] ) {
				continue;
			}

			$file['created'] = $matches[1];

			$versions[] = $file;
		}

		return $versions;
	}

	/**
	 * Create a clean array of ALL versions of a file.
	 *
	 * This array is useful when needing to display a table of all versions.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file Filename.
	 * @return array
	 */
	public function get_versions_clean( $file ) {
		$versions_clean = array();
		$versions       = $this->find_all( $file );

		foreach ( $versions as $type => $type_versions ) {
			foreach ( $type_versions as $version_key => $version_data ) {
				switch ( $type ) {
					case 'current':
						$versions_clean[] = array(
							'type'        => $type,
							// This is UTC by default.
							'lastmodunix' => $version_data['lastmodunix'],
							'size'        => $version_data['size'],
						);
						break;
					case 'historical':
						$versions_clean[] = array(
							'type'        => $type,
							'created'     => $version_data['created'],
							// This is UTC by default.
							'lastmodunix' => $version_data['lastmodunix'],
							'size'        => $version_data['size'],
							'name'        => $version_data['name'],
						);
						break;
					case 'in_archives':
						$versions_clean[] = array(
							'type'             => $type,
							'created'          => $version_data['created'],
							// This was previously converted to unix time.
							'lastmodunix'      => $version_data['mtime'],
							'size'             => $version_data['size'],
							'archive_filepath' => $version_data['archive_filepath'],
						);
						break;
				}
			}
		}

		usort( $versions_clean, function( $a, $b ) {
			return $a['lastmodunix'] > $b['lastmodunix'];
		} );

		return $versions_clean;
	}

	/**
	 * Find all versions of a file.
	 *
	 * We find the current file, all historical versions, and even versions in
	 * all of the backups.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file Filename.
	 * @return array
	 */
	public function find_all( $file ) {
		$versions = array();

		$current = $this->core->wp_filesystem->dirlist( ABSPATH . $file );
		if ( ! empty( $current ) ) {
			$versions['current'] = $current;
		}

		$historical = $this->get_versions( $file );
		if ( ! empty( $historical ) ) {
			$versions['historical'] = $historical;
		}

		$in_archives = $this->find_in_archives( $file );
		if ( ! empty( $in_archives ) ) {
			$versions['in_archives'] = $in_archives;
		}

		return $versions;
	}

	/**
	 * Search for a file in all of our archives.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file Filename.
	 * @return array
	 */
	public function find_in_archives( $file ) {
		$in_archives = array();
		$archives    = $this->core->get_archive_list();

		foreach ( $archives as $archive ) {
			$this->core->archive->init( $archive['filepath'] );
			$file_contents = $this->core->archive->get_file( $file, true );

			if ( ! empty( $file_contents ) ) {
				$file_contents[0]['archive_filepath'] = $archive['filepath'];
				$file_contents[0]['created']          = $archive['lastmodunix'];
				$in_archives[]                        = $file_contents[0];
			}
		}

		return $in_archives;
	}

	/**
	 * Render the historical page.
	 *
	 * @since 1.5.3
	 */
	public function page() {
		$file = ! empty( $_GET['file'] ) ? $_GET['file'] : null; // phpcs:ignore

		include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/historical.php';
	}

	/**
	 * Restore a historical file.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file         Example: "wp-content/index.php".
	 * @param  string $file_version Example: "1508848488.1508773689.index.php".
	 * @return bool
	 */
	public function restore( $file, $file_version ) {
		$this->set_dir();

		$full_historical = Boldgrid_Backup_Admin_Utility::trailingslashit( $this->dir ) .
			dirname( $file ) . DIRECTORY_SEPARATOR . $file_version;
		$full_current    = ABSPATH . $file;

		if ( ! $this->core->wp_filesystem->exists( $full_historical ) ) {
			$this->errors[] = __( 'Historical file does not exist.', 'boldgrid-backup' );
			return false;
		}

		/*
		 * Create directory to store this historical file.
		 *
		 * WordPress' mkdir does not support recursive, so we'll do what they
		 * do and simply mkdir with @.
		 */
		$dir = dirname( $full_current );
		if ( ! $this->core->wp_filesystem->exists( $dir ) ) {
			@mkdir( $dir, FS_CHMOD_DIR, true ); // phpcs:ignore
		}

		$this->save( $file );

		$copied = $this->core->wp_filesystem->copy( $full_historical, $full_current, true );
		if ( ! $copied ) {
			$this->errors[] = __( 'Unable to copy historical file copy into place.', 'boldgrid-backup' );
			return false;
		}

		$touched = false;
		preg_match( $this->filename_expression, $file_version, $matches );
		if ( ! empty( $matches[2] ) ) {
			$touched = $this->core->wp_filesystem->touch( $full_current, $matches[2] );
		}
		if ( ! $touched ) {
			$this->errors[] = __( 'Unable to touch file.', 'boldgrid-backup' );
			return false;
		}

		$this->premium_core->history->add( sprintf(
			// Translators: 1: File, 2: Date/Time.
			__( 'A copy of %1$s from %2$s was restored.', 'boldgrid-backup' ),
			$file,
			date( 'Y.m.d h:i:s a', $matches[2] )
		));

		return true;
	}

	/**
	 * Save a file to the historical folder.
	 *
	 * @since 1.5.3
	 *
	 * @param  string $file Relative to ABSPATH.
	 * @return bool
	 */
	public function save( $file ) {

		$exists = $this->exists( $file );
		if ( $exists ) {
			return true;
		}

		$dirlist = $this->core->wp_filesystem->dirlist( ABSPATH . $file );

		if ( empty( $dirlist ) ) {
			return false;
		}

		$this->set_dir();

		// Create path to new historical file.
		$parts        = pathinfo( $file );
		$lastmodunix  = $dirlist[ $parts['basename'] ]['lastmodunix'];
		$new_filename = $parts['dirname'] . DIRECTORY_SEPARATOR . time() . '.' . $lastmodunix . '.' . $parts['basename'];
		$new_path     = Boldgrid_Backup_Admin_Utility::trailingslashit( $this->dir ) . $new_filename;

		/*
		 * Create directory to store this historical file.
		 *
		 * WordPress' mkdir does not support recursive, so we'll do what they
		 * do and simply mkdir with @.
		 */
		$new_path_parts = pathinfo( $new_path );
		@mkdir( $new_path_parts['dirname'], 0700, true ); // phpcs:ignore

		// Copy the file and adjust the timestamp.
		$copied = $this->core->wp_filesystem->copy( ABSPATH . $file, $new_path );
		if ( ! $copied ) {
			return false;
		}
		$touched = $this->core->wp_filesystem->touch( $new_path, $lastmodunix );

		if ( $touched ) {
			$this->premium_core->history->add( sprintf(
				// Translators: 1: File.
				__( 'A copy of the following file was saved: %1$s', 'boldgrid-backup' ),
				$file
			));
		}

		return $touched;
	}

	/**
	 * Set the historical dir.
	 *
	 * Usually the dir will be set by self::create_dir_config, but this method
	 * is to guarantee we have it set.
	 *
	 * @since 1.5.3
	 */
	public function set_dir() {
		if ( ! is_null( $this->dir ) ) {
			return;
		}

		$this->core->backup_dir->get();
	}

	/**
	 * Set lang.
	 *
	 * @since 1.5.3
	 */
	public function set_lang() {
		$icon_current    = '<span class="dashicons dashicons-media-default"></span> ';
		$icon_archive    = '<span class="dashicons dashicons-archive"></span> ';
		$icon_historical = '<span class="dashicons dashicons-admin-page"></span> ';

		$this->lang = array(
			'archive_file'                => $icon_archive . __( 'Copy in a backup archive', 'boldgrid-backup' ),
			'archive_file_description'    => __( 'This is a copy of the file currently stored in a local archive file (zip file).', 'boldgrid-backup' ),
			'current_file'                => $icon_current . __( 'Current file', 'boldgrid-backup' ),
			'current_file_description'    => __( 'This is the actual file right now.', 'boldgrid-backup' ),
			'historical_file'             => $icon_historical . __( 'Local copy', 'boldgrid-backup' ),
			'historical_file_description' => __( 'A "local copy" of a file is a copy of that file made that doesn\'t actually exist within a backup / .zip file. Local copies of files are usually created in one of two ways: <strong>(1)</strong> If you restore a single file from a backup, a copy of the file is made first. <strong>(2)</strong> You have used the <strong>save a copy</strong> feature we have added to the WordPress Plugin Editor.', 'boldgrid-backup' ),
			'icon_warning'                => $this->core->lang['icon_warning'],
			'reloading_table'             => __( 'Reloading table', 'boldgrid-backup' ),
			'restoring'                   => __( 'Restoring', 'boldgrid-backup' ),
			'unknown_error_load'          => __( 'An unknown error occurred when attempting to find all versions of this file.', 'boldgrid-backup' ),
			'unknown_error_restore'       => __( 'An unknown error occurred when attempting to restore this file.', 'boldgrid-backup' ),
		);
	}

	/**
	 * Get an html table that lists all versions.
	 *
	 * @since 1.5.3
	 */
	public function wp_ajax_get_historical_versions() {
		$error = __( 'There was an error retrieving historical versions.', 'boldgrid-backup' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( $error . ' ' . __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! check_ajax_referer( 'boldgrid_backup_remote_storage_upload', 'security', false ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$file = ! empty( $_POST['file'] ) ? $_POST['file'] : false; // phpcs:ignore
		if ( empty( $file ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid file / version.', 'boldgrid-backup' ) );
		}

		$versions_clean = $this->get_versions_clean( $file );

		// Create a count of how many versions of a file we have.
		$version_count = array();
		foreach ( $versions_clean as $version ) {
			$count = empty( $version_count[ $version['lastmodunix'] ] ) ?
				1 : $version_count[ $version['lastmodunix'] ] + 1;

			$version_count[ $version['lastmodunix'] ] = $count;
		}

		$versions_table = '<p>' .
			sprintf(
				// Translators: 1: File count.
				__( 'We found %1$s different version(s) of this file you can restore.', 'boldgrid-backup' ),
				count( $version_count )
			) . '</p>';

		$versions_table .= sprintf( '
			<table class="wp-list-table striped fixed widefat">
			<thead>
				<tr>
					<td class="check-column">#</td>
					<th>%1$s</th>
					<th>%2$s</th>
					<th class="column-date">%3$s</th>
				</tr>
			</thead>
			<tbody>',
			__( 'Type', 'boldgrid-bacukp' ),
			__( 'Last Modified', 'boldgrid-backup' ),
			__( 'Size', 'boldgrid-backup' )
		);

		// Loop through each version and create a <tr> for it.
		$last_modified  = null;
		$version_number = 0;
		foreach ( $versions_clean as $version ) {
			$versions_table .= include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/historical/entry.php';
		}

		$versions_table .= '</table>';

		if ( empty( $versions_clean ) ) {
			$versions_table = sprintf(
				'<p>%1$s %2$s</p>',
				$this->core->lang['icon_warning'],
				__( 'No versions of this file could be found.', 'boldgrid-backup' )
			);
		}

		wp_send_json_success( $versions_table );
	}

	/**
	 * Restore a historical version.
	 *
	 * @since 1.5.3
	 */
	public function wp_ajax_restore_historical() {
		$error = __( 'An error occurred while attempting to restore this file:', 'boldgrid-backup' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( $error . ' ' . __( 'Permission denied.', 'boldgrid-backup' ) );
		}

		if ( ! check_ajax_referer( 'boldgrid_backup_remote_storage_upload', 'security', false ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid nonce.', 'boldgrid-backup' ) );
		}

		$file_version = ! empty( $_POST['file_version'] ) ? $_POST['file_version'] : false; // phpcs:ignore
		$file = ! empty( $_POST['file'] ) ? $_POST['file'] : false; // phpcs:ignore
		if ( empty( $file_version ) || empty( $file ) ) {
			wp_send_json_error( $error . ' ' . __( 'Invalid file / version.', 'boldgrid-backup' ) );
		}

		$restored = $this->restore( $file, $file_version );

		if ( $restored ) {
			wp_send_json_success( __( '&#10003; Restored', 'boldgrid-backup' ) );
		}

		$error_message = ! empty( $this->errors ) ? implode( ' ', $this->errors ) : __( 'Unknown error.', 'boldgrid-backup' );
		wp_send_json_error( $error . ' ' . $error_message );
	}
}
