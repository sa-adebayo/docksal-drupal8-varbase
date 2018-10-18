<?php

namespace Drupal\ckeditor_media_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'ckeditor_media_embed_link_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "ckeditor_media_embed_link_formatter",
 *   label = @Translation("Oembed element using CKEditor Media Embed provider"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class CKEditorMediaEmbedFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();

    foreach ($items as $delta => $item) {
      $url = $item->getUrl();

      $embed = \Drupal::service('ckeditor_media_embed');
      $output = $embed->getEmbedObject($url->toUriString());

      if (isset($output->html)) {
        $element[$delta] = array(
          '#type' => 'inline_template',
          '#template' => '{{ content|raw }}',
          '#context' => array(
            'content' => $output->html,
          ),
        );
      }
      else {
        // If we didn't get an oembed response, just show the URL.
        $element[$delta] = array(
          '#type' => 'link',
          '#url' => $url,
          '#title' => $url->toString(),
        );
      }
    }

    return $element;
  }

}

