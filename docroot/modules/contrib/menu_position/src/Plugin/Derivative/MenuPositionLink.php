<?php

namespace Drupal\menu_position\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides menu links for Menu Position Rules.
 *
 * @see \Drupal\menu_position\Plugin\Menu\MenuPositionLink
 */
class MenuPositionLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The menu_position_rule storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $menu_position_rule_storage
   *   The menu_position_rule storage.
   */
  public function __construct(EntityStorageInterface $menu_position_rule_storage) {
    $this->storage = $menu_position_rule_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')->getStorage('menu_position_rule')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Reset the discovered definitions.
    $this->derivatives = [];
    foreach ($this->storage->loadMultiple() as $menu_position_rule) {
      /* @var \Drupal\menu_position\Entity\MenuPositionRule $menu_position_rule */
      /* @var \Drupal\menu_position\Plugin\Menu\MenuPositionLink $menu_link */
      if ($menu_link = $menu_position_rule->getMenuLinkPlugin()) {
        // Link already exists, use that.
        $definition = $menu_link->getPluginDefinition();
      }
      else {
        // Provide defaults, they will be updated by the rule.
        $definition = [
          'id' => $base_plugin_definition['id'] . ':' . $menu_position_rule->id(),
          'title' => t('@label (menu position rule)', [
            '@label' => $menu_position_rule->getLabel(),
          ]),
          'menu_name' => $menu_position_rule->getMenuName(),
          'parent' => $menu_position_rule->getParent(),
          'weight' => 0,
          'metadata' => [
            'entity_id' => $menu_position_rule->id(),
          ],
          // Links are enabled (i.e. visible) depending on the modules' settings.
          'enabled' => \Drupal::config('menu_position.settings')->get('link_display') === 'child',
        ];
      }
      $this->derivatives[$menu_position_rule->id()] = $definition + $base_plugin_definition;
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
