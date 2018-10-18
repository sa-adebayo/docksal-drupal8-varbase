<?php

namespace Drupal\media_entity_googledocs\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\media_entity_googledocs\Plugin\media\Source\GoogleDocs;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the GoogleDocsEmbedCode constraint.
 */
class GoogleDocsEmbedCodeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $data = [];
    if (is_string($value)) {
      $data[] = $value;
    }
    elseif ($value instanceof FieldItemInterface) {
      $class = get_class($value);
      $property = $class::mainPropertyName();
      if ($property) {
        $data[] = $value->{$property};
      }
    }
    elseif ($value instanceof FieldItemListInterface) {
      foreach ($value as $item_value) {
        $class = get_class($item_value);

        if (method_exists($class, 'mainPropertyName')) {
          $property = $class::mainPropertyName();
          if ($property) {
            $data[] = $item_value->{$property};
          }
        }
      }
    }

    if ($data) {
      foreach ($data as $item_data) {
        $matches = [];
        foreach (GoogleDocs::$validationRegexp as $pattern => $key) {
          if (preg_match($pattern, $item_data, $item_matches)) {
            $matches[] = $item_matches;
          }
        }

        if (empty($matches)) {
          $this->context->addViolation($constraint->message);
        }
      }
    }

  }

}
