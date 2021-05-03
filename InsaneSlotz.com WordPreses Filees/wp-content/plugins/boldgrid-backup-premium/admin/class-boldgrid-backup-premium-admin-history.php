<?php
/**
 * History class.
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
 * History class.
 *
 * @since 1.5.3
 */
class Boldgrid_Backup_Premium_Admin_History {
	/**
	 * The core class object.
	 *
	 * @since 1.5.3
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * Max number of entries to keep in the history.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	public $max_entries = 100;

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
	}

	/**
	 * Add a message to the history log.
	 *
	 * @since 1.5.3
	 *
	 * @param string $message Message.
	 */
	public function add( $message ) {
		if ( empty( $message ) ) {
			return;
		}

		$history = $this->get();

		// Determine our user_id.
		$sapi_type = php_sapi_name();
		if ( $this->core->doing_cron && 'cli ' === substr( $sapi_type, 0, 3 ) ) {
			$user_id = 'Cron';
		} elseif ( $this->core->doing_cron ) {
			$user_id = 'WP Cron';
		} else {
			$user_id = get_current_user_id();
		}

		$history[] = array(
			'user_id'   => $user_id,
			'timestamp' => time(),
			'message'   => $message,
		);

		$this->save( $history );
	}

	/**
	 * Enqueue scripts for the history page.
	 *
	 * @since 1.5.3
	 *
	 * @param string $hook Hook name.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'admin_page_boldgrid-backup-history' !== $hook ) {
			return;
		}
	}

	/**
	 * Filter tools section.
	 *
	 * @since 1.5.3
	 *
	 * @param  array $sections Sections.
	 * @return array
	 */
	public function filter_tools_section( array $sections ) {
		$history = $this->get();

		$sections['sections'][] = array(
			'id'      => 'section_history',
			'title'   => __( 'History', 'boldgrid-backup' ),
			'content' => include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/history.php',
		);

		return $sections;
	}

	/**
	 * Get history.
	 *
	 * @since 1.5.3
	 *
	 * @return array
	 */
	public function get() {
		$history = get_site_option( 'boldgrid_backup_history', array() );

		return $history;
	}

	/**
	 * Take action after a backup has been generated.
	 *
	 * @since 1.5.3
	 *
	 * @param array $info Info.
	 */
	public function post_archive_files( array $info ) {
		if ( empty( $info['filepath'] ) ) {
			return;
		}

		$this->add( sprintf(
			// Translators: 1: File path.
			__( 'Backup file created: %1$s.', 'boldgrid-backup' ),
			$info['filepath']
		) );
	}

	/**
	 * Take action when a remove backup has been deleted due to retention
	 * settings.
	 *
	 * @since 1.5.3
	 *
	 * @param string $title Title.
	 * @param string $message Message.
	 */
	public function remote_retention_deleted( $title, $message ) {
		if ( empty( $title ) || empty( $message ) ) {
			return;
		}

		$this->add( sprintf(
			// Translators: 1: Title, 2: Message.
			__( 'Due to your retention settings with %1$s, the following was deleted remotely: %2$s.', 'boldgrid-backup' ),
			$title,
			$message
		) );
	}

	/**
	 * Take action when a file has been uploaded to a remove storage provider.
	 *
	 * @since 1.5.3
	 *
	 * @param string $title Title.
	 * @param string $filepath File path.
	 */
	public function remote_uploaded( $title, $filepath ) {
		if ( empty( $title ) || empty( $filepath ) ) {
			return;
		}

		$this->add( sprintf(
			// Translators: 1: File path, 2: Title.
			__( 'Backup file %1$s was uploaded to %2$s.', 'boldgrid-backup' ),
			$filepath,
			$title
		) );
	}

	/**
	 * Take action when a local backup has been deleted due to retention
	 * settings.
	 *
	 * @since 1.5.3
	 *
	 * @param string $filepath File path.
	 */
	public function retention_deleted( $filepath ) {
		if ( empty( $filepath ) ) {
			return;
		}

		$this->add( sprintf(
			// Translators: 1: File path.
			__( 'Due to retention settings, the following backup was deleted: %1$s', 'boldgrid-backup' ),
			$filepath
		) );
	}

	/**
	 * Take action when a plugin has been deleted.
	 *
	 * @since 1.5.3
	 *
	 * @param string $plugin_file Plugin file name.
	 */
	public function delete_plugin( $plugin_file ) {
		$data = $this->core->utility->get_plugin_data( $plugin_file );
		// Translators: 1: Plugin name, 2: Plugin version.
		$this->add( sprintf( __( '%1$s plugin (version %2$s) deleted.', 'boldgrid-backup' ), $data['Name'], $data['Version'] ) );
	}

	/**
	 * Save our history.
	 *
	 * @since 1.5.3
	 *
	 * @param  array $history History.
	 * @return bool
	 */
	public function save( $history ) {
		if ( ! is_array( $history ) ) {
			return false;
		}

		$number_to_delete = count( $history ) - $this->max_entries;

		if ( $number_to_delete > 0 ) {
			for ( $x = 1; $x <= $number_to_delete; $x++ ) {
				array_shift( $history );
			}
		}

		$updated = update_site_option( 'boldgrid_backup_history', $history );

		return $updated;
	}

	/**
	 * Take action when the settings have been updated.
	 *
	 * @since 1.5.3
	 */
	public function settings_updated() {
		$this->add( BOLDGRID_BACKUP_TITLE . ' ' . __( 'settings updated.', 'boldgrid-backup' ) );
	}

