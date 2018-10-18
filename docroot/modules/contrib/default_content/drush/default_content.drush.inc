<?php

/**
 * @file
 * Drush integration for the default_content module.
 */
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Implements hook_drush_command().
 */
function default_content_drush_command() {
  $items['default-content-export'] = [
    'description' => dt('Exports a single entity'),
    'arguments' => [
      'entity_type' => dt('The entity type to export.'),
      'entity_id' => dt('The ID of the entity to export.'),
    ],
    'options' => [
      'file' => dt('Write out the exported content to a file instead of stdout'),
    ],
    'aliases' => ['dce'],
    'required-arguments' => 2,
  ];
  $items['default-content-export-references'] = [
    'description' => dt('Exports an entity and all its referenced entities.'),
    'arguments' => [
      'entity_type' => dt('The entity type to export.'),
      'entity_id' => dt('The ID of the entity to export.'),
    ],
    'options' => [
      'folder' => dt('Folder to export to, entities are grouped by entity type into directories.'),
    ],
    'aliases' => ['dcer'],
    'required-arguments' => 1,
  ];
  $items['default-content-export-module'] = [
    'description' => dt('Exports all the content defined in a module info file.'),
    'arguments' => [
      'module' => dt('The name of the module.'),
    ],
    'aliases' => ['dcem'],
    'required-arguments' => 1,
  ];
  $items['default-content-export-all'] = [
    'description' => dt('Exports all content from any appropriate entity types.'),
    'options' => [
      'folder' =>  dt('Folder to export to, entities are grouped by entity type into directories.'),
      'exclude' => [
        'description' => dt('Entity types and/or bundles to exclude'),
        'example-value' => 'node:blog:page,user',
      ]
    ],
    'aliases' => ['dcea'],
    'examples' => array(
      'drush dcea' => 'Export all content entities on the site into the current directory.',
      'drush dcea --folder=/path/to/content' => 'Export all content entities into the specified directory.',
      'drush dcea --exclude=taxonomy_term' => 'Export all content entities except taxonomy terms.',
      'drush dcea --exclude=node:page:blog' => "Export all content entities except the node bundles 'page' and 'blog'.",
      'drush dcea --exclude=node:page,user' => "Export all content entities except the node bundle 'page' and users."
    ),
  ];

  return $items;
}

/**
 * Exports a piece of content into the stdout or into a file.
 *
 * @param string $entity_type_id
 *   The entity type ID.
 * @param mixed $entity_id
 *   The entity ID to export.
 */
function drush_default_content_export($entity_type_id, $entity_id) {
  /** @var \Drupal\default_content\DefaultContentExporterInterface $exporter */
  $exporter = \Drupal::service('default_content.exporter');
  $export = $exporter->exportContent($entity_type_id, $entity_id);

  if ($file = drush_get_option('file')) {
    file_put_contents($file, $export);
  }
  else {
    drush_print($export);
  }
}

/**
 * Exports a piece of content and all its referenced entities.
 *
 * @param string $entity_type_id
 *   The entity type ID.
 * @param mixed $entity_id
 *   (Optional) The entity ID to export or all entities will be exported.
 */
function drush_default_content_export_references($entity_type_id, $entity_id = NULL) {
  /** @var \Drupal\default_content\DefaultContentExporterInterface $exporter */
  $exporter = \Drupal::service('default_content.exporter');

  $folder = drush_get_option('folder', '.');
  if (is_null($entity_id)) {
    $entities = \Drupal::entityQuery($entity_type_id)->execute();
  }
  else {
    $entities = [$entity_id];
  }
  // @todo Add paging.
  foreach ($entities as $entity_id) {
    $serialized_by_type = $exporter->exportContentWithReferences($entity_type_id, $entity_id);
    $exporter->writeDefaultContent($serialized_by_type, $folder);
  }
}

/**
 * Exports all of the content for a given module.
 *
 * @param string $module_name
 *   The module name to export.
 */
function drush_default_content_export_module($module_name) {
  /** @var \Drupal\default_content\DefaultContentExporterInterface $exporter */
  $exporter = \Drupal::service('default_content.exporter');
  $serialized_by_type = $exporter->exportModuleContent($module_name);
  $module_folder = \Drupal::moduleHandler()
    ->getModule($module_name)
    ->getPath() . '/content';
  $exporter->writeDefaultContent($serialized_by_type, $module_folder);
}

/**
 * Exports all content optionally filtered by entity type and/or bundle.
 */
function drush_default_content_export_all() {
  // Filter out any non-content entity types.
  /** @var \Drupal\Core\Entity\EntityTypeInterface[] $definitions */
  $definitions = \Drupal::entityTypeManager()->getDefinitions();
  $definitions = array_filter($definitions, function (EntityTypeInterface $definition) {
    return is_a($definition->getClass(), ContentEntityInterface::class, TRUE);
  });
  // Filter out entire entity types specified in the exclude option.
  $exclude_option = drush_get_option('exclude');
  if ($exclude_option) {
    $excluded = _default_content_drush_parse_exclude_option($exclude_option);
    $definitions = array_filter($definitions, function (EntityTypeInterface $definition) use ($excluded) {
      return !in_array($definition->id(), $excluded['entity_type']);
    });
  }
  $folder = drush_get_option('folder', '.');
  /** @var \Drupal\default_content\ExporterInterface $exporter */
  $exporter = \Drupal::service('default_content.exporter');
  /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info */
  $bundle_info = \Drupal::service('entity_type.bundle.info');

  drush_print(dt("Exporting"), 0, STDERR);
  foreach ($definitions as $entity_type_id => $definition) {
    // Filter out bundles specified in the exclude option.
    $bundles = NULL;
    if (!empty($excluded['bundle'][$entity_type_id])) {
      $all_bundles = array_keys($bundle_info->getBundleInfo($entity_type_id));
      $bundles = array_diff($all_bundles, $excluded['bundle'][$entity_type_id]);
    }
    $serialized_by_type = $exporter->exportEntityTypeContent($entity_type_id, $bundles);
    if ($serialized_by_type) {
      $exporter->writeDefaultContent($serialized_by_type, $folder);
      $count = count($serialized_by_type[$entity_type_id]);
      drush_print($definition->getLabel() . ':' . $count, 1, STDERR);
    }
  }
}

/**
 * Parse the default-content-export-all exclude option.
 *
 * @param string $exclude_option
 *   The exclude option string.
 *
 * @return array
 * An associative array keyed by:
 * - entity_type: An array of entity type IDs to be fully excluded
 * - bundle: An associative array of bundles to exclude, keyed by entity type
 *   ID.
 *
 * @see drush_default_content_export_all()
 */
function _default_content_drush_parse_exclude_option($exclude_option) {
  $excluded_entity_types = [];
  $excluded_bundles = [];

  foreach (explode(',', $exclude_option) as $value) {
    $pieces = explode(':', $value);
    // The first piece is the entity type ID.
    $entity_type_id = array_shift($pieces);
    // Any remaining pieces are bundles.
    if ($pieces) {
      $excluded_bundles[$entity_type_id] = $pieces;
    }
    else {
      $excluded_entity_types[] = $entity_type_id;
    }
  }

  return [
    'entity_type' => $excluded_entity_types,
    'bundle' => $excluded_bundles,
  ];
}
