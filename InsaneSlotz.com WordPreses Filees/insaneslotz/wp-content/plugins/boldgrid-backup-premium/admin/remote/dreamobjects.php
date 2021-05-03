<?php // phpcs:ignore
/**
 * Dreamobjects class.
 *
 * @link  https://www.boldgrid.com
 * @since 1.2.0
 *
 * @package    Boldgrid_Backup
 * @subpackage Boldgrid_Backup/admin/remote
 * @copyright  BoldGrid
 * @version    $Id$
 * @author     BoldGrid <support@boldgrid.com>
 */

/**
 * Dreamobjects class.
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_Dreamobjects extends Boldgrid_Backup_Premium_Admin_Remote_S3_Provider {
	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_Dreamobjects_Hooks.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Hooks
	 */
	public $hooks;

	/**
	 * An instance of Boldgrid_Backup_Premium_Admin_Remote_Dreamobjects_Page.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Page
	 */
	public $page;

	/**
	 * Key.
	 *
	 * @since 1.2.0
	 * @var string
	 * @access protected
	 */
	protected $key = 'dreamobjects';

	/**
	 * Title.
	 *
	 * @since 1.2.0
	 * @access protected
	 * @var string
	 */
	protected $title = 'DreamObjects';

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		parent::__construct();

		$this->hooks = new Boldgrid_Backup_Premium_Admin_Remote_S3_Hooks( $this );
		$this->page  = new Boldgrid_Backup_Premium_Admin_Remote_S3_Page( $this );
	}
}
