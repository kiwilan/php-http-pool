<?php

namespace Kiwilan\HttpPool;

use Illuminate\Support\Collection;
use Kiwilan\HttpPool\Http\HttpPoolOptions;
use Kiwilan\HttpPool\Http\HttpPoolRequest;
use Kiwilan\HttpPool\Http\HttpPoolResponse;
use stdClass;

class HttpPool
{
    /**
     * @param  Collection<string,mixed>  $requests
     * @param  Collection<string,HttpPoolResponse>  $responses
     * @param  Collection<string,HttpPoolResponse>  $rejected
     * @param  Collection<string,HttpPoolResponse>  $fullfilled
     */
    protected function __construct(
        protected iterable $requestsOrigin,
        protected int $requestCount,
        protected HttpPoolOptions $options,
        //
        protected Collection $requests,
        protected Collection $responses,
        protected Collection $rejected,
        protected Collection $fullfilled,
        //
        protected int $fullfilledCount = 0,
        protected int $rejectedCount = 0,
        //
        public string $identifierKey = 'id',
        public string $urlKey = 'url',
        public bool $urlAsIdentifier = false,
        public bool $isExecuted = false,
        public bool $isRaw = false,
        public bool $isFailed = true,
        public ?string $error = null,
        protected ?float $executionTime = null,
        protected bool $isAllowMemoryPeak = false,
        protected string $maximumMemory = '10G',
    ) {
    }

