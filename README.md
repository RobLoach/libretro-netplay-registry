libretro-netplay-registry
=========================

[![Build Status](https://travis-ci.org/libretro/libretro-netplay-registry.svg?branch=master)](https://travis-ci.org/libretro/libretro-netplay-registry)

Stores a netplay registry for libretro.

# Requirements

* PHP 5.6 or higher
* [Composer](https://getcomposer.org/download/)
* And the requirements that are checked with: `php bin/symfony_requirements`

# Installation

1. Clone repository: `git clone https://github.com/libretro/libretro-netplay-registry.git`
2. Go into the directory: `cd libretro-netplay-registry`
3. Install dependencies: `composer install`
4. Create database: `php bin/console doctrine:database:create`

# Development

1. Follow the installation.
2. Run application: `php bin/console server:run`

# TODO

* Add some validation and security behind adding entries.
* Add continuous integration.

# Hosting

The server, hosted at [http://lobby.libretro.com](http://lobby.libretro.com) is running the following software:

* PHP 5.5.9
* SQLite3
* Nginx

# Documentation

For more information visit the [documentation](./src/AppBundle/Resources/doc/index.rst).
