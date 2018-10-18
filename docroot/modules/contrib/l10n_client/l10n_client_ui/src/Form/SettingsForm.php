<?php

namespace Drupal\l10n_client_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Settings form for the localization client user interface module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'l10n_client_ui_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['l10n_client_ui.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('l10n_client_ui.settings');

    $form['disabled_paths'] = array(
      '#title'         => t('Disable on-page translation on the following system paths'),
      '#type'          => 'textarea',
      '#description'   => t('One per line. Wildcard-enabled. Examples: system/ajax, admin*'),
      '#default_value' => $config->get('disabled_paths'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('l10n_client_ui.settings')
      ->set('disabled_paths', $form_state->getValue('disabled_paths'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
