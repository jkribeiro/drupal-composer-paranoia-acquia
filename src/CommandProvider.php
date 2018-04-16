<?php

namespace Jkribeiro\DrupalComposerParanoiaAcquia;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

/**
 * Class CommandProvider.
 *
 * @package Jkribeiro\DrupalComposerParanoiaAcquia
 */
class CommandProvider implements CommandProviderCapability {

  /**
   * {@inheritdoc}
   */
  public function getCommands() {
    return array(
      new DrupalComposerParanoiaAcquiaCommand(),
    );
  }

}
