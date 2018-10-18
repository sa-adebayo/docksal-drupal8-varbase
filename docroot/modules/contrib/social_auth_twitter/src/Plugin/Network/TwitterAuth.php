<?php

namespace Drupal\social_auth_twitter\Plugin\Network;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\MetadataBubblingUrlGenerator;
use Drupal\social_api\SocialApiException;
use Drupal\social_auth\Plugin\Network\SocialAuthNetwork;
use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;

/**
 * Defines Social Auth Twitter Network Plugin.
 *
 * @Network(
 *   id = "social_auth_twitter",
 *   social_network = "Twitter",
 *   type = "social_auth",
 *   handlers = {
 *      "settings": {
 *          "class": "\Drupal\social_auth_twitter\Settings\TwitterAuthSettings",
 *          "config_id": "social_auth_twitter.settings"
 *      }
 *   }
 * )
 */
class TwitterAuth extends SocialAuthNetwork implements TwitterAuthInterface {
  /**
   * The url generator.
   *
   * @var \Drupal\Core\Render\MetadataBubblingUrlGenerator
   */
  protected $urlGenerator;

  /**
   * The site settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $siteSettings;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('url_generator'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('settings')
    );
  }

  /**
   * TwitterLogin constructor.
   *
   * @param \Drupal\Core\Render\MetadataBubblingUrlGenerator $url_generator
   *   Used to generate a absolute url for authentication.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings factory.
   */
  public function __construct(MetadataBubblingUrlGenerator $url_generator,
                              array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              Settings $settings) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $config_factory);

    $this->urlGenerator = $url_generator;
    $this->siteSettings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function initSdk() {
    $class_name = '\Abraham\TwitterOAuth\TwitterOAuth';
    if (!class_exists($class_name)) {
      throw new SocialApiException(sprintf('The PHP SDK for Twitter Client could not be found. Class: %s.', $class_name));
    }

    /* @var \Drupal\social_auth_twitter\Settings\TwitterAuthSettings $settings */
    $settings = $this->settings;

    // Creates a and sets data to TwitterOAuth object.
    $client = new TwitterOAuth($settings->getConsumerKey(), $settings->getConsumerSecret());

    $proxy = $this->getProxy();
    if ($proxy) {
      $client->setProxy($proxy);
    }

    return $client;
  }

  /**
   * {@inheritdoc}
   */
  public function getOauthCallback() {
    return $this->urlGenerator->generateFromRoute('social_auth_twitter.callback', [], ['absolute' => TRUE]);
  }

  /**
   * {@inheritdoc}
   */
  public function getSdk2($oauth_token, $oauth_token_secret) {
    /* @var \Drupal\social_auth_twitter\Settings\TwitterAuthSettings $settings */
    $settings = $this->settings;

    $client = new TwitterOAuth($settings->getConsumerKey(), $settings->getConsumerSecret(),
                $oauth_token, $oauth_token_secret);

    $proxy = $this->getProxy();
    if ($proxy) {
      $client->setProxy($proxy);
    }

    return $client;
  }

  /**
   * Parse proxy settings.
   *
   * @return array
   *   The proxy settings or NULL if not set.
   */
  private function getProxy() {
    $proxy = NULL;

    $proxyUrl = $this->siteSettings->get('http_client_config')['proxy']['https'];

    if ($proxyUrl) {
      $proxy_settings = parse_url($proxyUrl);

      if ($proxy_settings) {
        $proxy = [
          'CURLOPT_PROXY' => $proxy_settings['host'],
          'CURLOPT_PROXYUSERPWD' => "{$proxy_settings['user']}:{$proxy_settings['pass']}",
          'CURLOPT_PROXYPORT' => $proxy_settings['port'],
        ];
      }
    }
    return $proxy;
  }

}
