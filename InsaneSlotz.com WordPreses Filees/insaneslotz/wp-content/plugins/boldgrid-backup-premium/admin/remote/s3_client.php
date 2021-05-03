<?php // phpcs:ignore
/**
 * S3 Client class.
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

use Aws\S3\S3Client;

/**
 * S3 Client class.
 *
 * @since 1.1.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Client {
	/**
	 * Our s3 client.
	 *
	 * @since 1.2.0
	 * @var Aws\S3\S3Client
	 * @access protected
	 */
	protected $client;

	/**
	 * Our provider.
	 *
	 * Not required to use this class. If set, allows a bucket to figure out which provider it
	 * belongs to.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Provider
	 * @access protected
	 */
	protected $provider;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param array $args An array of args.
	 */
	public function __construct( array $args ) {
		$credentials = new \Aws\Credentials\Credentials( $args['key'], $args['secret'] );

		$this->client = new \Aws\S3\S3Client( array(
			/*
			 * A "version" configuration value is required. Specifying a version constraint ensures
			 * that your code will not be affected by a breaking change made to the service.
			 * @link https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-version
			 */
			'version'     => '2006-03-01',
			'endpoint'    => $args['endpoint'],
			'credentials' => $credentials,
			/*
			 * Configure our region. While we're using a custom endpoint, if this variable is missing
			 * we'll get a fatal.
			 */
			'region'      => '',
		) );
	}

	/**
	 * Get the client.
	 *
	 * @since 1.2.0
	 *
	 * @return Aws\S3\S3Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Get our provider.
	 *
	 * @since 1.2.0
	 *
	 * @return Boldgrid_Backup_Premium_Admin_Remote_S3_Provider
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * Whether or not this client has a provider.
	 *
	 * A client can be instantied by itself and not belong to a client.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	public function has_provider() {
		return ! is_null( $this->provider );
	}

	/**
	 * Whether or not this client is valid.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	public function is_valid() {
		// @todo Probably need better logic here.
		try {
			$buckets = new Boldgrid_Backup_Premium_Admin_Remote_S3_Buckets( $this );
			$buckets->set_buckets();

			$buckets->get_buckets();

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Set our provider.
	 *
	 * @since 1.2.0
	 *
	 * @param Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider
	 */
	public function set_provider( Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider ) {
		$this->provider = $provider;
	}
}
