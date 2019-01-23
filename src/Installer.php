<?php

namespace Jkribeiro\DrupalComposerParanoiaAcquia;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem as ComposerFilesystem;
use DrupalComposer\DrupalParanoia\Installer as DrupalParanoiaInstaller;
use Symfony\Component\Finder\Finder;
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
   * DrupalParanoia installer object.
   *
   * @var \DrupalComposer\DrupalParanoia\Installer
   */
  protected $drupalParanoiaInstaller;

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

    $this->drupalParanoiaInstaller = new DrupalParanoiaInstaller($composer, $io);

    // BC: See https://github.com/drupal-composer/drupal-paranoia/pull/12
    if (method_exists($this->drupalParanoiaInstaller, 'getConfig')) {
      $app_dir = $this->drupalParanoiaInstaller->getConfig('app-dir');
      $web_dir = $this->drupalParanoiaInstaller->getConfig('web-dir');
    }
    else {
      $extra = $composer->getPackage()->getExtra();
      $app_dir = $extra['drupal-app-dir'];
      $web_dir = $extra['drupal-web-dir'];
    }

    /*
     * Checks if the web directory folder is set to 'docroot'.
     * See https://docs.acquia.com/article/docroot-definition.
     */
    if ($web_dir != $this::ACQUIA_WEB_DIR) {
      throw new \RuntimeException('Set Drupal Paranoia "web-dir" config to "docroot" in your composer.json.');
    }

    $this->appDir = $app_dir;
    $this->webDir = $web_dir;
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
    $finder = new Finder();
    $cfs = new ComposerFilesystem();
    $fs = new SymfonyFilesystem();

    $finder->in($this->appDir . '/sites')->depth(0);

    /** @var \Symfony\Component\Finder\SplFileInfo $directory */
    foreach ($finder->directories() as $directory) {
      $site = $directory->getFilename();

      /*
       * Acquia cloud expects a settings.php file at "docroot/sites/default"
       * to automatically create the 'files' symlink pointing to Acquia server
       * files folder.
       * See https://docs.acquia.com/cloud/files.
       *
       * This step creates a stub settings.php file in the docroot directory.
       */
      $this->drupalParanoiaInstaller->createStubPhpFile("sites/{$site}/settings.php");

      /*
       * Change the public files folder symlink to works on Acquia and locally.
       *
       * The default AssetInstaller symlinks:
       * docroot/sites/<site>/files (symlink) -> app/sites/<site>/files (folder)
       *
       * On Acquia, we need to invert the symlinks:
       * app/sites/<site>/files (symlink) -> docroot/sites/<site>/files (Acquia symlink to server files)
       *
       * On local environment we need:
       * app/sites/<site>/files (symlink) -> docroot/sites/<site>/files (symlink)
       * docroot/sites/<site>/files (symlink) -> app/public-files(-<site>) (folder)
       * This is necessary because the docroot repo is deleted and recreated every
       * time that the paranoia installation runs.
       */
      $appDirDefaultFiles = realpath($this->appDir) . "/sites/{$site}/files";

      if (!is_link($appDirDefaultFiles) && !file_exists($appDirDefaultFiles)) {
        continue;
      }

      $appDirPublicFiles = realpath($this->appDir) . "/public-files-{$site}";
      if ($site == 'default') {
        $appDirPublicFiles = realpath($this->appDir) . "/public-files";
      }

      $webDirDefaultFiles = realpath($this->webDir) . "/sites/{$site}/files";

      if (!file_exists($appDirPublicFiles)) {
        // Copy app/sites/<site>/files to app/public-files(-<site>).
        $cfs->copyThenRemove($appDirDefaultFiles, $appDirPublicFiles);
      }

      // Destroy the symlink docroot/sites/<site>/files.
      $fs->remove($webDirDefaultFiles);

      // Symlink docroot/sites/<site>/files -> app/public-files(-<site>).
      $cfs->relativeSymlink($appDirPublicFiles, $webDirDefaultFiles);

      if (!is_link($appDirDefaultFiles)) {
        // Symlink app/sites/<site>/files -> docroot/sites/<site>/files.
        $cfs->relativeSymlink($webDirDefaultFiles, $appDirDefaultFiles);
      }
    }

    $this->io->write("> drupal-composer-paranoia-acquia: Additional installation for Acquia environments has been made.");
  }

}
