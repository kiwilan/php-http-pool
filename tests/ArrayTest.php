<?php

use Kiwilan\HttpPool\Http\HttpPoolResponse;
use Kiwilan\HttpPool\HttpPool;

it('can handle urls', function () {
    $urls = urls;

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();
    expect($item instanceof HttpPoolResponse)->toBeTrue();
    expect($item->isSuccess())->toBeTrue();
    expect($item->getBody()->isJson())->toBeFalse();
});

it('can handle api urls', function () {
    $urls = api_urls;

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $requests = $pool->getRequests();
    $item = $requests->first();

    expect($item->id)->toBe(0);

    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);
});

it('can handle api urls with keys', function () {
    $urls = api_urls_with_keys;

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $requests = $pool->getRequests();
    $item = $requests->first();

    expect($item->id)->toBe(100);

    $pool = $pool->execute();
    $item = $pool->getResponses()->first();
    expect($item->getId())->toBeIn([100, 125, 150, 175, 200]);
});

it('can handle fake urls', function () {
    $urls = fake_urls;

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(0);
    expect($pool->getRejectedCount())->toBe(5);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();
    expect($item instanceof HttpPoolResponse)->toBeTrue();
    expect($item->isSuccess())->toBeFalse();
});

it('can use objects', function () {
    $urls = objectUrls();

    $pool = HttpPool::make($urls)
        ->setIdentifierKey('uuid')
        ->setUrlKey('api')
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();

    expect($item->getId())->toBeIn([100, 125, 150, 175, 200]);
});

it('can use collection', function () {
    $urls = collectionUrls();

    $pool = HttpPool::make($urls)
        ->setIdentifierKey('uuid')
        ->setUrlKey('api')
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();

    expect($item->getId())->toBeIn([100, 125, 150, 175, 200]);
});

it('can use collection models', function () {
    $urls = collectionModelsUrls();

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();

    expect($item->getId())->toBeIn([100, 125, 150, 175, 200]);
});

it('can use xml body', function () {
    $urls = podcasts;

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();

    expect($item->getId())->toBeIn(['zqsd', '2hdp', 'apero', 'cosycorner', 'comicsoutcast']);
    expect($item->getBody()->isXml())->toBeTrue();
    expect($item->getBody()->getXml())->toBeInstanceOf(SimpleXMLElement::class);
});

it('can handle failed', function () {
    $urls = collectionFailed();

    $pool = HttpPool::make($urls)
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getRequestCount())->toBe(5);

    expect($pool->isFailed())->toBeTrue();
    expect($pool->getError())->toBeString();
});

it('can use collection classes', function () {
    $urls = collectionClassUrls();

    $pool = HttpPool::make($urls)
        ->setIdentifierKey('uuid')
        ->setUrlKey('endpoint')
        ->disallowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(5);
    expect($pool->getRejectedCount())->toBe(0);
    expect($pool->getRequestCount())->toBe(5);

    $item = $pool->getResponses()->first();

    expect($item->getId())->toBeIn([100, 125, 150, 175, 200]);
});