	/**
	 * Take action when a theme has been switched.
	 *
	 * @since 1.5.3
	 *
	 * @param string   $new_name  Name of the new theme.
	 * @param WP_Theme $new_theme WP_Theme instance of the new theme.
	 * @param WP_Theme $old_theme WP_Theme instance of the old theme.
	 */
	public function switch_theme( $new_name, $new_theme, $old_theme ) {
		// Translators: 1: Theme name, 2: Theme version.
		$this->add( sprintf( __( '%1$s theme (version %2$s) deactivated.', 'boldgrid-backup' ), $old_theme->get( 'Name' ), $old_theme->get( 'Version' ) ) );
		// Translators: 1: Theme name, 2: Theme version.
		$this->add( sprintf( __( '%1$s theme (version %2$s) activated.', 'boldgrid-backup' ), $new_theme->get( 'Name' ), $new_theme->get( 'Version' ) ) );
	}

	/**
	 * Take option when the active_plugins option is updated.
	 *
	 * Read the values, determine which plugins were activated and which were
	 * deactivated.
	 *
	 * @since 1.5.3
	 *
	 * @param array  $old_value Old active plugins.
	 * @param array  $value     New active plugins.
	 * @param string $option    Option name.
	 */
	public function update_option_active_plugins( $old_value, $value, $option ) {
		$old_value = ! is_array( $old_value ) ? array() : $old_value;
		$value     = ! is_array( $value ) ? array() : $value;

		$activated   = array_diff( $value, $old_value );
		$deactivated = array_diff( $old_value, $value );

		foreach ( $activated as $key => $plugin ) {
			$data = $this->core->utility->get_plugin_data( $plugin );
			// Translators: 1: Plugin name, 2: Plugin version.
			$this->add( sprintf( __( '%1$s plugin (version %2$s) activated.', 'boldgrid-backup' ), $data['Name'], $data['Version'] ) );
		}

		foreach ( $deactivated as $key => $plugin ) {
			$data = $this->core->utility->get_plugin_data( $plugin );
			// Translators: 1: Plugin name, 2: Plugin version.
			$this->add( sprintf( __( '%1$s plugin (version %2$s) deactivated.', 'boldgrid-backup' ), $data['Name'], $data['Version'] ) );
		}
	}

	/**
	 * Log whenever core, a plugin, or a theme are upgraded.
	 *
	 * @since 1.5.3
	 *
	 * @param object $upgrader_object Upgrader object.
	 * @param array  $options         Example: https://pastebin.com/ah4E048B .
	 */
	public function upgrader_process_complete( $upgrader_object, $options ) {
		$action = ! empty( $options['action'] ) ? $options['action'] : null;
		$type   = ! empty( $options['type'] ) ? $options['type'] : null;

		if ( 'update' !== $action ) {
			return;
		}

		switch ( $type ) {
			case 'core':
				$wordpress_version = get_bloginfo( 'version' );
				$this->add( sprintf(
					// Translators: 1: WordPress version.
					__( 'WordPress updated to version %1$s.', 'boldgrid-backup' ),
					get_bloginfo( 'version' )
				));
				break;
			case 'theme':
				/*
				 * Get a list of themes that have been updated.
				 *
				 * During a bulk upgrade, such as in Dashboard > Updates, we'll have an array of
				 * themes in $options['themes']. During an autoupdate, we'll just have one theme in
				 * #options['theme'].
				 */
				if ( ! empty( $options['themes'] ) ) {
					$themes = $options['themes'];
				} else {
					$themes[] = $options['theme'];
				}

				foreach ( $themes as $theme ) {
					/*
					 * Get our theme's new name and version.
					 *
					 * Typically, we could use wp_get_theme() to get this information. It works
					 * during a bulk upgrade, but during an autoupdate it returns the old version
					 * number and not the new one. Therefore, we'll just get the data directly from
					 * the theme's style.css.
					 */
					$theme           = wp_get_theme( $theme );
					$style_path      = $theme->get_stylesheet_directory() . '/style.css';
					$default_headers = [
						'Version' => 'Version',
						'Name'    => 'Theme Name',
					];
					$data            = get_file_data( $style_path, $default_headers );

					$this->add( sprintf(
						// Translators: 1: Theme version.
						__( '%1$s theme upgraded to version %2$s.', 'boldgrid-backup' ),
						$data['Name'],
						$data['Version']
					));
				}
				break;
			case 'plugin':
				$plugins = ! empty( $options['plugins'] ) ?
					$options['plugins'] : array( $options['plugin'] );

				foreach ( $plugins as $plugin ) {
					$data = $this->core->utility->get_plugin_data( $plugin );
					$this->add( sprintf(
						// Translators: 1: Plugin version.
						__( '%1$s plugin upgraded to version %2$s.', 'boldgrid-backup' ),
						$data['Name'],
						$data['Version']
					));
				}
				break;
		}
	}

	/**
	 * Take action when a user has deleted a backup.
	 *
	 * @since 1.5.3
	 *
	 * @param string $filepath File path.
	 * @param bool   $deleted  Is deleted.
	 */
	public function user_deleted_backup( $filepath, $deleted ) {
		if ( empty( $filepath ) || ! $deleted ) {
			return;
		}

		$this->add( sprintf(
			// Translators: 1: File path that was deleted.
			__( 'The following backup file was deleted: %1$s', 'boldgrid-backup' ),
			$filepath
		) );
	}
}
