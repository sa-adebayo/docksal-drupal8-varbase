<?php

namespace Drupal\tour_builder;

use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Component\Utility\Html;

use Drupal\tour_ui\TourListBuilder;

/**
 * Provides a listing of tours.
 */
class TourBuilderListBuilder extends TourListBuilder {

  /**
   * Constructs a new TourListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage
   *   The config storage class.
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigEntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);

    $tour = $entity->getOriginalId();

    $operations['clone'] = [
      'title' => t('Clone'),
      'url' => $entity->toUrl('clone-form'),
      'weight' => 11,
    ];

    $operations['export'] = [
      'title' => t('Export'),
      'url' => $entity->toUrl('export-form'),
      'weight' => 12,
    ];

    $user = \Drupal::currentUser();

    if ($user->hasPermission('export configuration')) {
      $operations['export-config'] = [
        'title' => t('Export (configuration)'),
        'url' => Url::fromRoute('config.export_single', [
          'config_type' => 'tour',
          'config_name' => $tour
        ]),
        'weight' => 13,
      ];
    }

    // TODO: fix me
//    $operations['patch'] = [
//      'title' => t('Patch'),
//      'url' => $entity->toUrl('edit-form'),
//      'weight' => 11,
//    ];

    return $operations;
  }

}
