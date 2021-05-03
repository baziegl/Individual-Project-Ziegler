<?php // phpcs:ignore
/**
 * S3 Transient class.
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
 * S3 Transient class.
 *
 * @since 1.2.0
 */
class Boldgrid_Backup_Premium_Admin_Remote_S3_Transient {
	/**
	 * The name of the transient storing our buckets.
	 *
	 * @since 1.2.0
	 * @var string
	 * @access private
	 */
	private $name_buckets;

	/**
	 * Our provider.
	 *
	 * @since 1.2.0
	 * @var Boldgrid_Backup_Premium_Admin_Remote_S3_Provider
	 * @access private
	 */
	private $provider;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 *
	 * @param Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider
	 */
	public function __construct( Boldgrid_Backup_Premium_Admin_Remote_S3_Provider $provider ) {
		$this->provider = $provider;

		$this->name_buckets = 'boldgrid_backup_s3_' . $this->provider->get_key() . '_buckets';
	}

	/**
	 * Delete all of our provider's transients.
	 *
	 * @since 1.2.0
	 *
	 * @global wpdb @wpdb
	 */
	public function delete_all() {
		global $wpdb;

		$transients = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `option_name`
				FROM $wpdb->options
				WHERE `option_name` LIKE %s;",
				'_transient_boldgrid_backup_s3_' . $this->provider->get_key() . '_%'
			)
		);

		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient->option_name );

			delete_transient( $transient_name );
		}
	}

	/**
	 * Delete our backups from transients.
	 *
	 * @since 1.2.0
	 *
	 * @param string $bucket_id A bucket id.
	 */
	public function delete_backups( $bucket_id ) {
		$transient_name = $this->get_name_backups( $bucket_id );

		delete_transient( $transient_name );
	}

	/**
	 * Delete our buckets from transients.
	 *
	 * @since 1.2.0
	 */
	public function delete_buckets() {
		delete_transient( $this->name_buckets );
	}

	/**
	 * Delete our objects cache.
	 *
	 * @since 1.2.0
	 *
	 * @param string $bucket_id A bucket id.
	 */
	public function delete_objects( $bucket_id ) {
		$transient_name = $this->get_name_objects( $bucket_id );

		delete_transient( $transient_name );

		// Backups are created from objects, so delete backups transient too.
		$this->delete_backups( $bucket_id );
	}

	/**
	 * Get our objects from cache.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $bucket_id A bucket id.
	 * @return mixed False if objects do not exist, array if they do.
	 */
	public function get_objects( $bucket_id ) {
		$transient_name = $this->get_name_objects( $bucket_id );

		return get_transient( $transient_name );
	}

	/**
	 * Get the name of our backups transient.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $bucket_id A bucket id.
	 * @return string
	 */
	public function get_name_backups( $bucket_id ) {
		return 'boldgrid_backup_s3_' . $this->provider->get_key() . '_bucket_' . $bucket_id . '_backups';
	}

	/**
	 * Get the name of our objects transient.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $bucket_id A bucket id.
	 * @return string
	 */
	public function get_name_objects( $bucket_id ) {
		return 'boldgrid_backup_s3_' . $this->provider->get_key() . '_bucket_' . $bucket_id . '_objects';
	}

	/**
	 * Get backups from transient.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $bucket_id A bucket id.
	 * @return array
	 */
	public function get_backups( $bucket_id ) {
		$transient_name = $this->get_name_backups( $bucket_id );

		return get_transient( $transient_name );
	}

	/**
	 * Get our buckets from transients.
	 *
	 * @since 1.2.0
	 *
	 * @return mixed False if transient is missing, otherwise Guzzle\Service\Resource\Model.
	 */
	public function get_buckets() {
		return get_transient( $this->name_buckets );
	}

	/**
	 * Set our backups transient.
	 *
	 * @since 1.2.0
	 *
	 * @param array  $backups An array of backups.
	 * @param string $bucket_id A bucket id.
	 */
	public function set_backups( array $backups, $bucket_id ) {
		$transient_name = $this->get_name_backups( $bucket_id );

		set_transient( $transient_name, $backups, DAY_IN_SECONDS );
	}

	/**
	 * Set our buckets transients.
	 *
	 * @since 1.2.0
	 *
	 * @param Guzzle\Service\Resource\Model $buckets
	 */
	public function set_buckets( \Aws\Result $buckets ) {
		set_transient( $this->name_buckets, $buckets, DAY_IN_SECONDS );
	}

	/**
	 * Set our objects transient.
	 *
	 * @since 1.2.0
	 *
	 * @param array  $objects   An array of objects.
	 * @param string $bucket_id A bucket id.
	 */
	public function set_objects( array $objects, $bucket_id ) {
		$transient_name = $this->get_name_objects( $bucket_id );

		set_transient( $transient_name, $objects, DAY_IN_SECONDS );

		/*
		 * Because the backups transient is based off of the objects transient, any change to the
		 * objects transient should reset the backups transient.
		 */
		$this->delete_backups( $bucket_id );
	}
}
