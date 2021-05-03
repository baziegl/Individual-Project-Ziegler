<?php
namespace NJT\FastDup\Endpoint;

use NJT\FastDup\Admin\Database\Database as Database;
use NJT\FastDup\Admin\Helper\Helper as Helper;
use NJT\FastDup\Admin\Helper\LogHelper;
use NJT\FastDup\Admin\Helper\PackageHelper;
use NJT\FastDup\Admin\Helper\ServerHelper;
use NJT\FastDup\Admin\Package as Package;

defined('ABSPATH') || exit;
class PackageApi {
  /**
   * Instance of this class.
   *
   * @since    0.8.1
   * @var      object
   */
  protected static $instance = null;

  protected $package;

  /**
   * Return an instance of this class.
   *
   * @since     0.8.1
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {

    // If the single instance hasn't been set, set it now.
    if (null == self::$instance) {
      self::$instance = new self;
      self::$instance->do_hooks();
    }

    return self::$instance;
  }

  /**
   * Initialize the plugin by setting localization and loading public scripts
   * and styles.
   *
   * @since     0.8.1
   */
  private function __construct() {
    $this->package = new Package();
  }

  /**
   * Set up WordPress hooks and filters
   *
   * @return void
   */
  public function do_hooks() {
    add_action('rest_api_init', array($this, 'register_routes'));
  }

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $namespace = 'njt-fastdup/v1';
    // Rest Api
    register_rest_route($namespace, '/packages', array(
      array(
        'methods' => 'GET',
        'callback' => array($this, 'process_build'),
        'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
      ),
      array(
        'methods' => 'POST',
        'callback' => array($this, 'build_package'),
        'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
      )
    ));

    register_rest_route($namespace, '/packages/scan-package', array(
      'methods' => 'POST',
      'callback' => array($this, 'scan_package'),
      'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
    ));

    register_rest_route($namespace, '/packages/download', array(
      'methods' => 'POST',
      'callback' => array($this, 'download'),
      'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
    ));

    register_rest_route($namespace, "/packages/view-log", array(
      'methods' => 'GET',
      'callback' => array($this, 'view_log'),
      'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
    ));

    register_rest_route($namespace, '/packages/update-status', array(
      'methods' => 'POST',
      'callback' => array($this, 'update_status'),
      'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
    ));

    register_rest_route($namespace, '/packages/delete', array(
      'methods' => 'POST',
      'callback' => array($this, 'delete_package'),
      'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
    ));

