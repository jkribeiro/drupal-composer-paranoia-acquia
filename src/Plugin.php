<?php

namespace Jkribeiro\DrupalComposerParanoiaAcquia;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin to move all PHP files out of the docroot on Acquia envs.
 */
class Plugin implements PluginInterface, EventSubscriberInterface, Capable {

  /**
   * The installer object.
   *
   * @var \Jkribeiro\DrupalComposerParanoiaAcquia\Installer
   */
  protected $installer;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->installer = new Installer($composer, $io);
  }

  /**
   * {@inheritdoc}
   */
  public function getCapabilities() {
    return array(
      'Composer\Plugin\Capability\CommandProvider' => 'Jkribeiro\DrupalComposerParanoiaAcquia\CommandProvider',
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array(
      ScriptEvents::POST_INSTALL_CMD => array('postCmd', -2),
      ScriptEvents::POST_UPDATE_CMD => array('postCmd', -2),
    );
  }

  /**
   * Post command event callback.
   *
   * @param \Composer\Script\Event $event
   *   Event object.
   */
  public function postCmd(Event $event) {
    $this->installer->onPostCmdEvent();
  }

}
