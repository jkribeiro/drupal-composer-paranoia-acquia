# Drupal Composer paranoia mode for Acquia Cloud environments
Composer plugin for improving the website security for composer-based Drupal projects by moving all PHP files out of docroot, for Acquia environments.

This plugin has the dependency of the [drupal-paranoia](https://github.com/drupal-composer/drupal-paranoia) plugin, performing additional installation steps to run the paranoia mode on Acquia Cloud environments.

Would like to know more about it?
- https://github.com/drupal-composer/drupal-paranoia
- [Moving all PHP files out of the docroot](https://www.drupal.org/node/2767907)
- [#1672986: Option to have all php files outside of web root](https://www.drupal.org/node/1672986)
- [Remote Code Execution - SA-CONTRIB-2016-039](https://www.drupal.org/node/2765575)
- https://twitter.com/drupalsecurity/status/753263548458004480

## Configuration
Make sure you have a [drupal-composer/drupal-project](https://github.com/drupal-composer/drupal-project)-based project created.

Rename your Acquia repo docroot directory to `app`.
```
mv docroot app
```

Update the `composer.json` of your root package with the following changes:
```json
"extra": {
    "installer-paths": {
        "app/core": ["type:drupal-core"],
        "app/libraries/{$name}": ["type:drupal-library"],
        "app/modules/contrib/{$name}": ["type:drupal-module"],
        "app/profiles/contrib/{$name}": ["type:drupal-profile"],
        "app/themes/contrib/{$name}": ["type:drupal-theme"],
        "drush/contrib/{$name}": ["type:drupal-drush"]
    },
    "drupal-app-dir": "app",
    "drupal-web-dir": "docroot"
    "..."
}
```

Use `composer require ...` to install this Plugin on your project.
```
composer require jkribeiro/drupal-composer-paranoia-acquia:~1
```

Run the following commands to make sure that the new folders are installed:
```
composer drupal:paranoia
composer drupal:paranoia-acquia
```

Done! Plugin and new docroot are now installed.

## Folder structure
Your project now is basically structured on two folders.
- __app__: Contains the files and folders of the full Drupal installation.
- __docroot__: Contains only the __symlinks of the assets files__ and the __PHP stub files__ from the `app` folder.

Every time that you install or update a Drupal package via Composer, the `docroot` folder is automatically recreated.

If necessary, you can rebuild it manually, running the command
```
composer drupal:paranoia
```

This could be necessary when updating themes images, CSS and JS files.

Last step is to commit and push the changes to Acquia Cloud git repository.
