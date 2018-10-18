<?php

namespace Drupal\l10n_client_ui;

use Drupal\Core\StringTranslation\Translator\TranslatorInterface;

/**
 * String translation listener to collect data.
 */
class InterfaceTranslationRecorder implements TranslatorInterface  {

  /**
   * String that were attempted to be looked up in this request.
   *
   * @var array
   */
  protected $strings = array();

  /**
   * @inheritdoc
   */
  public function getStringTranslation($langcode, $string, $context) {
    if ($langcode != 'en' || locale_is_translatable('en')) {
      $this->strings[$langcode][$context][$string] = TRUE;
    }
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function reset() {
    $this->strings = array();
  }

  /**
   * @inheritdoc
   *
   * @return array
   *   Array of strings keyed by language code and context.
   */
  public function getRecordedData() {
    return $this->strings;
  }
}
