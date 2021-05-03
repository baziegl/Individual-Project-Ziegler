<?php
namespace NJT\FastDup\Admin;

use NJT\FastDup\Admin\Database\Database as Database;
use NJT\FastDup\Admin\Helper\Helper;
use NJT\FastDup\Admin\Helper\LogHelper;
use NJT\FastDup\Admin\Helper\PackageHelper;
use NJT\FastDup\Admin\Helper\ServerHelper;
use NJT\FastDup\Admin\Template as Template;

defined('ABSPATH') || exit;

final class Package_Status {
  const ERROR = -1;
  const CREATED = 10;
  const DBSTART = 30;
  const DBDONE = 50;
  const ARCSTART = 70;
  const ARCDONE = 80;
  const CLEANSTART = 90;
  const CLEANDONE = 95;
  const COMPLETE = 100;
}

class Package {
  // Package
  public $id;
  public $name;
  public $hash;
  public $name_hash;
  public $status;

  //Scan
  public $scan_file;
  public $site_url;

  // Template
  public $template_id;
  public $filter_exts;
  public $filter_files;
  public $filter_dirs;
  public $filter_tables;

  // Store Path
  public $store_path;
  public $store_url;
  public $store_path_temp;

  // Objects
  public $Archive;
  public $Database;

  public function __construct() {
    // Store
    $this->store_url = NJT_FASTDUP_ARCHIVE_URL_PACKAGES;
    $this->store_path_temp = NJT_FASTDUP_ARCHIVE_DIR_PATH_TMP;
    $this->store_path = NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES;

    //Objects
    $this->Archive = new Package\Package_Archive();
    $this->Database = new Package\Package_Database();

    add_filter('cron_schedules', array($this, 'add_custom_recurrence'), 999, 1);
    add_action('njt-fastdup-event', array('NJT\\FastDup\\Admin\\Package', 'build_event'), 10, 1);
  }

  public function add_custom_recurrence($schedules) {
    $schedules['njt-fastdup-month'] = array(
      'interval' => 31 * 60 * 60 * 24, 'display' => __("FastDup Month", 'njt-fastdup'),
    );
    return $schedules;
  }

  /**
   * Build package: Databse, Installer, Zip
   * @param archive filter setting from template
   * @return package_id, time_process
   */
  public function build_package() {
    try {
      session_start();
      session_destroy();

      $time_start = time();
      ServerHelper::set_limit_excute_script();

      $this->Database->build($this);

      $this->Archive->build($this);

      $this->build_clean_up();

      $time_end = time();
      $time_process = $time_end - $time_start > 0 ? gmdate("H:i:s", $time_end - $time_start) . 's' : '1s';
      PackageHelper::update_option_package_active($this->id, 'done', false, $time_process);
    } catch (\Exception $ex) {
      $status = false;
      $log_message = 'Exception (' . get_class($ex) . ') occurred during restore: ' . $ex->getMessage() . ' (Code: ' . $ex->getCode() . ', line ' . $ex->getLine() . ' in ' . $ex->getFile() . ')';
      $display_log_message = sprintf('A PHP exception (%s) has occurred: %s', get_class($ex), $ex->getMessage());
      if (function_exists('wp_debug_backtrace_summary')) {
        $log_message .= ' Backtrace: ' . wp_debug_backtrace_summary();
      }
      LogHelper::write_log($this->name_hash, 'error', $log_message);
      LogHelper::write_log($this->name_hash, 'error', $display_log_message);
      $this->set_status(Package_Status::ERROR);
    } catch (\Error $ex) {
      $status = false;
      $log_message = 'PHP Fatal error (' . get_class($ex) . ') has occurred. Error Message: ' . $ex->getMessage() . ' (Code: ' . $ex->getCode() . ', line ' . $ex->getLine() . ' in ' . $ex->getFile() . ')';
      $display_log_message = sprintf('A PHP fatal error (%s) has occurred: %s', get_class($ex), $ex->getMessage());
      if (function_exists('wp_debug_backtrace_summary')) {
        $log_message .= ' Backtrace: ' . wp_debug_backtrace_summary();
      }
      LogHelper::write_log($this->name_hash, 'error', $log_message);
      LogHelper::write_log($this->name_hash, 'error', $display_log_message);
      $this->set_status(Package_Status::ERROR);
    }
    PackageHelper::unschedule_build($this->id);
    LogHelper::write_log($this->name_hash, 'info', "Unschedule Package {$this->id}");
    $status = $this->status == 100 ? true : false;
    @ignore_user_abort(false);
  }

