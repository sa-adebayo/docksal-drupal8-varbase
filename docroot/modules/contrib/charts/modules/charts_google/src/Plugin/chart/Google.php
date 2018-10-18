<?php

namespace Drupal\charts_google\Plugin\chart;

use Drupal\charts\Plugin\chart\AbstractChart;
use Drupal\charts_google\Settings\Google\GoogleOptions;
use Drupal\charts_google\Settings\Google\ChartType;
use Drupal\charts_google\Settings\Google\ChartArea;
use Drupal\charts_google\Settings\Google\ChartAxes;

/**
 * Define a concrete class for a Chart.
 *
 * @Chart(
 *   id = "google",
 *   name = @Translation("Google")
 * )
 */
class Google extends AbstractChart {

  /**
   * Creates a JSON Object formatted for Google Charts JavaScript to use.
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

    $categoriesCount = count($categories);
    $seriesCount = count($seriesData);

    // Creates an array of the length of the series data.
    $dataCount = [];
    for ($x = 0; $x < $seriesCount; $x++) {
      $dataCountTemp = count($seriesData[$x]['data']);
      array_push($dataCount, $dataCountTemp);
    }

    /**
     * For pie and donut chart types, depending on the number of data fields,
     * the charts will either use data fields or label fields for the
     * categories. If only one data field is selected, then the label field
     * will serve as the categories. If multiple data fields are selected,
     * they will become the categories.
     * */
    if ($options['type'] == 'pie' || $options['type'] == 'donut') {
      if ($seriesCount > 1) {
        $dataTable = [];
        for ($j = 0; $j < $seriesCount; $j++) {
          $rowDataTable = [];
          $rowDataTabletemp = array_sum($seriesData[$j]['data']);
          array_push($rowDataTable, $rowDataTabletemp);
          array_unshift($rowDataTable, $seriesData[$j]['name']);
          array_push($dataTable, $rowDataTable);
        }
        $dataTableHeader = ['label', 'value'];
        array_unshift($dataTable, $dataTableHeader);
      }
      else {
        $dataTable = [];
        for ($j = 0; $j < $categoriesCount; $j++) {
          $rowDataTable = [];
          for ($i = 0; $i < $seriesCount; $i++) {
            $rowDataTabletemp = $seriesData[$i]['data'][$j];
            array_push($rowDataTable, $rowDataTabletemp);
          }
          array_unshift($rowDataTable, $categories[$j]);
          array_push($dataTable, $rowDataTable);
        }
        $dataTableHeader = [];
        for ($r = 0; $r < $seriesCount; $r++) {
          array_push($dataTableHeader, $seriesData[$r]['name']);
        }
        array_unshift($dataTableHeader, 'label');
        array_unshift($dataTable, $dataTableHeader);
      }
    }
    else {
      $dataTable = [];
      for ($j = 0; $j < $categoriesCount; $j++) {
        $rowDataTable = [];
        for ($i = 0; $i < $seriesCount; $i++) {
          $rowDataTabletemp = $seriesData[$i]['data'][$j];
          array_push($rowDataTable, $rowDataTabletemp);
        }
        array_unshift($rowDataTable, $categories[$j]);
        array_push($dataTable, $rowDataTable);
      }
      $dataTableHeader = [];
      for ($r = 0; $r < $seriesCount; $r++) {
        array_push($dataTableHeader, $seriesData[$r]['name']);
      }
      array_unshift($dataTableHeader, 'label');
      array_unshift($dataTable, $dataTableHeader);
    }

    $googleOptions = $this->createChartsOptions($options, $seriesData, $attachmentDisplayOptions);
    $googleChartType = $this->createChartType($options);

    // Override Google classes. These will only override what is in
    // charts_google/src/Settings/Google/GoogleOptions.php
    // but you can use more of the Google Charts API, as you are not constrained
    // to what is in this class. See:
    // charts_google/src/Plugin/override/GoogleOverrides.php
    foreach($customOptions as $option => $key) {
      $setter = 'set' . ucfirst($option);
      if (method_exists($googleOptions, $setter)) {
        $googleOptions->$setter($customOptions[$option]);
      }
    }

