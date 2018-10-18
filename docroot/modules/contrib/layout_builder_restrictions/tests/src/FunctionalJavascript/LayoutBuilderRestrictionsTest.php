<?php

namespace Drupal\Tests\layout_builder_restrictions\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Demonstrate that blocks can be individually restricted.
 *
 * @group layout_builder_restrictions
 */
class LayoutBuilderRestrictionsTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'layout_builder',
    'layout_builder_restrictions',
    'node',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a node bundle.
    $this->createContentType(['type' => 'bundle_with_section_field']);

    $this->drupalLogin($this->drupalCreateUser([
      'access administration pages',
      'administer blocks',
      'administer node display',
      'administer node fields',
      'configure any layout',
    ]));

  }

  /**
   * Verify that the UI can restrict blocks in Layout Builder settings tray.
   */
  public function testBlockRestriction() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $field_ui_prefix = 'admin/structure/types/manage/bundle_with_section_field';
    // From the manage display page, go to manage the layout.
    $this->drupalGet("$field_ui_prefix/display/default");
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');
    $this->clickLink('Add Block');
    $assert_session->assertWaitOnAjaxRequest();
    // Establish that initially, the body field is available.
    $assert_session->linkExists('Body');

    // Restrict all 'Content' fields from options.
    $this->drupalGet("$field_ui_prefix/display/default");
    $element = $page->find('xpath', '//*[@id="edit-layout-layout-builder-restrictions-allowed-blocks"]/summary');
    $element->click();
    $element = $page->find('xpath', '//*[@id="edit-layout-builder-restrictions-allowed-blocks-content-restriction-all"]');
    $assert_session->checkboxChecked('edit-layout-builder-restrictions-allowed-blocks-content-restriction-all');
    $assert_session->checkboxNotChecked('edit-layout-builder-restrictions-allowed-blocks-content-restriction-restricted');
    $element = $page->find('xpath', '//*[@id="edit-layout-builder-restrictions-allowed-blocks-content-restriction-restricted"]');
    $element->click();
    $page->pressButton('Save');

    // Establish that the 'body' field is no longer present.
    $this->drupalGet("$field_ui_prefix/display/default");
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals("$field_ui_prefix/display-layout/default");
    // The "body" field is no longer present.
    $assert_session->elementExists('css', '.field--name-body');
    $this->clickLink('Add Block');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkNotExists('Body');

    // Allow only 'body' field as an option.
    $this->drupalGet("$field_ui_prefix/display/default");
    $element = $page->find('xpath', '//*[@id="edit-layout-layout-builder-restrictions-allowed-blocks"]/summary');
    $element->click();
    $assert_session->checkboxChecked('edit-layout-builder-restrictions-allowed-blocks-content-restriction-restricted');
    $page->checkField('layout_builder_restrictions[allowed_blocks][Content][field_block:node:bundle_with_section_field:body]');
    $page->pressButton('Save');

    $this->drupalGet("$field_ui_prefix/display/default");
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals("$field_ui_prefix/display-layout/default");
    // The "body" field is once again present.
    $assert_session->elementExists('css', '.field--name-body');
    $this->clickLink('Add Block');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkExists('Body');
    // ... but other 'content' fields aren't.
    $assert_session->linkNotExists('Promoted to front page');
    $assert_session->linkNotExists('Sticky at top of lists');
  }

  /**
   * Verify that the UI can restrict layouts in Layout Builder settings tray.
   */
  public function testLayoutRestriction() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $field_ui_prefix = 'admin/structure/types/manage/bundle_with_section_field';
    $this->drupalGet("$field_ui_prefix/display/default");
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals("$field_ui_prefix/display-layout/default");
    // Baseline: 'One column' & 'Two column' layouts are available.
    $assert_session->elementExists('css', '.field--name-body');
    $this->clickLink('Add Section');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkExists('One column');
    $assert_session->linkExists('Two column');

    // Allow only 'Two column' layout.
    $this->drupalGet("$field_ui_prefix/display/default");
    $element = $page->find('xpath', '//*[@id="edit-layout-builder-restrictions-allowed-layouts"]/summary');
    $element->click();
    $element = $page->find('xpath', '//*[@id="edit-layout-builder-restrictions-allowed-layouts-layout-restriction-all"]');
    $assert_session->checkboxChecked('edit-layout-builder-restrictions-allowed-layouts-layout-restriction-all');
    $assert_session->checkboxNotChecked('edit-layout-builder-restrictions-allowed-layouts-layout-restriction-restricted');
    $element = $page->find('xpath', '//*[@id="edit-layout-builder-restrictions-allowed-layouts-layout-restriction-restricted"]');
    $element->click();
    $element = $page->find('xpath', '//*[@id="edit-layout-builder-restrictions-allowed-layouts-layouts-layout-twocol"]');
    $element->click();
    $page->pressButton('Save');

    // Verify 'Two column' is allowed, 'One column' restricted.
    $this->drupalGet("$field_ui_prefix/display/default");
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');
    $assert_session->addressEquals("$field_ui_prefix/display-layout/default");
    $this->clickLink('Add Section');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->linkNotExists('One column');
    $assert_session->linkExists('Two column');
  }

}
