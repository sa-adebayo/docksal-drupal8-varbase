<?php

namespace Drupal\social_auth_linkedin;

use Drupal\social_auth\AuthManager\OAuth2Manager;
use Drupal\Core\Config\ConfigFactory;

/**
 * Contains all the logic for LinkedIn login integration.
 */
class LinkedInAuthManager extends OAuth2Manager {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Used for accessing configuration object factory.
   */
  public function __construct(ConfigFactory $configFactory) {
    parent::__construct($configFactory->get('social_auth_linkedin.settings'));
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate() {
    $this->setAccessToken($this->client->getAccessToken('authorization_code',
      ['code' => $_GET['code']]));
  }

  /**
   * {@inheritdoc}
   */
  public function getUserInfo() {
    $this->user = $this->client->getResourceOwner($this->getAccessToken());
    return $this->user;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorizationUrl() {
    $scopes = ['r_basicprofile', 'r_emailaddress'];

    $extra_scopes = $this->getScopes();
    if ($extra_scopes) {
      if (strpos($extra_scopes, ',')) {
        $scopes = array_merge($scopes, explode(',', $extra_scopes));
      }
      else {
        $scopes[] = $extra_scopes;
      }
    }

    // Returns the URL where user will be redirected.
    return $this->client->getAuthorizationUrl([
      'scope' => $scopes,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function requestEndPoint($path) {

    $url = 'https://api.linkedin.com' . $path . '?format=json';

    $request = $this->client->getAuthenticatedRequest('GET', $url, $this->getAccessToken());

    $response = $this->client->getResponse($request);

    return $response->getBody()->getContents();
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->client->getState();
  }

}
