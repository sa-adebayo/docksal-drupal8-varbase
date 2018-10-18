<?php
/**
 * @file
 * Provides an API for integrating the jQuery Masonry plugin with Drupal.
 *
 * Sponsored by: www.freelance-drupal.com
 */

namespace Drupal\masonry\Services;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Wrapper methods for Masonry API methods.
 *
 *
 * @ingroup masonry
 */
class MasonryService {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a MasonryService object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler
   *
   */
  function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get default Masonry options.
   *
   * @return
   *   An associative array of default options for Masonry.
   *   Contains:
   *   - masonry_column_width: The width of each column (in pixels or as a
   *     percentage).
   *   - masonry_column_width_units: The units to use for the column width ('px'
   *     or '%').
   *   - masonry_gutter_width: The spacing between each column (in pixels).
   *   - masonry_resizable: Automatically rearrange items when the container is
   *     resized.
   *   - masonry_animated: Animate item rearrangements.
   *   - masonry_animation_duration: The duration of animations (in milliseconds).
   *   - masonry_fit_width: Sets the width of the container to the nearest column.
   *     Ideal for centering Masonry layouts.
   *   - masonry_rtl: Display items from right-to-left.
   *   - masonry_images_first: Load all images first before triggering Masonry.
   */
  public function getMasonryDefaultOptions() {
    return array(
      'layoutColumnWidth' => '',
      'layoutColumnWidthUnit' => 'px',
      'gutterWidth' => '0',
      'isLayoutResizable' => TRUE,
      'isLayoutAnimated' => TRUE,
      'layoutAnimationDuration' => '500',
      'isLayoutFitsWidth' => FALSE,
      'isLayoutRtlMode' => FALSE,
      'isLayoutImagesLoadedFirst' => TRUE,
      'stampSelector' => '',
      'isItemsPositionInPercent' => FALSE,
    );
  }

