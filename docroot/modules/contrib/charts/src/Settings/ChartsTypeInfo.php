<?php


namespace Drupal\charts\Settings;

use Drupal\charts\Theme\ChartsInterface;


class ChartsTypeInfo {

  /**
   * Get charts types info.
   *
   * @return array
   *   Chart types.
   */
  public function chartsChartsTypeInfo() {
    $chart_types['area'] = [
      'label' => t('Area'),
      'axis' => ChartsInterface::CHARTS_DUAL_AXIS,
      'stacking' => TRUE,
    ];
    $chart_types['bar'] = [
      'label' => t('Bar'),
      'axis' => ChartsInterface::CHARTS_DUAL_AXIS,
      'axis_inverted' => TRUE,
      'stacking' => TRUE,
    ];
    $chart_types['column'] = [
      'label' => t('Column'),
      'axis' => ChartsInterface::CHARTS_DUAL_AXIS,
      'stacking' => TRUE,
    ];
    $chart_types['donut'] = [
      'label' => t('Donut'),
      'axis' => ChartsInterface::CHARTS_SINGLE_AXIS,
    ];
    $chart_types['gauge'] = [
      'label' => t('Gauge'),
      'axis' => ChartsInterface::CHARTS_SINGLE_AXIS,
      'stacking' => FALSE,
    ];
    $chart_types['line'] = [
      'label' => t('Line'),
      'axis' => ChartsInterface::CHARTS_DUAL_AXIS,
    ];
    $chart_types['pie'] = [
      'label' => t('Pie'),
      'axis' => ChartsInterface::CHARTS_SINGLE_AXIS,
    ];
    $chart_types['scatter'] = [
      'label' => t('Scatter'),
      'axis' => ChartsInterface::CHARTS_DUAL_AXIS,
    ];
    $chart_types['spline'] = [
      'label' => t('Spline'),
      'axis' => ChartsInterface::CHARTS_DUAL_AXIS,
    ];

    return $chart_types;
  }

  public function getChartTypes() {
    $chart_types = $this->chartsChartsTypeInfo();
    $type_options = [];
    foreach ($chart_types as $chart_type => $chart_type_info) {
      $type_options[$chart_type] = $chart_type_info['label'];
    }

    // Set data attributes to identify special properties of different types.
    foreach ($chart_types as $chart_type => $chart_type_info) {
      if (isset($chart_type_info['axis_inverted']) && $chart_type_info['axis_inverted']) {
        $form['type'][$chart_type]['#attributes']['data-axis-inverted'] = TRUE;
      }
      if ($chart_type_info['axis'] === ChartsInterface::CHARTS_SINGLE_AXIS) {
        $form['type'][$chart_type]['#attributes']['data-axis-single'] = TRUE;
      }
    }

    return $type_options;
  }

  /**
   * Retrieve a specific chart type.
   *
   * @param string $chart_type
   *   The type of chart selected for display.
   *
   * @return mixed
   *   If not false, returns an array of values from charts_charts_type_info.
   */
  public function getChartType($chart_type) {
    $chart_types = new ChartsTypeInfo();
    $types = $chart_types->chartsChartsTypeInfo();
    return ($types[$chart_type]) ? $types[$chart_type] : FALSE;
  }


}
