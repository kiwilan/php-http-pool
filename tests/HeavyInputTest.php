<?php

use Kiwilan\HttpPool\HttpPool;

it('can use heavy input', function () {
    $authors = getJson(authors);
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
        ->setPoolLimit(100)
        ->allowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(244);
});

it('can use very heavy input', function () {
    $books = getJson(books_title);

    $pool = [];
    foreach ($books as $key => $book) {
        $pool[$key] = [
            'name' => $book,
            'wikipedia' => wikipediaQuery($book),
        ];
    }

    $pool = HttpPool::make($pool)
        ->setUrlKey('wikipedia')
        ->allowMemoryPeak()
        ->setPoolLimit(500)
        ->allowPrintConsole();
    $pool = $pool->execute();

    expect($pool->getFullfilledCount())->toBe(1639);
});
