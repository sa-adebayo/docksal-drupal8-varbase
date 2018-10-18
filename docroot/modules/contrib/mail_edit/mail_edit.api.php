<?php

/**
 * @file
 * Documentation of the hooks provided by Mail Edit.
 */

/**
 * Define the email templates provided by a module.
 *
 * @return array
 *   A nested array. The first level indicates the config keys which store the
 *   email templates. Each item within that should be in a 'key' => 'label'
 *   structure, but just 'key' will also work. In both cases the 'key' will be
 *   used as the array key within the config structure and is expected to be an
 *   array with keys named 'subject' and 'body', representing the email's
 *   subject and body fields respectively.
 *   Note: if these config objects or email values do not exist they can be
 *   dynamically created at runtime by editing and saving them. This allows
 *   email templates to be defined without necessarily a default value being
 *   present for them.
 *
 * @see user_mail_edit_templates()
 */
function hook_mail_edit_templates() {
  return [
    // Config object name.
    'mymodule.emails' => [
      // Template name => template label.
      'never_gonna_give_you_up' => t('Never gonna give you up'),
      'never_gonna_let_you_down' => t('Never gonna let you down'),
      'never_gonna_run_around' => t('Never gonna run around'),
      'and_desert_you' => t('And desert you'),
      // Template name.
      'never_gonna_make_you_cry',
      'never_gonna_say_goodbye',
      'never_gonna_tell_a_lie',
      'and_hurt_you',
    ],
  ];
}

/**
 * Allow the list of email templates to be modified.
 *
 * @param array $templates
 *   The list of templates available to be edited by the module.
 */
function hook_mail_edit_templates_alter(array &$templates) {
  // Disallow editing the 'confirm account cancel' message.
  unset($templates['user.email.cancel_confirm']);
}

/**
 * Token types that may be used in the token browser while editing an email.
 *
 * This is more of a special function than a hook, it is only called when
 * editing an email.
 *
 * @param string $template_name
 *   The machine name of the email template being edited.
 *
 * @return array
 *   A list of each token type that can be used in emails and by the token
 *   browser from the Token module. The 'user' token type will always be
 *   available.
 */
function hook_mail_edit_token_types($template_name) {
  if ($template_name == 'new_order_notification') {
    return ['commerce_order', 'commerce_product'];
  }
}
