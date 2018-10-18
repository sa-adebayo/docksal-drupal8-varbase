<?php

/**
 * @file
 * Drush command file for Drush 8.
 */

/**
 * Implements hook_drush_command().
 */
function drd_agent_drush_command() {
  $items = [];

  $items['drd-agent-setup'] = [
    'arguments' => [
      'token' => 'Base64 and json encoded array of all variables required such that DRD can communicate with this domain in the future',
    ],
  ];

  return $items;
}

/**
 * Drush command to configure this domain for communication with a DRD instance.
 *
 * @param string $token
 *   The token for validation.
 *
 * @see drd_agent_setup()
 */
function drush_drd_agent_setup($token) {
  /* @var \Drupal\drd_agent\Setup $service */
  $service = \Drupal::service('drd_agent.setup');
  $service
    ->setRemoteSetupToken($token)
    ->execute();
}
