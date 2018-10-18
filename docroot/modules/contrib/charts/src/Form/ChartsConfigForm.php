<?php

namespace Drupal\charts\Form;


use Drupal\charts\Theme\ChartsInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\charts\Settings\ChartsBaseSettingsForm;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\charts\Settings\ChartsDefaultSettings;
use Drupal\charts\Settings\ChartsTypeInfo;

/**
 * Charts Config Form.
 */
class ChartsConfigForm extends ConfigFormBase {

  protected $moduleHandler;

  protected $config;

  protected $defaults;

  protected $chart_types;

  protected $chartsBaseSettingsForm;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->config = $this->configFactory->getEditable('charts.settings');
    $this->defaults = new ChartsDefaultSettings();
    $this->chart_types = new ChartsTypeInfo();
    $this->chartsBaseSettingsForm = new ChartsBaseSettingsForm();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('module_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'charts_form_base';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['charts.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $parents = ['charts_default_settings'];
    $default_config = (array) $this->config->get('charts_default_settings');
    $defaults = $default_config + $this->defaults->getDefaults();

    $field_options = [];
    $form['help'] = [
      '#type' => 'markup',
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#markup' => $this->t('The settings on this page are used to set
        <strong>default</strong> settings. They do not affect existing charts.
        To make a new chart, <a href="@create">create a new view</a> and select
        the display format of "Chart".', [
        '@create' => Url::fromRoute('views_ui.add')
          ->toString(),
      ]),
      '#weight' => -100,
    ];
    // Reuse the global settings form for defaults, but remove JS classes.
    $form = $this->chartsBaseSettingsForm->getChartsBaseSettingsForm($form, $defaults, $field_options, $parents, 'config_form');
    $form['xaxis']['#attributes']['class'] = [];
    $form['yaxis']['#attributes']['class'] = [];
    $form['display']['colors']['#prefix'] = NULL;
    $form['display']['colors']['#suffix'] = NULL;
    // Put settings into vertical tabs.
    $form['display']['#group'] = 'defaults';
    $form['xaxis']['#group'] = 'defaults';
    $form['yaxis']['#group'] = 'defaults';
    $form['defaults'] = ['#type' => 'vertical_tabs'];

    // Add submit buttons and normal saving behavior.
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save defaults'),
        '#button_type' => 'primary',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config->set('charts_default_settings', $form_state->getValue('charts_default_settings'));
    $this->config->save();
  }

}
