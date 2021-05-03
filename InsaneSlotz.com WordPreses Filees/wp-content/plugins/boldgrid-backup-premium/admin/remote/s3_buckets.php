<?php // phpcs:ignore
/**
 * S3 Buckets class.
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
 * S3 Buckets class.
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Buckets {
	/**
	 * A listing of our buckets.
	 *
	 * This list is raw data.
	 *
	 * @since 1.2.0
	 * @var Guzzle\Service\Resource\Model
	 * @access private
	 */
	private $buckets;

	/**
	 * Our client.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Client
	 * @access private
	 */
	private $client;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param Boldgrid_Backup_Premium_Admin_Remote_S3_Client $client Our client.
	 */
	public function __construct( Boldgrid_Backup_Premium_Admin_Remote_S3_Client $client ) {
		$this->client = $client;
	}

	/**
	 * Determine whether or not a bucket exists.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $bucket_id The bucket id to check.
	 * @return bool
	 */
	public function has_bucket( $bucket_id ) {
		$this->set_buckets();

		$buckets = $this->buckets->get( 'Buckets' );

		foreach ( $buckets as $bucket ) {
			if ( $bucket['Name'] === $bucket_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get our buckets.
	 *
	 * @since 1.2.0
	 *
	 * @return Guzzle\Service\Resource\Model
	 */
	public function get_buckets() {
		return $this->buckets;
	}

	/**
	 * Initialize our buckets.
	 *
	 * @since 1.2.0
	 */
	public function set_buckets() {
		// First, try to set buckets via transient.
		if ( is_null( $this->buckets ) && $this->client->has_provider() ) {
			if ( $from_transient = $this->client->get_provider()->get_transient()->get_buckets() ) { // phpcs:ignore
				$this->buckets = $from_transient;
			}
		}

		/*
		 * If no transient data, get the buckets fresh and set the transient.
		 *
		 * Transient is cleared whenever we create a new bucket.
		 */
		if ( is_null( $this->buckets ) ) {
			$this->buckets = $this->client->get_client()->listBuckets();

			if ( $this->client->has_provider() ) {
				$this->client->get_provider()->get_transient()->set_buckets( $this->buckets );
			}
		}
	}
}
