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

## Format Validation

If you want to format the verification messages, follow these steps:

Firstly import the `ValidationException` class:

```php
use Illuminate\Validation\ValidationException;
```

Add (if need) the `dontReport` list:

```php
/**
 * A list of the exception types that should not be reported.
 *
 * @var array
 */
protected $dontReport = [
    // ...
    ValidationException::class,
];
```

And finally update the `render` method:

```php
/**
 * Render an exception into an HTTP response.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Throwable  $exception
 * @return \Symfony\Component\HttpFoundation\Response
 *
 * @throws \Throwable
 */
public function render($request, Throwable $exception)
{
    if ($exception instanceof ValidationException) {
        return $this->convertValidationExceptionToResponse($exception, $request);
    }

    return parent::render($request, $exception);
}

/**
 * Create a response object from the given validation exception.
 *
 * @param  \Illuminate\Validation\ValidationException  $e
 * @param  \Illuminate\Http\Request  $request
 * @return \Symfony\Component\HttpFoundation\Response
 */
protected function convertValidationExceptionToResponse(ValidationException $e, $request)
{
    if ($e->response) {
        return $e->response;
    }

    return response()->json([
        'message' => $e->getMessage(),
        'errors' => $e->errors(),
    ], $e->status);
}
```

## License

This package is licensed under [The MIT License (MIT)](LICENSE).
