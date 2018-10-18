<?php

namespace Drupal\charts_overrides\Plugin\override;

use Drupal\charts_highcharts\Plugin\override\HighchartsOverrides;

/**
 * Defines a concrete class for a Highcharts.
 *
 * @ChartOverride(
 *   id = "charts_overrides_highcharts",
 *   name = @Translation("Highcharts Overrides")
 * )
 */
class ChartsOverridesHighcharts extends HighchartsOverrides {

  public function chartOverrides(array $originalOptions = []) {

    $options = [];

    //    The following are currently available for overriding; they are the
    //    private variables in charts_c3/src/Settings/CThree/CThree.php
    //
    //    $options['
    //    An example of how to override plotOptions property.
    //    $options['plotOptions'] = [
    //        'series' => [
    //            'dataLabels' => [
    //                'enabled' => true,
    //                'color' => 'red'
    //            ],
    //        ],
    //    ];

    return $options;
  }

}
