<?php

namespace Drupal\l10n_client_contributor\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;

/**
 * Plugin implementation of the 'l10n_client_contributor_key_widget' widget.
 *
 * @FieldWidget(
 *   id = "l10n_client_contributor_key_widget",
 *   label = @Translation("Localization client contributor key"),
 *   field_types = {
 *     "l10n_client_contributor_key"
 *   }
 * )
 */
class ClientApiKeyDefaultWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $config = \Drupal::configFactory()->getEditable('l10n_client_contributor.settings');
    /** @var \Drupal\user\UserInterface $account */
    $account = $items->getEntity();

    if (!$config->get('use_server') || !($account instanceof UserInterface) || !$account->hasPermission('contribute translations to localization server')) {
      // Should not expose a widget if we are not using a server, the
      // entity is not a user or the user does not have permission to
      // contribute.
      return array();
    }

    $server_root = $config->get('server');
    $server_api_link = $server_root . '?q=translate/remote/userkey/' . l10n_client_contributor_user_token($account);

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#title'] = $this->t('Your API key for @server', array('@server' => $server_root));
    $element['value']['#description'] = $this->t('This is a unique key that will allow you to send translations to the remote server. To get your API key go to <a href=":server">:server</a>.', array(':server' => $server_api_link));

    return $element;
  }
}
