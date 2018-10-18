<?php

namespace Drupal\media_entity_googledocs\Plugin\media\Source;

use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceBase;
use Drupal\media\MediaSourceFieldConstraintsInterface;

/**
 * Provides media type plugin for GoogleDocs.
 *
 * @MediaSource(
 *   id = "googledocs",
 *   label = @Translation("GoogleDocs"),
 *   description = @Translation("Provides business logic and metadata for GoogleDocs."),
 *   allowed_field_types = {"string", "string_long", "link"},
 *   default_thumbnail_filename = "googledocs_generic.png"
 * )
 */
class GoogleDocs extends MediaSourceBase implements MediaSourceFieldConstraintsInterface {

  /**
   * List of validation regular expressions.
   *
   * @var array
   */
  public static $validationRegexp = [
    '@(?<shortcode>((http|https):){0,1}//(www\.){0,1}docs\.google\.com/.*(?<type>(spreadsheets|presentation|document|forms)+)/d/(e/)?(?<id>[a-zA-Z0-9_-]+)/(pubhtml|pub\?embedded=true|embed|viewform\?embedded=true)[^\"]*+)@i' => 'shortcode',
    '@<iframe src="(?<shortcode>((http|https):){0,1}//(www\.){0,1}docs\.google\.com/.*(?<type>(spreadsheets|presentation|document|forms)+)/d/(e/)?(?<id>[a-zA-Z0-9_-]+)/(pubhtml|pub\?embedded=true|embed|viewform\?embedded=true)[^\"]*+)"(.*)>(.*)</iframe>@i' => 'shortcode',
  ];

  /**
   * {@inheritdoc}
   */
  public function getMetadataAttributes() {
    $attributes = [
      'shortcode' => $this->t('GoogleDocs shortcode'),
      'type' => $this->t('Document type (document, presentation, ...)'),
      'id' => $this->t('Document ID'),
    ];

    return $attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    // Try to get a specific thumbnail matching the document type.
    if ($attribute_name == 'thumbnail_uri') {
      $type = $this->getMetadata($media, 'type');
      if (in_array($type, ['document', 'spreadsheets', 'presentation', 'forms'])) {
        return $this->configFactory->get('media.settings')->get('icon_base_uri') . '/googledocs_' . $type . '.png';
      }
    }

    $matches = $this->matchRegexp($media);

    if (!$matches['shortcode']) {
      return FALSE;
    }

    if (!empty($matches[$attribute_name])) {
      return $matches[$attribute_name];
    }

    return parent::getMetadata($media, $attribute_name);
  }

  /**
   * Runs preg_match on embed code/URL.
   *
   * @param MediaInterface $media
   *   Media object.
   *
   * @return array|bool
   *   Array of preg matches or FALSE if no match.
   *
   * @see preg_match()
   */
  protected function matchRegexp(MediaInterface $media) {
    $matches = [];
    if (isset($this->configuration['source_field'])) {
      $source_field = $this->configuration['source_field'];
      if ($media->hasField($source_field)) {
        $property_name = $media->{$source_field}->first()->mainPropertyName();
        foreach (static::$validationRegexp as $pattern => $key) {
          if (preg_match($pattern, $media->{$source_field}->{$property_name}, $matches)) {
            return $matches;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceFieldConstraints() {
    return ['GoogleDocsEmbedCode' => []];
  }

}
