[![PHP](https://img.shields.io/badge/PHP-7.0%2B-blue.svg)](https://secure.php.net/migration70)
[![Latest Stable Version](https://poser.pugx.org/webinarium/pignus-bundle/v/stable)](https://packagist.org/packages/webinarium/pignus-bundle)
[![Build Status](https://travis-ci.org/webinarium/PignusBundle.svg?branch=master)](https://travis-ci.org/webinarium/PignusBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webinarium/PignusBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webinarium/PignusBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/webinarium/PignusBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/webinarium/PignusBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/acb34716-39ea-4b28-b14c-60d24137f9b5/mini.png)](https://insight.sensiolabs.com/projects/acb34716-39ea-4b28-b14c-60d24137f9b5)

# Pignus for Symfony

The bundle provides few classes and features related to the Security component:

- [User entity trait](//github.com/webinarium/PignusBundle/wiki/User-entity)
- [User provider](//github.com/webinarium/PignusBundle/wiki/User-provider)
- [Generic authenticator](//github.com/webinarium/PignusBundle/wiki/Abstract-authenticator)
- [OAuth2 authenticator](//github.com/webinarium/PignusBundle/wiki/OAuth2-authenticator)
- [Login page](//github.com/webinarium/PignusBundle/wiki/Login-page)
- [Translations](//github.com/webinarium/PignusBundle/wiki/Translations)

All the features are decoupled from each other and can be used independently, so you are free to use only those you need.

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```console
composer require webinarium/pignus-bundle
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
public function registerBundles()
{
    $bundles = [
        // ...
        new Pignus\PignusBundle(),
    ];
}
```

## Development

```console
./vendor/bin/php-cs-fixer fix
./vendor/bin/phpunit --coverage-html=vendor/coverage
```