  public function rename_win($oldfile, $newfile) {
    if (!rename($oldfile, $newfile)) {
      if (copy($oldfile, $newfile)) {
        unlink($oldfile);
        return true;
      }
      return false;
    }
    return true;
  }

  public function build_clean_up() {
    LogHelper::write_log($this->name_hash, 'info', "//========CLEAN UP: START========//");
    $this->set_status(Package_Status::CLEANSTART);

    $files = PackageHelper::list_files(NJT_FASTDUP_ARCHIVE_DIR_PATH_TMP);
    $new_path = NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES;

    foreach ($files as $file) {
      if (file_exists($file) && is_file($file)) {
        $name = basename($file);
        $to = "{$new_path}/{$name}";
        $status = $this->rename_win($file, $to);
        LogHelper::write_log($this->name_hash, 'info', "Move File {$file} To Packages Directory " . ($status ? "Successfully" : "Unsuccessfully"));
      }
    }

    $this->set_status(Package_Status::CLEANDONE);
    $this->set_status(Package_Status::COMPLETE);
    LogHelper::write_log($this->name_hash, 'info', "//========CLEAN UP: DONE========//");
    LogHelper::write_log($this->name_hash, 'info', "//========PACKAGE: DONE========//");
  }

  public function update_archive_filter_template($template_id, $archive_filter) {
    $template_data = Template::get_template($template_id);
    $template_data['archive_filter'] = $archive_filter;
    $updated = Template::update_template($template_id, $template_data);
    return $updated;
  }

  public function schedule_build($package_id) {
    PackageHelper::unschedule_build($package_id);
    return wp_schedule_event(time() + 3, 'njt-fastdup-month', 'njt-fastdup-event', array($package_id));
  }

