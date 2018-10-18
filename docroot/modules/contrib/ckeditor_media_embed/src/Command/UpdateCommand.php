<?php

namespace Drupal\ckeditor_media_embed\Command;

use Alchemy\Zippy\Zippy;
use Drupal\ckeditor_media_embed\AssetManager;
use Drupal\ckeditor\CKEditorPluginManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Console\Core\Command\Shared\ContainerAwareCommandTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Helper\HelperTrait;
use Drupal\Core\Asset\LibraryDiscovery;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class UpdateCommand.
 *
 * @package Drupal\ckeditor_media_embed
 *
 * @DrupalCommand (
 *     extension="ckeditor_media_embed",
 *     extensionType="module"
 * )
 */
class UpdateCommand extends InstallCommand {

  protected function configure() {
    $this
      ->setName('ckeditor_media_embed:update')
      ->setDescription($this->trans('commands.ckeditor_media_embed.update.description'));
  }

}
