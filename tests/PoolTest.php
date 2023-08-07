<?php

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Kiwilan\HttpPool\Http\HttpPoolResponse;
use Kiwilan\HttpPool\Http\HttpPoolResponseBody;
use Kiwilan\HttpPool\HttpPool;

it('can use options', function () {
    $urls = api_urls;

    $pool = HttpPool::make($urls)
        ->allowPrintConsole();
    $item = $pool->getRequests()->first();
    $options = $pool->getOptions();

    expect($item->id)->toBe(0);
    expect($options->poolLimit)->toBe(250);
    expect($options->maxCurlHandles)->toBe(100);
    expect($options->maxRedirects)->toBe(10);
    expect($options->timeout)->toBe(30);
    expect($options->concurrencyMaximum)->toBe(5);

    $pool = HttpPool::make($urls)
        ->setUrlAsIdentifier();
    $item = $pool->getRequests()->first();

    expect($item->id)->toBe('https://jsonplaceholder.typicode.com/posts');

    $pool = HttpPool::make($urls)
        ->setPoolLimit(100)
        ->setMaxCurlHandles(50)
        ->setMaxRedirects(25)
        ->setTimeout(60)
        ->setConcurrencyMaximum(10);
    $options = $pool->getOptions();

    expect($options->poolLimit)->toBe(100);
    expect($options->maxCurlHandles)->toBe(50);
    expect($options->maxRedirects)->toBe(25);
    expect($options->timeout)->toBe(60);
    expect($options->concurrencyMaximum)->toBe(10);
});

it('can use associative array', function () {
    $authors = getJson(authors_short);

    $pool = [];
    foreach ($authors as $key => $author) {
        $pool[$key] = [
            'name' => $author,
            'wikipedia' => wikipediaQuery($author),
        ];
    }

    $pool = HttpPool::make($pool)
        ->setIdentifierKey('name')
        ->setUrlKey('wikipedia')
        ->allowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    expect($pool->getFullfilled())->toBeInstanceOf(Collection::class);
    expect($pool->getFullfilled()->isNotEmpty())->toBeTrue();
    expect($pool->getRejected()->isNotEmpty())->toBeFalse();
    expect($pool->getResponses()->isNotEmpty())->toBeTrue();
    expect($pool->isRaw())->toBeTrue();
    expect($pool->isExecuted())->toBeTrue();
    expect($pool->getExecutionTime())->toBeFloat();

    $item = $pool->getResponses()->first();
    expect($item instanceof HttpPoolResponse)->toBeTrue();
    expect($item->isSuccess())->toBeTrue();
    expect($item->getMetadata()->isSuccess())->toBeTrue();
    expect($item->getMetadata()->isFailed())->toBeFalse();
    expect($item->getMetadata()->isJson())->toBeTrue();
    expect($item->getMetadata()->isXml())->toBeFalse();
    expect($item->getMetadata()->getStatusCode())->toBe(200);

    expect($item->getMetadata()->getReason())->toBe('OK');
    expect($item->getMetadata()->getServer())->toBeString();
    expect($item->getMetadata()->getDate())->toBeInstanceOf(DateTime::class);
    expect($item->getMetadata()->getContentType())->toBeString();
    expect($item->getMetadata()->getRequest())->toBeString();
    expect($item->getMetadata()->getHeaders())->toBeArray();
    expect($item->getMetadata()->getHeader('Origin'))->toBe($item->getMetadata()->getRequest());

    $body = $item->getBody();
    expect($body)->toBeInstanceOf(HttpPoolResponseBody::class);
    expect($body->isExists())->toBeTrue();
    expect($body->isJson())->toBeTrue();
    expect($body->isXml())->toBeFalse();
    expect($body->isString())->toBeFalse();
    expect($body->getContents())->toBeString();
    expect($body->getJson())->toBeObject();
    expect($body->getContents())->toBeString();
    expect($body->toArray())->toBeArray();

    expect($body->find('searchinfo'))->toBeArray();
    expect($body->find('key'))->toBeNull();

    expect($item->getGuzzle())->toBeInstanceOf(Response::class);
    expect($item->isBodyAvailable())->toBeTrue();
});
