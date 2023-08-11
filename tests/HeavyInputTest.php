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
    $fullfilled = $pool->execute();

    expect($fullfilled->getFullfilledCount())->toBe(244);
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
        ->setPoolLimit(500)
        ->allowPrintConsole();
    $fullfilled = $pool->execute();

    expect($fullfilled->getFullfilledCount())->toBe(1639);
});

it('can use memory peak handler', function () {
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
        ->setPoolLimit(500)
        ->allowMemoryPeak()
        ->allowPrintConsole();
    $fullfilled = $pool->execute();

    expect($fullfilled->getFullfilledCount())->toBe(1639);
});

it('can use array with empty url', function () {
    $authors = getJson(authors_list);

    $fullfilled = HttpPool::make($authors)
        ->allowMemoryPeak()
        ->allowPrintConsole()
        ->execute();

    expect($fullfilled->getFullfilledCount())->toBe(239);
});

it('can use array with no url', function () {
    $authors = getJson(authors_list);
    $temp = [];
    foreach ($authors as $author) {
        $class = new stdClass();
        $class->name = $author['title'];
        $class->url = null;
        $temp[] = $class;
    }

    $fullfilled = HttpPool::make($temp)
        ->allowMemoryPeak()
        ->allowPrintConsole()
        ->execute();

    expect($fullfilled->getFullfilledCount())->toBe(0);
});
