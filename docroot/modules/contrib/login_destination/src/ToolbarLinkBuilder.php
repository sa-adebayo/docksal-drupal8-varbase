<?php

namespace Drupal\login_destination;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\ToolbarLinkBuilder as UserToolbarLinkBuilder;


/**
 * ToolbarLinkBuilder fills out the placeholders generated in user_toolbar().
 */
class ToolbarLinkBuilder extends UserToolbarLinkBuilder {

  /**
   * The decorated service.
   */
  protected $innerService;

  /**
   * ToolbarHandler constructor.
   *
   * @param $inner_service
   *   The decorated service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   */
  public function __construct($inner_service, AccountProxyInterface $account) {
    $this->innerService = $inner_service;
    parent::__construct($account);
  }

  /**
   * Pass any undefined method calls onto the inner service.
   *
   * @param $method
   *   The method being called.
   * @param $args
   *   The arguments passed to the method.
   * @return mixed
   *   The inner services response.
   */
  public function __call($method, $args) {
    return call_user_func_array(array($this->innerService, $method), $args);
  }

  /**
   * Lazy builder callback for rendering toolbar links.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   */
  public function renderToolbarLinks() {
    $build = $this->innerService->renderToolbarLinks();

    if ($this->account->getAccount()->isAuthenticated()) {
      $url = &$build['#links']['logout']['url'];

      $current = \Drupal::service('path.current')->getPath();

      // Add current param to be able to evaluate previous page.
      $url->setOptions(['query' => ['current' => $current]]);
    }

    return $build;
  }

}
