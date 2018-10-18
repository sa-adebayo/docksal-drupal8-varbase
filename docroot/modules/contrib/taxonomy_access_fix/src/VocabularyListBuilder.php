<?php

namespace Drupal\taxonomy_access_fix;

use Drupal;
use Drupal\taxonomy\VocabularyListBuilder as VocabularyListBuilderBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy_access_fix\TaxonomyAccessFixPermissions;

class VocabularyListBuilder extends VocabularyListBuilderBase {

  /**
   * Override Drupal\Core\Config\Entity\ConfigEntityListBuilder::load().
   */
  public function load() {
    $entities = parent::load();
    // Remove vocabularies the current user doesn't have any access for.
    foreach ($entities as $id => $entity) {
      if (!TaxonomyAccessFixPermissions::fixAccess('list terms', $entity)) {
        unset($entities[$id]);
      }
    }
    return $entities;
  }

  /**
   * Override Drupal\taxonomy\VocabularyListBuilder::render().
   */
  public function render() {
    // Remove vocabulary sorting for non-admins.
    if (!Drupal::currentUser()->hasPermission('administer taxonomy')) {
      unset($this->weightKey);
    }
    return parent::render();
  }

  /**
   * Override Drupal\Core\Entity\EntityListBuilder::getOperations().
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    if (!TaxonomyAccessFixPermissions::fixAccess('add terms', $entity)) {
      unset($operations['add']);
    }
    return $operations;
  }

}
