<?php

use Kiwilan\HttpPool\HttpPool;

it('can convert all requests', function () {
    $types = [
        'urls' => [
            'data' => urls,
            'properties' => [],
        ],
        'api_urls' => [
            'data' => api_urls,
            'properties' => [],
        ],
        'api_urls_with_keys' => [
            'data' => api_urls_with_keys,
            'properties' => [],
        ],
        'fake_urls' => [
            'data' => fake_urls,
            'properties' => [],
        ],
        'objectUrls' => [
            'data' => objectUrls(),
            'properties' => ['id' => 'uuid', 'url' => 'api'],
        ],
        'collectionUrls' => [
            'data' => collectionUrls(),
            'properties' => ['id' => 'uuid', 'url' => 'api'],
        ],
        'collectionModelsUrls' => [
            'data' => collectionModelsUrls(),
            'properties' => [],
        ],
        'podcasts' => [
            'data' => podcasts,
            'properties' => [],
        ],
        'collectionClassUrls' => [
            'data' => collectionClassUrls(),
            'properties' => ['id' => 'uuid', 'url' => 'endpoint'],
        ],
    ];

    foreach ($types as $name => $type) {
        $pool = HttpPool::make($type['data']);

        if (! empty($type['properties'])) {
            $id = $type['properties']['id'];
            $url = $type['properties']['url'];

            $pool->setIdentifierKey($id);
            $pool->setUrlKey($url);
        }

        foreach ($pool->getRequests() as $key => $request) {
            expect($request->id)->toBe($key);
            expect($request->url)->toBeString();
        }
    }
});
