<?php
namespace NJT\FastDup\Admin\Database;

defined('ABSPATH') || exit;
class Database {
  /**
   * PACKAGES
   */
  public static function get_packages() {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_packages";
    $query = $wpdb->get_results("SELECT * FROM `{$table_name}` ORDER BY id DESC", ARRAY_A);
    return $query;
  }

  public static function get_package_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_packages";
    $sql = $wpdb->prepare("SELECT * FROM `{$table_name}` WHERE ID = %d", $id);
    $row = $wpdb->get_row($sql);
    return $row;
  }

  public static function update_package($id, $fields = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_packages";
    $result = $wpdb->update($table_name, $fields, array("id" => $id));

    return $result === 0 || $result > 0 ? true : false;
  }

  public static function delete_package($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_packages";
    $result = $wpdb->delete($table_name, array('id' => $id));
    return $result;
  }

  public static function create_table_njt_fastdup_packages() {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_packages";

    //PRIMARY KEY must have 2 spaces before for dbDelta to work
    //see: https://codex.wordpress.org/Creating_Tables_with_Plugins
    $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(250) NOT NULL,
                hash VARCHAR(50) NOT NULL,
                status INT(11) NOT NULL,
                created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                package MEDIUMBLOB NOT NULL,
                PRIMARY KEY  (id),
                KEY hash (hash))";
    require_once NJT_FASTDUP_WEB_ROOTPATH . 'wp-admin/includes/upgrade.php';
    @dbDelta($sql);
  }

  public static function create_table_njt_fastdup_entities() {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_entities";

    //PRIMARY KEY must have 2 spaces before for dbDelta to work
    //see: https://codex.wordpress.org/Creating_Tables_with_Plugins
    $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
                id INT NOT NULL AUTO_INCREMENT,
                type VARCHAR(255) NOT NULL,
                data TEXT NOT NULL,
                PRIMARY KEY  (id))";

    require_once NJT_FASTDUP_WEB_ROOTPATH . 'wp-admin/includes/upgrade.php';
    @dbDelta($sql);
  }

  public static function create_new_package($package) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_packages";
    $row = $wpdb->insert($table_name,
      array(
        'name' => $package->name,
        'hash' => $package->hash,
        'status' => $package->status,
        'created' => $package->created,
        'package' => serialize($package),
      )
    );
    return array('status' => $row, 'id' => $wpdb->insert_id);
  }

  public static function get_tables() {
    global $wpdb;
    $tables = $wpdb->get_results("SHOW FULL TABLES FROM `" . DB_NAME . "` WHERE Table_Type = 'BASE TABLE' ", ARRAY_N);
    return $tables;
  }

  /**
   * ENTITIES
   */
  public static function create_entity($type, $entity) {
    global $wpdb;

    $table_name = $wpdb->prefix . "njt_fastdup_entities";

    $query = "INSERT INTO " . $table_name;
    $query .= " (type, data) VALUES (%s, %s)";

    $serialized_entity = json_encode($entity);
    $prepared_query = $wpdb->prepare($query, $type, $serialized_entity);

    $wpdb->query($prepared_query);

    if ($wpdb->insert_id) {
      return true;
    } else {
      return false;
    }
  }

  public static function get_entities($type) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_entities";

    $query = "SELECT id, data FROM " . $table_name;
    $query .= " WHERE type = %s";

    $prepared_query = $wpdb->prepare($query, $type);

    $results = $wpdb->get_results($prepared_query, ARRAY_A);

    return $results;
  }

  public static function get_entity($type, $entity_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_entities";

    $query = "SELECT id, data FROM " . $table_name;
    $query .= " WHERE id = %d AND type = %s ";

    $prepared_query = $wpdb->prepare($query, $entity_id, $type);

    $result = $wpdb->get_results($prepared_query, ARRAY_A);

    return isset($result[0]) ? $result[0] : $result;
  }

  public static function update_entity($type, $entity_id, $entity_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_entities";

    $serialized_data = json_encode($entity_data);
    $result = $wpdb->update($table_name, array('type' => $type, 'data' => $serialized_data), array("id" => $entity_id));

    return $result === 0 || $result > 0 ? true : false;
  }

  public static function delete_entity($entity_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_entities";
    $result = $wpdb->delete($table_name, array('id' => $entity_id));
    return $result;
  }

  public static function delete_list_entity($ids) {
    global $wpdb;
    $table_name = $wpdb->prefix . "njt_fastdup_entities";

    $ids = implode(',', array_map('absint', $ids));
    $query = "DELETE FROM " . $table_name;
    $query .= " WHERE id IN($ids)";

    $result = $wpdb->query($query);

    return $result;
  }

}
