<?php

/**
 * @file
 * Contains Drupal\mail_edit\Tests\MailEditorDummyTest.
 */

namespace Drupal\mail_edit\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests true to allow the testbot to run.
 *
 * @group mail_editor
 */
class MailEditorDummyTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();
  }

  /**
   * Tests success (to allow the testbot to run).
   */
  public function testMailEditorSuccess()  {
    $this->assertTrue(TRUE);
  }

}
