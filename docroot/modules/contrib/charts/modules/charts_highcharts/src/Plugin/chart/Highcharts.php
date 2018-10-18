<?php

namespace Drupal\charts_highcharts\Plugin\chart;

use Drupal\charts\Plugin\chart\AbstractChart;
use Drupal\charts_highcharts\Settings\Highcharts\Chart;
use Drupal\charts_highcharts\Settings\Highcharts\ChartTitle;
use Drupal\charts_highcharts\Settings\Highcharts\ExportingOptions;
use Drupal\charts_highcharts\Settings\Highcharts\Marker;
use Drupal\charts_highcharts\Settings\Highcharts\Pane;
use Drupal\charts_highcharts\Settings\Highcharts\PlotBands;
use Drupal\charts_highcharts\Settings\Highcharts\PlotOptionsStacking;
use Drupal\charts_highcharts\Settings\Highcharts\ThreeDimensionalOptions;
use Drupal\charts_highcharts\Settings\Highcharts\Xaxis;
use Drupal\charts_highcharts\Settings\Highcharts\XaxisTitle;
use Drupal\charts_highcharts\Settings\Highcharts\ChartLabel;
use Drupal\charts_highcharts\Settings\Highcharts\YaxisLabel;
use Drupal\charts_highcharts\Settings\Highcharts\Yaxis;
use Drupal\charts_highcharts\Settings\Highcharts\YaxisTitle;
use Drupal\charts_highcharts\Settings\Highcharts\PlotOptions;
use Drupal\charts_highcharts\Settings\Highcharts\PlotOptionsSeries;
use Drupal\charts_highcharts\Settings\Highcharts\PlotOptionsSeriesDataLabels;
use Drupal\charts_highcharts\Settings\Highcharts\Tooltip;
use Drupal\charts_highcharts\Settings\Highcharts\ChartCredits;
use Drupal\charts_highcharts\Settings\Highcharts\ChartLegend;
use Drupal\charts_highcharts\Settings\Highcharts\HighchartsOptions;

/**
 * Defines a concrete class for a Highcharts.
 *
 * @Chart(
 *   id = "highcharts",
 *   name = @Translation("Highcharts")
 * )
 */
class Highcharts extends AbstractChart {

  /**
   * @param $options
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\ChartLegend
   */
  private function buildChartLegend($options) {
    $chartLegend = new ChartLegend();
    if (empty($options['legend_position'])) {
      $chartLegend->setEnabled(FALSE);
    }
    elseif (in_array($options['legend_position'], ['left', 'right'])) {
      $chartLegend->setAlign($options['legend_position']);
      $chartLegend->setVerticalAlign('top');
      $chartLegend->setY(80);
      if ($options['legend_position'] == 'left') {
        $chartLegend->setX(0);
      }
    }
    elseif ($options['legend_position'] == 'bottom') {
      $chartLegend->setVerticalAlign($options['legend_position']);
      $chartLegend->setAlign('center');
      $chartLegend->setX(0);
      $chartLegend->setY(0);
      $chartLegend->setFloating(FALSE);
    }
    else {
      $chartLegend->setVerticalAlign($options['legend_position']);
      $chartLegend->setAlign('center');
      $chartLegend->setX(0);
      $chartLegend->setY(0);
      $chartLegend->setFloating(FALSE);
    }

    return $chartLegend;
  }

