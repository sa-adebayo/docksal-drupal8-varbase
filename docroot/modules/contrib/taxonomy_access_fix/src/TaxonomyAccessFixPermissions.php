<?php

namespace Drupal\taxonomy_access_fix;

use Symfony\Component\Routing\Route;
use Drupal\Core\Access\AccessResult;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Routing\RouteMatchInterface;

class TaxonomyAccessFixPermissions {

  /**
   * Permission callback for TAF's MODULE.permissions.yml.
   *
   * @see taxonomy_access_fix.permissions.yml
   */
  public static function getAccess() {
    $vocabularies = Vocabulary::loadMultiple();

    $permissions = [];
    foreach ($vocabularies as $vocabulary) {
      $permissions['view terms in ' . $vocabulary->id()] = [
        'title' => t('View terms in %vocabulary', ['%vocabulary' => $vocabulary->label()]),
      ];
      $permissions['add terms in ' . $vocabulary->id()] = [
        'title' => t('Add terms in %vocabulary', ['%vocabulary' => $vocabulary->label()]),
      ];
      $permissions['reorder terms in ' . $vocabulary->id()] = [
        'title' => t('Reorder terms in %vocabulary', ['%vocabulary' => $vocabulary->label()]),
      ];
    }

    return $permissions;
  }

  /**
   * Access callback for common CUSTOM taxonomy operations.
   */
  public static function fixAccess($op, $vocabulary = NULL) {
    // Admin: always.
    if (\Drupal::currentUser()->hasPermission('administer taxonomy')) {
      return TRUE;
    }
    if ($vocabulary && is_string($vocabulary)) {
      $vocabulary = Vocabulary::load($vocabulary);
    }
    // Others: well, that depends.
    switch ($op) {
      case 'index':
        // Allow access when the user has access to at least one vocabulary.
        foreach (Vocabulary::loadMultiple() as $vocabulary) {
          if (self::fixAccess('list terms', $vocabulary)) {
            return TRUE;
          }
        }
        break;
      case 'list terms':
        if ($vocabulary) {
          $vid = $vocabulary->id();
          $perm1 = sprintf('edit terms in %s', $vid);
          $perm2 = sprintf('delete terms in %s', $vid);
          $perm3 = sprintf('add terms in %s', $vid);
          $perm4 = sprintf('reorder terms in %s', $vid);
          $perm5 = sprintf('view terms in %s', $vid);
          if (\Drupal::currentUser()
              ->hasPermission($perm1) || \Drupal::currentUser()
              ->hasPermission($perm2) || \Drupal::currentUser()
              ->hasPermission($perm3) || \Drupal::currentUser()
              ->hasPermission($perm4) || \Drupal::currentUser()
              ->hasPermission($perm5)) {
            return TRUE;
          }
        }
        break;
      case 'reorder terms':
        if ($vocabulary) {
          if (\Drupal::currentUser()
            ->hasPermission('reorder terms in ' . $vocabulary->id())) {
            return TRUE;
          }
        }
        break;
      case 'add terms':
        if ($vocabulary) {
          if (\Drupal::currentUser()
            ->hasPermission('add terms in ' . $vocabulary->id())) {
            return TRUE;
          }
        }
        break;
      case 'view terms':
        if ($vocabulary) {
          if (\Drupal::currentUser()
            ->hasPermission('view terms in ' . $vocabulary->id())) {
            return TRUE;
          }
        }
        break;
      case 'edit terms':
        if ($vocabulary) {
          if (\Drupal::currentUser()
            ->hasPermission('edit terms in ' . $vocabulary->id())) {
            return TRUE;
          }
        }
        break;
      case 'delete terms':
        if ($vocabulary) {
          if (\Drupal::currentUser()
            ->hasPermission('delete terms in ' . $vocabulary->id())) {
            return TRUE;
          }
        }
        break;
    }
    return FALSE;
  }

  /**
   * Route access callback
   */
  public static function fixRouteAccess(Route $route, RouteMatchInterface $match) {
    $op = $route->getOption('op');
    $vocabulary = $match->getParameter('taxonomy_vocabulary');
    if (self::fixAccess($op, $vocabulary)) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