    register_rest_route($namespace, '/packages/multi-delete', array(
      'methods' => 'POST',
      'callback' => array($this, 'multi_delete'),
      'permission_callback' => array($this, 'njt_fastdup_permissions_check'),
    ));
  }

  public function scan_package($request) {
    $template_id = $request->get_json_params()['template_id'];

    ServerHelper::set_limit_excute_script();
    Helper::init_archive_directory();
    $scan_data = Package::run_scanner($template_id);

    $response_object = array(
      'status' => $scan_data ? true : false,
      'scan_data' => $scan_data,
    );
    return new \WP_REST_Response($response_object, 200);
  }

  public function delete_package($request) {
    $payload = $request->get_json_params();
    $option_package_active = get_option(NJT_FASTDUP_PACKAGE_ACTIVE);
    if ($payload['id'] == $option_package_active['package_id']) {
      PackageHelper::update_option_package_active('', '', false, '');
    }
    $result = Package::delete_package($payload['id'], $payload['name_hash']);
    $list_package = $this->package->list_package();

    $response_object = array(
      'status' => $result ? true : false,
      'list_package' => $list_package,
      'message' => $result ? __('Delete Successfully!', 'njt-fastdup') : __('Delete Unsuccessfully!', 'njt-fastdup'),
    );
    return new \WP_REST_Response($response_object, 200);
  }

  public function multi_delete($request) {
    $payload = $request->get_json_params();
    $packages = $payload['packages'];

    foreach ($packages as $key => $package) {
      Package::delete_package($package['id'], $package['name_hash']);
    }
    $list_package = $this->package->list_package();

    $response_object = array(
      'status' => true,
      'list_package' => $list_package,
      'message' => __('Delete Selected Successfully!', 'njt-fastdup'),
    );
    return new \WP_REST_Response($response_object, 200);
  }

  public function build_package($request) {
    $payload = $request->get_json_params();
    $template_id = $payload['template_id'];
    $archive_filter = $payload['archive_filter'];

    $old_id = get_option(NJT_FASTDUP_PACKAGE_ACTIVE)['package_id'];
    if (isset($old_id)) {
      PackageHelper::unschedule_build($old_id);
    }

    $package_id = $this->package->create_package($template_id, $archive_filter);
    if (isset($package_id)) {
      $is_schedule = $this->package->schedule_build($package_id);
    }

    $response_object = array(
      'status' => $is_schedule ? true : false,
      'result' => array(
        'package_id' => $package_id,
      ),
      'message' => $is_schedule ? __('Create Package Successfully!', 'njt-fastdup') : __('Create Package Unsuccessfully!', 'njt-fastdup'),
    );
    return new \WP_REST_Response($response_object, 200);
  }

  public function process_build() {
    $option = get_option(NJT_FASTDUP_PACKAGE_ACTIVE);
    $package = Database::get_package_id($option['package_id']);
    $process_percent = isset($package->status) ? $package->status : 0;

    $files_download = array();
    if ($process_percent == 100) {
      $package = Package::initial_package_by_id($option['package_id']);
      $archive_path = NJT_FASTDUP_ARCHIVE_DIR_PATH_PACKAGES . '/' . "{$package->name_hash}_archive.zip";
      $log_path = NJT_FASTDUP_PATH_LOG . '/' . "logs-{$package->name_hash}.txt";
      if ($package) {
        array_push($files_download, array(
          'key' => 'archive',
          'title' => __('Archive', 'njt-fastdup'),
          'size' => file_exists($archive_path) ? Helper::format_bytes(filesize($archive_path)) : '0B',
        ));
        array_push($files_download, array(
          'key' => 'log',
          'title' => __('Log', 'njt-fastdup'),
          'size' => file_exists($log_path) ? Helper::format_bytes(filesize($log_path)) : '0B',
        ));
      }
    }

    $response_object = array(
      'list_package' => $this->package->list_package(),
      'files_download' => $files_download,
      'package_id' => $option['package_id'],
      'build_step' => $option['build_step'],
      'is_build' => $option['is_build'],
      'time_process' => $option['time_process'],
      'process_percent' => $process_percent,
    );
    return new \WP_REST_Response($response_object, 200);
  }

  /**
   * DOWNLOAD PACKAGE
   */
  public function download($request) {
    $payload = $request->get_json_params();
    $id = $payload['id'];
    $type = $payload['type'];

    if ($type == 'installer') {
      $package = new Package();
    } else {
      $package = Package::initial_package_by_id($id);
    }

    $file_path = $package->get_local_package_file($type);
    $file_content = null;
    $result = $file_path;

    if ($type == 'installer' || $type == 'log') {
      @session_write_close();
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private", false);
      header("Content-Transfer-Encoding: binary");

      if (isset($file_path)) {
        $fp = fopen($file_path, 'rb');
        if ($fp !== false) {
          $file_content = file_get_contents($file_path);
        }
      }
      $result = base64_encode($file_content);
    }

    $response_object = array(
      'status' => $result ? true : false,
      'result' => $result,
      'message' => $result ? __('Download Successfully!', 'njt-fastdup') : __('Download Unsuccessfully!', 'njt-fastdup'),
    );
    return new \WP_REST_Response($response_object, 200);
  }

  /**
   * Update status when status server > 500
   */
  public function update_status($request) {
    $payload = $request->get_json_params();
    $package_id = $payload['id'];
    $package = Package::initial_package_by_id($package_id);
    if ($package) {
      $name_hash = $package->name_hash;
      $result = Database::update_package($package_id, array('status' => -1));
      LogHelper::write_log($name_hash, 'error', '//========BUILD PACKAGE ERROR========//');
      LogHelper::write_log($name_hash, 'error', $payload['error']);
      LogHelper::write_log($name_hash, 'error', $payload['error_message']);
      LogHelper::write_log($name_hash, 'error', '//========BUILD PACKAGE ERROR========//');
      $option = get_option(NJT_FASTDUP_PACKAGE_ACTIVE);
      if ($option['package_id'] == $package_id) {
        PackageHelper::update_option_package_active($package_id, 'error', false, '');
      }
    } else {
      $result = false;
    }

    $list_package = $this->package->list_package();
    $response_object = array(
      'status' => $result ? true : false,
      'list_package' => $list_package,
    );
    return new \WP_REST_Response($response_object, 200);
  }

  public function view_log($request) {
    $payload = stripslashes_deep($request);
    $package = Package::initial_package_by_id($payload['package_id']);
    $name_hash = $package->name_hash;

    $file_path = Helper::safe_path(NJT_FASTDUP_PATH_LOG . DIRECTORY_SEPARATOR . "logs-{$name_hash}.txt");
    $result = file_get_contents($file_path);

    $response_object = array(
      'status' => $result ? true : false,
      'result' => $result,
    );
    return new \WP_REST_Response($response_object, 200);
  }

  /**
   * Check if a given request has permission
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function njt_fastdup_permissions_check($request) {
    return current_user_can('edit_posts');
  }
}
