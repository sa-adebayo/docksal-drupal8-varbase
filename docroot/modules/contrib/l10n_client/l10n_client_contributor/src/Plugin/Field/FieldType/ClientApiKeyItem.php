<?php

namespace Drupal\l10n_client_contributor\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the localization client contributor api key entity field type.
 *
 * @FieldType(
 *   id = "l10n_client_contributor_key",
 *   label = @Translation("Localization client contributor API key"),
 *   description = @Translation("API key for localization contribution."),
 *   category = @Translation("Text"),
 *   default_widget = "l10n_client_contributor_key_widget",
 *   no_ui = TRUE
 * )
 */
class ClientApiKeyItem extends StringItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Localization contributor API key'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return array();
  }
}
