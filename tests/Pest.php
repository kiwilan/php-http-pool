<?php

use Illuminate\Support\Collection;

define('authors', __DIR__.'/data/authors.json');
define('authors_list', __DIR__.'/data/authors-list.json');
define('authors_short', __DIR__.'/data/authors-short.json');
define('books_title', __DIR__.'/data/books-title.json');
define('books', __DIR__.'/data/books.json');

define('urls', [
    'https://www.youtube.com/',
    'https://www.nexusmods.com/',
    'https://github.com',
    'https://discord.com/',
    'https://spotify.com',
]);
define('fake_urls', [
    'https://ewilan-riviere-a.com',
    'https://ewilan-riviere-b.com',
    'https://ewilan-riviere-c.com',
    'https://ewilan-riviere-d.com',
    'https://ewilan-riviere-e.com',
]);
define('api_urls', [
    'https://jsonplaceholder.typicode.com/posts',
    'https://jsonplaceholder.typicode.com/comments',
    'https://jsonplaceholder.typicode.com/albums',
    'https://jsonplaceholder.typicode.com/photos',
    'https://jsonplaceholder.typicode.com/todos',
]);

define('api_urls_with_keys', [
    100 => 'https://jsonplaceholder.typicode.com/posts',
    125 => 'https://jsonplaceholder.typicode.com/comments',
    150 => 'https://jsonplaceholder.typicode.com/albums',
    175 => 'https://jsonplaceholder.typicode.com/photos',
    200 => 'https://jsonplaceholder.typicode.com/todos',
]);

define('podcasts', [
    'zqsd' => 'http://www.zqsd.fr/zqsd.xml',
    '2hdp' => 'https://2hdp.fr/2HDP.xml',
    'apero' => 'http://feeds.feedburner.com/LaperoDuCaptain',
    'cosycorner' => 'https://podcloud.fr/podcast/le-cosy-corner',
    'comicsoutcast' => 'https://soute.vaisseauhypersensas.fr/flux/comicsoutcast.xml',
]);

function getJson(string $path): array
{
    return json_decode(file_get_contents($path), true);
}

function wikipediaQuery(string $queryString): string
{
    $queryString = urlencode($queryString);

    // generator search images: https://commons.wikimedia.org/w/api.php?action=query&generator=search&gsrsearch=Jul%20Maroh&gsrprop=snippet&prop=imageinfo&iiprop=url&rawcontinue&gsrnamespace=6&format=json
    // generator search: https://en.wikipedia.org/w/api.php?action=query&generator=search&gsrsearch=Baxter%20Stephen&prop=info|extracts|pageimages&format=json
    // current search: https://fr.wikipedia.org/w/api.php?action=query&list=search&srsearch=intitle:Les%20Annales%20du%20Disque-Monde&format=json
    $baseURL = 'https://en.wikipedia.org/w/api.php?';
    $queries = [
        'action' => 'query',
        'list' => 'search',
        'srsearch' => "intitle:{$queryString}",
        'format' => 'json',
    ];

    return $baseURL.http_build_query($queries);

}

function wikipediaPageId(string $pageId): string
{
    // current search: http://fr.wikipedia.org/w/api.php?action=query&prop=info&pageids=1340228&inprop=url&format=json&prop=info|extracts|pageimages&pithumbsize=512
    $url = 'http://en.wikipedia.org/w/api.php?';
    $queries = [
        'action' => 'query',
        'pageids' => $pageId,
        'inprop' => 'url',
        'format' => 'json',
        'prop' => 'info|extracts|pageimages',
        'pithumbsize' => 512,
    ];

    return $url.http_build_query($queries);
}

/**
 * @return array<stdClass>
 */
function objectUrls(): array
{
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
        [
            'uuid' => 150,
            'name' => 'albums',
            'api' => 'https://jsonplaceholder.typicode.com/albums',
        ],
        [
            'uuid' => 175,
            'name' => 'photos',
            'api' => 'https://jsonplaceholder.typicode.com/photos',
        ],
        [
            'uuid' => 200,
            'name' => 'todos',
            'api' => 'https://jsonplaceholder.typicode.com/todos',
        ],
    ];

    $temp = [];
    foreach ($urls as $object) {
        $obj = new stdClass();
        $obj->uuid = $object['uuid'];
        $obj->name = $object['name'];
        $obj->api = $object['api'];

        $temp[$obj->uuid] = $obj;
    }

    return $temp;
}

