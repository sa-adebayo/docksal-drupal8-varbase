<?php

namespace Drupal\l10n_client_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Settings form for the localization client user interface module.
 */
class TranslationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'l10n_client_ui_translation_form';
  }

  public function setValues($languages, $strings) {
    $this->languages = $languages;
    $this->strings = $strings;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = array(
      '#type' => 'container',
    );
    $form['filters']['language'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'select',
      '#options' => $this->languages,
    );
    $form['filters']['stats'] = array(
      '#title' => $this->t('Stats'),
      '#type' => 'item',
      '#markup' => '<div class="l10n_client_ui--stats"><div class="l10n_client_ui--stats-done"></div></div>',
    );
    $form['filters']['type'] = array(
      '#title' => $this->t('Find and translate'),
      '#type' => 'select',
      '#options' => array(
        'false' => $this->t('Untranslated strings'),
        'true' => $this->t('Translated strings'),
      ),
    );
    $form['filters']['search'] = array(
      '#title' => $this->t('Contains'),
      '#type' => 'search',
      '#placeholder' => $this->t('Search')
    );

    $form['list'] = array(
      '#type' => 'container',
    );
    $form['list']['table'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Source'), $this->t('Translation'), $this->t('Save'), $this->t('Skip'))
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }
}
