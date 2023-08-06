<?php

namespace Kiwilan\HttpPool\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Kiwilan\HttpPool\Utils\PrintConsole;

/**
 * HttpPoolRequest to handle Guzzle Pool requests.
 */
class HttpPoolRequest
{
    /**
     * @param  Collection<string,string>  $requests
     * @param  Collection<string,Response>  $fullfilled
     * @param  Collection<string,Response>  $rejected
     * @param  Collection<string,Response>  $all
     */
    protected function __construct(
        protected HttpPoolOptions $options,
        protected Collection $requests,
        protected Collection $fullfilled,
        protected Collection $rejected,
        protected Collection $all,
        protected int $requestCount = 0,
        protected int $fullfilledCount = 0,
        protected int $rejectedCount = 0,
        protected ?float $executionTime = null,
    ) {
    }

    /**
     * Create a HttpPoolRequest instance.
     *
     * @param  Collection<string,string>  $requests
     */
    public static function make(iterable $requests, HttpPoolOptions $options): self
    {
        $self = new self(
            options: $options,
            requests: collect([]),
            fullfilled: collect([]),
            rejected: collect([]),
            all: collect([]),
        );

        $self->requests = $requests;
        $self->requestCount = count($requests);

        $res = $self->execute($requests);

        $self->fullfilled = $res->get('fullfilled');
        $self->rejected = $res->get('rejected');
        $self->all = $res->get('all');

        return $self;
    }

    /**
     * @return Collection<string,Response>
     */
    public function getFullfilled(): Collection
    {
        return $this->fullfilled;
    }

    public function getFullfilledCount(): int
    {
        return $this->fullfilledCount;
    }

    public function getRejectedCount(): int
    {
        return $this->rejectedCount;
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    /**
     * @return Collection<string,Response>
     */
    public function getRejected(): Collection
    {
        return $this->rejected;
    }

    /**
     * @return Collection<string,Response>
     */
    public function getAll(): Collection
    {
        return $this->all;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    /**
     * Execute requests with Guzzle Pool.
     *
     * @param  Collection<int,string>  $urls
     */
    private function execute(Collection $urls)
    {
        $console = PrintConsole::make($this->options->allowPrintConsole);

        /**
         * Chunk by limit into arrays.
         */
        $urls_count = count($urls);

        /**
         * @var Collection<int,Collection<int,string>> $chunks
         */
        $chunks = $urls->chunk($this->options->poolLimit);

        $chunks_size = count($chunks);

        $start_time = microtime(true);

        if ($urls_count > 0) {
            $firstUrl = $urls->first();
            $domain = parse_url($firstUrl, PHP_URL_HOST);
            $console->newLine();
            $console->print("  HttpService pool {$domain} with async requests...", 'bright-blue');

            // if (! $this->options->poolable) {
            //     $console->print('  Pool is disabled!', 'red');
            // }
            $console->print("  Pool is limited to {$this->options->poolLimit} from options, {$urls_count} requests will be converted into {$chunks_size} chunks.");
        }

        $fullfilled = collect([]);
        $rejected = collect([]);
        $all = collect([]);

        foreach ($chunks as $chunk_key => $chunk_urls) {
            $chunk_urls_count = count($chunk_urls);
            $current_chunk = $chunk_key + 1;
            $console->print("  Execute {$chunk_urls_count} requests from chunk {$current_chunk}/{$chunks_size}...");

            $res = $this->pool($chunk_urls);

            $fullfilled = $fullfilled->merge($res->get('fullfilled'));
            $rejected = $rejected->merge($res->get('rejected'));
            $all = $all->merge($res->get('all'));
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        $execution_time = number_format((float) $execution_time, 2, '.', '');
        $this->executionTime = $execution_time;

        $color = $this->rejectedCount > 0 ? 'bright-red' : 'bright-green';
        $console->print("  {$this->fullfilledCount} requests fullfilled, {$this->rejectedCount} requests rejected.", $color);
        $console->print("  Done in {$execution_time} seconds.");
        $console->newLine();

        return collect([
            'fullfilled' => $fullfilled,
            'rejected' => $rejected,
            'all' => $all,
        ]);
    }

    /**
     * Create and make `GET` requests from `$urls`.
     *
     * @param  Collection<int,string>  $urls
     * @return Collection<string,mixed>
     */
    private function pool(Collection $urls): Collection
    {
        // Need to have curl extension.
        if (extension_loaded('curl')) {
            $handler = HandlerStack::create(
                new CurlMultiHandler([
                    'handle_factory' => new CurlFactory($this->options->maxCurlHandles),
                    'select_timeout' => $this->options->timeout,
                ])
            );
        } else {
            $handler = HandlerStack::create();
        }

        // Create the client and turn off Exception throwing.
        $client = new Client([
            // No exceptions of 404, 500 etc.
            'http_errors' => false,
            'handler' => $handler,
            // Curl options, any CURLOPT_* option is available
            'curl' => [
                // CURLOPT_BINARYTRANSFER => true,
            ],
            RequestOptions::CONNECT_TIMEOUT => $this->options->timeout,
            // Allow redirects?
            // Set this to RequestOptions::ALLOW_REDIRECTS => false, to turn off.
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => $this->options->maxRedirects,        // allow at most 10 redirects.
                'strict' => true,      // use "strict" RFC compliant redirects.
                'track_redirects' => false,
            ],
        ]);

        // Prepare requests with `id` and `url`.
        $requests = [];

        foreach ($urls as $id => $url) {
            if ($url) {
                $requests[$id] = new Request('GET', $url);
            }
        }

        /** @var Collection<int,?Response> */
        $fullfilled = collect([]);

        /** @var Collection<int,?mixed> */
        $rejected = collect([]);

        // Create GuzzleHttp pool.
        $pool = new Pool($client, $requests, [
            'concurrency' => $this->options->concurrencyMaximum,
            'fulfilled' => function (Response $response, $index) use ($fullfilled, $urls) {
                $response = $response->withHeader('Origin', $urls[$index] ?? null); // Add Origin header for URL
                $response = $response->withHeader('ID', $index ?? null);
                $fullfilled->put($index, $response);

                $this->fullfilledCount++;
            },
            'rejected' => function (mixed $reason, $index) use ($fullfilled, $rejected, $urls) {
                $type = json_encode([$reason, $index, $urls[$index]]);
                $message = "HttpPool: one request rejected. {$type}";
                error_log($message);
                $response = new Response(
                    status: 500,
                    headers: [
                        'Origin' => $urls[$index] ?? null,
                        'ID' => $index ?? null,
                    ],
                    reason: $reason,
                );
                $fullfilled->put($index, $response);
                $rejected->put($index, $response);

                $this->rejectedCount++;
            },
        ]);

        // Execute pool.
        $pool->promise()->wait();

        $res = collect([]);
        $res->put('fullfilled', $fullfilled);
        $res->put('rejected', $rejected);
        $res->put('all', $fullfilled->merge($rejected));

        return $res;
    }
}