/**
 * @return Collection<int, stdClass>
 */
function collectionUrls(): Collection
{
    $objects = [
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
        [
            'uuid' => 150,
            'name' => 'albums',
            'api' => 'https://jsonplaceholder.typicode.com/albums',
        ],
        [
            'uuid' => 175,
            'name' => 'photos',
            'api' => 'https://jsonplaceholder.typicode.com/photos',
        ],
        [
            'uuid' => 200,
            'name' => 'todos',
            'api' => 'https://jsonplaceholder.typicode.com/todos',
        ],
    ];

    $temp = collect([]);
    foreach ($objects as $object) {
        $obj = new stdClass();
        $obj->id = $object['uuid'];
        $obj->name = $object['name'];
        $obj->api = $object['api'];

        $temp->put($obj->id, $obj);
    }

    return $temp;
}

/**
 * @return Collection<int, stdClass>
 */
function collectionModelsUrls(): Collection
{
    $objects = [
        [
            'id' => 100,
            'name' => 'posts',
            'url' => 'https://jsonplaceholder.typicode.com/posts',
        ],
        [
            'id' => 125,
            'name' => 'comments',
            'url' => 'https://jsonplaceholder.typicode.com/comments',
        ],
        [
            'id' => 150,
            'name' => 'albums',
            'url' => 'https://jsonplaceholder.typicode.com/albums',
        ],
        [
            'id' => 175,
            'name' => 'photos',
            'url' => 'https://jsonplaceholder.typicode.com/photos',
        ],
        [
            'id' => 200,
            'name' => 'todos',
            'url' => 'https://jsonplaceholder.typicode.com/todos',
        ],
    ];

    $temp = collect([]);
    foreach ($objects as $object) {
        $obj = new stdClass();
        $obj->id = $object['id'];
        $obj->name = $object['name'];
        $obj->url = $object['url'];

        $temp->put($obj->id, $obj);
    }

    return $temp;
}

/**
 * @return Collection<int, stdClass>
 */
function collectionFailed(): Collection
{
    $objects = [
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
        [
            'uuid' => 150,
            'name' => 'albums',
            'api' => 'https://jsonplaceholder.typicode.com/albums',
        ],
        [
            'uuid' => 175,
            'name' => 'photos',
            'api' => 'https://jsonplaceholder.typicode.com/photos',
        ],
        [
            'uuid' => 200,
            'name' => 'todos',
            'api' => 'https://jsonplaceholder.typicode.com/todos',
        ],
    ];

    $temp = collect([]);
    foreach ($objects as $object) {
        $obj = new stdClass();
        $obj->id = $object['uuid'];
        $obj->name = $object['name'];
        $obj->api = $object['api'];

        $temp->put($obj->id, $obj);
    }

    return $temp;
}

/**
 * @return Collection<int, stdClass>
 */
function collectionClassUrls(): Collection
{
    $objects = [
        [
            'id' => 100,
            'name' => 'posts',
            'url' => 'https://jsonplaceholder.typicode.com/posts',
        ],
        [
            'id' => 125,
            'name' => 'comments',
            'url' => 'https://jsonplaceholder.typicode.com/comments',
        ],
        [
            'id' => 150,
            'name' => 'albums',
            'url' => 'https://jsonplaceholder.typicode.com/albums',
        ],
        [
            'id' => 175,
            'name' => 'photos',
            'url' => 'https://jsonplaceholder.typicode.com/photos',
        ],
        [
            'id' => 200,
            'name' => 'todos',
            'url' => 'https://jsonplaceholder.typicode.com/todos',
        ],
    ];

    $books = collect([]);
    foreach ($objects as $object) {
        $book = new Book(
            $object['id'],
            $object['name'],
            $object['url'],
        );

        $books->put($book->getUuid(), $book);
    }

    return $books;
}

class Book
{
    public function __construct(
        protected int $uuid,
        protected string $title,
        protected string $endpoint,
    ) {
    }

    public function getUuid(): int
    {
        return $this->uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }
}