  /**
   * Apply Masonry to a container.
   *
   * @param $container
   *   The CSS selector of the container element to apply Masonry to.
   * @param $options
   *   An associative array of Masonry options.
   *   Contains:
   *   - masonry_item_selector: The CSS selector of the items within the
   *     container.
   *   - masonry_column_width: The width of each column (in pixels or as a
   *     percentage).
   *   - masonry_column_width_units: The units to use for the column width ('px'
   *     or '%').
   *   - masonry_gutter_width: The spacing between each column (in pixels).
   *   - masonry_resizable: Automatically rearrange items when the container is
   *     resized.
   *   - masonry_animated: Animate item rearrangements.
   *   - masonry_animation_duration: The duration of animations (in milliseconds).
   *   - masonry_fit_width: Sets the width of the container to the nearest column.
   *     Ideal for centering Masonry layouts.
   *   - masonry_rtl: Display items from right-to-left.
   *   - masonry_images_first: Load all images first before triggering Masonry.
   */
  public function applyMasonryDisplay(&$form, $container, $item_selector, $options = array()) {

    //if (masonry_loaded() && !empty($container)) {
    if (!empty($container)) {
      // For any options not specified, use default options
      $options += $this->getMasonryDefaultOptions();
      if (!isset($item_selector)) {
        $item_selector = '';
      }

      // Setup Masonry script
      $masonry = array(
        'masonry' => array(
          $container => array(
            'item_selector' => $item_selector,
            'column_width' => $options['layoutColumnWidth'],
            'column_width_units' => $options['layoutColumnWidthUnit'],
            'gutter_width' => (int) $options['gutterWidth'],
            'resizable' => (bool) $options['isLayoutResizable'],
            'animated' => (bool) $options['isLayoutAnimated'],
            'animation_duration' => (int) $options['layoutAnimationDuration'],
            'fit_width' => (bool) $options['isLayoutFitsWidth'],
            'rtl' => (bool) $options['isLayoutRtlMode'],
            'images_first' => (bool) $options['isLayoutImagesLoadedFirst'],
            'stamp' => $options['stampSelector'],
            'percent_position' => (bool) $options['isItemsPositionInPercent'],
          ),
        ),
      );

      // Allow other modules to alter the Masonry script
      $context = array(
        'container' => $container,
        'item_selector' => $item_selector,
        'options' => $options,
      );
      $this->moduleHandler->alter('masonry_script', $masonry, $context);

      $form['#attached']['library'][] =  'masonry/masonry.layout';
      $form['#attached']['drupalSettings'] = $masonry;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildSettingsForm($default_values = array()) {

    // Load module default values if empty.
    if (empty($default_values)) {
      $default_values = $this->getMasonryDefaultOptions();
    }

    $form['layoutColumnWidth'] = array(
      '#type' => 'textfield',
      '#title' => t('Column width'),
      '#description' => t("The width of each column, enter pixels, percentage, or string of css selector"),
      '#default_value' => $default_values['layoutColumnWidth'],
    );
    $form['layoutColumnWidthUnit'] = array(
      '#type' => 'radios',
      '#title' => t('Column width units'),
      '#description' => t("The units to use for the column width."),
      '#options' => array(
        'px' => t("Pixels"),
        '%' => t("Percentage (of container's width)"),
        'css' => t("CSS selector (you must configure your css to set widths for .masonry-item)"),
      ),
      '#default_value' => $default_values['layoutColumnWidthUnit'],
    );
    $form['gutterWidth'] = array(
      '#type' => 'textfield',
      '#title' => t('Gutter width'),
      '#description' => t("The spacing between each column."),
      '#default_value' => $default_values['gutterWidth'],
      '#size' => 4,
      '#maxlength' => 3,
      '#field_suffix' => t('px'),
    );
    $form['stampSelector'] = array(
      '#type' => 'textfield',
      '#title' => t('Stamp Selector'),
      '#description' => t("Specifies which elements are stamped within the layout using css selector"),
      '#default_value' => $default_values['stampSelector'],
    );
    $form['isLayoutResizable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Resizable'),
      '#description' => t("Automatically rearrange items when the container is resized."),
      '#default_value' => $default_values['isLayoutResizable'],
    );
    $form['isLayoutAnimated'] = array(
      '#type' => 'checkbox',
      '#title' => t('Animated'),
      '#description' => t("Animate item rearrangements."),
      '#default_value' => $default_values['isLayoutAnimated'],
      '#states' => array(
        'visible' => array(
          'input.form-checkbox[name*="isLayoutResizable"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['layoutAnimationDuration'] = array(
      '#type' => 'textfield',
      '#title' => t('Animation duration'),
      '#description' => t("The duration of animations (1000 ms = 1 sec)."),
      '#default_value' => $default_values['layoutAnimationDuration'],
      '#size' => 5,
      '#maxlength' => 4,
      '#field_suffix' => t('ms'),
      '#states' => array(
        'visible' => array(
          'input.form-checkbox[name*="isLayoutResizable"]' => array('checked' => TRUE),
          'input.form-checkbox[name*="isLayoutAnimated"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['isLayoutFitsWidth'] = array(
      '#type' => 'checkbox',
      '#title' => t('Fit width'),
      '#description' => t("Sets the width of the container to the nearest column. Ideal for centering Masonry layouts. See the <a href='http://masonry.desandro.com/demos/centered.html'>'Centered' demo</a> for more information."),
      '#default_value' => $default_values['isLayoutFitsWidth'],
    );
    $form['isLayoutRtlMode'] = array(
      '#type' => 'checkbox',
      '#title' => t('RTL layout'),
      '#description' => t("Display items from right-to-left."),
      '#default_value' => $default_values['isLayoutRtlMode'],
    );
    $form['isLayoutImagesLoadedFirst'] = array(
      '#type' => 'checkbox',
      '#title' => t('Load images first'),
      '#description' => t("Load all images first before triggering Masonry."),
      '#default_value' => $default_values['isLayoutImagesLoadedFirst'],
    );
    $form['isItemsPositionInPercent'] = array(
      '#type' => 'checkbox',
      '#title' => t('Percent position'),
      '#description' => t("Sets item positions in percent values, rather than pixel values. Checking this will works well with percent-width items, as items will not transition their position on resize. See the <a href='http://masonry.desandro.com/options.html#percentposition'>masonry doc</a> for more information."),
      '#default_value' => $default_values['isItemsPositionInPercent'],
    );

    // Allow other modules to alter the form.
    $this->moduleHandler->alter('masonry_options_form', $form, $default_values);

    return $form;
  }

  /**
   * Check if the Masonry and imagesLoaded libraries are installed.
   *
   * @return
   *   A boolean indicating the installed status.
   */
  function isMasonryInstalled() {
    $masonry = libraries_detect('masonry');
    $imagesloaded = libraries_detect('imagesloaded');
    if ((!empty($masonry['installed'])) && (!empty($imagesloaded['installed']))) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}