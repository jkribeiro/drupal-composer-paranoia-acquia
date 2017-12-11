<?php

namespace Jkribeiro\DrupalComposerParanoiaAcquia;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin to move all PHP files out of the docroot on Acquia envs.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

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

  /**
   * Script callback for installing the paranoia mode.
   *
   * @param \Composer\Script\Event $event
   *   Event object.
   */
  public static function install(Event $event) {
    $installer = new Installer($event->getComposer(), $event->getIO());
    $installer->install();
  }

}
