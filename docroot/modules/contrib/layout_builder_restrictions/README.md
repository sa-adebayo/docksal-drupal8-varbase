"Layout Builder Restrictions" (module)
---------------------

 * Introduction
 * Requirements
 * Installation & management in the UI
 * Advanced restrictions using hooks
 * Maintainers

INTRODUCTION
------------
Out of the box, the core [Layout Builder](https://www.drupal.org/project/ideas/issues/2884601) module allows all blocks/fields and layouts to be used. This module allows site builders (i.e., those managing content types) to set which which blocks and which layouts should be available for placement in Layout Builder.

Each entity type can be restricted individually. The following image shows the user interface for restricting the "one-column" layout:

![alt text](https://www.drupal.org/files/layout_builder_restrictions.gif "Restrict one-column layout with checkbox in UI")

REQUIREMENTS
------------
- PHP: 5.5.9 or above
- Drupal core: 8.6.x or above
- Layout Builder (core module)

INSTALLATION & MANAGEMENT IN THE UI
-----------------------------------
1. After enabling this module, go to any node content type's edit page
(e.g., `/admin/structure/types/manage/page`)
2. Expand the "Layout options" fieldset and choose either "Blocks available for placement" or "Layouts available for placement". Initially, all blocks and layouts are available, as would be the case if the module were not enabled. For blocks, each "provider" is listed, and can either be whitelisted to allow all blocks from the given provider, or restricted with the "Choose specific..." option:

![alt text](https://www.drupal.org/files/issues/2018-06-05/layout_builder_restrictions_ui.png "Logo Title Text 1")

Restrictions will affect both which blocks/layouts are available when setting the entity type's defaults, and individual content item overrides (note: you must check "Allow each content item to have its layout customized" to support overrides).

ADVANCED RESTRICTIONS USING HOOKS
---------------------------------
Many sites might want to do more advanced things, such as restricting certain content fields (like the "Title" field from placement). To achieve this you can add your own implementation of [hook_plugin_filter_TYPE__CONSUMER_alter()](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Plugin%21plugin.api.php/function/hook_plugin_filter_TYPE__CONSUMER_alter/8.6.x) (which is invoked for both themes and modules).

This hook is what this module, itself uses (see the .module file); an example implementation, from the layout_builder_test.module file, is below:

```php
/**
 * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
 */
function layout_builder_test_plugin_filter_block__layout_builder_alter(array &$definitions) {
  // Explicitly remove the "Help" blocks from the list.
  unset($definitions['help_block']);

  // Explicitly remove the "Sticky at top of lists field_block".
  $disallowed_fields = [
    'sticky',
  ];

  foreach ($definitions as $plugin_id => $definition) {
    // Field block IDs are in the form 'field_block:{entity}:{bundle}:{name}',
    // for example 'field_block:node:article:revision_timestamp'.
    preg_match('/field_block:.*:.*:(.*)/', $plugin_id, $parts);
    if (isset($parts[1]) && in_array($parts[1], $disallowed_fields, TRUE)) {
      // Unset any field blocks that match our predefined list.
      unset($definitions[$plugin_id]);
    }
  }
}
```

MAINTAINERS
-----------
Current maintainers:
 * eiriksm - https://www.drupal.org/u/eiriksm
 * Mark Fullmer (mark_fullmer) - https://www.drupal.org/u/mark_fullmer

This project has been sponsored by:
* [The University of Texas at Austin](https://www.drupal.org/university-of-texas-at-austin)
* [NY Media AS](https://www.drupal.org/ny-media-as)
