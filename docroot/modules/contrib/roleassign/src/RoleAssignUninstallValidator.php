<?php

/**
 * @file
 * Contains \Drupal\roleassign\RoleAssignUninstallValidator.
 */

namespace Drupal\roleassign;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Prevents uninstallation of roleassign module by restricted users.
 */
class RoleAssignUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new RoleAssignUninstallValidator.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == "roleassign") {
      if (!\Drupal::currentUser()->hasPermission('administer roles')) {
        $reasons[] = $this->t('You are not allowed to disable this module.');
      }
    }
    return $reasons;
  }

}
