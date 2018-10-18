<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 19/06/17
 * Time: 12:57
 */

namespace Drupal\tour_ui\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TourUIController extends ControllerBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler= $moduleHandler;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  public function getModules(Request $request) {
    $matches= [];

    $part = $request->query->get('q');
    if ($part) {
      $matches[] = $part;

      // Escape user input
      $part = preg_quote($part);

      $modules = $this->moduleHandler->getModuleList();
      foreach($modules as $module => $data) {
        if (preg_match("/$part/", $module)) {
          $matches[] = $module;
        }
      }
    }

    return new JsonResponse($matches);

  }
}