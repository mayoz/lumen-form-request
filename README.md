# Lumen Form Request

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mayoz/lumen-form-request.svg?style=flat-square)](https://packagist.org/packages/mayoz/lumen-form-request)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://styleci.io/repos/259593361/shield?branch=master)](https://styleci.io/repos/259593361)

Adopt the Laravel Form Request to Lumen framework.

* [Installation](#installation)
* [Usage](#usage)
* [License](#license)

## Installation

You can install the package via composer:

``` bash
composer require mayoz/lumen-form-request
```

Register the service provider in your `boostrap/app.php` configuration file:

```php
$app->register(Illuminate\Foundation\Providers\FormRequestServiceProvider::class);
```

## Usage

Let's continue the official [Laravel's documantation](https://laravel.com/docs/7.x/validation).

## License

This package is licensed under [The MIT License (MIT)](LICENSE).
