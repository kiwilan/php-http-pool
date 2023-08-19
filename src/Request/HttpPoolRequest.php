<?php

namespace Kiwilan\HttpPool\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Kiwilan\HttpPool\HttpPoolOptions;
use Kiwilan\HttpPool\Utils\PrintConsole;

/**
 * HttpPoolRequest to handle Guzzle Pool requests.
 */
class HttpPoolRequest
{
    /**
     * @param  Collection<string,HttpPoolRequestItem>  $requests
     * @param  Collection<string,Response>  $fullfilled
     * @param  Collection<string,Response>  $rejected
     * @param  Collection<string,Response>  $responses
     */
    protected function __construct(
        protected HttpPoolOptions $options,
        protected Collection $requests,
        protected Collection $responses,
        protected int $requestCount = 0,
        protected ?float $executionTime = null,
        protected int $diff = 0,
    ) {
    }

    /**
     * Create a HttpPoolRequest instance.
     *
     * @param  Collection<string,HttpPoolRequestItem>  $requests
     */
    public static function make(iterable $requests, HttpPoolOptions $options): self
    {
        $self = new self(
            options: $options,
            requests: collect([]),
            responses: collect([]),
        );

        $self->requests = $requests;
        $self->requestCount = count($requests);

        $self->responses = $self->execute($requests);
        $self->diff = $self->requestCount - $self->getFullfilledCount();

        $console = PrintConsole::make($self->options->allowPrintConsole);

        $color = $self->getRejectedCount() > 0 ? 'bright-red' : 'bright-green';
        $console->print("  {$self->getFullfilledCount()} requests fullfilled, {$self->getRejectedCount()} requests rejected.", $color);

        $fullCount = $self->getFullfilledCount() + $self->getRejectedCount();
        $diff = 0;
        if ($fullCount !== $self->requestCount) {
            $diff = $self->requestCount - $fullCount;
            $console->print("  On {$self->requestCount} requests, {$diff} requests cannot be executed because URL is not valid.", 'yellow');
        }

        $console->print("  Done in {$self->executionTime} seconds.");
        $console->newLine();

        return $self;
    }

    /**
     * @return Collection<string,Response>
     */
    public function getFullfilled(): Collection
    {
        return $this->responses->filter(fn (Response $response) => $response->getHeaderLine('status') === 'fullfilled');
    }

    public function getFullfilledCount(): int
    {
        return $this->responses->filter(fn (Response $response) => $response->getHeaderLine('status') === 'fullfilled')->count();
    }

    public function getRejectedCount(): int
    {
        return $this->responses->filter(fn (Response $response) => $response->getHeaderLine('status') === 'rejected')->count();
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    /**
     * Only rejected responses.
     *
     * @return Collection<string,Response>
     */
    public function getRejected(): Collection
    {
        return $this->responses->filter(fn (Response $response) => $response->getHeaderLine('status') === 'rejected');
    }

    /**
     * All responses.
     *
     * @return Collection<string,Response>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    /**
     * Get execution time.
     */
    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    /**
     * Get diff.
     */
    public function getDiff(): int
    {
        return $this->diff;
    }

    /**
     * Prepare requests to be executed.
     *
     * @param  Collection<int,HttpPoolRequestItem>  $urls
     * @return Collection<string,mixed>
     */
    private function execute(Collection $urls): Collection
    {
        $console = PrintConsole::make($this->options->allowPrintConsole);

        /**
         * Chunk by limit into arrays.
         */
        $urls_count = count($urls);

        /**
         * @var Collection<int,Collection<int,HttpPoolRequestItem>> $chunks
         */
        $chunks = $urls->chunk($this->options->poolLimit);

        $chunks_size = count($chunks);

        $start_time = microtime(true);

        if ($urls_count > 0) {
            $firstItem = $urls->first();
            $console->newLine();
            if ($firstItem->url) {
                $domain = parse_url($firstItem->url, PHP_URL_HOST);
                $console->print("  HttpPool {$domain} with async requests...", 'bright-blue');
            }
            $console->print("  Pool is limited to {$this->options->poolLimit} from options, {$urls_count} requests will be converted into {$chunks_size} chunks.");
        }

        $responses = collect([]);

        foreach ($chunks as $chunk_key => $chunk_items) {
            $chunk_items_count = count($chunk_items);
            $current_chunk = $chunk_key + 1;
            $console->print("  Execute {$chunk_items_count} requests from chunk {$current_chunk}/{$chunks_size}...");

            $res = $this->pool($chunk_items);
            $responses = $responses->merge($res);
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        $execution_time = number_format((float) $execution_time, 2, '.', '');
        $this->executionTime = $execution_time;

        return $responses;
    }

    /**
     * Execute requests with Guzzle pool.
     *
     * @param  Collection<int,HttpPoolRequestItem>  $urls
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

        foreach ($urls as $item) {
            if ($item->url) {
                $requests[$item->id] = new Request('GET', $item->url);
            }
        }

        /** @var Collection<int,?mixed> */
        $responses = collect([]);

        // Create GuzzleHttp pool.
        $pool = new Pool($client, $requests, [
            'concurrency' => $this->options->concurrencyMaximum,
            'fulfilled' => function (Response $response, $index) use ($responses, $urls) {
                $item = $urls->first(fn (HttpPoolRequestItem $item) => $item->id === $index);
                $response = $response->withHeader('origin', $item->url ?? null); // Add Origin header for URL
                $response = $response->withHeader('id', $index ?? null);
                $response = $response->withHeader('status', 'fullfilled');
                $responses->put($index, $response);
            },
            'rejected' => function (mixed $reason, $index) use ($responses, $urls) {
                $item = $urls->first(fn (HttpPoolRequestItem $item) => $item->id === $index);
                $type = json_encode([$reason, $index, $item->url]);
                $message = "HttpPool: one request rejected. {$type}";
                error_log($message);
                $response = new Response(
                    status: 500,
                    headers: [
                        'origin' => $item->url ?? null,
                        'id' => $index ?? null,
                        'status' => 'rejected',
                    ],
                    reason: $reason,
                );
                $responses->put($index, $response);
            },
        ]);

        // Execute pool.
        $pool->promise()->wait();

        return $responses;
    }
}
