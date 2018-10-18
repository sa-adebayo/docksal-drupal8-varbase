<?php

/**
 * @file
 * Contains \Drupal\advanced_text_formatter\Plugin\field\formatter\AdvancedTextFormatter.
 */

namespace Drupal\advanced_text_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'advanced_text_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "advanced_text",
 *   module = "advanced_text_formatter",
 *   label = @Translation("Advanced Text"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class AdvancedTextFormatter extends FormatterBase {
  const FORMAT_DRUPAL = 'drupal';
  const FORMAT_INPUT = 'input';
  const FORMAT_NONE = 'none';
  const FORMAT_PHP = 'php';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'trim_length' => 600,
      'ellipsis' => 1,
      'word_boundary' => 1,
      'token_replace' => 0,
      'filter' => 'input',
      'format' => 'plain_text',
      'allowed_html' => '<a> <b> <br> <dd> <dl> <dt> <em> <i> <li> <ol> <p> <strong> <u> <ul>',
      'autop' => 0,
      'use_summary' => 0,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elTrimLengthId = Html::getUniqueId('advanced_text_formatter_trim');
    $elFilterId     = Html::getUniqueId('advanced_text_formatter_filter');

    $element['trim_length'] = array(
      '#id'            => $elTrimLengthId,
      '#type'          => 'number',
      '#title'         => t('Trim length'),
      '#description'   => t("Set this to 0 if you don't want to cut the text. Otherwise, input a positive integer."),
      '#size'          => 10,
      '#default_value' => $this->getSetting('trim_length'),
      '#required'      => TRUE,
    );

    $element['ellipsis'] = array(
      '#type'          => 'checkbox',
      '#title'         => t('Ellipsis'),
      '#description'   => t('If checked, a "..." will be added if a field was trimmed.'),
      '#default_value' => $this->getSetting('ellipsis'),
      '#states'        => array(
        'visible' => array(
          '#' . $elTrimLengthId => array('!value' => '0'),
        ),
      ),
    );

    $element['word_boundary'] = array(
      '#type'          => 'checkbox',
      '#title'         => t('Word Boundary'),
      '#description'   => t('If checked, this field be trimmed only on a word boundary.'),
      '#default_value' => $this->getSetting('word_boundary'),
      '#states'        => array(
        'visible' => array(
          '#' . $elTrimLengthId => array('!value' => '0'),
        ),
      ),
    );

    $element['use_summary'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Use Summary'),
      '#description'    => t('If a summary exists, use it.'),
      '#default_value'  => $this->getSetting('use_summary'),
    );

    $token_link = _advanced_text_formatter_browse_tokens($this->fieldDefinition->getTargetEntityTypeId());

    $element['token_replace'] = array(
      '#type'          => 'checkbox',
      '#description'   => t('Replace text pattern. e.g %node-title-token or %node-author-name-token, by token values.', array(
                            '%node-title-token'       => '[node:title]',
                            '%node-author-name-token' => '[node:author:name]',
                          )) . ' ' /*. $token_link*/,
      '#title'         => t('Token Replace'),
      '#default_value' => $this->getSetting('token_replace'),
    );

    $element['filter'] = array(
      '#id'      => $elFilterId,
      '#title'   => t('Filter'),
      '#type'    => 'select',
      '#options' => array(
        static::FORMAT_NONE   => t('None'),
        static::FORMAT_INPUT  => t('Selected Text Format'),
        static::FORMAT_PHP    => t('Limit allowed HTML tags'),
        static::FORMAT_DRUPAL => t('Drupal'),
      ),
      '#default_value' => $this->getSetting('filter'),
    );

    $element['format'] = array(
      '#title'         => t('Format'),
      '#type'          => 'select',
      '#options'       => array(),
      '#default_value' => $this->getSetting('format'),
      '#states'        => array(
        'visible' => array(
          '#' . $elFilterId  => array('value' => 'drupal'),
        ),
      ),
    );

    $formats = filter_formats();

    foreach ($formats as $formatId => $format) {
      $element['format']['#options'][$formatId] = $format->get('name');
    }

    $allowedHtml = $this->getSetting('allowed_html');

    if (empty($allowedHtml)) {
      $tags = '';
    }
    elseif (is_string($allowedHtml)) {
      $tags = $allowedHtml;
    }
    else {
      $tags = '<' . implode('> <', $allowedHtml) . '>';
    }

    $element['allowed_html'] = array(
      '#type'             => 'textfield',
      '#title'            => t('Allowed HTML tags'),
      '#description'      => t('See <a href="@link" target="_blank">filter_xss()</a> for more information', array(
                                '@link' => 'http://api.drupal.org/api/drupal/core%21includes%21common.inc/function/filter_xss/8',
                              )),
      '#default_value'    => $tags,
      '#element_validate' => array('_advanced_text_formatter_validate_allowed_html'),
      '#states'           => array(
        'visible' => array(
          '#' . $elFilterId => array('value' => 'php'),
        ),
      ),
    );

    $element['autop'] = array(
      '#title'         => t('Converts line breaks into HTML (i.e. &lt;br&gt; and &lt;p&gt;) tags.'),
      '#type'          => 'checkbox',
      '#return_value'  => 1,
      '#default_value' => $this->getSetting('autop'),
      '#states'        => array(
        'invisible' => array(
          '#' . $elFilterId  => array('!value' => 'php'),
        ),
      ),
    );

    $element['br'] = array('#markup' => '<br/>');

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $yes     = t('Yes');
    $no      = t('No');

    if ($this->getSetting('trim_length') > 0) {
      $summary[] = t('Trim length') . ': ' . $this->getSetting('trim_length');
      $summary[] = t('Ellipsis') . ': ' . ($this->getSetting('ellipsis') ? $yes : $no);
      $summary[] = t('Word Boundary') . ': ' . ($this->getSetting('word_boundary') ? $yes : $no);
      $summary[] = t('Use Summary') . ': ' . ($this->getSetting('use_summary') ? $yes : $no);
    }

    $summary[] = t('Token Replace') . ': ' . ($this->getSetting('token_replace') ? $yes : $no);

    switch ($this->getSetting('filter')) {
      case static::FORMAT_DRUPAL:
        $formats = filter_formats();
        $format  = $this->getSetting('format');
        $format  = isset($formats[$format]) ? $formats[$format]->name : t('Unknown');

        $summary[] = t('Filter: @filter', array('@filter' => t('Drupal')));
        $summary[] = t('Format: @format', array('@format' => $format));

        break;

      case static::FORMAT_PHP:
        $text  = array();
        $tags  = $this->getSetting('allowed_html');
        $autop = $this->getSetting('autop');

        if (is_array($tags) && !empty($tags)) {
          $tags = '<' . implode('> <', $tags) . '>';
        }

        if (empty($tags)) {
          $text[] = t('Remove all HTML tags.');
        }
        else {
          $text[] = t('Limit allowed HTML tags: !tags.', array('!tags' => $tags));
        }

        if (!empty($autop)) {
          $text[] = t('Convert line breaks into HTML.');
        }

        $summary[] = t('Filter: @filter', array('@filter' => implode(' ', $text)));

        break;

      case static::FORMAT_INPUT:
        $summary[] = t('Filter: @filter', array('@filter' => t('Selected Text Format')));

        break;

      default:
        $summary[] = t('Filter: @filter', array('@filter' => t('None')));

        break;
    }

    $summary = array_filter($summary);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $token_data = array(
      'user' => \Drupal::currentUser(),
      $items->getEntity()->getEntityTypeId() => $items->getEntity(),
    );

    foreach ($items as $delta => $item) {
      if ($this->getSetting('use_summary') && !empty($item->summary)) {
        $output = $item->summary;
      }
      else {
        $output = $item->value;
      }

      if ($this->getSetting('token_replace')) {
        $output = \Drupal::token()->replace($output, $token_data);
      }

      switch ($this->getSetting('filter')) {
        case static::FORMAT_DRUPAL:
          $output = check_markup($output, $this->getSetting('format'), $item->getLangcode());

          break;

        case static::FORMAT_PHP:
          $output = Xss::filter($output, $this->getSetting('allowed_html'));

          if ($this->getSetting('autop')) {
            $output = _filter_autop($output);
          }

          break;

        case static::FORMAT_INPUT:
          $output = check_markup($output, $item->format, $item->getLangcode());

          break;
      }

      if ($this->getSetting('trim_length') > 0) {
        $options  = array(
          'word_boundary' => $this->getSetting('word_boundary'),
          'max_length'    => $this->getSetting('trim_length'),
          'ellipsis'      => $this->getSetting('ellipsis'),
        );

        $output = advanced_text_formatter_trim_text($output, $options);
      }

      $elements[$delta] = array(
        '#type' => 'processed_text',
        '#text' => $output,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      );
    }

    return $elements;
  }
}
