<?php

namespace Drupal\drd_agent;

/**
 * Class Setup.
 *
 * @package Drupal\drd_agent
 */
class Setup {

  protected $values;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    if (isset($_SESSION['drd_agent_authorization_values'])) {
      $this->setRemoteSetupToken($_SESSION['drd_agent_authorization_values']);
    }
  }

  /**
   * Set the remote setup token which contains the configuration.
   *
   * @param string $remoteSetupToken
   *   The remote setup token.
   *
   * @return $this
   */
  public function setRemoteSetupToken($remoteSetupToken) {
    $values = strtr($remoteSetupToken, ['-' => '+', '_' => '/']);
    $this->values = json_decode(base64_decode($values), TRUE);
    return $this;
  }

  /**
   * Perform the configuration with the data from the token.
   *
   * @return array
   *   The configuration data for this domain.
   */
  public function execute() {
    $config = \Drupal::configFactory()->getEditable('drd_agent.settings');

    $authorised = $config->get('authorised');

    $this->values['timestamp'] = \Drupal::time()->getRequestTime();
    $this->values['ip'] = \Drupal::request()->getClientIp();
    $authorised[$this->values['uuid']] = $this->values;

    $config->set('authorised', $authorised)->save(TRUE);
    return $this->values;
  }

  /**
   * Get the hostname to which we should redirect after confirmation.
   *
   * @return string
   *   The hostname.
   */
  public function getDomain() {
    return parse_url($this->values['redirect'], PHP_URL_HOST);
  }

}
