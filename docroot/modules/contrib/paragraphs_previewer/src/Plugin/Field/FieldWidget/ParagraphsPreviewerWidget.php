<?php

namespace Drupal\paragraphs_previewer\Plugin\Field\FieldWidget;

use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Component\Utility\NestedArray;

/**
 * Plugin implementation of the 'paragraphs_previwer' widget.
 *
 * @FieldWidget(
 *   id = "paragraphs_previwer",
 *   label = @Translation("Paragraphs Previewer & Paragraphs EXPERIMENTAL"),
 *   description = @Translation("An paragraphs experimental form widget with a previewer."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParagraphsPreviewerWidget extends ParagraphsWidget {

  use ParagraphsPreviewerWidgetTrait;

  /**
   * The default edit mode.
   *
   * @var string
   */
  const PARAGRAPHS_PREVIEWER_DEFAULT_EDIT_MODE = 'closed';

}
