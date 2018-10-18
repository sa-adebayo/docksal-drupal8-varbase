<?php

namespace Drupal\taxonomy_access_fix\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * New and improved hook_menu_alter().
   */
  public function alterRoutes(RouteCollection $collection) {
    // admin/structure/taxonomy
    if ($route = $collection->get('entity.taxonomy_vocabulary.collection')) {
      $route->setRequirements([
        '_custom_access' => '\Drupal\taxonomy_access_fix\TaxonomyAccessFixPermissions::fixRouteAccess',
      ]);
      $route->setOption('op', 'index');
    }

    // admin/structure/taxonomy/%vocabulary
    if ($route = $collection->get('entity.taxonomy_vocabulary.overview_form')) {
      $route->setRequirements([
        '_custom_access' => '\Drupal\taxonomy_access_fix\TaxonomyAccessFixPermissions::fixRouteAccess',
      ]);
      $route->setOption('op', 'list terms');
    }

    // admin/structure/taxonomy/%vocabulary/add
    if ($route = $collection->get('entity.taxonomy_term.add_form')) {
      $route->setRequirements([
        '_custom_access' => '\Drupal\taxonomy_access_fix\TaxonomyAccessFixPermissions::fixRouteAccess',
      ]);
      $route->setOption('op', 'add terms');
    }

    // admin/structure/taxonomy/manage/%vocabulary/reset
    if ($route = $collection->get('entity.taxonomy_vocabulary.reset_form')) {
      $route->setRequirements([
        '_custom_access' => '\Drupal\taxonomy_access_fix\TaxonomyAccessFixPermissions::fixRouteAccess',
      ]);
      $route->setOption('op', 'reorder terms');
    }
  }

}
