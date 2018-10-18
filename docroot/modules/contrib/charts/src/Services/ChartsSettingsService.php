<?php

namespace Drupal\charts\Services;

use Drupal\Core\Config\ConfigFactory;

/**
 * Charts Settings Service.
 */
class ChartsSettingsService implements ChartsSettingsServiceInterface {

  private $configFactory;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory.
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getChartsSettings() {
    $config = $this->configFactory->getEditable('charts.settings');

    return $config->get('charts_default_settings');
  }

}
