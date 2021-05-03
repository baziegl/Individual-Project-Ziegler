<?php
namespace NJT\FastDup\Admin\Package;

use NJT\FastDup\Admin\Helper\Helper;

defined('ABSPATH') || exit;
class Package_Scan {
  public $recursive_links = array();
  public $unreadable = array();
  public $size_checks = array();
  public $addon_sites = array();

  /**
   * Scan directory
   */
  public function archive_scan($path, $skip_filter_template) {
    $filter_template = $this->format_file_dirs($skip_filter_template);
    $template_filter_dir = $filter_template['dir_path'];
    $template_filter_file = $filter_template['file_path'];
    $template_filter_exts = $filter_template['filter_exts'];

    $exclude = array('.', '..', '.git', 'node_modules', '.vscode', '.DS_Store', '.svn', '.gitignore', '.gitkeep', '.gitmodules', '.gitattributes', '.github', '.md', '.cache', '.db', 'Thumbs');
    if ($path === NJT_FASTDUP_WEB_ROOTPATH) {
      array_push($exclude, 'wp-admin', 'wp-includes');
      $files = array_diff(scandir($path), $exclude);
    } else {
      $files = array_diff(scandir($path), $exclude);

      if (in_array('wp-admin', $files) || in_array('wp-includes', $files)) {
        $config_path = Helper::safe_path($path . DIRECTORY_SEPARATOR . 'wp-config.php');
        if (file_exists($config_path)) {
          $this->addon_sites[] = $config_path;
        }
      }
    }

    foreach ($files as $key => $file) {
      $full_path = str_replace('//', '/', "{$path}/{$file}");

      // Unreadable
      if (!is_readable($full_path)) {
        $this->unreadable[] = $full_path;
        continue;
      }

      // Directory
      if (is_dir($full_path)) {
        //Skip dir from template
        if (count($template_filter_dir) > 0) {
          if (in_array($full_path, $template_filter_dir)) {
            continue;
          }
        }

        $add = true;
        if (is_link($full_path)) {
          $add = false;
          $this->recursive_links[] = $full_path;
        }

        if ($add) {
          $this->archive_scan($full_path, $skip_filter_template);
        }
      } else {
        //Skip Extensions from template
        if (count($template_filter_exts) > 0) {
          $file_exts = pathinfo($full_path, PATHINFO_EXTENSION);
          if (in_array($file_exts, $template_filter_exts)) {
            continue;
          }
        }

        //Skip file from template
        if (count($template_filter_file) > 0) {
          if (in_array($full_path, $template_filter_file)) {
            continue;
          }
        }

        // File Large
        $is_image = $this->is_file_image($full_path);
        $file_size = $this->is_file_large(filesize($full_path), $is_image);
        if ($file_size) {
          array_push($this->size_checks, array(
            "full_path" => $full_path,
            'file_size' => $file_size,
          ));
        }
      }
    }
    return $this;
  }

  private function format_file_dirs($template_filter_data) {
    $result = array(
      'filter_exts' => explode(";", $template_filter_data['filter_exts'], -1),
      'file_path' => array(),
      'dir_path' => array(),
    );

    if (isset($template_filter_data['filter_file_dirs'])) {
      $file_dirs = explode(";", $template_filter_data['filter_file_dirs'], -1);
      foreach ($file_dirs as $key => $value) {
        $full_path = NJT_FASTDUP_WEB_ROOTPATH . trim($value);
        if (is_dir($full_path)) {
          $result['dir_path'][] = $full_path;
        } else {
          $result['file_path'][] = $full_path;
        }
      }
    }

    return $result;
  }

  private function is_file_image($file_path) {
    $extension = pathinfo($file_path, PATHINFO_EXTENSION);
    if (in_array(strtolower($extension), array('apng', 'bmp', 'gif', 'ico', 'cur', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'svg', 'tif', 'tiff', 'webp'))) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Format file size from bytes
   * If file is image > 4MB and other file is > 150MB
   * @param $bytes file
   * @return string File size format
   */
  private function is_file_large($bytes, $is_image) {
    $file_size = $is_image ? 4000000 : 150000000;

    if ($bytes > $file_size) {
      if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
      } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
      }
      return $bytes;
    }
    return false;
  }

  /**
   * Format file large to tree data
   * @return string Array object
   */
  public function format_file_large($files) {
    $results = array();
    if (isset($files)) {
      foreach ($files as $key => $file) {
        $file_size = isset($file['file_size']) ? ' [' . $file['file_size'] . ']' : '';
        $full_path = str_replace(NJT_FASTDUP_WEB_ROOTPATH, '', $file['full_path']);
        $full_path_explode = explode("/", $full_path);

        $current_path = "";
        $target_chilren = &$results;

        foreach ($full_path_explode as $key_ex => $item_ex) {
          $current_path .= $key_ex == 0 ? $item_ex : '/' . $item_ex;

          if ($key_ex == count($full_path_explode) - 1) {
            $is_file = is_file($current_path);
          } else {
            $is_file = false;
          }

          if ($is_file !== true) { //Folder
            $folder_exist = false;
            for ($i = 0; $i < count($target_chilren); $i++) {
              $folder = &$target_chilren[$i];
              if ($folder["type"] == 'dir' && $folder["full_path"] == $current_path) {
                $target_chilren = &$folder['children'];
                $folder_exist = true;
                break;
              }
            }

            if ($folder_exist) {
              continue;
            }
          }

          $node = array(
            "key" => $current_path,
            "title" => $is_file ? $item_ex . $file_size : $item_ex,
            "full_path" => $current_path,
            'type' => $is_file ? 'file' : 'dir',
            'scopedSlots' => $is_file ? array('icon' => 'file') : array('icon' => 'custom'),
            'isLeaf' => $is_file ? true : false,
          );

          if ($is_file !== true) {
            $node['children'] = array();
          }

          array_push($target_chilren, $node);

          if ($is_file !== true) {
            $target_chilren = &$target_chilren[count($target_chilren) - 1]['children'];
          }

        }
      }
    }
    return $results;
  }

}
