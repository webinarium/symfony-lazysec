[![PHP](https://img.shields.io/badge/PHP-7.0%2B-blue.svg)](https://secure.php.net/migration70)
[![Latest Stable Version](https://poser.pugx.org/webinarium/pignus-bundle/v/stable)](https://packagist.org/packages/webinarium/pignus-bundle)

# Pignus for Symfony

The bundle provides few classes and features related to the Security component.

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
