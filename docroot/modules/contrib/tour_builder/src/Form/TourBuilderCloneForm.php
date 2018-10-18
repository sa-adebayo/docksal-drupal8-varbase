<?php

namespace Drupal\tour_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the tour entity clone form.
 */
class TourBuilderCloneForm extends EntityForm {

  /**
   * Entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Constructs a TourForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\comment\CommentManagerInterface $comment_manager
   *   The comment manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tour name'),
      '#required' => TRUE,
      '#default_value' => $this->entity->label(),
    ];

    $form['old_name'] = array(
      '#type' => 'value',
      '#value' => $this->entity->getOriginalId(),
    );

    $form['new_name'] = array(
      '#title' => 'File name for new tour item.',
      '#type' => 'textfield',
      '#description' => 'This value should start with <strong>tour.tour.</strong> and may not exists.',
      '#field_prefix' => 'tour.tour.',
      '#default_value' => $this->entity->getOriginalId(),
    );

    $form['module'] = array(
      '#title' => 'Module for this tour.',
      '#type' => 'textfield',
      '#default_value' => $this->entity->getModule(),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, $redirect = TRUE) {
    // Make sure new_name does not exists
    // Make sure module exists
    $old_name = $form_state->getValue('old_name');
    $new_name = $form_state->getValue('new_name');

    if ($form_state->isValueEmpty('new_name')) {
      $form_state->setError($form['new_name'], $this->t('The tour file name cannot be empty.'));
    }

    if ($old_name == $new_name) {
      $form_state->setError($form['new_name'], $this->t('You must change the new tour file name', ['%tip' => 'XXX']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $redirect = TRUE) {
    // No need to submit the form as we did nothing to the original entity did we?!
    // parent::submitForm($form, $form_state);

    $this->entity = $this->entity->createDuplicate();
    //$this->entity->set('label', $form_state->getValue('label'));
    $this->entity->set('id', $form_state->getValue('new_name'));
    $this->entity->save();

    // Redirect to Entity edition.
    if ($redirect) {
      $form_state->setRedirect('entity.tour.edit_form', ['tour' => $this->entity->id()]);
    }
  }

}
