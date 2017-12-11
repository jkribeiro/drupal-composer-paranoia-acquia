# Drupal Composer paranoia mode for Acquia Cloud environments
This is an experimental Composer plugin to improve the website security for composer-based Drupal projects by moving all PHP files out of docroot, for Acquia environments.

This plugin has the dependency of the [drupal-composer-paranoia](https://github.com/jkribeiro/drupal-composer-paranoia) plugin, performing additional installation steps to run the paranoia mode on Acquia Cloud environments.

Would like to know more about it? 
- https://github.com/jkribeiro/drupal-composer-paranoia
- [Moving all PHP files out of the docroot](https://www.drupal.org/node/2767907)
- [#1672986: Option to have all php files outside of web root](https://www.drupal.org/node/1672986)
- https://github.com/drupal-composer/drupal-project/issues/176
- [Remote Code Execution - SA-CONTRIB-2016-039](https://www.drupal.org/node/2765575)
- https://twitter.com/drupalsecurity/status/753263548458004480

## Usage
Make sure you have a [drupal-composer/drupal-project](https://github.com/drupal-composer/drupal-project)-based project created.

Rename your current docroot directory to `app`.
```
mv docroot app
```

Update the `composer.json` of your root package with the following changes:
```
    "scripts": {
        "drupal-paranoia": [
            "Jkribeiro\\DrupalComposerParanoia\\Plugin::install",
            "Jkribeiro\\DrupalComposerParanoiaAcquia\\Plugin::install"
        ],
        "..."
    }
```
```
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
        "drupal-web-dir": "docroot",
        "drupal-web-dir-public-files": "app/public-files"
        "..."
    }
```

Use `composer require ...` to install this Plugin in your project.
```
composer require jkribeiro/drupal-composer-paranoia-acquia:~1
```

Done! Plugin and new docroot installed.

Now, every time you install or update a Drupal package, the paranoia installer will rebuild the `docroot` folder with the symlinks of the asset files from the `app` folder.

If you need to rebuild the docroot folder, for example, when developing locally in the `app` folder with new themes images, CSS and JS, you can use the command:
```
composer run-script drupal-paranoia
```

Last step is to commit and push the changes to Acquia Cloud git repository.
