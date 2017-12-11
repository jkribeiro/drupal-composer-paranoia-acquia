<?php

namespace Jkribeiro\DrupalComposerParanoiaAcquia;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem as ComposerFilesystem;
use Jkribeiro\DrupalComposerParanoia\Installer as DrupalComposerParanoiaInstaller;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Class Installer.
 *
 * @package Jkribeiro\DrupalComposerParanoiaAcquia
 */
class Installer {

  /**
   * Acquia Docroot dir name.
   */
  const ACQUIA_WEB_DIR = 'docroot';

  /**
   * DrupalComposerParanoia installer object.
   *
   * @var \Jkribeiro\DrupalComposerParanoia\Installer
   */
  protected $drupalComposerParanoiaInstaller;

  /**
   * IO object.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * The app directory path relative to the root package.
   *
   * @var string
   */
  public $appDir;

  /**
   * The web directory path relative to the root package.
   *
   * @var string
   */
  public $webDir;

  /**
   * Installer constructor.
   *
   * @param \Composer\Composer $composer
   *   The Composer object.
   * @param \Composer\IO\IOInterface $io
   *   The IO object.
   */
  public function __construct(Composer $composer, IOInterface $io) {
    $this->io = $io;

    $this->drupalComposerParanoiaInstaller = new DrupalComposerParanoiaInstaller($composer, $io);

    /*
     * Checks if the web directory folder is set to 'docroot'.
     * See https://docs.acquia.com/article/docroot-definition.
     */
    $extra = $composer->getPackage()->getExtra();
    if ($extra['drupal-web-dir'] != $this::ACQUIA_WEB_DIR) {
      throw new \RuntimeException('To install paranoia mode on Acquia Cloud servers, set "drupal-web-dir" to "docroot" in your composer.json.');
    }

    $this->appDir = $extra['drupal-app-dir'];
    $this->webDir = $extra['drupal-web-dir'];
  }

  /**
   * Post install command event to execute the installation.
   */
  public function onPostCmdEvent() {
    $this->install();
  }

  /**
   * Additional installation to run paranoia mode on Acquia cloud servers.
   */
  public function install() {
    /*
     * Acquia cloud expects a settings.php file at "docroot/sites/default"
     * to automatically create the 'files' symlink pointing to Acquia server
     * files folder.
     * See https://docs.acquia.com/cloud/files.
     *
     * This step creates a stub settings.php file in the web directory.
     */
    $this->drupalComposerParanoiaInstaller->createStubPhpFile('sites/default/settings.php');

    /*
     * Change the public files folder symlink to works on Acquia and locally.
     *
     * The default AssetInstaller symlinks:
     * docroot/sites/default/files (symlink) -> app/sites/default/files (folder)
     *
     * On Acquia, we need to invert the symlinks:
     * app/sites/default/files (symlink) -> docroot/sites/default/files (Acquia symlink to server files)
     *
     * On local environment we need:
     * app/sites/default/files (symlink) -> docroot/sites/default/files (symlink)
     * docroot/sites/default/files (symlink) -> app/public-files (folder)
     * This is necessary because the web repo is deleted and recreated every
     * time that the paranoia installation runs.
     */
    $appDirDefaultFiles = realpath($this->appDir) . '/sites/default/files';

    // Skip this step if the symlink already exist.
    if (is_link($appDirDefaultFiles)) {
      return;
    }

    $cfs = new ComposerFilesystem();
    $fs = new SymfonyFilesystem();

    $webDirDefaultFiles = realpath($this->webDir) . '/sites/default/files';
    $appDirPublicFiles = realpath($this->appDir) . '/public-files';

    // Copy app/sites/default/files to app/public-files.
    $cfs->copyThenRemove($appDirDefaultFiles, $appDirPublicFiles);

    // Destroy the symlink web/sites/default/files.
    $fs->remove($webDirDefaultFiles);

    // Symlink docroot/sites/default/files -> app/public-files.
    $cfs->relativeSymlink($appDirPublicFiles, $webDirDefaultFiles);

    // Symlink app/sites/default/files -> web/sites/default/files.
    $cfs->relativeSymlink($webDirDefaultFiles, $appDirDefaultFiles);

    $this->io->write("> drupal-composer-paranoia-acquia: Additional installation for Acquia environments has been made.");
  }

}