  /**
   * Create Package
   * @param $template_id
   * @return boolean
   */
  public function create_package($template_id, $archive_filter) {
    PackageHelper::update_option_package_active('', 'database', true, '');
    $filter_exts = array();
    $filter_tables = array();

    if ($template_id !== 'only_db') {
      if ($template_id !== 'full_site') {
        $status_update_template = $this->update_archive_filter_template($template_id, $archive_filter);
        if (!$status_update_template) {
          return false;
        }

        $template_data = Template::get_template($template_id);
        $archive_filter = isset($template_data['archive_filter']) ? $template_data['archive_filter'] : array();

        $format_file_dirs = Helper::format_path_filter($template_data['filter_file_dirs'], true);
        $format_file_dirs = PackageHelper::format_file_dirs($format_file_dirs);

        if (isset($template_data['filter_exts'])) {
          $filter_exts = Helper::format_path_filter($template_data['filter_exts'], true);
        }

        if (isset($template_data['filter_tables'])) {
          $filter_tables = $template_data['filter_tables'];
        }
      } else {
        $format_file_dirs = array(
          'filter_dirs' => array(NJT_FASTDUP_ARCHIVE_DIR_PATH),
          'filter_files' => array(),
        );
      }

      if (count($archive_filter['size_checks']) > 0) {
        foreach ($archive_filter['size_checks'] as $key => $value) {
          $full_path = Helper::format_path_filter(NJT_FASTDUP_WEB_ROOTPATH . trim($value));
          if (is_file($full_path)) {
            $format_file_dirs['filter_files'][] = $full_path;
          }
        }
      }
    }

    // Package
    $package_name = isset($template_data['package_name']) ? $template_data['package_name'] : '';
    $this->name = Helper::default_package_name($package_name);
    $this->hash = time();
    $this->name_hash = sanitize_text_field("{$this->name}_{$this->hash}");
    $this->created = date("Y-m-d H:i:s");
    $this->status = Package_Status::CREATED;

    // Template
    $this->template_id = $template_id;

    //Archive
    $this->Archive->file = "{$this->name_hash}_archive.zip";
    $this->Archive->compress_dir = rtrim(NJT_FASTDUP_WEB_ROOTPATH, '/');
    $this->Archive->is_filter = isset($template_data['enable_file_dirs_filter']) ? 1 : 0;
    $this->Archive->export_onlydb = $template_id == 'only_db' ? true : (isset($template_data['export_onlydb']) ? $template_data['export_onlydb'] : false);
    $this->Archive->filter_dirs = $format_file_dirs['filter_dirs'];
    $this->Archive->filter_files = $format_file_dirs['filter_files'];
    $this->Archive->filter_exts = $filter_exts;
    $this->Archive->read_checks = isset($archive_filter['read_checks']) ? $archive_filter['read_checks'] : array();
    $this->Archive->recursive_links = isset($archive_filter['recursive_links']) ? $archive_filter['recursive_links'] : array();
    $this->Archive->addon_sites = isset($archive_filter['addon_sites']) ? $archive_filter['addon_sites'] : array();

    // Database
    $this->Database->file = "{$this->name_hash}_database.sql";
    global $wpdb;
    $this->Database->prefix = $wpdb->prefix;
    $this->Database->is_filter = isset($template_data['enable_table_filter']) ? $template_data['enable_table_filter'] : false;
    $this->Database->filter_tables = $filter_tables;

    //JSON
    $this->site_url = $wpdb->get_var("SELECT option_value from $wpdb->options where option_name = 'siteurl'");
    // $this->site_url = get_option('siteurl');

    // Create Package
    $package = Database::create_new_package($this);
    $this->id = $package['id'];

    LogHelper::write_log($this->name_hash, 'info', "//========PACKAGE: START========//");
    if (isset($this->id)) {
      PackageHelper::update_option_package_active($this->id, 'database', true, '');
      $this->njt_fastdup_archive_name = basename(WP_CONTENT_DIR) . '/njt-fastdup';
      $this->njt_fastdup_dir_plugin = basename(WP_CONTENT_DIR) . '/plugins';
      $data_log = json_encode($this, JSON_PRETTY_PRINT);
      LogHelper::write_log($this->name_hash, 'info', "Create Package: ID: {$this->id}");
      LogHelper::write_log($this->name_hash, 'info', "Package Data : {$data_log}");

      // JSON
      if (!$this->Archive->export_onlydb) {
        LogHelper::write_log($this->name_hash, 'info', "//========SCAN PACKAGE JSON: START========//");
        $this->scan_file = "{$this->name_hash}_scan.json";
        PackageHelper::write_file_scan($this->scan_file, $data_log);
        LogHelper::write_log($this->name_hash, 'info', "//========SCAN PACKAGE JSON: DONE========//");
      }
      Database::update_package($this->id, array('package' => serialize($this)));
    } else {
      LogHelper::write_log($this->name_hash, 'error', "Create Package Fail");
    }
    return $this->id;
  }

  public static function delete_package($id, $name_hash) {
    PackageHelper::delete_package_local_storage($name_hash);
    $result = Database::delete_package($id);
    return $result;
  }

