<?php

namespace Drupal\taxonomy_access_fix;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the taxonomy term entity type.
 *
 * @see \Drupal\taxonomy\Entity\Term
 */
class TermAccessFixTermControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, [
          "view terms in {$entity->bundle()}",
          'administer taxonomy',
        ], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, [
          "edit terms in {$entity->bundle()}",
          'administer taxonomy',
        ], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, [
          "delete terms in {$entity->bundle()}",
          'administer taxonomy',
        ], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, "add terms in $entity_bundle");
  }

}
