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
 * Plugin implementation of the 'entity_reference paragraphs' widget.
 *
 * We hide add / remove buttons when translating to avoid accidental loss of
 * data because these actions effect all languages.
 *
 * @FieldWidget(
 *   id = "entity_reference_paragraphs_previewer",
 *   label = @Translation("Paragraphs Previewer & Paragraphs Classic"),
 *   description = @Translation("An paragraphs inline form widget with a previewer."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class InlineParagraphsPreviewerWidget extends ParagraphsWidget {

  use ParagraphsPreviewerWidgetTrait;

  /**
   * The default edit mode.
   *
   * @var string
   */
  const PARAGRAPHS_PREVIEWER_DEFAULT_EDIT_MODE = 'closed';

}
