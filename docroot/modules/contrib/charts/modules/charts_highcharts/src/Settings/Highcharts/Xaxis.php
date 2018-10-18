<?php

namespace Drupal\charts_highcharts\Settings\Highcharts;

/**
 * X Axis.
 */
class Xaxis implements \JsonSerializable {

  private $categories = [];

  private $title;

  private $labels;

  /**
   * Get Categories.
   *
   * @return array
   *   Categories.
   */
  public function getCategories() {
    return $this->categories;
  }

  /**
   * Set Categories.
   *
   * @param mixed $categories
   *   Categories.
   */
  public function setCategories($categories) {
    $this->categories = $categories;
  }

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
   * @return mixed
   *   Labels.
   */
  public function getLabels() {
    return $this->labels;
  }

  /**
   * Set Labels.
   *
   * @param mixed $labels
   *   Labels.
   */
  public function setLabels($labels) {
    $this->labels = $labels;
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
