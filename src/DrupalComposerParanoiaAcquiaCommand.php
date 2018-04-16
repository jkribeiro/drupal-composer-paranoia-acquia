<?php

namespace Jkribeiro\DrupalComposerParanoiaAcquia;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DrupalComposerParanoiaAcquiaCommand.
 *
 * @package Jkribeiro\DrupalComposerParanoiaAcquia
 */
class DrupalComposerParanoiaAcquiaCommand extends BaseCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();
    $this
      ->setName('drupal:paranoia-acquia')
      ->setAliases(['drupal-paranoia-acquia'])
      ->setDescription('Execute the installation of the paranoia mode to Acquia environments.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $installer = new Installer($this->getComposer(), $this->getIO());
    $installer->install();
  }

}
