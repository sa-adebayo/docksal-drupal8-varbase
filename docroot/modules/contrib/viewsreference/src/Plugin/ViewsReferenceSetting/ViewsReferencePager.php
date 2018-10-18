<?php

namespace Drupal\viewsreference\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting pager plugin.
 *
 * @ViewsReferenceSetting(
 *   id = "pager",
 *   label = @Translation("Pagination"),
 *   default_value = 0,
 * )
 */
class ViewsReferencePager extends PluginBase implements ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(&$form_field) {
    $form_field['#type'] = 'select';
    $form_field['#options'] = [
      'some' => $this->t('Hide'),
      'full' => $this->t('Full'),
      'mini' => $this->t('Mini'),
    ];
    $form_field['#weight'] = 35;
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    if (!empty($value)) {
      $pager = $view->display_handler->getOption('pager');
      $pager['type'] = $value;
      $view->display_handler->setOption('pager', $pager);
    }
  }

}
