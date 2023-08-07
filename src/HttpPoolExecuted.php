<?php

namespace Kiwilan\HttpPool;

use Illuminate\Support\Collection;
use Kiwilan\HttpPool\Response\HttpPoolResponse;

class HttpPoolExecuted
{
    /**
     * @param  Collection<mixed,HttpPoolResponse>  $responses
     * @param  Collection<mixed,HttpPoolResponse>  $rejected
     * @param  Collection<mixed,HttpPoolResponse>  $fullfilled
     */
    protected function __construct(
        protected HttpPool $pool,
        protected Collection $responses,
        protected Collection $fullfilled,
        protected Collection $rejected,
        //
        protected int $responsesCount = 0,
        protected int $fullfilledCount = 0,
        protected int $rejectedCount = 0,
        //
        protected ?float $executionTime = null,
        public bool $isFailed = false,
        public ?array $errors = null,
    ) {
    }

    public static function make(
        HttpPool $pool,
        Collection $responses,
        Collection $fullfilled,
        Collection $rejected,
        float $executionTime = null,
    ): self {
        return new self(
            pool: $pool,
            responses: $responses,
            fullfilled: $fullfilled,
            rejected: $rejected,

            responsesCount: $responses->count(),
            fullfilledCount: $fullfilled->count(),
            rejectedCount: $rejected->count(),

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
        return $this->fullfilled;
    }

    /**
     * Get rejected responses.
     *
     * @return Collection<mixed,HttpPoolResponse>
     */
    public function getRejected(): Collection
    {
        return $this->rejected;
    }

    /**
     * Get responses count.
     */
    public function getResponsesCount(): int
    {
        return $this->responsesCount;
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
}
