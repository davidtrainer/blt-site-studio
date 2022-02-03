Acquia BLT integration with Site Studio
====

This is an [Acquia BLT](https://github.com/acquia/blt) plugin providing [Acquia Site Studio](https://www.acquia.com/products-services/acquia-cohesion) integration to add the necessary site studio commands to blt setup to automate the Site Studio configuration setup and deployment.

This plugin automates the necessary Site Studio drush commands into BLT setup to do the following:
* Import Site Studio assets and configuration from the package file.
* Import Site studio configuration from the sync folder.
* Rebuild Site Studio.

## Installation

To use this plugin, you must already have a Drupal project using BLT 10 or later, and Acquia Site Studio 6.4.0 or later.

The `1.x` branch supports Site Studio 6.7.x and below.

The `2.x` branch supports Site Studio 6.8.0 and above.

Add the following to the `repositories` section of your project's composer.json:

```
"blt-site-studio": {
    "type": "vcs",
    "url": "https://github.com/davidtrainer/blt-site-studio.git",
    "no-api": true
}
```

or run:

```
composer config repositories.blt-site-studio '{"type": "vcs", "url": "https://github.com/davidtrainer/blt-site-studio.git", "no-api": true}'
```

Require the plugin with Composer:

`composer require acquia/blt-site-studio`

## Usage

This plugin will run Site Studio package import and asset rebuild after BLT's `drupal:config:import` command on sites that use Site Studio.

## Customization

### Multiline Configuration

This plugin will automatically set the `site_studio_package_multiline` value to true in site-studio.settings.php. This value can be overriden if desired by changing to false.

See the [Site Studio 6.7](https://cohesiondocs.acquia.com/6.7/user-guide/version-6-7-0-release-details) release notes for more information.

### Securing Site Studio Credentials

By default, Site Studio will store api keys for Site Studio in the cohesion.settings.yml configuration file. It is advised to instead store these in a [platform secrets](https://docs.acquia.com/resource/secrets) file and/or a local settings file and to keep the API keys out of your git repository.

This can be accomplished in PHP with the following config overrides:

```
$config['cohesion.settings']['api_key'] = '';
$config['cohesion.settings']['organization_key'] = '';
```
