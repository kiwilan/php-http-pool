<?php

namespace Kiwilan\HttpPool;

use Illuminate\Support\Collection;
use Kiwilan\HttpPool\Response\HttpPoolResponse;

class HttpPoolFullfilled
{
    /**
     * @param  Collection<mixed,HttpPoolResponse>  $responses
     * @param  Collection<mixed,HttpPoolResponse>  $rejected
     * @param  Collection<mixed,HttpPoolResponse>  $fullfilled
     */
    protected function __construct(
        protected HttpPool $pool,
        protected Collection $responses,
        protected ?float $executionTime = null,
        protected bool $isFailed = false,
        protected ?array $errors = null,
    ) {
    }

    public static function make(
        HttpPool $pool,
        Collection $responses,
        ?float $executionTime = null,
    ): self {
        return new self(
            pool: $pool,
            responses: $responses,
            executionTime: $executionTime,
            isFailed: $pool->isFailed(),
            errors: $pool->getErrors(),
        );
    }

    /**
     * Get pool instance.
     */
    public function getPool(): HttpPool
    {
        return $this->pool;
    }

    /**
     * All responses, including rejected.
     *
     * @return Collection<mixed,HttpPoolResponse>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    /**
     * Get fullfilled responses.
     *
     * @return Collection<mixed,HttpPoolResponse>
     */
    public function getFullfilled(): Collection
    {
        return $this->responses->filter(fn (HttpPoolResponse $response) => $response->isSuccess());
    }

    /**
     * Get rejected responses.
     *
     * @return Collection<mixed,HttpPoolResponse>
     */
    public function getRejected(): Collection
    {
        return $this->responses->filter(fn (HttpPoolResponse $response) => ! $response->isSuccess());
    }

    /**
     * Get responses count.
     */
    public function getResponsesCount(): int
    {
        return $this->responses->count();
    }

    /**
     * Count of fullfilled responses.
     */
    public function getFullfilledCount(): int
    {
        return $this->responses->filter(fn (HttpPoolResponse $response) => $response->isSuccess())->count();
    }

    /**
     * Count of rejected responses.
     */
    public function getRejectedCount(): int
    {
        return $this->responses->filter(fn (HttpPoolResponse $response) => ! $response->isSuccess())->count();
    }

    /**
     * Get execution time.
     */
    public function getExecutionTime(): ?float
    {
        return $this->executionTime;
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
     * All responses, including rejected, as array.
     *
     * @return array<mixed,HttpPoolResponse>
     */
    public function toArray(): array
    {
        return $this->responses->toArray();
    }
}