  /**
   * @param $options
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\PlotOptions
   */
  private function buildPlotOptions($options) {
    $plotOptions = new PlotOptions();
    $plotOptionsStacking = new PlotOptionsStacking();
    $plotOptionsSeries = new PlotOptionsSeries();
    $plotOptionsSeriesDataLabels = new PlotOptionsSeriesDataLabels();
    $plotOptionsSeriesDataLabels->setEnabled($options['data_labels']);
    // Set plot options if stacked chart.
    if (!empty($options['grouping'])) {
      $plotOptions->setPlotOptions($options['type'], $plotOptionsStacking);
      $plotOptionsStacking->setDataLabels($plotOptionsSeriesDataLabels);
      // Set markers if grouped
      if (($options['type'] == 'line') || ($options['type'] == 'spline')) {
        $marker = new Marker();
        if ($options['data_markers'] == 'FALSE') {
          $marker->setEnabled(FALSE);
        }
        else {
          $marker->setEnabled(TRUE);
        }
        $plotOptionsStacking->setMarker($marker);
      }
      if ($options['type'] == 'gauge') {
        $plotOptionsStacking->setStacking('');
      }
    }
    else {
      $plotOptions->setPlotOptions($options['type'], $plotOptionsSeries);
      $plotOptionsSeries->setDataLabels($plotOptionsSeriesDataLabels);
      // Set markers if not grouped
      if (($options['type'] == 'line') || ($options['type'] == 'spline')) {
        $marker = new Marker();
        if ($options['data_markers'] == 'FALSE') {
          $marker->setEnabled(FALSE);
        }
        else {
          $marker->setEnabled(TRUE);
        }
        $plotOptionsSeries->setMarker($marker);
      }
    }
    if (isset($options['data_labels'])) {
      $plotOptionsSeriesDataLabels->setEnabled($options['data_labels']);
    }
    // Determines if chart is three-dimensional.
    if (!empty($options['three_dimensional'])) {
      $plotOptionsSeries->setDepth(45);
    }

    return $plotOptions;
  }

  /**
   * @param $options
   * @param $seriesData
   * @param $categories
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\Xaxis
   */
  private function buildXaxis($options, $seriesData, $categories) {
    $chartXaxis = new Xaxis();
    $chartLabels = new ChartLabel();
    // Set x-axis label rotation.
    if (isset($options['xaxis_labels_rotation'])) {
      $chartLabels->setRotation($options['xaxis_labels_rotation']);
    }
    $xAxisTitle = new XaxisTitle();
    if (isset($options['xaxis_title'])) {
      $xAxisTitle->setText($options['xaxis_title']);
    }
    // If donut or pie and only one data point with multiple fields in use.
    if (($options['type'] == 'pie' || $options['type'] == 'donut') && (count($seriesData[0]['data']) == 1)) {
      unset($categories);
      $categories = [];
      for ($i = 0; $i < count($seriesData); $i++) {
        array_push($categories, $seriesData[$i]['name']);
      }
    }
    $chartXaxis->setCategories($categories);
    // Set x-axis title.
    $chartXaxis->setTitle($xAxisTitle);
    $chartXaxis->setLabels($chartLabels);

    return $chartXaxis;
  }

  /**
   * @param $options
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\YaxisLabel
   */
  private function buildYaxisLabels($options) {
    $yaxisLabels = new YaxisLabel();
    if (!empty($options['yaxis_suffix'])) {
      $yaxisLabels->setYaxisLabelSuffix($options['yaxis_suffix']);
    }
    if (!empty($options['yaxis_prefix'])) {
      $yaxisLabels->setYaxisLabelPrefix($options['yaxis_prefix']);
    }

    return $yaxisLabels;
  }

  /**
   * @param $attachmentDisplayOptions
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\Yaxis
   */
  private function buildSecondaryYaxis($attachmentDisplayOptions) {
    $chartYaxisSecondary = new Yaxis();
    $yAxisTitleSecondary = new YaxisTitle();
    $yAxisTitleSecondary->setText($attachmentDisplayOptions[0]['style']['options']['yaxis_title']);
    $chartYaxisSecondary->setTitle($yAxisTitleSecondary);
    $yaxisLabelsSecondary = new YaxisLabel();
    if (!empty($attachmentDisplayOptions[0]['style']['options']['yaxis_suffix'])) {
      $yaxisLabelsSecondary->setYaxisLabelSuffix($attachmentDisplayOptions[0]['style']['options']['yaxis_suffix']);
    }
    if (!empty($attachmentDisplayOptions[0]['style']['options']['yaxis_prefix'])) {
      $yaxisLabelsSecondary->setYaxisLabelPrefix($attachmentDisplayOptions[0]['style']['options']['yaxis_prefix']);
    }
    $chartYaxisSecondary->setLabels($yaxisLabelsSecondary);
    $chartYaxisSecondary->opposite = 'true';
    if (!empty($attachmentDisplayOptions[0]['style']['options']['yaxis_min'])) {
      $chartYaxisSecondary->setMin($attachmentDisplayOptions[0]['style']['options']['yaxis_min']);
    }
    if (!empty($attachmentDisplayOptions[0]['style']['options']['yaxis_max'])) {
      $chartYaxisSecondary->setMax($attachmentDisplayOptions[0]['style']['options']['yaxis_max']);
    }

    return $chartYaxisSecondary;
  }

