<?php
namespace NJT\FastDup\Admin;

use NJT\FastDup\Admin\Database\Database as Database;

defined('ABSPATH') || exit;
class Template {
  /**
   * TEMPLATE
   */
  const TYPE_ENTITY = 'NJT_FASTDUP_TEMPLATE_ENTITY';

  public $template_name;

  public $package_name;

  public $export_onlydb = 0;

  public $filter_file_dirs = array();

  public $filter_exts = "";

  public $filter_tables = array();

  /**
   * __CONSTRUCT
   */
  public function __construct() {
  }

  public static function create_template($template_data) {
    $result = Database::create_entity(self::TYPE_ENTITY, $template_data);
    return $result;
  }

  public static function get_list_template() {
    $templates = Database::get_entities(self::TYPE_ENTITY);
    $results = array(
      array(
        "id" => "only_db",
        "current_template" => "default",
        "template_name" => __('Only Database', 'njt-fastdup'),
      ),
      array(
        "id" => "full_site",
        "current_template" => "default",
        "template_name" => __('Full Site', 'njt-fastdup'),
      ),
    );

    foreach ($templates as $key => $template) {
      $data = json_decode($template['data']);
      if ($data) {
        $results[] = array(
          'id' => $template['id'],
          'template_name' => $data->template_name,
          'is_hover' => false,
        );
      }
    }
    return $results;
  }

  public static function get_template($template_id) {
    $template = Database::get_entity(self::TYPE_ENTITY, $template_id);
    if (count($template) > 0) {
      $template = json_decode($template['data'], true);
      if (is_array($template['filter_file_dirs']) && count($template['filter_file_dirs']) == 0) {
        $template['filter_file_dirs'] = '';
      }
    }
    return $template;
  }

  public static function update_template($template_id, $template_data) {
    $template = Database::update_entity(self::TYPE_ENTITY, $template_id, $template_data);
    return $template;
  }

  public static function delete_template($template_id) {
    $template = Database::delete_entity($template_id);
    return $template;
  }

  public static function delete_selected_template($template_id) {
    $template = Database::delete_list_entity($template_id);
    return $template;
  }
}
