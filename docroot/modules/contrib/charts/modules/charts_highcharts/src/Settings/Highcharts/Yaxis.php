<?php

namespace Drupal\charts_highcharts\Settings\Highcharts;

/**
 * Y Axis.
 */
class Yaxis implements \JsonSerializable {

  private $title;

  private $labels = '';

  private $plotBands = NULL;

  private $min;

  private $max;

  /**
   * Get Title.
   *
   * @return mixed
   *   Title.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Set Title.
   *
   * @param mixed $title
   *   Title.
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * Get Labels.
   *
   * @return string
   *   Labels.
   */
  public function getLabels() {
    return $this->labels;
  }

  /**
   * Set Labels.
   *
   * @param string $labels
   *   Labels.
   */
  public function setLabels($labels) {
    $this->labels = $labels;
  }

  /**
   * @return array
   */
  public function getPlotBands() {
    return $this->plotBands;
  }

  /**
   * @param array $plotBands
   */
  public function setPlotBands($plotBands) {
    $this->plotBands = $plotBands;
  }

  /**
   * @return int
   */
  public function getMin() {
    return $this->min;
  }

  /**
   * @param int $min
   */
  public function setMin($min) {
    $this->min = $min;
  }

  /**
   * @return int
   */
  public function getMax() {
    return $this->max;
  }

  /**
   * @param int $max
   */
  public function setMax($max) {
    $this->max = $max;
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