    /**
     * Create HttpPool instance.
     *
     * @param  iterable  $requests Can be `string[]`, `mixed[]`, `array<mixed, mixed>`, `array`, `Collection`, `Collection<int,object>`
     */
    public static function make(iterable $requests): self
    {
        $self = new self(
            requestsOrigin: $requests,
            requestCount: count($requests),
            options: new HttpPoolOptions(),
            responses: collect([]),
            rejected: collect([]),
            fullfilled: collect([]),
            requests: collect([]),
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
     * Disallow print console, default is `true`.
     */
    public function disallowPrintConsole(): self
    {
        $this->options->allowPrintConsole = false;

        return $this;
    }

    /**
     * WARNING: This option can be dangerous.
     * Allow memory peak, default is `false`.
     *
     * If you set very high concurrency or requests with big responses, you can set this option.
     * Default maximum memory is `10G`, you can set it with `setMaximumMemory` method.
     */
    public function allowMemoryPeak(): self
    {
        $this->isAllowMemoryPeak = true;

        return $this;
    }

    /**
     * WARNING: This option can be dangerous.
     * You have to set `allowMemoryPeak` option to `true` before using this option.
     *
     * Set maximum memory, default is `10G`.
     *
     * If you set very high concurrency or requests with big responses, you can set this option.
     */
    public function setMaximumMemory(string $maximum = '10G'): self
    {
        $this->maximumMemory = $maximum;

        return $this;
    }

    /**
     * Get requests.
     *
     * @return Collection<string,mixed>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    /**
     * Get fullfilled responses.
     *
     * @return Collection<string,HttpPoolResponse>
     */
    public function getFullfilled(): Collection
    {
        return $this->fullfilled;
    }

    /**
     * Get rejected responses.
     *
     * @return Collection<string,HttpPoolResponse>
     */
    public function getRejected(): Collection
    {
        return $this->rejected;
    }

    /**
     * All responses, including rejected.
     *
     * @return Collection<string,HttpPoolResponse>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    /**
     * Count of fullfilled responses.
     */
    public function getFullfilledCount(): int
    {
        return $this->fullfilledCount;
    }

    /**
     * Count of rejected responses.
     */
    public function getRejectedCount(): int
    {
        return $this->rejectedCount;
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
     * Check if requests is a raw array.
     */
    public function isRaw(): bool
    {
        return $this->isRaw;
    }

    /**
     * Check if requests are executed.
     */
    public function isExecuted(): bool
    {
        return $this->isExecuted;
    }

    /**
     * Check if all requests are failed.
     */
    public function isFailed(): bool
    {
        return $this->isFailed;
    }

    /**
     * Get error message.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get execution time.
     */
    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
    }

    /**
     * Execute requests with Pool.
     */
    public function execute(): self
    {
        $urls = $this->prepareRequests();
        if ($urls->isEmpty()) {
            $this->isExecuted = true;
            $this->isFailed = true;
            $this->error = "No requests to execute, input array can be empty or doesn't have `{$this->urlKey}` key";

            return $this;
        }

        try {
            if ($this->isAllowMemoryPeak) {
                ini_set('memory_limit', "{$this->maximumMemory}");
            }

            $pool = HttpPoolRequest::make($urls, $this->options);

            $this->responses = $this->toHttpPoolResponse($pool->getAll());

            $this->fullfilled = $this->responses->filter(fn (HttpPoolResponse $response) => $response->isSuccess());
            $this->fullfilledCount = $this->fullfilled->count();
            $this->rejected = $this->responses->filter(fn (HttpPoolResponse $response) => ! $response->isSuccess());
            $this->rejectedCount = $this->rejected->count();
            $this->isExecuted = true;
            $this->executionTime = $pool->getExecutionTime();

            if ($this->rejectedCount === 0) {
                ini_restore('memory_limit');
            }
        } catch (\Throwable $th) {
            throw new \Exception("Pool execution failed: {$th->getMessage()}");
        }

        return $this;
    }

    /**
     * Prepare requests.
     *
     * @return Collection<string,string>
     */
    private function prepareRequests(): Collection
    {
        /** @var Collection<string, string> */
        $urls = collect([]);

        foreach ($this->requests as $key => $item) {
            if (! $item) {
                continue;
            }

            $identifierKey = $this->identifierKey;
            $urlKey = $this->urlKey;

            if ($this->isRaw) {
                $identifierKey = 'id';
                $urlKey = 'url';
            }

            $identifier = null;

            if (method_exists($item, 'get'.ucfirst($identifierKey))) {
                $identifier = $item->{'get'.ucfirst($identifierKey)}();
            } elseif (method_exists($item, $identifierKey)) {
                $identifier = $item->{$identifierKey}();
            } elseif (property_exists($item, $identifierKey)) {
                $identifier = $item->{$identifierKey};
            }

            $url = null;

            if (method_exists($item, 'get'.ucfirst($urlKey))) {
                $url = $item->{'get'.ucfirst($urlKey)}();
            } elseif (method_exists($item, $urlKey)) {
                $url = $item->{$urlKey}();
            } elseif (property_exists($item, $urlKey)) {
                $url = $item->{$urlKey};
            }

            if (! $identifier) {
                $identifier = $key;
            }

            if ($url) {
                $urls->put($identifier, $url);
            }
        }

        return $urls;
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

    private function transformRequests(mixed $requests): mixed
    {
        if (! is_iterable($requests)) {
            throw new \Exception('`$requests` must be an iterable');
        }

        $parsed = null;

        if ($requests instanceof Collection && is_object($requests->first())) {
            $parsed = $requests;
        } else {
            $parsed = $this->transformArrauRequests($requests);
        }

        return $parsed;
    }

    /**
     * Transform Collection input to Collection of objects with `model_id` and `url` properties.
     *
     * @param  Collection<int, mixed>|string[]  $iterable
     * @return Collection<int, object>
     */
    private function transformArrauRequests(mixed $iterable): Collection
    {
        /** @var Collection<int,object> */
        $requests = collect([]);
        $this->isRaw = true;

        foreach ($iterable as $key => $item) {
            $id = $key;
            if (is_array($item) && array_key_exists($this->identifierKey, $item)) {
                $id = $item[$this->identifierKey];
            } elseif (is_object($item) && property_exists($item, $this->identifierKey)) {
                $id = $item->{$this->identifierKey};
            } elseif (is_string($item)) {
                if ($this->urlAsIdentifier) {
                    $id = $item;
                } else {
                    $id = $key;
                }
            }

            $url = null;
            if (is_array($item) && array_key_exists($this->urlKey, $item)) {
                $url = $item[$this->urlKey];
            } elseif (is_object($item) && property_exists($item, $this->urlKey)) {
                $url = $item->{$this->urlKey};
            } elseif (is_string($item)) {
                $url = $item;
            }

            $object = new stdClass();
            $object->id = $id ?? $key;
            $object->url = $url;

            if (! $object->url) {
                $object->url = $item;
            }

            $requests->put($key, $object);
        }

        return $requests;
    }
}
