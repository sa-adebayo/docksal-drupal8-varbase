<?php

namespace Drupal\drd_agent\Commands;

use Drush\Commands\DrushCommands;

/**
 * Class Base.
 *
 * @package Drupal\drd_agent
 */
class Drush extends DrushCommands {

  /**
   * Configure this domain for communication with a DRD instance.
   *
   * @param string $token
   *   Base64 and json encoded array of all variables required such that
   *   DRD can communicate with this domain in the future.
   *
   * @command drd:agent:setup
   * @aliases drd-agent-setup
   */
  public function setup($token) {
    $_SESSION['drd_agent_authorization_values'] = $token;
    $service = \Drupal::service('drd_agent.setup');
    $service->execute();
    unset($_SESSION['drd_agent_authorization_values']);
  }

}
