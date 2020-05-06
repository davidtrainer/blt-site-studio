Acquia BLT integration with Cohesion
====

This is an [Acquia BLT](https://github.com/acquia/blt) plugin providing [Cohesion](https://www.acquia.com/products-services/acquia-cohesion) integration.

This plugin is **community-created** and **community-supported**. Acquia does not provide any direct support for this software or provide any warranty as to its stability.

## Installation

To use this plugin, you must already have a Drupal project using BLT 10 or later, and Acquia Cohesion.

Add the following to the `repositories` section of your project's composer.json:

```
"blt-cohesion": {
    "type": "vcs",
    "url": "https://github.com/davidtrainer/blt-cohesion.git"
}
```

Require the plugin with Composer:

`composer require docksal/blt-cohesion`

## Usage

This plugin will run Cohesion package import and asset rebuild after BLT's `drupal:update` command on sites that use Cohesion.
