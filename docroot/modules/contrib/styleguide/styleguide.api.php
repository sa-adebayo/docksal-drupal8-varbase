<?php

/**
 * Alter styleguide elements.
 *
 * @param &$items
 *   An array of items to be displayed.
 *
 * @return
 *   No return value. Modify $items by reference.
 *
 * @see hook_styleguide()
 */
function hook_styleguide_alter(&$items) {
  // Add a class to the text test.
  $items['text']['content'] = '<div class="mytestclass">' . $items['text']['content'] . '</div>';
  // Remove the headings tests.
  unset($items['headings']);
}
