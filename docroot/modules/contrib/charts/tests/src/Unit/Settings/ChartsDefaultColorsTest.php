<?php


namespace Drupal\Tests\charts\Unit\Settings;

use Drupal\charts\Settings\ChartsDefaultColors;
use Drupal\Tests\UnitTestCase;

/**
 * @coversChartsDefaultColors \Drupal\charts\Settings
 * @group charts
 *
 * @internal
 */
class ChartsDefaultColorsTest extends UnitTestCase {

  /**
   * @var \Drupal\charts\Settings\ChartsDefaultColors
   */
  private $chartsDefaultColors;

  public function setUp() {
    parent::setUp();
    $this->chartsDefaultColors = new ChartsDefaultColors();
  }

  public function tearDown() {
    parent::tearDown();
    $this->chartsDefaultColors = NULL;
  }

  /**
   * @test
   */
  public function checkDefaultColors() {
    $this->assertCount(10, $this->chartsDefaultColors->getDefaultColors());
  }

  /**
   * @test
   *
   * @dataProvider colorProvider
   */
  public function setDefaultColors($color) {
    $this->chartsDefaultColors->setDefaultColors($color);
    $this->assertArrayEquals($color, $this->chartsDefaultColors->getDefaultColors());
  }

  public function colorProvider() {
    return [
      [
        ['#2f7ed8'],
      ],
    ];
  }

}
