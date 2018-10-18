<?php

namespace Drupal\Tests\testing_inherited\Functional;

use Drupal\block\BlockInterface;
use Drupal\block\Entity\Block;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests inherited profiles.
 *
 * @group profiles
 */
class InheritedProfileTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'testing_inherited';

  /**
   * Tests inherited installation profile.
   */
  public function testInheritedProfile() {
    // Check that the stable_login block exists.
    $this->assertInstanceOf(BlockInterface::class, Block::load('stable_login'));

    // Check that stable is the default theme.
    $this->assertEquals('stable', $this->config('system.theme')->get('default'));

    // Check that parent dependencies are installed.
    $this->assertTrue(\Drupal::moduleHandler()->moduleExists('config'));

    // Check that all themes were installed.
    $this->assertTrue(\Drupal::service('theme_handler')->themeExists('stable'));
  }

}
