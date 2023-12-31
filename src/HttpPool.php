<?php

namespace Kiwilan\HttpPool;

use Illuminate\Support\Collection;
use Kiwilan\HttpPool\Request\HttpPoolRequest;
use Kiwilan\HttpPool\Request\HttpPoolRequestItem;
use Kiwilan\HttpPool\Response\HttpPoolResponse;
use ReflectionProperty;
use Throwable;

class HttpPool
{
    /**
     * @param  Collection<mixed,HttpPoolRequestItem>  $requests
     */
    protected function __construct(
        protected iterable $requestsOrigin,
        protected int $requestCount,
        protected HttpPoolOptions $options,
        protected Collection $requests,
        //
        protected string $identifierKey = 'id',
        protected string $urlKey = 'url',
        protected bool $urlAsIdentifier = false,
        //
        protected bool $isFailed = false,
        protected ?array $errors = null,
        //
        protected bool $isAllowThrowErrors = true,
        protected bool $isAllowMemoryPeak = false,
        protected string $memoryMaximum = '2G',
    ) {
    }

    /**
     * Create HttpPool instance.
     *
     * @param  iterable  $requests Can be `string[]`, `mixed[]`, `array<mixed, mixed>`, `array`, `Collection`, `Collection<int,object>`
     */
    public static function make(iterable $requests, bool $throwErrors = true): self
    {
        $self = new self(
            requestsOrigin: $requests,
            requestCount: count($requests),
            options: new HttpPoolOptions(),
            requests: collect([]),
            isAllowThrowErrors: $throwErrors,
        );

        $self->requests = $self->transformRequests($requests);

        return $self;
    }

    /**
     * Set option max curl handles, default is `100`.
     */
    public function setMaxCurlHandles(int $maximum = 100): self
    {
        $this->options->maxCurlHandles = $maximum;

        return $this;
    }

    /**
     * Set option max redirects, default is `10`.
     */
    public function setMaxRedirects(int $maximum = 10): self
    {
        $this->options->maxRedirects = $maximum;

        return $this;
    }

    /**
     * Set option timeout, default is `30`.
     */
    public function setTimeout(int $timeout = 30): self
    {
        $this->options->timeout = $timeout;

        return $this;
    }

    /**
     * Set option concurrency, default is `5`.
     */
    public function setConcurrencyMaximum(int $maximum = 5): self
    {
        $this->options->concurrencyMaximum = $maximum;

        return $this;
    }

    /**
     * Set option Pool limit, default is `250`.
     */
    public function setPoolLimit(int $limit = 250): self
    {
        $this->options->poolLimit = $limit;

        return $this;
    }

    /**
     * Set option headers for all requests, default is `[]`.
     */
    public function setHeaders(array $headers = []): self
    {
        $this->options->headers = $headers;

        return $this;
    }

    /**
     * Set model attribute with id, default is `id`.
     */
    public function setIdentifierKey(string $field = 'id'): self
    {
        $this->identifierKey = $field;
        $this->requests = $this->transformRequests($this->requestsOrigin);

        return $this;
    }

    /**
     * Set model attribute with url, default is `url`.
     */
    public function setUrlKey(string $field = 'url'): self
    {
        $this->urlKey = $field;
        $this->requests = $this->transformRequests($this->requestsOrigin);

        return $this;
    }

    /**
     * Set `url` attribute as identifier.
     */
    public function setUrlAsIdentifier(): self
    {
        $this->urlAsIdentifier = true;
        $this->requests = $this->transformRequests($this->requestsOrigin);

        return $this;
    }

    /**
     * Allow print console, default is `false`.
     */
    public function allowPrintConsole(): self
    {
        $this->options->allowPrintConsole = true;

        return $this;
    }

    /**
     * WARNING: This option can be dangerous. Use it carefully.
     *
     * Allow memory peak, default is `false`.
     *
     * Default maximum memory is `2G`.
     */
    public function allowMemoryPeak(string $memoryMaximum = '2G'): self
    {
        $this->isAllowMemoryPeak = true;
        $this->memoryMaximum = $memoryMaximum;

        return $this;
    }

    /**
     * Get requests.
     *
     * @return Collection<mixed,HttpPoolRequestItem>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    /**
     * Count of all requests.
     */
    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    /**
     * Get Guzzle options.
     */
    public function getOptions(): HttpPoolOptions
    {
        return $this->options;
    }

