<?php

namespace Drupal\tour_builder\Form;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Serialization\Yaml;

/**
 * Form controller for the tour entity clone form.
 */
class TourBuilderExportForm extends EntityForm {

  /**
   * Entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.storage')
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
  public function __construct(EntityTypeManagerInterface $entity_type_manager, StorageInterface $config_storage) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configStorage = $config_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $tour = $this->entity;

    $definition = $this->entityManager->getDefinition('tour');
    $name = $definition->getConfigPrefix() . '.' . $this->entity->getOriginalId();


    $content = Yaml::encode($this->configStorage->read($name));

//    $filename = $name . '.yml';
//    $path = 'temporary://' . $filename;
//    //$link = \Drupal::l($name, Url::fromUri($path));
//    file_put_contents($path, $content);

//    $form['link'] = array(
//      '#type' => 'item',
//      '#title' => $this->t('Download'),
//      '#description' => 'Right click to download.',
//      '#markup' => $link,
//    );
    $form['export'] = [
      '#type' => 'textarea',
      '#title' => $this->t('YAML Content'),
      '#description' => $this->t('Filename: %name', ['%name' => $name . '.yml']),
      '#rows' => 15,
      '#default_value' => $content,
    ];

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

    // Redirect to Entity edition.
    if ($redirect) {
      $form_state->setRedirect('entity.tour.edit_form', ['tour' => $this->entity->id()]);
    }
  }

}