  public function list_package() {
    $packages = Database::get_packages();
    $list_package = array();

    foreach ($packages as $key => $package) {
      $package_data = unserialize($package['package']);
      $archive_path = $package_data->store_path . '/' . $package_data->Archive->file;

      // Package Size
      if (file_exists($archive_path)) {
        $package_size = Helper::format_bytes(filesize($archive_path));
      } else {
        $package_size = __("In Process", 'njt-fastdup');
      }

      $created_format = get_date_from_gmt(gmdate('Y-m-d H:i:s', strtotime($package['created'])));
      $created = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($created_format));

      $name_hash = $package['name'] . '_' . $package['hash'];
      $archive_path = Helper::safe_path_slash(NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES . DIRECTORY_SEPARATOR . "{$name_hash}_archive.zip");
      $log_path = Helper::safe_path_slash(NJT_FASTDUP_PATH_LOG . DIRECTORY_SEPARATOR . "logs-{$name_hash}.txt");

      $files_download = array();
      if (file_exists($archive_path)) {
        $files_download[] = array(
          'key' => 'archive',
          'title' => __('Archive', 'njt-fastdup'),
          'size' => Helper::format_bytes(filesize($archive_path)),
        );
      }

      if (file_exists($log_path)) {
        $files_download[] = array(
          'key' => 'log',
          'title' => __('Log', 'njt-fastdup'),
          'size' => Helper::format_bytes(filesize($log_path)),
        );
      }

      $package = array(
        'key' => $key,
        'id' => $package['id'],
        'name_hash' => sanitize_text_field($package['name'] . "_" . $package['hash']),
        'size' => $package_size,
        'status' => (int) $package['status'],
        'created' => $created,
        'is_hover' => false,
        'files_download' => $files_download,
      );
      $list_package[] = $package;
    }
    return $list_package;
  }

  public static function run_scanner($template_id) {
    // Template
    $system_scan = ServerHelper::system_scan();
    $archive = array();
    $archive_filter = array();
    $is_export_onlydb = false;

    if ($template_id !== 'only_db') {
      $template_data = Template::get_template($template_id);
      $is_export_onlydb = isset($template_data['export_onlydb']) ? $template_data['export_onlydb'] : false;

      if (!$is_export_onlydb) {
        if (count($template_data) > 0) {
          $skip_filter_template = array(
            'filter_file_dirs' => $template_data['filter_file_dirs'],
            'filter_exts' => $template_data['filter_exts'],
          );
        } else {
          $skip_filter_template = array(
            'filter_file_dirs' => NJT_FASTDUP_ARCHIVE_NAME,
            'filter_exts' => "",
          );
        }

        $package_scan = new Package\Package_Scan();
        $archive_scan = $package_scan->archive_scan(NJT_FASTDUP_WEB_ROOTPATH, $skip_filter_template);

        $archive = array(
          'addon_sites' => $archive_scan->addon_sites,
          'size_checks' => $package_scan->format_file_large($archive_scan->size_checks),
          'read_checks' => $archive_scan->unreadable,
          'recursive_links' => $archive_scan->recursive_links,
        );
        $archive_filter = isset($template_data['archive_filter']) ? $template_data['archive_filter'] : array();
      }
    }

    return array(
      'general_require' => $system_scan,
      'archive' => $archive,
      'archive_filter' => $archive_filter,
      'is_export_onlydb' => $is_export_onlydb,
    );
  }

  public function set_status($percent) {
    if (isset($this->id)) {
      Database::update_package($this->id, array('status' => $percent));
    }
  }

  public static function build_event($package_id) {
    $package = Package::initial_package_by_id($package_id);
    $package->id = $package_id;
    $package->build_package();
  }

  public static function initial_package_by_id($package_id) {
    $obj = new Package();
    $row = Database::get_package_id($package_id);

    if (is_object($row)) {
      $obj = @unserialize($row->package);
    }
    $obj = (is_object($obj)) ? $obj : null;
    return $obj;
  }

  public function get_local_package_file($type_file) {
    $result = null;
    switch ($type_file) {
      case 'installer':
        $file_type = '.php';
        $file_path = Helper::safe_path(NJT_FASTDUP_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'installer.tmpl');
        break;

      case 'log':
        $file_type = '.txt';
        $file_name = 'logs-' . $this->name_hash . $file_type;
        $file_path = Helper::safe_path(NJT_FASTDUP_PATH_LOG) . DIRECTORY_SEPARATOR . "$file_name";
        break;

      case 'archive':
        $file_type = '.zip';
        $file_name = $this->name_hash . '_' . $type_file . $file_type;
        $file_path = Helper::safe_path(NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES) . DIRECTORY_SEPARATOR . "$file_name";
        break;
    }

    if (file_exists($file_path)) {
      $result = $type_file == "archive" ? $this->store_url . "$file_name" : $file_path;
    }
    return $result;
  }
}
