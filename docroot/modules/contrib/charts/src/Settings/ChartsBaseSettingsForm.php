<?php


namespace Drupal\charts\Settings;

use Drupal\charts\Settings\ChartsDefaultSettings;
use Drupal\charts\Settings\ChartsTypeInfo;


class ChartsBaseSettingsForm {

  /**
   * Charts Settings Form.
   *
   * @param mixed $form
   *   The form array to which this form will be added.
   * @param array $defaults
   *   An array of existing values which will be used to populate defaults.
   * @param array $field_options
   *   An array of key => value names of fields within this chart.
   * @param array $parents
   *   If all the contents of this form should be parented under a particular
   *   namespace, an array of parent names that will be prepended to each
   *   element's #parents property.
   * @param string $pluginType
   *   A string to determine which layout to use.
   *
   * @return mixed Form.
   * Form.
   */
  function getChartsBaseSettingsForm($form, $defaults = [], $field_options = [], $parents = [], $pluginType) {

    // Set options from defaults.
    $defaultSettings = new ChartsDefaultSettings();
    $options = array_merge($defaultSettings->defaults, $defaults);

    // Using plugins to get the available installed libraries.
    $plugin_manager = \Drupal::service('plugin.manager.charts');
    $plugin_definitions = $plugin_manager->getDefinitions();
    $library_options = [];

    foreach ($plugin_definitions as $plugin_definition) {
      $library_options[$plugin_definition['id']] = $plugin_definition['name'];
    }

    $form['library'] = [
      '#title' => t('Charting library'),
      '#type' => 'select',
      '#options' => $library_options,
      '#default_value' => $options['library'],
      '#required' => TRUE,
      '#access' => count($library_options) > 0,
      '#attributes' => ['class' => ['chart-library-select']],
      '#weight' => -15,
      '#parents' => array_merge($parents, ['library']),
    ];

    $chartTypes = new ChartsTypeInfo();
    $form['type'] = [
      '#title' => t('Chart type'),
      '#type' => 'radios',
      '#default_value' => $options['type'],
      '#options' => $chartTypes->getChartTypes(),
      '#required' => TRUE,
      '#weight' => -20,
      '#attributes' => [
        'class' => [
          'chart-type-radios',
          'container-inline',
        ],
      ],
      '#parents' => array_merge($parents, ['type']),
    ];

    // Views-specific form elements.
    if (isset($pluginType) && $pluginType == 'view') {

      // Field options for Views.
      if ($field_options) {
        $first_field = key($field_options);

        $form['fields'] = [
          '#title' => t('Charts fields'),
          '#type' => 'fieldset',
        ];

        $form['fields']['label_field'] = [
          '#type' => 'radios',
          '#title' => t('Label field'),
          '#options' => $field_options + ['' => t('No label field')],
          '#default_value' => isset($options['label_field']) ? $options['label_field'] : $first_field,
          '#weight' => -10,
          '#parents' => array_merge($parents, ['label_field']),
        ];

        $form['fields']['table'] = [
          '#type' => 'table',
          '#header' => [t('Field Name'), t('Provides Data'), t('Color')],
          '#tabledrag' => [
            [
              'action' => 'order',
              'relationship' => 'sibling',
              'group' => 'weight',
            ],
          ],
        ];

        $field_count = 0;
        foreach ($field_options as $field_name => $field_label) {
          $form['fields']['table'][$field_count]['label_label'] = [
            '#type' => 'label',
            '#title' => $field_label,
            '#column' => 'one',
          ];

          $form['fields']['table'][$field_count]['data_fields'][$field_name] = [
            '#type' => 'checkbox',
            '#title' => $field_name,
            '#default_value' => $options['data_fields'][$field_name],
            '#return_value' => $field_name,
            '#weight' => -9,
            '#states' => [
              'disabled' => [
                ':input[name="style_options[label_field]"]' => ['value' => $field_name],
              ],
            ],
            '#parents' => array_merge($parents, ['data_fields', $field_name]),
            '#column' => 'two',
          ];

          $form['fields']['table'][$field_count]['field_colors'][$field_name] = [
            '#type' => 'textfield',
            '#attributes' => ['TYPE' => 'color'],
            '#size' => 10,
            '#maxlength' => 7,
            '#theme_wrappers' => [],
            '#default_value' => !empty($options['field_colors'][$field_name]) ? $options['field_colors'][$field_name] : $options['colors'][$field_count],
            '#parents' => array_merge($parents, ['field_colors', $field_name]),
            '#column' => 'three',
          ];
          $field_count++;
        }
      }

      $form['display'] = [
        '#title' => t('Display'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];

      $form['display']['title'] = [
        '#title' => t('Chart title'),
        '#type' => 'textfield',
        '#default_value' => $options['title'],
        '#parents' => array_merge($parents, ['title']),
      ];

      $form['xaxis'] = [
        '#title' => t('Horizontal axis'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-xaxis']],
      ];

      $form['yaxis'] = [
        '#title' => t('Vertical axis'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-yaxis']],
      ];

    }

    // Block-specific settings.
    if (isset($pluginType) && $pluginType == 'block') {

      $form['display'] = [
        '#title' => t('Display'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];

      $form['display']['title'] = [
        '#title' => t('Chart title'),
        '#type' => 'textfield',
        '#default_value' => $options['title'],
        '#parents' => array_merge($parents, ['title']),
      ];

      $form['xaxis'] = [
        '#title' => t('Horizontal axis'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-xaxis']],
      ];

      $form['yaxis'] = [
        '#title' => t('Vertical axis'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-yaxis']],
      ];

      $form['yaxis']['inherit_yaxis'] = [
        '#title' => t('Add a secondary y-axis'),
        '#type' => 'checkbox',
        '#default_value' => $options['inherit_yaxis'],
        '#description' => t('Only one additional (secondary) y-axis can be created.'),
        '#weight' => 14,
      ];

      $form['yaxis']['secondary_yaxis'] = [
        '#title' => t('Secondary vertical axis'),
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-yaxis']],
        '#weight' => 15,
        '#states' => [
          'visible' => [
            ':input[name="settings[yaxis][inherit_yaxis]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['yaxis']['secondary_yaxis']['title'] = [
        '#title' => t('Custom title'),
        '#type' => 'textfield',
        '#default_value' => $options['secondary_yaxis']['yaxis_title'],
      ];

      $form['yaxis']['secondary_yaxis']['minmax'] = [
        '#title' => t('Value range'),
        '#theme_wrappers' => ['form_element'],
      ];

      $form['yaxis']['secondary_yaxis']['minmax']['min'] = [
        '#type' => 'textfield',
        '#attributes' => [
          'TYPE' => 'number',
          'max' => 999999999,
          'placeholder' => t('Minimum'),
        ],
        '#default_value' => $options['secondary_yaxis']['yaxis_min'],
        '#size' => 12,
        '#suffix' => ' ',
        '#theme_wrappers' => [],
      ];
      $form['yaxis']['secondary_yaxis']['minmax']['max'] = [
        '#type' => 'textfield',
        '#attributes' => [
          'TYPE' => 'number',
          'max' => 999999999,
          'placeholder' => t('Maximum'),
        ],
        '#default_value' => $options['secondary_yaxis']['yaxis_max'],
        '#size' => 12,
        '#theme_wrappers' => [],
      ];

      $form['yaxis']['secondary_yaxis']['prefix'] = [
        '#title' => t('Value prefix'),
        '#type' => 'textfield',
        '#default_value' => $options['secondary_yaxis']['yaxis_prefix'],
        '#size' => 12,
      ];

      $form['yaxis']['secondary_yaxis']['suffix'] = [
        '#title' => t('Value suffix'),
        '#type' => 'textfield',
        '#default_value' => $options['secondary_yaxis']['yaxis_suffix'],
        '#size' => 12,
      ];

      $form['yaxis']['secondary_yaxis']['decimal_count'] = [
        '#title' => t('Decimal count'),
        '#type' => 'textfield',
        '#attributes' => [
          'TYPE' => 'number',
          'step' => 1,
          'min' => 0,
          'max' => 20,
          'placeholder' => t('auto'),
        ],
        '#default_value' => $options['secondary_yaxis']['yaxis_decimal_count'],
        '#size' => 5,
        '#description' => t('Enforce a certain number of decimal-place digits in displayed values.'),
      ];

      $form['yaxis']['secondary_yaxis']['labels_rotation'] = [
        '#title' => t('Labels rotation'),
        '#type' => 'select',
        '#options' => [
          0 => t('0°'),
          30 => t('30°'),
          45 => t('45°'),
          60 => t('60°'),
          90 => t('90°'),
        ],
        // This is only shown on inverted charts.
        '#attributes' => ['class' => ['axis-inverted-show']],
        '#default_value' => $options['secondary_yaxis']['yaxis_labels_rotation'],
      ];


    }

    // Configuration form-specific settings.
    if (isset($pluginType) && $pluginType == 'config_form') {

      $form['display'] = [
        '#title' => t('Display'),
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];

      $form['xaxis'] = [
        '#title' => t('Horizontal axis'),
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-xaxis']],
      ];

      $form['yaxis'] = [
        '#title' => t('Vertical axis'),
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#attributes' => ['class' => ['chart-yaxis']],
      ];

      $form['display']['title'] = [
        '#title' => t('Chart title'),
        '#type' => 'textfield',
        '#default_value' => $options['title'],
        '#parents' => array_merge($parents, ['title']),
      ];

      $form['display']['colors'] = [
        '#title' => t('Chart colors'),
        '#theme_wrappers' => ['form_element'],
        '#prefix' => '<div class="chart-colors">',
        '#suffix' => '</div>',
      ];

      for ($color_count = 0; $color_count < 10; $color_count++) {
        $form['display']['colors'][$color_count] = [
          '#type' => 'textfield',
          '#attributes' => ['TYPE' => 'color'],
          '#size' => 10,
          '#maxlength' => 7,
          '#theme_wrappers' => [],
          '#suffix' => ' ',
          '#default_value' => $options['colors'][$color_count],
          '#parents' => array_merge($parents, ['colors', $color_count]),
        ];

      }

    }

    $form['display']['title_position'] = [
      '#title' => t('Title position'),
      '#type' => 'select',
      '#options' => [
        '' => t('None'),
        'out' => t('Outside'),
        'in' => t('Inside'),
      ],
      '#default_value' => $options['title_position'],
      '#parents' => array_merge($parents, ['title_position']),
    ];

    $form['display']['tooltips'] = [
      '#title' => t('Tooltips'),
      '#type' => 'select',
      '#options' => [
        '' => t('Disabled'),
        'TRUE' => t('Enabled'),
      ],
      '#description' => t('Show data details on mouse over? Note: unavailable for print or on mobile devices.'),
      '#default_value' => $options['tooltips'],
      '#parents' => array_merge($parents, ['tooltips']),
    ];

    $form['display']['data_labels'] = [
      '#title' => t('Data labels'),
      '#type' => 'select',
      '#options' => [
        '' => t('Disabled'),
        'TRUE' => t('Enabled'),
      ],
      '#default_value' => $options['data_labels'],
      '#description' => t('Show data details as labels on chart? Note: recommended for print or on mobile devices.'),
      '#parents' => array_merge($parents, ['data_labels']),
    ];

    $form['display']['data_markers'] = [
      '#title' => t('Data markers'),
      '#type' => 'select',
      '#options' => [
        'FALSE' => t('Disabled'),
        'TRUE' => t('Enabled'),
      ],
      '#default_value' => $options['data_markers'],
      '#description' => t('Show data markers (points) on line charts?'),
      '#parents' => array_merge($parents, ['data_markers']),
    ];

    $form['display']['legend_position'] = [
      '#title' => t('Legend position'),
      '#type' => 'select',
      '#options' => [
        '' => t('None'),
        'top' => t('Top'),
        'right' => t('Right'),
        'bottom' => t('Bottom'),
        'left' => t('Left'),
      ],
      '#default_value' => $options['legend_position'],
      '#parents' => array_merge($parents, ['legend_position']),
    ];

    $form['display']['background'] = [
      '#title' => t('Background color'),
      '#type' => 'textfield',
      '#size' => 10,
      '#maxlength' => 7,
      '#attributes' => ['placeholder' => t('transparent')],
      '#description' => t('Leave blank for a transparent background.'),
      '#default_value' => $options['background'],
      '#parents' => array_merge($parents, ['background']),
    ];

    $form['display']['three_dimensional'] = [
      '#title' => t('Make chart three-dimensional (3D)'),
      '#type' => 'checkbox',
      '#default_value' => $options['three_dimensional'],
      '#parents' => array_merge($parents, ['three_dimensional']),
      '#attributes' => [
        'class' => [
          'chart-type-checkbox',
          'container-inline',
        ],
      ],
    ];

    $form['display']['polar'] = [
      '#title' => t('Transform cartesian charts into the polar coordinate system'),
      '#type' => 'checkbox',
      '#default_value' => $options['polar'],
      '#parents' => array_merge($parents, ['polar']),
      '#attributes' => [
        'class' => [
          'chart-type-checkbox',
          'container-inline',
        ],
      ],
    ];

    $form['display']['dimensions'] = [
      '#title' => t('Dimensions'),
      '#theme_wrappers' => ['form_element'],
      '#description' => t('If dimensions are left empty, the chart will fill its containing element.'),
    ];

    $form['display']['dimensions']['width'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'number',
        'step' => 1,
        'min' => 0,
        'max' => 9999,
        'placeholder' => t('auto'),
      ],
      '#default_value' => $options['width'],
      '#size' => 8,
      '#theme_wrappers' => [],
      '#parents' => array_merge($parents, ['width']),
    ];

    $form['display']['dimensions']['width_units'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'text',
        'placeholder' => t('%'),
      ],
      '#default_value' => $options['width_units'],
      '#suffix' => ' x ',
      '#size' => 2,
      '#theme_wrappers' => [],
      '#parents' => array_merge($parents, ['width_units']),
    ];

    $form['display']['dimensions']['height'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'number',
        'step' => 1,
        'min' => 0,
        'max' => 9999,
        'placeholder' => t('auto'),
      ],
      '#default_value' => $options['height'],
      '#size' => 8,
      '#theme_wrappers' => [],
      '#parents' => array_merge($parents, ['height']),
    ];

    $form['display']['dimensions']['height_units'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'text',
        'placeholder' => t('px'),
      ],
      '#default_value' => $options['height_units'],
      '#size' => 2,
      '#theme_wrappers' => [],
      '#parents' => array_merge($parents, ['height_units']),
    ];

    $form['xaxis']['xaxis_title'] = [
      '#title' => t('Custom title'),
      '#type' => 'textfield',
      '#default_value' => $options['xaxis_title'],
      '#parents' => array_merge($parents, ['xaxis_title']),
    ];

    $form['xaxis']['labels_rotation'] = [
      '#title' => t('Labels rotation'),
      '#type' => 'select',
      '#options' => [
        0 => t('0°'),
        30 => t('30°'),
        45 => t('45°'),
        60 => t('60°'),
        90 => t('90°'),
      ],
      // This is only shown on non-inverted charts.
      '#attributes' => ['class' => ['axis-inverted-hide']],
      '#default_value' => $options['xaxis_labels_rotation'],
      '#parents' => array_merge($parents, ['xaxis_labels_rotation']),
    ];


    $form['yaxis']['title'] = [
      '#title' => t('Custom title'),
      '#type' => 'textfield',
      '#default_value' => $options['yaxis_title'],
      '#parents' => array_merge($parents, ['yaxis_title']),
    ];

    $form['yaxis']['minmax'] = [
      '#title' => t('Value range'),
      '#theme_wrappers' => ['form_element'],
    ];

    $form['yaxis']['minmax']['min'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'number',
        'max' => 999999999,
        'placeholder' => t('Minimum'),
      ],
      '#default_value' => $options['yaxis_min'],
      '#size' => 12,
      '#parents' => array_merge($parents, ['yaxis_min']),
      '#suffix' => ' ',
      '#theme_wrappers' => [],
    ];
    $form['yaxis']['minmax']['max'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'number',
        'max' => 999999999,
        'placeholder' => t('Maximum'),
      ],
      '#default_value' => $options['yaxis_max'],
      '#size' => 12,
      '#parents' => array_merge($parents, ['yaxis_max']),
      '#theme_wrappers' => [],
    ];

    $form['yaxis']['prefix'] = [
      '#title' => t('Value prefix'),
      '#type' => 'textfield',
      '#default_value' => $options['yaxis_prefix'],
      '#size' => 12,
      '#parents' => array_merge($parents, ['yaxis_prefix']),
    ];

    $form['yaxis']['suffix'] = [
      '#title' => t('Value suffix'),
      '#type' => 'textfield',
      '#default_value' => $options['yaxis_suffix'],
      '#size' => 12,
      '#parents' => array_merge($parents, ['yaxis_suffix']),
    ];

    $form['yaxis']['decimal_count'] = [
      '#title' => t('Decimal count'),
      '#type' => 'textfield',
      '#attributes' => [
        'TYPE' => 'number',
        'step' => 1,
        'min' => 0,
        'max' => 20,
        'placeholder' => t('auto'),
      ],
      '#default_value' => $options['yaxis_decimal_count'],
      '#size' => 5,
      '#description' => t('Enforce a certain number of decimal-place digits in displayed values.'),
      '#parents' => array_merge($parents, ['yaxis_decimal_count']),
    ];

    $form['yaxis']['labels_rotation'] = [
      '#title' => t('Labels rotation'),
      '#type' => 'select',
      '#options' => [
        0 => t('0°'),
        30 => t('30°'),
        45 => t('45°'),
        60 => t('60°'),
        90 => t('90°'),
      ],
      // This is only shown on inverted charts.
      '#attributes' => ['class' => ['axis-inverted-show']],
      '#default_value' => $options['yaxis_labels_rotation'],
      '#parents' => array_merge($parents, ['yaxis_labels_rotation']),
    ];

    // Settings for gauges.
    $form['display']['gauge'] = [
      '#title' => t('Gauge settings'),
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#states' => [
        'invisible' => [],
      ],
      '#parents' => array_merge($parents, ['gauge']),
      'max' => [
        '#title' => t('Gauge maximum value'),
        '#type' => 'number',
        '#default_value' => $options['max'],
        '#parents' => array_merge($parents, ['max']),
      ],
      'min' => [
        '#title' => t('Gauge minimum value'),
        '#type' => 'number',
        '#default_value' => $options['min'],
        '#parents' => array_merge($parents, ['min']),
      ],
      'green_from' => [
        '#title' => t('Green minimum value'),
        '#type' => 'number',
        '#default_value' => $options['green_from'],
        '#parents' => array_merge($parents, ['green_from']),
      ],
      'green_to' => [
        '#title' => t('Green maximum value'),
        '#type' => 'number',
        '#default_value' => $options['green_to'],
        '#parents' => array_merge($parents, ['green_to']),
      ],
      'yellow_from' => [
        '#title' => t('Yellow minimum value'),
        '#type' => 'number',
        '#default_value' => $options['yellow_from'],
        '#parents' => array_merge($parents, ['yellow_from']),
      ],
      'yellow_to' => [
        '#title' => t('Yellow maximum value'),
        '#type' => 'number',
        '#default_value' => $options['yellow_to'],
        '#parents' => array_merge($parents, ['yellow_to']),
      ],
      'red_from' => [
        '#title' => t('Red minimum value'),
        '#type' => 'number',
        '#default_value' => $options['red_from'],
        '#parents' => array_merge($parents, ['red_from']),
      ],
      'red_to' => [
        '#title' => t('Red maximum value'),
        '#type' => 'number',
        '#default_value' => $options['red_to'],
        '#parents' => array_merge($parents, ['red_to']),
      ],
    ];

    return $form;

  }


}