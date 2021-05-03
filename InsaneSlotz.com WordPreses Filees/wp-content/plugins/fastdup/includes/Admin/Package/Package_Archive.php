<?php
namespace NJT\FastDup\Admin\Package;

use NJT\FastDup\Admin\Helper\Helper;
use NJT\FastDup\Admin\Helper\LogHelper;
use NJT\FastDup\Admin\Helper\PackageHelper;
use NJT\FastDup\Admin\Package_Status;

defined('ABSPATH') || exit;
class Package_Archive {

  // Filter Archive
  public $is_filter;
  public $export_onlydb;
  public $filter_exts = array();
  public $filter_dirs = array();
  public $filter_files = array();
  public $read_checks = array();
  public $recursive_links = array();
  public $addon_sites = array();

  // Zip
  public $file;
  public $sql_path;
  public $zip_archive;
  public $zip_path;
  public $compress_dir;

  public $unreadable_log = array();
  public $dir_exclude_log = array();
  public $recursive_links_log = array();
  public $exts_exclude_log = array();
  public $add_file_error_log = array();
  public $file_exclude_log = array();

  // Package Object
  protected $package;

  /**
   * Builds the archive with ZipArchive
   *
   * @param obj $package The package object that started this process
   * @return boolean
   */
  public function build($package) {
    $this->package = $package;
    $this->package->set_status(Package_Status::ARCSTART);
    PackageHelper::update_option_package_active($this->package->id, 'archive', true, '');
    LogHelper::write_log($this->package->name_hash, 'info', "//========ARCHIVE: START========//");
    if (class_exists('ZipArchive')) {
      $status = $this->zip();
      LogHelper::write_log($this->package->name_hash, 'info', "Zip Source " . ($status ? 'Successfully' : 'Unsuccessfully'));
    } else {
      LogHelper::write_log($this->package->name_hash, 'error', "ZipArchive Not Support");
      $status = false;
    }

    if (!$status) {
      $this->package->set_status(Package_Status::ERROR);
    } else {
      $this->package->set_status(Package_Status::ARCDONE);
    }
    LogHelper::write_log($this->package->name_hash, 'info', "//========ARCHIVE: DONE========//");
  }

