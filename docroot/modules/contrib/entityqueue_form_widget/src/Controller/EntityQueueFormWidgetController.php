<?php

namespace Drupal\entityqueue_form_widget\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Module controller.
 */
class EntityQueueFormWidgetController extends ControllerBase {

  /**
   * Content method: used to show module home page content.
   */
  public function content() {
    return array(
      '#type' => 'markup',
      '#markup' => t('Entity Queue Form Widget Custom Page'),
    );
  }

}
