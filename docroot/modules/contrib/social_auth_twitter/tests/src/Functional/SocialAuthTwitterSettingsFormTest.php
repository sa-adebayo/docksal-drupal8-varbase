<?php

namespace Drupal\Tests\social_auth_twitter\Functional;

use Drupal\social_api\SocialApiSettingsFormBaseTest;

/**
 * Test Social Auth Twitter settings form.
 *
 * @group social_auth
 *
 * @ingroup social_auth_twitter
 */
class SocialAuthTwitterSettingsFormTest extends SocialApiSettingsFormBaseTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['social_auth_twitter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->module = 'social_auth_twitter';
    $this->socialNetwork = 'twitter';
    $this->moduleType = 'social-auth';

    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  public function testIsAvailableInIntegrationList() {
    $this->fields = ['consumer_key', 'consumer_secret'];

    parent::testIsAvailableInIntegrationList();
  }

  /**
   * {@inheritdoc}
   */
  public function testSettingsFormSubmission() {
    $this->edit = [
      'consumer_key' => $this->randomString(10),
      'consumer_secret' => $this->randomString(10),
    ];

    parent::testSettingsFormSubmission();
  }

}
