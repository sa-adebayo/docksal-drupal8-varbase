<?php

namespace Drupal\vmi;

use Symfony\Component\Yaml\Yaml;

/**
 * View Modes Inventory Factory.
 */
class ViewModesInventoryFactory {

  /**
   * Constructs a Drupal\vmi\ViewModesInventoryFactory object.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Get data from view_modes.list.yml file.
   *
   * @return array
   *   Data array for the list of default view modes.
   *
   * @throws Exception
   */
  public static function getViewModesList() {
    $vmi_filename = \Drupal::root() . '/' . drupal_get_path('module', 'vmi') . '/src/assets/view_modes.list.vmi.yml';

    if (is_file($vmi_filename)) {
      $vmi_list = (array) Yaml::parse(file_get_contents($vmi_filename));
      return $vmi_list;
    }
    else {
      throw new \Exception('View modes inventory layouts list file does not exist!');
    }
  }

  /**
   * Get get data from layouts.mapping.yml file.
   *
   * @return array
   *   Data array for the default mapping layouts with view modes.
   *
   * @throws Exception
   */
  public static function getLayoutsMapping() {
    $vmi_layout_filename = \Drupal::root() . '/' . drupal_get_path('module', 'vmi') . '/src/assets/layouts.mapping.vmi.yml';

    if (is_file($vmi_layout_filename)) {
      $vmi_layout_list = (array) Yaml::parse(file_get_contents($vmi_layout_filename));
      return $vmi_layout_list;
    }
    else {
      throw new \Exception('View modes inventory layouts list file does not exist!');
    }
  }

  /**
   * Map a view mode with a layout and default configuration template.
   *
   * @param string $selected_view_mode
   *   Selected view mode in the custom display settings form.
   * @param string $default_mapped_layout
   *   Default mapped layout.
   * @param string $entity_type
   *   Entity type like node, block, user.
   * @param string $bundle_name
   *   Bundle name.
   * @param string $config_template_file
   *   Config template file name.
   * @param string $config_name
   *   Config name to map to.
   */
  public static function mapViewModeWithLayout($selected_view_mode, $default_mapped_layout, $entity_type, $bundle_name, $config_template_file, $config_name) {

    // Replace CONTENT_TYPE_NAME with the bundle name for the config name.
    $real_config_name = str_replace('CONTENT_TYPE_NAME', $bundle_name, $config_name);

    $view_mode_config = \Drupal::service('config.factory')->getEditable($real_config_name);

    // Load the config template.
    $full_config_template_file = \Drupal::root() . '/' . drupal_get_path('module', 'vmi') . $config_template_file;
    $config_template_content = file_get_contents($full_config_template_file);

    // Replace CONTENT_TYPE_NAME with the bundle name in the config template.
    $real_config_template_content = str_replace('CONTENT_TYPE_NAME', $bundle_name, $config_template_content);

    // Parse real config template conten to data.
    $real_config_template_content_data = (array) Yaml::parse($real_config_template_content);

    // Set and save new message value.
    $view_mode_config->setData($real_config_template_content_data)->save();

  }

}
