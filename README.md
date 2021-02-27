Acquia BLT integration with Site Studio
====

This is an [Acquia BLT](https://github.com/acquia/blt) plugin providing [Acquia Site Studio](https://www.acquia.com/products-services/acquia-cohesion) integration to add the necessary site studio commands to blt setup to automate the Site Studio configuration setup and deployment.

This plugin automates the necessary Site Studio drush commands into BLT setup to do the following: 
* Import Site Studio assets and configuration from the package file.
* Import Site studio configuration from the sync folder.
* Rebuild Site Studio.

## Installation

To use this plugin, you must already have a Drupal project using BLT 10 or later, and Acquia Site Studio 6.4.0 or later.

Add the following to the `repositories` section of your project's composer.json:

```
"blt-site-studio": {
    "type": "vcs",
    "url": "https://github.com/davidtrainer/blt-site-studio.git"
}
```

or run:

```
composer config repositories.blt-site-studio vcs https://github.com/davidtrainer/blt-site-studio.git
```

Require the plugin with Composer:

`composer require acquia/blt-site-studio`

## Usage

This plugin will run Site Studio package import and asset rebuild after BLT's `drupal:config:import` command on sites that use Site Studio.
