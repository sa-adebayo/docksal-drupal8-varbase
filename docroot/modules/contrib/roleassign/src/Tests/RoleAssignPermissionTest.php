<?php

/**
 * @file
 * Contains \Drupal\roleassign\Tests\RoleAssignPermissionTest.
 */

namespace Drupal\roleassign\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;

/**
 * Tests that users can (un)assign roles based on the RoleAssign settings.
 *
 * @group roleassign
 */
class RoleAssignPermissionTest extends WebTestBase {

  /**
   * The user object to test (un)assigning roles to.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testaccount;

  /**
   * The user object that has restricted access to assign roles but not
   * administer permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $restricted_user;

  /**
   * The user object that has access to administer users & permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin_user;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['roleassign'];

  protected function setUp() {
    parent::setUp();

    // Add Editor role
    $this->drupalCreateRole(array(), 'editor', 'Editor');
    // Add Webmaster role
    $this->drupalCreateRole(array('administer users', 'assign roles'), 'webmaster', 'Webmaster');
    // Add 'protected' SiteAdmin role
    $this->drupalCreateRole(array('administer users', 'administer permissions'), 'siteadmin', 'SiteAdmin');

    // Configure RoleAssign module - only editor & webmaster roles are
    // assignable by restricted users (i.e. webmasters)
    $this->config('roleassign.settings')
      ->set('roleassign_roles', array('editor' => 'editor', 'webmaster' => 'webmaster'))
      ->save();

    // Create a testaccount that we will be trying to assign roles.
    $this->testaccount = $this->drupalCreateUser();

    // Create a test restricted user without "administer permissions" permission
    // but with "assign roles" permission provided by RoleAssign.
    $this->restricted_user = $this->drupalCreateUser(array('administer users', 'assign roles'));

    // Create a test admin user with "administer users " &
    // "administer permissions" permissions, where RoleAssign will have no
    // effect on.
    $this->admin_user = $this->drupalCreateUser(array('administer users', 'administer permissions'));
  }

  /**
   * Tests that RoleAssign settings are set up correctly.
   */
  function testRoleAssignSettings()  {
    $assignable_roles = array_filter(\Drupal::config('roleassign.settings')->get('roleassign_roles'));
    $this->assertIdentical(array('editor' => 'editor', 'webmaster' => 'webmaster'), $assignable_roles);
  }

  /**
   * Tests that a restricted user can only (un)assign configured roles.
   */
  function testRoleAssignRestrictedUser()  {
    // Login as restricted user to test RoleAssign
    $this->drupalLogin($this->restricted_user);

    // Load account edit page
    $this->drupalGet('user/' . $this->testaccount->id() . '/edit');

    // Check that only assignable roles are displayed
    $this->assertText(t('Assignable roles'));
    $this->assertNoFieldChecked('edit-roles-editor');
    $this->assertNoFieldChecked('edit-roles-webmaster');
    $this->assertNoField('edit-roles-siteadmin');

    // Assign the role "editor" to the account.
    $this->drupalPostForm('user/' . $this->testaccount->id() . '/edit', array("roles[editor]" => "editor"), t('Save'));
    $this->assertText(t('The changes have been saved.'));
    $this->assertFieldChecked('edit-roles-editor', 'Role editor is assigned.');
    $this->assertNoFieldChecked('edit-roles-webmaster');
    $this->assertNoField('edit-roles-siteadmin');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, 'editor');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, RoleInterface::AUTHENTICATED_ID);

    // Remove the role "editor" from the account.
    $this->drupalPostForm('user/' . $this->testaccount->id() . '/edit', array("roles[editor]" => FALSE), t('Save'));
    $this->assertText(t('The changes have been saved.'));
    $this->assertNoFieldChecked('edit-roles-editor', 'Role editor is removed.');
    $this->assertNoFieldChecked('edit-roles-webmaster');
    $this->assertNoField('edit-roles-siteadmin');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, 'editor', FALSE);
    $this->userLoadAndCheckRoleAssigned($this->testaccount, RoleInterface::AUTHENTICATED_ID);

    // Try to assign a restricted role programmatically to a new user.
    $values = array(
      'name' => $this->randomString(),
      'roles' => array('editor', 'siteadmin'),
    );
    $code_account = User::create($values);
    $code_account->save();

    // Check that user only gets editor role, but not siteadmin role.
    $this->assertTrue($code_account->hasRole('editor'));
    $this->assertFalse($code_account->hasRole('siteadmin'));
  }

  /**
   * Tests that an admin user with "administer permissions" can add all roles.
   */
  function testRoleAssignAdminUser()  {
    // Login as admin user
    $this->drupalLogin($this->admin_user);

    // Load account edit page
    $this->drupalGet('user/' . $this->testaccount->id() . '/edit');

    // Check that Drupal default roles checkboxes are shown.
    $this->assertText(t('Roles'));
    $this->assertNoFieldChecked('edit-roles-editor');
    $this->assertNoFieldChecked('edit-roles-webmaster');
    $this->assertNoFieldChecked('edit-roles-siteadmin');

    // Assign the role "SiteAdmin" to the account.
    $this->drupalPostForm('user/' . $this->testaccount->id() . '/edit', array("roles[siteadmin]" => "siteadmin"), t('Save'));
    $this->assertText(t('The changes have been saved.'));
    $this->assertFieldChecked('edit-roles-siteadmin', 'Role siteadmin is assigned.');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, 'siteadmin');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, RoleInterface::AUTHENTICATED_ID);

    // Now log in as restricted user again
    $this->drupalLogin($this->restricted_user);

    // Assign the role "editor" to the account, and test that the assigned
    // "siteadmin" role doesn't get lost.
    $this->drupalPostForm('user/' . $this->testaccount->id() . '/edit', array("roles[editor]" => "editor"), t('Save'));
    $this->assertText(t('The changes have been saved.'));
    $this->assertFieldChecked('edit-roles-editor', 'Role editor is assigned.');
    $this->assertNoField('edit-roles-siteadmin');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, 'editor');
    $this->userLoadAndCheckRoleAssigned($this->testaccount, RoleInterface::AUTHENTICATED_ID);
    $this->userLoadAndCheckRoleAssigned($this->testaccount, 'siteadmin');
  }

  /**
   * Check role on user object.
   *
   * @param object $account
   *   The user account to check.
   * @param string $rid
   *   The role ID to search for.
   * @param bool $is_assigned
   *   (optional) Whether to assert that $rid exists (TRUE) or not (FALSE).
   *   Defaults to TRUE.
   */
  private function userLoadAndCheckRoleAssigned($account, $rid, $is_assigned = TRUE) {
    $user_storage = $this->container->get('entity.manager')->getStorage('user');
    $user_storage->resetCache(array($account->id()));
    $account = $user_storage->load($account->id());
    if ($is_assigned) {
      $this->assertFalse(array_search($rid, $account->getRoles()) === FALSE, 'The role is present in the user object.');
    }
    else {
      $this->assertTrue(array_search($rid, $account->getRoles()) === FALSE, 'The role is not present in the user object.');
    }
  }

}
