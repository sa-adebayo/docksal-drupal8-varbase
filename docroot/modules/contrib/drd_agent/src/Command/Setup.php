<?php

namespace Drupal\drd_agent\Command;

use Drupal\Console\Core\Command\Shared\CommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Setup.
 *
 * @package Drupal\drd_agent
 */
class Setup extends Command {

  use CommandTrait;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();
    $this
      ->setName('drd:agent:setup')
      ->setDescription($this->trans('Initially setup the site for the DRD Agent, only used internally by the setup process.'))
      ->addArgument(
        'token',
        InputArgument::REQUIRED,
        $this->trans('Base64 and json encoded array of all variables required such that DRD can communicate with this domain in the future')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $service = \Drupal::service('drd_agent.setup');
    $service->run($input->getArgument('token'));
  }

}
