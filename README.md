# PHP HTTP Pool

![Banner with cards catalog picture in background and PHP XML Reader title](https://raw.githubusercontent.com/kiwilan/php-http-pool/main/docs/banner.jpg)

[![php][php-version-src]][php-version-href]
[![version][version-src]][version-href]
[![downloads][downloads-src]][downloads-href]
[![license][license-src]][license-href]
[![tests][tests-src]][tests-href]
[![codecov][codecov-src]][codecov-href]

PHP package with easy-to-use [`GuzzleHttp`](https://docs.guzzlephp.org/en/stable/quickstart.html) pool wrapper, works with `GuzzleHttp\Pool` and `GuzzleHttp\Client` to make concurrent requests. Built to be more flexible that Laravel [`Http`](https://laravel.com/docs/10.x/http-client#customizing-concurrent-requests) Pool. Works with Laravel [`Illuminate\Support\Collection`](https://laravel.com/docs/10.x/collections) to improve output.

> [!NOTE]\
> I love `GuzzleHttp/Pool`, but I would to build a wrapper to make it easier to use and Laravel `Http/Pool` is cool but not flexible enough for me. So `HttpPool` allow you to send an `array` or a `Collection` of requests and get a `Collection` of `HttpPoolResponse` with all `GuzzleHttp` features and more.

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-http-pool
```

## Usage

```php
$skeleton = new Kiwilan\HttpPool();
echo $skeleton->echoPhrase('Hello, Kiwilan!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

-   [Guzzle](https://docs.guzzlephp.org/en/stable/quickstart.html) for the awesome HTTP client
-   [Laravel](https://laravel.com/docs/10.x/http-client) for `Illuminate\Support\Collection`
-   [Spatie](https://github.com/spatie/package-skeleton-php) for the package skeleton
-   [Ewilan Rivière](https://github.com/kiwilan)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[<img src="https://user-images.githubusercontent.com/48261459/201463225-0a5a084e-df15-4b11-b1d2-40fafd3555cf.svg" height="120rem" width="100%" />](https://github.com/kiwilan)

[version-src]: https://img.shields.io/packagist/v/kiwilan/php-http-pool.svg?style=flat-square&colorA=18181B&colorB=777BB4
[version-href]: https://packagist.org/packages/kiwilan/php-http-pool
[php-version-src]: https://img.shields.io/static/v1?style=flat-square&label=PHP&message=v8.0&color=777BB4&logo=php&logoColor=ffffff&labelColor=18181b
[php-version-href]: https://www.php.net/
[downloads-src]: https://img.shields.io/packagist/dt/kiwilan/php-http-pool.svg?style=flat-square&colorA=18181B&colorB=777BB4
[downloads-href]: https://packagist.org/packages/kiwilan/php-http-pool
[license-src]: https://img.shields.io/github/license/kiwilan/php-http-pool.svg?style=flat-square&colorA=18181B&colorB=777BB4
[license-href]: https://github.com/kiwilan/php-http-pool/blob/main/README.md
[tests-src]: https://img.shields.io/github/actions/workflow/status/kiwilan/php-http-pool/run-tests.yml?branch=main&label=tests&style=flat-square&colorA=18181B
[tests-href]: https://packagist.org/packages/kiwilan/php-http-pool
[codecov-src]: https://codecov.io/gh/kiwilan/php-http-pool/branch/main/graph/badge.svg?token=P9XIK2KV9G
[codecov-href]: https://codecov.io/gh/kiwilan/php-http-pool
