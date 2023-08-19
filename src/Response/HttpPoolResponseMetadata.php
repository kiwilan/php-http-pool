<?php

namespace Kiwilan\HttpPool\Response;

use DateTime;
use GuzzleHttp\Psr7\Response;

/**
 * HttpPoolResponse metadata.
 */
class HttpPoolResponseMetadata
{
    protected function __construct(
        protected int $statusCode = 404,
        protected string $status = 'rejected',
        protected ?string $reason = null,
        protected bool $isSuccess = false,
        protected bool $isFailed = true,
        protected bool $isJson = false,
        protected bool $isXml = false,
        protected ?string $server = null,
        protected DateTime $date = new DateTime(),
        protected ?string $contentType = null,
        protected ?string $request = null,
        protected array $headers = [],
    ) {
    }

    /**
     * Create HttpPoolResponseMetadata from HttpPoolResponse.
     *
     * @param  ?\GuzzleHttp\Psr7\Response  $response
     */
    public static function make(?Response $response): self
    {
        $self = new HttpPoolResponseMetadata();

        if (! $response) {
            return $self;
        }

        $contentType = $response->getHeaderLine('Content-Type');

        $self->statusCode = $response->getStatusCode();
        $self->status = $response->getHeaderLine('status');
        $self->isSuccess = $self->status === 'fullfilled';
        $self->isFailed = ! $self->isSuccess;
        $self->reason = $response->getReasonPhrase();
        $self->isJson = str_contains($contentType, 'json');
        $self->isXml = str_contains($contentType, 'xml');
        $self->server = $response->getHeaderLine('Server');
        $self->date = new DateTime($response->getHeaderLine('Date'));
        $self->contentType = $contentType;
        $self->request = $response->getHeaderLine('origin');

        foreach ($response->getHeaders() as $type => $header) {
            $self->headers[$type] = $response->getHeaderLine($type);
        }

        return $self;
    }

    /**
     * Get status code from Response.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get reason from Response.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Check if Response is success.
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * Check if Response is failed.
     */
    public function isFailed(): bool
    {
        return $this->isFailed;
    }

    /**
     * Check if Response is JSON.
     */
    public function isJson(): bool
    {
        return $this->isJson;
    }

    /**
     * Check if Response is XML.
     */
    public function isXml(): bool
    {
        return $this->isXml;
    }

    /**
     * Get server from Response.
     */
    public function getServer(): ?string
    {
        return $this->server;
    }

    /**
     * Get date from Response.
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Get content type from Response.
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Get request.
     */
    public function getRequest(): ?string
    {
        return $this->request;
    }

    /**
     * Get headers.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get header.
     */
    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }
}
