<?php

namespace Drupal\mail_edit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;

/**
 * List all of the emails that may be edited by the module.
 */
class MailListController extends ControllerBase {

  /**
   * List the emails.
   *
   * @return array
   *   A render array.
   */
  public function listAll() {
    $render = [
      '#theme' => 'table',
      '#attributes' => [],
      '#header' => [
        $this->t('Module'),
        $this->t('Description'),
        $this->t('Length (characters)'),
        $this->t('Actions'),
      ],
      '#rows' => [],
    ];

    // Build rows out of the templates.
    foreach ($this->getAllTemplates() as $config_name => $templates) {
      $config_label = $config_name;
      if ($config_name == 'user.mail') {
        $config_label = t('Drupal core');
      }
      foreach ($templates as $name => $data) {
        $body_length = Unicode::strlen($data['body']);
        if ($body_length === 0) {
          $body_length = $this->t('empty');
        }
        $args = ['id' => $config_name . '.' . $name];
        $render['#rows'][] = [
          $config_label,
          Xss::filter($data['description']),
          $body_length,
          Link::createFromRoute($this->t('Edit'), 'mail_edit.edit', $args),
        ];
      }
    }

    return $render;
  }

  /**
   * Get a list of all templates provided by the site.
   *
   * @return array
   *   A list of all templates provided by module hooks.
   */
  private function getAllTemplates() {
    $all_templates = [];
    $module_handler = \Drupal::moduleHandler();

    // Trigger hook_mail_edit_templates().
    // Get a list of the email templates as defined by other modules.
    foreach ($module_handler->invokeAll('mail_edit_templates') as $config_name => $templates) {
      $config_data = $this->config($config_name)->getRawData();
      // Make sure data was actually found.
      if (empty($config_data)) {
        $config_data = [];
      }

      // Process each of the defined templates.
      foreach ($templates as $key => $label) {
        // Keys will not be numeric if they are in $key => $label format, so if
        // the key is numeric then it was provided as just a list of keys.
        if (is_numeric($key)) {
          $key = $label;
        }

        // Make sure the config structure exists.
        if (!isset($config_data[$key])) {
          $config_data[$key] = [
            'subject' => '',
            'body' => '',
          ];
        }

        $data = $config_data[$key];

        // If an email's description is provided then use it, otherwise just
        // use the email's subject line.
        if ($key != $label) {
          $data['description'] = $label;
        }
        else {
          $data['description'] = $data['subject'];
        }

        $all_templates[$config_name][$key] = $data;
      }
    }

    // Trigger hook_mail_edit_templates_list_alter().
    // Allow modules to adjust the list of available templates, e.g. to remove
    // items from the list for security purposes.
    $module_handler->alter('mail_edit_templates_list', $all_templates);

    return $all_templates;
  }

}
