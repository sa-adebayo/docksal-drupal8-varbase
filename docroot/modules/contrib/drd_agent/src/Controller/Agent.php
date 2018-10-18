<?php

namespace Drupal\drd_agent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drd\Agent\Action\Base as ActionBase;
use Drupal\drd\Crypt\Base as CryptBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Default.
 *
 * @package Drupal\drd_agent\Controller
 */
class Agent extends ControllerBase {

  /**
   * The agent configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Get an array of http response headers.
   *
   * @return array
   *   The array with headers.
   */
  public static function responseHeader() {
    return [
      'Content-Type' => 'text/plain; charset=utf-8',
      'X-DRD-Agent' => $_SERVER['HTTP_X_DRD_VERSION'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->config = \Drupal::configFactory()->get('drd_agent.settings');
  }

  /**
   * Route callback to execute an action and return their esult.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response to DRD.
   */
  public function get() {
    \Drupal::service('drd_agent.library')->load();
    return $this->deliver(ActionBase::run(8, $this->config->get('debug_mode')));
  }

  /**
   * Route callback to retrieve a list of available crypt methods.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response to DRD.
   */
  public function getCryptMethods() {
    \Drupal::service('drd_agent.library')->load();
    return $this->deliver(base64_encode(json_encode(CryptBase::getMethods())));
  }

  /**
   * Route callback to authorize a DRD instance by a secret.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response to DRD.
   */
  public function authorizeBySecret() {
    \Drupal::service('drd_agent.library')->load();
    return $this->deliver(ActionBase::authorizeBySecret(8, $this->config->get('debug_mode')));
  }

  /**
   * Callback to deliver the result of the action in json format.
   *
   * @param string|Response $data
   *   The result which should be delivered back to DRD.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response to DRD.
   */
  private function deliver($data) {
    return ($data instanceof Response) ? $data : new JsonResponse($data, 200, self::responseHeader());
  }

}
