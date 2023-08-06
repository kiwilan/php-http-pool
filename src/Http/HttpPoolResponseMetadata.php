<?php

namespace Kiwilan\HttpPool\Http;

use DateTime;
use GuzzleHttp\Psr7\Response;

/**
 * HttpPoolResponse metadata.
 */
class HttpPoolResponseMetadata
{
    protected function __construct(
        protected int $statusCode = 404,
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
        $self->isSuccess = $self->statusCode >= 200 && $self->statusCode < 300;
        $self->isFailed = ! $self->isSuccess;
        $self->reason = $response->getReasonPhrase();
        $self->isJson = str_contains($contentType, 'json');
        $self->isXml = str_contains($contentType, 'xml');
        $self->server = $response->getHeaderLine('Server');
        $self->date = new DateTime($response->getHeaderLine('Date'));
        $self->contentType = $contentType;
        $self->request = $self->setRequest($response);
        $self->headers = $response->getHeaders();

        return $self;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function isFailed(): bool
    {
        return $this->isFailed;
    }

    public function isJson(): bool
    {
        return $this->isJson;
    }

    public function isXml(): bool
    {
        return $this->isXml;
    }

    public function getServer(): ?string
    {
        return $this->server;
    }

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
     * @return array<string, array<string>>
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
        return $this->headers[$key][0] ?? null;
    }

    /**
     * Get query URL from Response.
     */
    private function setRequest(?Response $response): ?string
    {
        $origin = $response->getHeader('Origin');

        if (array_key_exists(0, $origin)) {
            return $origin[0];
        }

        return null;
    }
}
