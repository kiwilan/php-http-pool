# PHP HTTP Pool

![Banner with cards catalog picture in background and PHP XML Reader title](https://raw.githubusercontent.com/kiwilan/php-http-pool/main/docs/banner.jpg)

[![php][php-version-src]][php-version-href]
[![version][version-src]][version-href]
[![downloads][downloads-src]][downloads-href]
[![license][license-src]][license-href]
[![tests][tests-src]][tests-href]
[![codecov][codecov-src]][codecov-href]

PHP package with easy-to-use [`GuzzleHttp`](https://docs.guzzlephp.org/en/stable/quickstart.html) pool wrapper, works with `GuzzleHttp\Pool` and `GuzzleHttp\Client` to make concurrent requests.

> [!NOTE]\
> I love `GuzzleHttp\Pool`, but I would to build a wrapper to make it easier to use and Laravel `Http\Pool` is cool but not flexible enough for me. So `HttpPool` allow you to send an `array` or a `Collection` of requests and get a `Collection<mixed, HttpPoolResponse>` of with all `GuzzleHttp` features and more.
>
> Built to be more flexible that Laravel [`Http`](https://laravel.com/docs/10.x/http-client#customizing-concurrent-requests) Pool, if Laravel Pool is perfect for you, keep using it.

## Features

-   🚚 Works with very big pool of requests: requests chunked to avoid memory peak
-   🗂️ Keep identifier of each request: easy to put response into original item (in case of `Collection` of `Model` with Laravel, for example)
-   📦 `HttpPoolResponse` wrapper with some features to improve DX: original ID, body, metadata...
-   🏡 Keep original `GuzzleHttp` response in `HttpPoolResponse`: you're in home
-   🚨 Allow handle memory peak: if you have a lot of requests
-   🗃️ Works with simple arrays, with associative arrays, with array of objects, with Laravel [`Collection`](https://laravel.com/docs/10.x/collections): just define where to get identifier and URL
-   💬 Optional console output: you can disable it if you don't want to see progress
-   🚀 Works with any PHP frameworks, `Illuminate\Support\Collection` is a dependency but you can use it without Laravel, `toArray()` method is available after pool execution if you don't want to use `Collection`

## Installation

You can install the package via composer:

```bash
composer require kiwilan/php-http-pool
```

## Usage

### Input

When you want to use `HttpPool`, you have to pass an input, it could be: a simple array, an associative array, a Laravel `Collection` or an array of objects.

#### With simple array

```php
use Kiwilan\HttpPool\HttpPool;

// Key is the identifier, value is the URL
// Array could be associative or not
$urls = [
  2 => 'https://jsonplaceholder.typicode.com/posts',
  5 => 'https://jsonplaceholder.typicode.com/comments',
  10 => 'https://jsonplaceholder.typicode.com/albums',
  16 => 'https://jsonplaceholder.typicode.com/photos',
  24 => 'https://jsonplaceholder.typicode.com/todos',
];

// Create a pool with an array of URLs and some options
$pool = HttpPool::make($urls)
  ->setMaxCurlHandles(100)
  ->setMaxRedirects(10)
  ->setTimeout(30)
  ->setConcurrencyMaximum(5)
  ->setPoolLimit(250)
  ->setHeaders([
    'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
  ])
;

// Get original requests converted for `HttpPool`
$requests = $pool->getRequests();
$requestCount = $pool->getRequestCount();

// Execute pool
$res = $pool->execute();

// Get responses
$responses = $res->getResponses();

// Get responses as array
$responsesArray = $res->toArray();

// Get only fullfilled responses
$fullfilled = $res->getFullfilledResponses();

// Get only rejected responses
$rejected = $res->getRejectedResponses();

// Counts
$fullfilledCount = $res->getFullfilledCount();
$rejectedCount = $res->getRejectedCount();

// Get execution time
$executionTime = $res->getExecutionTime();

// Get pool instance
$pool = $res->getPool();
```

#### Associative array

> [!WARNING]\
> Identifier and URL have to not be nested.

```php
use Kiwilan\HttpPool\HttpPool;

$urls = [
  [
      'uuid' => 100,
      'name' => 'posts',
      'api' => 'https://jsonplaceholder.typicode.com/posts',
  ],
  [
      'uuid' => 125,
      'name' => 'comments',
      'api' => 'https://jsonplaceholder.typicode.com/comments',
  ],
];

$res = HttpPool::make($urls)
  ->setIdentifierKey('uuid') // Default is 'id'
  ->setUrlKey('api') // Default is 'url'
  ->execute()
;

$first = $res->getResponses()->first(); // HttpPoolResponse
$first->getId(); // 100, 125
```

#### Laravel models

Take a Laravel model collection and send requests with `HttpPool`. Here `Book` is a Laravel model, we assume that `Book` has an `id` attribute and a `google_book_api` attribute.

```php
use App\Models\Book;
use Kiwilan\HttpPool\HttpPool;

$books = Book::all(); // `Illuminate\Support\Collection` of `Book`

$pool = HttpPool::make($books)
  ->setUrlKey('google_book_api') // Default is 'url'
  ->execute()
;

$first = $pool->getResponses()->first(); // HttpPoolResponse
$first->getId(); // 1, 2, 3... (Book ID)
```

#### Array of objects

Here we take an array of objects, we assume that each object has an `uuid` attribute and an `url` attribute. You can just define getters like `getUuid()` and `getUrl()` or you can use `public` attributes, it's up to you.

> [!WARNING]\
> If attributes are `private` or `protected`, you have to define getters with logic names: `getUuid()` and `getUrl()`. You can use `uuid()` and `url()` too as getters. But here, if you create a getter `getBookUuid()`, it will not work.

```php
use Kiwilan\HttpPool\HttpPool;

$urls = [
  new Book(
    uuid: 100,
    name: 'posts',
    url: 'https://jsonplaceholder.typicode.com/posts',
  ),
  new Book(
    uuid: 125,
    name: 'comments',
    url: 'https://jsonplaceholder.typicode.com/comments',
  ),
];

$res = HttpPool::make($urls)
  ->setIdentifierKey('uuid') // Default is 'id'
  ->execute()
;

$first = $res->getResponses()->first(); // HttpPoolResponse
$first->getId(); // 100, 125
```

### Execution

To execute pool, you can use `execute()` method.

```php
use Kiwilan\HttpPool\HttpPool;

$pool = HttpPool::make($urls);
$res = $pool->execute();
```

`execute()` method returns a `HttpPoolFullfilled` object. You can get pool with `getPool()` method.

```php
use Kiwilan\HttpPool\HttpPool;

$pool = HttpPool::make($urls);
$res = $pool->execute();

$pool = $res->getPool();
```

In `HttpPoolFullfilled` object, you can get responses and more features. All methods `getResponses()`,`getFullfilled()`, `getRejected()` are `Illuminate\Support\Collection` of `HttpPoolResponse`.

```php
use Kiwilan\HttpPool\HttpPool;

$pool = HttpPool::make($urls);
$res = $pool->execute();

// Get all responses (fullfilled and rejected)
$responses = $res->getResponses();

// Get only fullfilled responses
$fullfilled = $res->getFullfilled();

// Get only rejected responses
$rejected = $res->getRejected();

// Get responses count
$responsesCount = $res->getResponsesCount();

// Get fullfilled responses count
$fullfilledCount = $res->getFullfilledCount();

// Get rejected responses count
$rejectedCount = $res->getRejectedCount();

// Get execution time
$executionTime = $res->getExecutionTime();

// Get if pool is failed
$isFailed = $res->isFailed();

// Get errors
$errors = $res->getErrors();
```

### Errors

To handle errors, you can just use `HttpPool::make()` method and errors will throw exceptions. But if you want to prevent errors, you can use `throwErrors` param.

```php
use Kiwilan\HttpPool\HttpPool;

$pool = HttpPool::make($urls, throwErrors: false);
```

All errors can be found in `getErrors()` method, after pool execution.

```php
$res = $pool->execute();
$isFailed = $res->isFailed();
$errors = $res->getErrors();
```

### Response

After pool execution, you can get responses with `getResponses()` method. It returns a `Collection` of `HttpPoolResponse`.

> [!NOTE]\
> The first item of `getResponses` could not be the first request you sent. It depends of the response time of each request. But you can retrieve the original request with `getMetadata()->getRequest()` method, the best way to find parent is to define an ID, that you could retrieve it with `getId()` method.

```php
$responses = $res->getResponses();
$first = $responses->first(); // HttpPoolResponse

$first->getId(); // Get original ID
$first->getMetadata(); // Get HttpPoolResponseMetadata
$first->getGuzzle(); // Get original GuzzleHttp\Psr7\Response
$first->getBody(); // Get HttpPoolResponseBody
$first->isSuccess(); // Get if response is success
$first->isBodyAvailable(); // Get if response body exists
```

### Metadata

`HttpPoolResponse` has a `HttpPoolResponseMetadata` attribute, it contains some useful data. Here `$first` is a `HttpPoolResponse`.

```php
$metadata = $first->getMetadata();

$statusCode = $metadata->getStatusCode(); // 200, 404, 500...
$status = $metadata->getStatus(); // Guzzle pool status: fullfilled, rejected
$reason = $metadata->getReason(); // OK, Not Found, Internal Server Error...
$isSuccess = $metadata->isSuccess(); // 200 <= $statusCode < 300
$isFailed = $metadata->isFailed(); // status code is not success
$isJson = $metadata->isJson(); // is a valid JSON
$isXml = $metadata->isXml(); // is a valid XML
$server = $metadata->getServer(); // Server header
$date = $metadata->getDate(); // Date header
$contentType = $metadata->getContentType(); // Content-Type header
$request = $metadata->getRequest(); // Original request
$headers = $metadata->getHeaders(); // Original headers as array<string, string>
$header = $metadata->getHeader('Content-Type'); // Extract header (safe method)
```

### Body

`HttpPoolResponseBody` is a wrapper of `GuzzleHttp\Psr7\Stream` with some useful methods. Here `$first` is a `HttpPoolResponse`.

```php
$body = $first->getBody();

$isExists = $body->isExists(); // Get if body exists
$contents = $body->getContents(); // Get body contents
$json = $body->getJson(); // Get body as JSON
$xml = $body->getXml(); // Get body as XML
$isBinary = $body->isBinary(); // Get if body is binary
$isJson = $body->isJson(); // Get if body is a valid JSON
$isXml = $body->isXml(); // Get if body is a valid XML
$isString = $body->isString(); // Get if body is a string
$toArray = $body->toArray(); // Get body as array
```

### Advanced

You can use some advanced options to customize your pool.

Use URL as identifier to replace ID.

```php
HttpPool::make($urls)
  ->setUrlAsIdentifier();
```

Enable console output.

```php
HttpPool::make($urls)
  ->allowPrintConsole();
```

#### Memory peak

Handle memory peak is optional, but if you have a lot of requests, you can use `allowMemoryPeak` to avoid memory peak. New memory peak will be set inside `execute()` method.

Memory peak is set to `2G` by default, you can change it with second param.

```php
HttpPool::make($urls)
  ->allowMemoryPeak('2G');
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
-   [Symfony](https://symfony.com/) for `symfony/console`
-   [Spatie](https://github.com/spatie/package-skeleton-php) for the package skeleton
-   [Ewilan Rivière](https://github.com/kiwilan)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[<img src="https://user-images.githubusercontent.com/48261459/201463225-0a5a084e-df15-4b11-b1d2-40fafd3555cf.svg" height="120rem" width="100%" />](https://github.com/kiwilan)

[version-src]: https://img.shields.io/packagist/v/kiwilan/php-http-pool.svg?style=flat&colorA=18181B&colorB=777BB4
[version-href]: https://packagist.org/packages/kiwilan/php-http-pool
[php-version-src]: https://img.shields.io/static/v1?style=flat&label=PHP&message=v8.1&color=777BB4&logo=php&logoColor=ffffff&labelColor=18181b
[php-version-href]: https://www.php.net/
[downloads-src]: https://img.shields.io/packagist/dt/kiwilan/php-http-pool.svg?style=flat&colorA=18181B&colorB=777BB4
[downloads-href]: https://packagist.org/packages/kiwilan/php-http-pool
[license-src]: https://img.shields.io/github/license/kiwilan/php-http-pool.svg?style=flat&colorA=18181B&colorB=777BB4
[license-href]: https://github.com/kiwilan/php-http-pool/blob/main/README.md
[tests-src]: https://img.shields.io/github/actions/workflow/status/kiwilan/php-http-pool/run-tests.yml?branch=main&label=tests&style=flat&colorA=18181B
[tests-href]: https://packagist.org/packages/kiwilan/php-http-pool
[codecov-src]: https://img.shields.io/codecov/c/gh/kiwilan/php-http-pool/main?style=flat&colorA=18181B&colorB=777BB4
[codecov-href]: https://codecov.io/gh/kiwilan/php-http-pool
