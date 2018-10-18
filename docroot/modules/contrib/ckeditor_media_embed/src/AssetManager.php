<?php

namespace Drupal\ckeditor_media_embed;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactory;

/**
 * The AssetManager facade for managing CKEditor plugins.
 */
class AssetManager {

  private static $libraryVersion = '4.5.x';
  private static $packagePrefix = 'ckeditor-dev';

  /**
   * Retrieve a list of all plugins to install.
   *
   * @return array
   *    An array of CKEditor plugin names that will be installed.
   */
  public static function getPlugins() {
    return [
      'autoembed',
      'autolink',
      'embed',
      'embedbase',
      'embedsemantic',
      'notification',
      'notificationaggregator',
    ];
  }

  /**
   * Retrieve the install status of all CKEditor plugins.
   */
  public static function getPluginsInstallStatuses() {
    $plugin_statuses = [];

    foreach (self::getPlugins() as $plugin_name) {
      $plugin_statuses[$plugin_name] = self::pluginIsInstalled($plugin_name);
    }

    return $plugin_statuses;
  }

  /**
   * Determine if all our CKEditor plugins are installed.
   *
   * @return bool
   *   Returns TRUE if all of our CKEditor plugins are installed and FALSE
   *   otherwise.
   */
  public static function pluginsAreInstalled() {
    $installed = TRUE;

    foreach (self::getPlugins() as $plugin_name) {
      if (!self::pluginIsInstalled($plugin_name)) {
        $installed = FALSE;
      }
    }

    return $installed;
  }

  /**
   * Determine if the specified plugin is installed.
   *
   * @param string $plugin_name
   *   The name of the plugin to check installation.
   *
   * @return bool
   *   Returns TRUE if the specfied CKEditor plugin is installed and FALSE
   *   otherwise.
   */
  public static function pluginIsInstalled($plugin_name) {
    $is_installed = FALSE;

    $library_plugin_path = self::getCKEditorLibraryPluginDirectory() . $plugin_name;
    if (is_dir($library_plugin_path) && is_file($library_plugin_path . '/plugin.js')) {
      $is_installed = TRUE;
    }

    return $is_installed;
  }

  /**
   * Retrieve version number of the currently installed CKEditor.
   *
   * @param LibraryDiscoveryInterface $library_discovery
   *   The library discovery service to use for retrieving information about
   *   the CKeditor library.
   * @param ConfigFactory
   *   The config factory service to use for retrieving configuration
   *   information about CKeditor Media Embed.
   * @param string $path
   *   The path to the library yml file, from the root. Defaults to 'core'.
   * @param string $extension
   *   The extension type of the library file. Defaults to 'core'.
   *
   * @return string
   *   The version number of the currently installed CKEditor.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorVersion(LibraryDiscoveryInterface $library_discovery, ConfigFactory $config_factory, $path = "core", $extension = "core") {
    $config_version = $config_factory->get('ckeditor_media_embed.settings')->get('ckeditor_version');
    if(!empty($config_version)){
      return $config_version;
    }

    $parsed_version =  self::parseForCoreCKEditorVersion($path, $extension);
    return empty($parsed_version) ? self::$libraryVersion : $parsed_version;
  }

  /**
   * Retrieve the currently installed version of the CKEditor plugins.
   *
   * @param ConfigFactory
   *   The config factory service to use for retrieving configuration settings.
   *
   * @return string
   *   The version number of the currently installed CKEditor plugins.
   */
  public static function getPluginsInstalledVersion(ConfigFactory $config_factory) {
    return $config_factory->get('ckeditor_media_embed.settings')->get('plugins_version_installed');
  }

  /*
   * Parse the core libraries to get the current version of CKEditor
   *
   * @param string $path
   *   The path to the library from the root.
   * @param string $extension
   *   The extension type of the library
   *
   * @return string
   *   Core's version of CKEditor.
   *
   * @see LibraryDiscoveryParser::parseLibraryInfo(). We use our own as Drupal's
   *      currently can falsely alter the version of the CKEditor library due
   *      https://www.drupal.org/node/2451411.
   */
  protected static function parseForCoreCKEditorVersion($path, $extension){
    $version = '';
    $libraries = [];

    $library_file = \Drupal::root() . '/' . $path . '/' . $extension . '.libraries.yml';
    if (file_exists($library_file)) {
      try {
        $libraries = Yaml::decode(file_get_contents($library_file));

        if (!empty($libraries['ckeditor']['version'])) {
          $version = $libraries['ckeditor']['version'];

          $version_extra_position = strpos($libraries['ckeditor']['version'], '+');
          if ($version_extra_position > 0) {
            $version = substr($version, 0, $version_extra_position);
          }
        }
      }
      catch (InvalidDataTypeException $e) {
        // Rethrow a more helpful exception to provide context.
        throw new InvalidLibraryFileException(sprintf('Invalid library definition in %s: %s', $library_file, $e->getMessage()), 0, $e);
      }
    }

    return $version;
  }

  /**
   * Retrieve the path of the CKEditor plugins for use in a URL.
   *
   * @return string
   *   The path to the CKEditor plugins for use in a URL.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorLibraryPluginPath() {
    return 'profiles/varbase/libraries/ckeditor/plugins/';
  }

  /**
   * Retrieve the system directory of the CKEditor plugins.
   *
   * The plugin directory is used with commands and abolute system path.
   *
   * @return string
   *   The diretory to the CKEditor plugins for use in a with commands and
   *   absolute system paths.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorLibraryPluginDirectory() {
    return \Drupal::root() . '/profiles/varbase/libraries/ckeditor/plugins/';
  }

  /**
   * Retrieve the URL of the source package to download.
   *
   * @param string $version
   *   The version of the CKEditor source package to download.
   *
   * @return string
   *   The absolute URL to the source package downloadable archive.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorDevFullPackageUrl($version) {
    return 'https://github.com/ckeditor/' . self::$packagePrefix . '/archive/' . $version . '.zip';
  }

  /**
   * Retrieve the source CKEditor package name based on the package version.
   *
   * @param string $version
   *   The version of the CKEditor source package.
   */
  // @codingStandardsIgnoreLine
  public static function getCKEditorDevFullPackageName($version) {
    return self::$packagePrefix . '-' . $version;
  }

}