  public function zip() {
    try {
      $this->compress_dir = rtrim(wp_normalize_path(Helper::safe_path($this->compress_dir)), '/');
      $this->sql_path = Helper::safe_path("{$this->package->store_path_temp}" . DIRECTORY_SEPARATOR . "{$this->package->Database->file}");
      $this->zip_path = Helper::safe_path_slash("{$this->package->store_path_temp}" . DIRECTORY_SEPARATOR . "{$this->file}");
      $this->scan_path = Helper::safe_path("{$this->package->store_path_temp}" . DIRECTORY_SEPARATOR . "{$this->package->scan_file}");

      $install_arc_folder = 'njt-fastdup-installer';
      $files_install = array();

      if ($this->export_onlydb) {
        $files_install = array(
          'database' => array(
            'file_path' => $this->sql_path,
            'zip_path' => "$install_arc_folder/database.sql",
          ),
        );
      } else {
        $files_install = array(
          'database' => array(
            'file_path' => $this->sql_path,
            'zip_path' => "$install_arc_folder/database.sql",
          ),
          'scan_json' => array(
            'file_path' => $this->scan_path,
            'zip_path' => "$install_arc_folder/scan_package.json",
          ),
          'htaccess' => array(
            'file_path' => NJT_FASTDUP_WEB_ROOTPATH . '.htaccess',
            'zip_path' => "$install_arc_folder/htaccess.origin",
          ),
          'wp-config' => array(
            'file_path' => file_exists(NJT_FASTDUP_WEB_ROOTPATH . 'wp-config.php') ? NJT_FASTDUP_WEB_ROOTPATH . 'wp-config.php' : NJT_FASTDUP_PLUGIN_PATH . '/inc/wp-config.tmpl',
            'zip_path' => "$install_arc_folder/wp-config.origin",
          ),
          'robots' => array(
            'file_path' => NJT_FASTDUP_WEB_ROOTPATH . 'robots.txt',
            'zip_path' => "$install_arc_folder/robots.origin",
          ),
        );
      }

      $zip_archive = new \ZipArchive();
      if ($zip_archive->open($this->zip_path, \ZipArchive::CREATE) === false) {
        LogHelper::write_log($this->package->name_hash, 'error', "Cannot open Zip file with PHP ZipArchive.");
        return false;
      }

      foreach ($files_install as $key => $item) {
        $file_path = Helper::safe_path($item['file_path']);
        $zip_path = Helper::safe_path_slash($item['zip_path']);
        LogHelper::write_log($this->package->name_hash, 'info', "File Path: " . $file_path);
        if (file_exists($file_path) && is_file($file_path)) {
          $status_file = $zip_archive->addFile($file_path, $zip_path);
          LogHelper::write_log($this->package->name_hash, 'info', "ZIP {$key}: " . ($status_file ? 'successfully' : 'unsuccessfully'));
        } else {
          LogHelper::write_log($this->package->name_hash, 'info', "ZIP {$key}: Not Found");
        }
      }

      if (!$this->export_onlydb) {
        LogHelper::write_log($this->package->name_hash, 'info', "-----ZIP Source: Start-----");
        $this->zip_source($zip_archive, $this->compress_dir, array(
          'exts_exclude' => array_filter($this->filter_exts, 'strlen'),
          'dirs_exclude' => array_merge($this->filter_dirs, $this->addon_sites, $this->recursive_links),
          'files_exclude' => $this->filter_files,
        ));

        register_shutdown_function(array($this, "check_for_fatal"));
        set_error_handler(array($this, "log_error"));
        set_exception_handler(array($this, "log_error"));
        ini_set("display_errors", "off");
        error_reporting(E_ALL);

        if (count($this->unreadable_log) > 0) {
          LogHelper::write_log($this->package->name_hash, 'info', "---Unreadable Start---");
          LogHelper::write_log($this->package->name_hash, 'info', $this->unreadable_log);
          LogHelper::write_log($this->package->name_hash, 'info', "---Unreadable End---");
        }

        if (count($this->dir_exclude_log) > 0) {
          LogHelper::write_log($this->package->name_hash, 'info', "---Directory Exclude Start---");
          LogHelper::write_log($this->package->name_hash, 'info', $this->dir_exclude_log);
          LogHelper::write_log($this->package->name_hash, 'info', "---Directory Exclude End---");
        }

        if (count($this->recursive_links_log) > 0) {
          LogHelper::write_log($this->package->name_hash, 'info', "---Recursive Links Start---");
          LogHelper::write_log($this->package->name_hash, 'info', $this->recursive_links_log);
          LogHelper::write_log($this->package->name_hash, 'info', "---Recursive Links End---");
        }

        if (count($this->exts_exclude_log) > 0) {
          LogHelper::write_log($this->package->name_hash, 'info', "---Extensions Exclude Start---");
          LogHelper::write_log($this->package->name_hash, 'info', $this->exts_exclude_log);
          LogHelper::write_log($this->package->name_hash, 'info', "---Extensions Exclude End---");
        }

        if (count($this->file_exclude_log) > 0) {
          LogHelper::write_log($this->package->name_hash, 'info', "---File Exclude Start---");
          LogHelper::write_log($this->package->name_hash, 'info', $this->file_exclude_log);
          LogHelper::write_log($this->package->name_hash, 'info', "---File Exclude End---");
        }

        if (count($this->add_file_error_log) > 0) {
          LogHelper::write_log($this->package->name_hash, 'info', "---Add File Error Start---");
          LogHelper::write_log($this->package->name_hash, 'info', $this->add_file_error_log);
          LogHelper::write_log($this->package->name_hash, 'info', "---Add File Error End---");
        }
        LogHelper::write_log($this->package->name_hash, 'info', "-----ZIP Source: End-----");
      }

      $status = $zip_archive->close();
    } catch (\Throwable $e) {
      $this->package->set_status(Package_Status::ERROR);
      $status = false;
      $trace = $e->getTrace();
      $log_message = $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . ' called from ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'];
      LogHelper::write_log($this->package->name_hash, 'error', $log_message);
    } catch (\Exception $ex) {
      $this->package->set_status(Package_Status::ERROR);
      $status = false;
      $log_message = 'Exception (' . get_class($ex) . ') occurred during restore: ' . $ex->getMessage() . ' (Code: ' . $ex->getCode() . ', line ' . $ex->getLine() . ' in ' . $ex->getFile() . ')';
      $display_log_message = sprintf('A PHP exception (%s) has occurred: %s', get_class($ex), $ex->getMessage());
      if (function_exists('wp_debug_backtrace_summary')) {
        $log_message .= ' Backtrace: ' . wp_debug_backtrace_summary();
      }
      LogHelper::write_log($this->package->name_hash, 'error', $log_message);
      LogHelper::write_log($this->package->name_hash, 'error', $display_log_message);
    } catch (\Error $ex) {
      $this->package->set_status(Package_Status::ERROR);
      $status = false;
      $log_message = 'PHP Fatal error (' . get_class($ex) . ') has occurred. Error Message: ' . $ex->getMessage() . ' (Code: ' . $ex->getCode() . ', line ' . $ex->getLine() . ' in ' . $ex->getFile() . ')';
      $display_log_message = sprintf('A PHP fatal error (%s) has occurred: %s', get_class($ex), $ex->getMessage());
      if (function_exists('wp_debug_backtrace_summary')) {
        $log_message .= ' Backtrace: ' . wp_debug_backtrace_summary();
      }
      LogHelper::write_log($this->package->name_hash, 'error', $log_message);
      LogHelper::write_log($this->package->name_hash, 'error', $display_log_message);
    }
    return $status;
  }

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
  public function log_error($num, $str, $file, $line, $context = null) {
    $this->log_exception(new \ErrorException($str, 0, $num, $file, $line));
  }

