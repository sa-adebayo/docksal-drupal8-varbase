<?php

namespace Drupal\paragraphs_features;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\WidgetInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Paragraphs features class.
 */
class ParagraphsFeatures {

  /**
   * List of available paragraphs features.
   *
   * @var array
   */
  public static $availableFeatures = [
    'add_in_between',
    'delete_confirmation',
    'split_text',
  ];

  /**
   * Getting paragraphs widget wrapper ID.
   *
   * Logic is copied from paragraphs module.
   *
   * @param array $parents
   *   List of parents for widget.
   * @param string $field_name
   *   Widget field name.
   *
   * @return string
   *   Returns widget wrapper ID.
   */
  public static function getWrapperId(array $parents, $field_name) {
    return Html::getId(implode('-', array_merge($parents, [$field_name])) . '-add-more-wrapper');
  }

  /**
   * Register features for paragraphs field widget.
   *
   * @param array $elements
   *   Render array for the field widget.
   * @param \Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget $widget
   *   Field widget object.
   * @param string $fieldWrapperId
   *   Field Wrapper ID, usually provided by ::getWrapperId().
   */
  public static function registerFormWidgetFeatures(array &$elements, ParagraphsWidget $widget, $fieldWrapperId) {
    foreach (static::$availableFeatures as $feature) {
      if ($widget->getThirdPartySetting('paragraphs_features', $feature)) {
        $elements['add_more']['#attached']['library'][] = 'paragraphs_features/drupal.paragraphs_features.' . $feature;
        $elements['add_more']['#attached']['drupalSettings']['paragraphs_features'][$feature][$fieldWrapperId] = TRUE;
        $elements['add_more']['#attached']['drupalSettings']['paragraphs_features'][$feature]['_path'] = drupal_get_path('module', 'paragraphs_features');
      }
    }
  }

  /**
   * Get 3rd party setting form for paragraphs features.
   *
   * @param \Drupal\Core\Field\WidgetInterface $plugin
   *   Widget plugin.
   * @param string $field_name
   *   Field name.
   *
   * @return array
   *   Returns 3rd party form elements.
   */
  public static function getThirdPartyForm(WidgetInterface $plugin, $field_name) {
    $elements = [];

    $elements['delete_confirmation'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable confirmation on paragraphs remove'),
      '#default_value' => $plugin->getThirdPartySetting('paragraphs_features', 'delete_confirmation'),
      '#attributes' => ['class' => ['paragraphs-features__delete-confirmation__option']],
    ];

    // Define rule for enabling/disabling options that depend on modal add mode.
    $modal_related_options_rule = [
      ':input[name="fields[' . $field_name . '][settings_edit_form][settings][add_mode]"]' => [
        'value' => 'modal',
      ],
    ];

    $elements['add_in_between'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable add in between buttons'),
      '#default_value' => $plugin->getThirdPartySetting('paragraphs_features', 'add_in_between'),
      '#attributes' => ['class' => ['paragraphs-features__add-in-between__option']],
      '#states' => [
        'enabled' => $modal_related_options_rule,
        'visible' => $modal_related_options_rule,
      ],
    ];

    $elements['split_text'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable split text for text paragraphs'),
      '#default_value' => $plugin->getThirdPartySetting('paragraphs_features', 'split_text'),
      '#attributes' => ['class' => ['paragraphs-features__split-text__option']],
      '#states' => [
        'enabled' => $modal_related_options_rule,
        'visible' => $modal_related_options_rule,
      ],
    ];

    return $elements;
  }

}