    /**
     * Check if all requests are failed.
     */
    public function isFailed(): bool
    {
        return $this->isFailed;
    }

    /**
     * Get error messages.
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Execute requests with Pool.
     */
    public function execute(): HttpPoolFullfilled
    {
        if ($this->isAllowMemoryPeak) {
            ini_set('memory_limit', "{$this->memoryMaximum}");
        }

        $urls = [];
        foreach ($this->requests as $request) {
            $urls[] = $request->url;
        }

        if ($this->requests->isEmpty()) {
            $this->error("No requests to execute, input array can be empty or doesn't have `{$this->urlKey}` key", 'execute()');

            return $this;
        }

        $responses = collect([]);
        $executionTime = null;

        try {
            $request = HttpPoolRequest::make($this->requests, $this->options);
            $executionTime = $request->getExecutionTime();

            if ($request->getFullfilledCount() === 0) {
                $this->isFailed = true;
                $this->errors[] = 'All requests are failed';
            }

            $responses = $this->toHttpPoolResponse($request->getResponses());

            if ($request->getDiff()) {
                $this->errors[] = "Some requests are not executed because URL is not valid: {$request->getDiff()}";
            }
        } catch (\Throwable $th) {
            $this->error('Pool execution failed', 'execute()', $th);
        }

        return HttpPoolFullfilled::make(
            pool: $this,
            responses: $responses,
            executionTime: $executionTime,
        );
    }

    /**
     * Transform GuzzleHttp Response to HttpPoolResponse.
     *
     * @param  Collection<int,?Response>  $responses
     * @return Collection<string,HttpPoolResponse>
     */
    private function toHttpPoolResponse(Collection $responses): Collection
    {
        /** @var Collection<string,HttpPoolResponse> */
        $list = collect([]);

        foreach ($responses as $id => $response) {
            $id = $response->getHeader('ID')[0];
            $response = HttpPoolResponse::make($id, $response);
            $list->put($id, $response);
        }

        return $list;
    }

    /**
     * Transform Collection input to Collection of objects with `id` and `url` properties.
     *
     * @param  Collection<int, mixed>|string[]  $iterable
     * @return Collection<mixed, HttpPoolRequestItem>
     */
    private function transformRequests(mixed $iterable): Collection
    {
        if (! is_iterable($iterable)) {
            $this->error('`make(iterable $requests)` => `$requests` must be an iterable', 'transformRequests()');
        }

        /** @var Collection<mixed,HttpPoolRequestItem> */
        $requests = collect([]);

        foreach ($iterable as $key => $item) {
            $id = $this->findKey($this->identifierKey, $item, $key);

            if ($this->urlAsIdentifier) {
                $id = $item;
            }

            $url = $this->findKey($this->urlKey, $item);

            $request = new HttpPoolRequestItem(
                id: $id ?? $key,
                url: $url,
            );

            if ($request->url === null && is_string($item)) {
                $request->url = $item;
            }

            $requests->put($key, $request);
        }

        return $requests;
    }

    private function findKey(string $key, mixed $item, mixed $default = null): mixed
    {
        if (is_array($item) && array_key_exists($key, $item)) {
            return $item[$key];
        }

        if (is_object($item)) {
            if (property_exists($item, $key)) {
                $rp = new ReflectionProperty($item, $key);

                if ($rp->isPublic()) {
                    return $item->{$key};
                }
            }

            if (method_exists($item, $key)) {
                return $item->{$key}();
            }

            if (method_exists($item, 'get'.ucfirst($key))) {
                return $item->{'get'.ucfirst($key)}();
            }
        }

        if ($default) {
            return $default;
        }

        return null;
    }

    private function error(string $message, string $method, ?Throwable $throwable = null): void
    {
        $message = "{$message}. Method: {$method}";

        if ($throwable) {
            $throwableMessage = "{$throwable->getMessage()}, line {$throwable->getLine()} of {$throwable->getFile()}. {$throwable->getTraceAsString()}";
            $message = "{$message}. Error: {$throwableMessage}";
        }

        $this->isFailed = true;
        $this->errors[] = $message;
        error_log($message);

        if ($this->isAllowThrowErrors) {
            throw new \Exception($message);
        }
    }
}