/**
 * Uncaught exception handler.
 */
  public function log_exception(\Exception $e) {
    $message = "Type: " . get_class($e) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
    LogHelper::write_log($this->package->name_hash, 'error', $message);
    $this->package->set_status(Package_Status::ERROR);
  }

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
  public function check_for_fatal() {
    $error = error_get_last();
    if (is_null($error)) { return; }
    if ($error["type"] == E_ERROR) {
      $this->log_error($error["type"], $error["message"], $error["file"], $error["line"]);
    }

  }

  private function is_utf8_file_safe($file) {
    $is_safe = true;
    $original_file = $file;

    if (PackageHelper::hasUTF8($file)) {
      $file = utf8_decode($file);
    }

    if (file_exists($file) === false) {
      if (file_exists($original_file) === false) {
        $this->unreadable_log[] = $file;
        $is_safe = false;
      }
    }

    return $is_safe;
  }

  public function zip_source(&$zipArchive, $directory, $exclude) {
    $exclude_default = array('.', '..', '.git', 'node_modules', '.vscode', '.DS_Store', '.svn', '.gitignore', '.gitkeep', '.gitmodules', '.gitattributes', '.github', '.md', '.cache', '.db', 'Thumbs', '.DS_STORE');
    $files = array_diff(@scandir($directory), $exclude_default);

    foreach ($files as $key => $file) {
      $file_path = str_replace('//', '/', "{$directory}/{$file}");
      if (preg_match('/[^\x20-\x7f]/', $file_path)) {
        if (!$this->is_utf8_file_safe($file_path)) {
          continue;
        }
      }

      if (is_link($file_path)) {
        $this->recursive_links_log[] = $file_path;
        continue;
      }

      if (!is_readable($file_path)) {
        $this->unreadable_log[] = $file_path;
        continue;
      }

      // Directory
      if (is_dir($file_path)) {
        //Skip dir from template
        if (count($exclude['dirs_exclude']) > 0) {
          if (in_array($file_path, $exclude['dirs_exclude'])) {
            $this->dir_exclude_log[] = $file_path;
            continue;
          }
        }

        $empty_dir_path = substr($directory, strlen($this->compress_dir) + 1);
        if ($empty_dir_path != '') {
          $zipArchive->addEmptyDir($empty_dir_path);
        }
        $this->zip_source($zipArchive, $file_path, $exclude);
      } else {
        //Skip Extensions from template
        if (count($exclude['exts_exclude']) > 0) {
          $file_exts = pathinfo($file_path, PATHINFO_EXTENSION);
          if (in_array($file_exts, $exclude['exts_exclude'])) {
            $this->exts_exclude_log[] = $file_path;
            continue;
          }
        }

        //Skip file from template
        if (count($exclude['files_exclude']) > 0) {
          if (in_array($file_path, $exclude['files_exclude'])) {
            $this->file_exclude_log[] = $file_path;
            continue;
          }
        }

        $relative_path = substr($file_path, strlen($this->compress_dir) + 1);
        $zip_status = $zipArchive->addFile($file_path, $relative_path);
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
          $zipArchive->setCompressionName($relative_path, \ZipArchive::CM_STORE);
        }

        if (!$zip_status) {
          array_push($this->add_file_error_log, $file_path);
          continue;
        }
      }
    }
  }
}
