<?php

namespace Drupal\charts_c3\Settings\CThree;

/**
 * Chart Axis.
 */
class ChartAxis implements \JsonSerializable {

  private $rotated = FALSE;

  private $x = ['type' => 'category'];

  /**
   * Get Rotated.
   *
   * @return mixed
   *   Rotated.
   */
  public function getRotated() {
    return $this->rotated;
  }

  /**
   * Set Rotated.
   *
   * @param mixed $rotated
   *   Rotated.
   */
  public function setRotated($rotated) {
    $this->rotated = $rotated;
  }

  /**
   * Json Serialize.
   *
   * @return array
   *   Json Serialize.
   */
  public function jsonSerialize() {
    $vars = get_object_vars($this);

    return $vars;
  }

}