    $variables['chart_type'] = 'google';
    $variables['attributes']['class'][0] = 'charts-google';
    $variables['attributes']['id'][0] = $chartId;
    $variables['content_attributes']['data-chart'][] = json_encode($dataTable);
    $variables['attributes']['google-options'][1] = json_encode($googleOptions);
    $variables['attributes']['google-chart-type'][2] = json_encode($googleChartType);
  }

  /**
   * Create charts options.
   *
   * @param array $options
   *   Options.
   * @param array $seriesData
   *   Series data.
   * @param array $attachmentDisplayOptions
   *   Attachment Display Options.
   * @param array $customOptions
   *   Overrides.
   *
   * @return \Drupal\charts_google\Settings\Google\GoogleOptions
   *   GoogleOptions object with chart options or settings to be used by google
   *   visualization framework.
   */
  private function createChartsOptions($options = [], $seriesData = [], $attachmentDisplayOptions = []) {
    $noAttachmentDisplays = count($attachmentDisplayOptions) === 0;

    $chartSelected = [];
    $seriesTypes = [];

    $firstVaxis = new ChartAxes();

    if (isset($options['yaxis_min'])) {
      $firstVaxis->setMinValue($options['yaxis_min']);
    }

    if (isset($options['yaxis_view_min'])) {
      $firstVaxis->setViewWindowValue('min', $options['yaxis_view_min']);
    }

    if (isset($options['yaxis_view_max'])) {
      $firstVaxis->setViewWindowValue('max', $options['yaxis_view_max']);
    }

    if (isset($options['yaxis_max'])) {
      $firstVaxis->setMaxValue($options['yaxis_max']);
    }

    // A format string for numeric or date axis labels.
    if (isset($options['yaxis_title'])) {
      $firstVaxis->setTitle($options['yaxis_title']);
    }

    if (isset($options['yaxis_title_color'])) {
      $firstVaxis->setTitleTextStyleValue('color', $options['yaxis_title_color']);
    }

    if (isset($options['yaxis_title_font'])) {
      $firstVaxis->setTitleTextStyleValue('fontName', $options['yaxis_title_font']);
    }

    if (isset($options['yaxis_title_size'])) {
      $firstVaxis->setTitleTextStyleValue('fontSize', $options['yaxis_title_size']);
    }

    if (isset($options['yaxis_title_bold'])) {
      $firstVaxis->setTitleTextStyleValue('bold', $options['yaxis_title_bold']);
    }

    if (isset($options['yaxis_title_italic'])) {
      $firstVaxis->setTitleTextStyleValue('italic', $options['yaxis_title_italic']);
    }

    // Axis title position.
    if (isset($options['yaxis_title_position'])) {
      $firstVaxis->setTextPosition($options['yaxis_title_position']);
    }

    if (isset($options['yaxis_baseline'])) {
      $firstVaxis->setBaseline($options['yaxis_baseline']);
    }

    if (isset($options['yaxis_baseline_color'])) {
      $firstVaxis->setBaselineColor($options['yaxis_baseline_color']);
    }

    if (isset($options['yaxis_direction'])) {
      $firstVaxis->setDirection($options['yaxis_direction']);
    }

    // A format string for numeric or date axis labels.
    if (isset($options['yaxis_format'])) {
      $firstVaxis->setFormat($options['yaxis_format']);
    }

    if (isset($options['yaxis_view_window_mode'])) {
      $firstVaxis->setViewWindowMode($options['yaxis_view_window_mode']);
    }

    $firstHaxis = new ChartAxes();

    if (isset($options['xaxis_min'])) {
      $firstHaxis->setMinValue($options['xaxis_min']);
    }

    if (isset($options['xaxis_view_min'])) {
      $firstHaxis->setViewWindowValue('min', $options['xaxis_view_min']);
    }

    if (isset($options['xaxis_view_max'])) {
      $firstHaxis->setViewWindowValue('max', $options['xaxis_view_max']);
    }

    if (isset($options['xaxis_max'])) {
      $firstHaxis->setMaxValue($options['xaxis_max']);
    }

    // A format string for numeric or date axis labels.
    if (isset($options['xaxis_title'])) {
      $firstHaxis->setTitle($options['xaxis_title']);
    }

    if (isset($options['xaxis_title_color'])) {
      $firstHaxis->setTitleTextStyleValue('color', $options['xaxis_title_color']);
    }

    if (isset($options['xaxis_title_font'])) {
      $firstHaxis->setTitleTextStyleValue('fontName', $options['xaxis_title_font']);
    }

    if (isset($options['xaxis_title_size'])) {
      $firstHaxis->setTitleTextStyleValue('fontSize', $options['xaxis_title_size']);
    }

    if (isset($options['xaxis_title_bold'])) {
      $firstHaxis->setTitleTextStyleValue('bold', $options['xaxis_title_bold']);
    }

    if (isset($options['xaxis_title_italic'])) {
      $firstHaxis->setTitleTextStyleValue('italic', $options['xaxis_title_italic']);
    }

    // Axis title position.
    if (isset($options['xaxis_title_position'])) {
      $firstHaxis->setTextPosition($options['xaxis_title_position']);
    }

    if (isset($options['xaxis_baseline'])) {
      $firstHaxis->setBaseline($options['xaxis_baseline']);
    }

    if (isset($options['xaxis_baseline_color'])) {
      $firstHaxis->setBaselineColor($options['xaxis_baseline_color']);
    }

    if (isset($options['xaxis_direction'])) {
      $firstHaxis->setDirection($options['xaxis_direction']);
    }

    // A format string for numeric or date axis labels.
    if (isset($options['xaxis_format'])) {
      $firstHaxis->setFormat($options['xaxis_format']);
    }

    if (isset($options['xaxis_view_window_mode'])) {
      $firstHaxis->setViewWindowMode($options['xaxis_view_window_mode']);
    }

    $vAxes = [];
    $hAxes = [];

    array_push($vAxes, $firstVaxis);
    array_push($hAxes, $firstHaxis);

    // Sets secondary axis from the first attachment only.
    if (!$noAttachmentDisplays && $attachmentDisplayOptions[0]['inherit_yaxis'] == 0) {
      $secondVaxis = new ChartAxes();
      $secondVaxis->setTitle($attachmentDisplayOptions[0]['style']['options']['yaxis_title']);
      array_push($vAxes, $secondVaxis);
    }

    array_push($chartSelected, $options['type']);

    // @todo: make sure this works for more than one attachment.
    for ($i = 0; $i < count($attachmentDisplayOptions); $i++) {
      $attachmentChartType = $attachmentDisplayOptions[$i]['style']['options']['type'];

      if ($attachmentChartType == 'column') {
        $attachmentChartType = 'bars';
      }

      if ($attachmentDisplayOptions[$i]['inherit_yaxis'] == 0 && $i == 0) {
        $seriesTypes[$i + 1] = [
          'type' => $attachmentChartType,
          'targetAxisIndex' => 1,
        ];
      }
      else {
        $seriesTypes[$i + 1] = ['type' => $attachmentChartType];
      }

      array_push($chartSelected, $attachmentChartType);
    }

    $chartSelected = array_unique($chartSelected);
    $googleOptions = new GoogleOptions();

    if (count($chartSelected) > 1) {
      $parentChartType = $options['type'];

      if ($parentChartType == 'column') {
        $parentChartType = 'bars';
      }
      if ($parentChartType == 'spline') {
        $parentChartType = 'line';
      }

      $googleOptions->seriesType = $parentChartType;
      $googleOptions->series = $seriesTypes;
    }

    $googleOptions->setTitle($options['title']);

    if (isset($options['subtitle'])) {
      $googleOptions->setSubTitle($options['subtitle']);
    }

    $googleOptions->setVerticalAxes($vAxes);
    $googleOptions->setHorizontalAxes($hAxes);

    if (in_array('donut', $chartSelected)) {
      $googleOptions->pieHole = '0.25';
    }

    $chartArea = new ChartArea();

    // Chart Area width.
    if (isset($options['chart_area']['width'])) {
      $chartArea->setWidth($options['chart_area']['width']);
    }

    // Chart Area height.
    if (isset($options['chart_area']['height'])) {
      $chartArea->setHeight($options['chart_area']['height']);
    }

    // Chart Area padding top.
    if (isset($options['chart_area']['top'])) {
      $chartArea->setPaddingTop($options['chart_area']['top']);
    }

    // Chart Area padding left.
    if (isset($options['chart_area']['left'])) {
      $chartArea->setPaddingLeft($options['chart_area']['left']);
    }

    $seriesCount = count($seriesData);
    $categoriesCount = count($seriesData[0]['data']);

    $seriesColors = [];
    if ($options['type'] == 'pie' || $options['type'] == 'donut') {
      if ($seriesCount > 1) {
        for ($i = 0; $i < $seriesCount; $i++) {
          $seriesColor = $seriesData[$i]['color'];
          array_push($seriesColors, $seriesColor);
        }
      }
      else {
        for ($i = 0; $i < $categoriesCount; $i++) {
          // Use default colors if only one series.
          $seriesColor = $options['colors'][$i];
          array_push($seriesColors, $seriesColor);
        }
      }
    }
    else {
      for ($i = 0; $i < $seriesCount; $i++) {
        $seriesColor = $seriesData[$i]['color'];
        array_push($seriesColors, $seriesColor);
      }
    }
    $googleOptions->setColors($seriesColors);

    // Width of the chart, in pixels.
    if (isset($options['width'])) {
      $googleOptions->setWidth($options['width']);
    }

    // Height of the chart, in pixels.
    if (isset($options['height'])) {
      $googleOptions->setHeight($options['height']);
    }

    // Determines if chart is three-dimensional.
    if (isset($options['three_dimensional'])) {
      $googleOptions->setThreeDimensional($options['three_dimensional']);
    }

    // Determines if chart is stacked.
    if (!empty($options['grouping'])) {
      $options['grouping'] = TRUE;
      $googleOptions->setStacking($options['grouping']);
    }

    // 'legend' can be a string (for position) or an array with legend
    // properties: [position: 'top', textStyle: [color: 'blue', fontSize: 16]].
    if (isset($options['legend'])) {
      $googleOptions->setLegend($options['legend']);
    }

    // Sets the markers.
    if (isset($options['data_markers'])) {
      if (($options['type'] == 'line') || ($options['type'] == 'spline')) {
        if ($options['data_markers'] == 'FALSE') {
          $googleOptions->setPointSize(0);
        }
        else {
          $googleOptions->setPointSize(5);
        }
      }
    }

    // Set legend position.
    if (isset($options['legend_position'])) {
      if (empty($options['legend_position'])) {
        $options['legend_position'] = 'none';
        $googleOptions->setLegend($options['legend_position']);
      }
      else {
        $googleOptions->setLegend($options['legend_position']);
      }
    }

    // Where to place the chart title, compared to the chart area.
    if (isset($options['title_position'])) {
      $googleOptions->setTitlePosition($options['title_position']);
    }

    // Where to place the axis titles, compared to the chart area.
    if (isset($options['axis_titles_position'])) {
      $googleOptions->setAxisTitlesPosition($options['axis_titles_position']);
    }

    // Set gauge specific options.
    if ($options['type'] == 'gauge') {
      $fields = [
        'green_to',
        'green_from',
        'red_to',
        'red_from',
        'yellow_to',
        'yellow_from',
        'max',
        'min',
      ];
      foreach ($fields as $field) {
        $options[$field] = isset($options[$field]) ? $options[$field] : NULL;
      }

      $googleOptions->setGreenTo($options['green_to']);
      $googleOptions->setGreenFrom($options['green_from']);
      $googleOptions->setRedTo($options['red_to']);
      $googleOptions->setRedFrom($options['red_from']);
      $googleOptions->setYellowTo($options['yellow_to']);
      $googleOptions->setYellowFrom($options['yellow_from']);
      $googleOptions->setMax($options['max']);
      $googleOptions->setMin($options['min']);
    }

    // Set curveType options if spline.
    if ($options['type'] == 'spline') {
      $googleOptions->setCurveType('function');
    }

    return $googleOptions;
  }

  /**
   * Create Chart Type.
   *
   * @param array $options
   *   Options.
   * @param array $customOptions
   *   Overrides.
   *
   * @return \Drupal\charts_google\Settings\Google\ChartType
   *   ChartType.
   */
  private function createChartType($options = []) {
    $googleChartType = new ChartType();
    $googleChartType->setChartType($options['type']);

    return $googleChartType;
  }

}
