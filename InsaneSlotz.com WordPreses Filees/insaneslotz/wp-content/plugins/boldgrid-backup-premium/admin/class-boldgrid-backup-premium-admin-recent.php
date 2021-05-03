<?php
/**
 * Recent class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.5.4
 *
 * @package    Boldgrid_Backup_Premium
 * @subpackage Boldgrid_Backup_Premium/admin
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Recent class.
 *
 * @since 1.5.4
 */
class Boldgrid_Backup_Premium_Admin_Recent {
	/**
	 * The core class object.
	 *
	 * @since 1.5.4
	 * @var Boldgrid_Backup_Admin_Core
	 */
	private $core;

	/**
	 * A list of all files recently modified.
	 *
	 * Will be populated by the get method.
	 *
	 * @since 1.5.4
	 * @var   array
	 */
	public $list = array();

	/**
	 * Page slug in the url.
	 *
	 * Example: "admin.php?page=this".
	 *
	 * @since 1.5.4
	 * @var   string
	 */
	public $page = 'boldgrid-backup-recent';

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Core.
	 *
	 * @since 1.5.4
	 * @var   Boldgrid_Backup_Premium_Admin_Core
	 */
	private $premium_core;

	/**
	 * Constructor.
	 *
	 * @since 1.5.4
	 *
	 * @param Boldgrid_Backup_Admin_Core         $core         Boldgrid_Backup_Admin_Core object.
	 * @param Boldgrid_Backup_Premium_Admin_Core $premium_core Boldgrid_Backup_Premium_Admin_Core object.
	 */
	public function __construct( Boldgrid_Backup_Admin_Core $core, Boldgrid_Backup_Premium_Admin_Core $premium_core ) {
		$this->core         = $core;
		$this->premium_core = $premium_core;
	}

	/**
	 * Filter tools section.
	 *
	 * @since 1.5.4
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public function filter_tools_section( array $sections ) {
		$minutes = ! empty( $_GET['mins'] ) && is_numeric( $_GET['mins'] ) ? intval( $_GET['mins'] ) : null; // phpcs:ignore

		if ( ! empty( $minutes ) ) {
			$this->get( $minutes );
			$this->sort();
		}

		$sections['sections'][] = array(
			'id'      => 'section_recent',
			'title'   => __( 'Recently Modified Files', 'boldgrid-backup' ),
			'content' => include BOLDGRID_BACKUP_PREMIUM_PATH . '/admin/partials/recent.php',
		);

		return $sections;
	}


	/**
	 * Get a list of all files recently modified.
	 *
	 * This method does not return any data, instead it modifies $this->list.
	 *
	 * @since 1.5.4
	 *
	 * @param int    $minutes Minutes.
	 * @param string $dir Directory.
	 */
	public function get( $minutes, $dir = ABSPATH ) {
		if ( ! is_numeric( $minutes ) ) {
			return;
		}

		$seconds      = 60 * $minutes;
		$dir          = Boldgrid_Backup_Admin_Utility::trailingslashit( $dir );
		$dirlist      = $this->core->wp_filesystem->dirlist( $dir );
		$skip_folders = array( '.git', 'node_modules' );

		foreach ( $dirlist as $name => $data ) {
			$is_folder      = 'd' === $data['type'];
			$is_skip_folder = $is_folder && in_array( $data['name'], $skip_folders, true );

			if ( $is_skip_folder ) {
				continue;
			}

			if ( $is_folder ) {
				$this->get( $minutes, $dir . $data['name'] );
				continue;
			}

			$is_match = $data['lastmodunix'] >= ( time() - $seconds );

			if ( $is_match ) {
				$data['path'] = $dir . $data['name'];
				$this->list[] = $data;
			}
		}
	}

	/**
	 * Sort our list of files.
	 *
	 * @since 1.5.4
	 */
	public function sort() {
		if ( empty( $this->list ) ) {
			return;
		}

		/**
		 * Sort by last modified timestamp.
		 *
		 * @param  int $a Timestamp.
		 * @param  int $b Timestamp.
		 * @return bool
		 */
		function sort_lastmodunix( $a, $b ) {
			return $a['lastmodunix'] < $b['lastmodunix'];
		}

		usort( $this->list, 'sort_lastmodunix' );
	}
}
