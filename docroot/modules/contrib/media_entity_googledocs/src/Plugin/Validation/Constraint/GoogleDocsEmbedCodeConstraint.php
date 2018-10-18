<?php

namespace Drupal\media_entity_googledocs\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check if a value is a valid GoogleDocs embed code/URL.
 *
 * @constraint(
 *   id = "GoogleDocsEmbedCode",
 *   label = @Translation("GoogleDocs embed code", context = "Validation"),
 *   type = { "link", "string", "string_long" }
 * )
 */
class GoogleDocsEmbedCodeConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Not valid GoogleDocs URL/Embed code.';

}
