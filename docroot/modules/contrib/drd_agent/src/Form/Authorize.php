<?php

namespace Drupal\drd_agent\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Authorize a new dashboard for this drd-agent.
 */
class Authorize extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drd_agent_authorize_form';
  }

  /**
   * Build the authorization form to paste the token from DRD.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The form.
   */
  protected function buildFormToken(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['token'] = [
      '#type' => 'textarea',
      '#title' => t('Authentication token'),
      '#description' => t('Paste the token for this domain from the DRD dashboard, which you want to authorize.'),
      '#default_value' => '',
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Validate'),
    ];

    return $form;
  }

  /**
   * Build the authorization confirmation form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The form.
   */
  protected function buildFormConfirmation(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\drd_agent\Setup $service */
    $service = \Drupal::service('drd_agent.setup');
    $form = [];

    $form['attention'] = [
      '#markup' => t('You are about to grant admin access to the Drupal Remote Dashboard on the following domain:'),
      '#prefix' => '<div>',
      '#suffix' => '</div>',
    ];
    $form['domain'] = [
      '#markup' => $service->getDomain(),
      '#prefix' => '<div class="domain">',
      '#suffix' => '</div>',
    ];
    $form['cancel'] = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Grant admin access'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = empty($_SESSION['drd_agent_authorization_values']) ?
      $this->buildFormToken($form, $form_state) :
      $this->buildFormConfirmation($form, $form_state);

    $form['#attributes'] = [
      'class' => ['drd-agent-auth'],
    ];
    $form['#attached']['library'][] = 'drd_agent/general';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (empty($_SESSION['drd_agent_authorization_values'])) {
      $_SESSION['drd_agent_authorization_values'] = $form_state->getValue('token');
    }
    else {
      if ($form_state->getValue('op') == $form['submit']['#value']) {
        $service = \Drupal::service('drd_agent.setup');
        $values = $service->execute();
        $form_state->setResponse(TrustedRedirectResponse::create($values['redirect']));
      }
      unset($_SESSION['drd_agent_authorization_values']);
    }
  }

}