  /**
   * @param $title
   * @param $yaxisLabels
   * @param $options
   * @param $seriesData
   * @param $categories
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\Yaxis
   */
  private function buildYaxis($title, $yaxisLabels, $options, $seriesData, $categories) {
    $chartYaxis = new Yaxis();
    $yAxisTitle = new YaxisTitle();
    $yAxisTitle->setText($title);
    if (!empty($options['yaxis_min'])) {
      $chartYaxis->setMin($options['yaxis_min']);
    }
    if (!empty($options['yaxis_max'])) {
      $chartYaxis->setMax($options['yaxis_max']);
    }
    // Gauge options.
    if ($options['type'] == 'gauge') {
      // Gauge will not work if grouping is set.
      $options['grouping'] = [];
      $plotBandsGreen = new PlotBands();
      $plotBandsYellow = new PlotBands();
      $plotBandsRed = new PlotBands();
      $gaugeColors = [];
      $plotBandsRed->setFrom($options['red_from']);
      $plotBandsRed->setTo($options['red_to']);
      $plotBandsRed->setColor('red');
      array_push($gaugeColors, $plotBandsRed);
      $plotBandsYellow->setFrom($options['yellow_from']);
      $plotBandsYellow->setTo($options['yellow_to']);
      $plotBandsYellow->setColor('yellow');
      array_push($gaugeColors, $plotBandsYellow);
      $plotBandsGreen->setFrom($options['green_from']);
      $plotBandsGreen->setTo($options['green_to']);
      $plotBandsGreen->setColor('green');
      array_push($gaugeColors, $plotBandsGreen);
      $chartYaxis->setPlotBands($gaugeColors);
      $chartYaxis->setMin((int) $options['min']);
      $chartYaxis->setMax((int) $options['max']);
      if (count($seriesData) > 1 || count($categories) > 1) {
        \Drupal::service('messenger')->addMessage(t('The gauge 
          chart type does not work well with more than one value.'), 'warning');
      }
    }
    $chartYaxis->setLabels($yaxisLabels);
    $chartYaxis->setTitle($yAxisTitle);

    return $chartYaxis;
  }

  /**
   * @param $options
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\Tooltip
   */
  private function buildToolTip($options) {
    $chartTooltip = new Tooltip();
    if (isset($options['tooltips'])) {
      $chartTooltip->setEnabled($options['tooltips']);
    }

    return $chartTooltip;
  }

  /**
   * @param $options
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\ChartTitle
   */
  private function buildChartTitle($options) {
    $chartTitle = new ChartTitle();
    if (isset($options['title'])) {
      $chartTitle->setText($options['title']);
    }
    // Set title position.
    if (isset($options['title_position'])) {
      if ($options['title_position'] == 'in') {
        $chartTitle->setVerticalAlign('middle');
      }
      else {
        $chartTitle->setVerticalOffset(20);
      }
    }

    return $chartTitle;
  }

  /**
   * @param $options
   *
   * @return \Drupal\charts_highcharts\Settings\Highcharts\Chart
   */
  private function buildChartType($options) {
    $chart = new Chart();
    $chart->setType($options['type']);

    // Set chart width.
    if (isset($options['width'])) {
      $chart->setWidth($options['width']);
    }

    // Set chart height.
    if (isset($options['height'])) {
      $chart->setHeight($options['height']);
    }

    // Set background color.
    if (isset($options['background'])) {
      $chart->setBackgroundColor($options['background']);
    }

    // Set polar plotting.
    if (isset($options['polar'])) {
      $chart->setPolar($options['polar']);
    }

    if (!empty($options['three_dimensional'])) {
      $threeDimensionOptions = new ThreeDimensionalOptions();
      $chart->setOptions3D($threeDimensionOptions);
      $threeDimensionOptions->setAlpha(55);
      $threeDimensionOptions->setViewDistance(0);
      $threeDimensionOptions->setBeta(0);
      if ($options['type'] != 'pie') {
        $threeDimensionOptions->setAlpha(15);
        $threeDimensionOptions->setViewDistance(25);
      }
    }

    return $chart;
  }

  /**
   * Creates a JSON Object formatted for Highcharts JavaScript to use.
   *
   * @param mixed $options
   *   Options.
   * @param mixed $categories
   *   Categories.
   * @param mixed $seriesData
   *   Series data.
   * @param mixed $attachmentDisplayOptions
   *   Attachment display options.
   * @param mixed $variables
   *   Variables.
   * @param mixed $chartId
   *   Chart Id.
   * @param array $customOptions
   *   Overrides.
   *
   * @return array|void
   */
  public function buildVariables($options, $categories = [], $seriesData = [], $attachmentDisplayOptions = [], &$variables, $chartId, $customOptions = []) {
    $noAttachmentDisplays = count($attachmentDisplayOptions) === 0;

    // @todo: make this so that it happens if any display uses donut.
    if ($options['type'] == 'donut') {
      $options['type'] = 'pie';
      // Remove donut from seriesData.
      foreach ($seriesData as &$value) {
        $value = str_replace('donut', 'pie', $value);
      }
      // Add innerSize to differentiate between donut and pie.
      foreach ($seriesData as &$value) {
        if ($options['type'] == 'pie') {
          $innerSize['showInLegend'] = 'true';
          $innerSize['innerSize'] = '40%';
          $chartPlacement = array_search($value, $seriesData);
          $seriesData[$chartPlacement] = array_merge($innerSize, $seriesData[$chartPlacement]);
        }
      }
    }
    $yAxes = [];
    $xAxisOptions = $this->buildXaxis($options, $seriesData, $categories);
    $yaxisLabels = $this->buildYaxisLabels($options);
    $chartYaxis = $this->buildYaxis($options['yaxis_title'], $yaxisLabels, $options, $seriesData, $categories);
    array_push($yAxes, $chartYaxis);
    // Chart libraries tend to support only one secondary axis.
    if (!$noAttachmentDisplays && $attachmentDisplayOptions[0]['inherit_yaxis'] == 0) {

      $chartYaxisSecondary = $this->buildSecondaryYaxis($attachmentDisplayOptions);
      array_push($yAxes, $chartYaxisSecondary);
    }
    // Set plot options.
    $plotOptions = $this->buildPlotOptions($options);
    $chartCredits = new ChartCredits();
    // Set charts legend.
    $chartLegend = $this->buildChartLegend($options);
    // Set exporting options.
    $exporting = new ExportingOptions();
    $highchart = new HighchartsOptions();
    $highchart->setChart($this->buildChartType($options));
    $highchart->setTitle($this->buildChartTitle($options));
    $highchart->setAxisX($xAxisOptions);
    $highchart->setAxisY($yAxes);
    if ($options['type'] == 'gauge') {
      $pane = new Pane();
      $highchart->setPane($pane);
    }
    $highchart->setTooltip($this->buildToolTip($options));
    $highchart->setPlotOptions($plotOptions);
    $highchart->setCredits($chartCredits);
    $highchart->setLegend($chartLegend);
    // Usually just set the series with seriesData.
    if (($options['type'] == 'pie' || $options['type'] == 'donut') && (count($seriesData[0]['data']) == 1)) {
      for ($i = 0; $i < count($seriesData); $i++) {
        $seriesData[$i]['y'] = $seriesData[$i]['data'][0];
        unset($seriesData[$i]['data']);
      }
      $chartData = ['data' => $seriesData];
      $highchart->setSeries([$chartData]);
    }
    else {
      $highchart->setSeries($seriesData);
    }
    $highchart->setExporting($exporting);

    // Override Highchart classes. These will only override what is in
    // charts_highcharts/src/Settings/Highcharts/HighchartsOptions.php
    // but you can use more of the Highcharts API, as you are not constrained
    // to what is in this class. See:
    // charts_highcharts/src/Plugin/override/HighchartsOverrides.php
    foreach($customOptions as $option => $key) {
      $setter = 'set' . ucfirst($option);
      if (method_exists($highchart, $setter)) {
        $highchart->$setter($customOptions[$option]);
      }
    }

    $variables['chart_type'] = 'highcharts';
    $variables['content_attributes']['data-chart'][] = json_encode($highchart);
    $variables['attributes']['id'][0] = $chartId;
    $variables['attributes']['class'][] = 'charts-highchart';
  }

}
