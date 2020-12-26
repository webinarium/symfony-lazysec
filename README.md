[![PHP](https://img.shields.io/badge/PHP-7.1%2B-blue.svg)](https://secure.php.net/migration71)
[![Latest Stable Version](https://poser.pugx.org/webinarium/symfony-lazysec/v/stable)](https://packagist.org/packages/webinarium/symfony-lazysec)
[![Build Status](https://travis-ci.org/webinarium/symfony-lazysec.svg?branch=master)](https://travis-ci.org/webinarium/symfony-lazysec)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webinarium/symfony-lazysec/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webinarium/symfony-lazysec/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/webinarium/symfony-lazysec/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/webinarium/symfony-lazysec/?branch=master)

# Lazy security for Symfony

The library provides few classes and features related to the Security component:

- [User entity trait](//github.com/webinarium/symfony-lazysec/wiki/User-entity)
- [User checker](//github.com/webinarium/symfony-lazysec/wiki/User-checker)
- [Translations](//github.com/webinarium/symfony-lazysec/wiki/Translations)

All the features are decoupled from each other and can be used independently, so you are free to use only those you need.

## Installation

Open a command console, enter your project directory and execute the following command to download the latest stable version of the library:

```console
composer require webinarium/symfony-lazysec
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

## Development

```console
./vendor/bin/php-cs-fixer fix
./vendor/bin/phpunit --coverage-html=vendor/coverage
```
