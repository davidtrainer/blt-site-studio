Acquia BLT integration with Site Studio
====

This is an [Acquia BLT](https://github.com/acquia/blt) plugin providing [Acquia Site Studio](https://www.acquia.com/products-services/acquia-cohesion) integration.

This plugin is **community-created** and **community-supported**. Acquia does not provide any direct support for this software or provide any warranty as to its stability.

## Installation

To use this plugin, you must already have a Drupal project using BLT 10 or later, and Acquia Site Studio.

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

This plugin will run Site Studio package import and asset rebuild after BLT's `drupal:config:import` command on sites that use Cohesion.
