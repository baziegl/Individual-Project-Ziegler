<?php
namespace NJT\FastDup\Admin\Package;

use Ifsnop\Mysqldump as IMysqldump;
use NJT\FastDup\Admin\Database\Database as Database;
use NJT\FastDup\Admin\Helper\LogHelper;
use NJT\FastDup\Admin\Helper\PackageHelper;
use NJT\FastDup\Admin\Package_Status;

defined('ABSPATH') || exit;
class Package_Database {
  // Is filter table database
  public $is_filter;
  // List tables remove
  public $filter_tables;
  // SQL File Name
  public $file;
  // Prefix table
  public $prefix;
  // Package Object
  protected $package;
  // Store path save file sql
  private $db_store_path;

  public function __construct() {

  }

  /**
   *  Build the database with mysqldump
   *  @param obj $package The package object that started this process
   *  @return boolean
   */
  public function build($package) {
    $this->package = $package;
    LogHelper::write_log($this->package->name_hash, 'info', "//========DATABASE: START========//");
    $this->package->set_status(Package_Status::DBSTART);
    try {
      $status = true;
      $this->db_store_path = "{$this->package->store_path_temp}/{$this->file}";
      PackageHelper::update_option_package_active($this->package->id, 'database', true, '');
      $this->mysqlDump();
    } catch (\Exception $ex) {
      $status = false;
      $log_message = 'Exception (' . get_class($ex) . ') occurred during restore: ' . $ex->getMessage() . ' (Code: ' . $ex->getCode() . ', line ' . $ex->getLine() . ' in ' . $ex->getFile() . ')';
      $display_log_message = sprintf('A PHP exception (%s) has occurred: %s', get_class($ex), $ex->getMessage());
      if (function_exists('wp_debug_backtrace_summary')) {
        $log_message .= ' Backtrace: ' . wp_debug_backtrace_summary();
      }
      LogHelper::write_log($this->package->name_hash, 'error', $log_message);
      LogHelper::write_log($this->package->name_hash, 'error', $display_log_message);
    }

    if (!$status) {
      $this->package->set_status(Package_Status::ERROR);
    } else {
      $this->package->set_status(Package_Status::DBDONE);
    }
    LogHelper::write_log($this->package->name_hash, 'info', "//========DATABASE: DONE========//");
  }

  private function mysqlDump() {
    $host = explode(':', DB_HOST);
    $host = reset($host);
    $db_name = DB_NAME;
    $dump_settings = array(
      'exclude-tables' => is_array($this->filter_tables) && $this->is_filter ? $this->filter_tables : array(),
      'no-autocommit' => false,
    );

    $dump = new IMysqldump\Mysqldump("mysql:host={$host};dbname={$db_name}", DB_USER, DB_PASSWORD, $dump_settings);
    $dump->start($this->db_store_path);
  }

}
