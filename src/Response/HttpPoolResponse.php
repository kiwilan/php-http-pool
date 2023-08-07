<?php

namespace Kiwilan\HttpPool\Response;

use GuzzleHttp\Psr7\Response;

/**
 * HttpPoolResponse contains the response of a request.
 */
class HttpPoolResponse
{
    protected function __construct(
        protected mixed $id,
        protected ?Response $guzzle,
        protected HttpPoolResponseMetadata $metadata,
        protected HttpPoolResponseBody $body,
        protected bool $success = false,
        protected bool $bodyAvailable = false,
    ) {
    }

    /**
     * Create HttpPoolResponse from Response.
     *
     * @param  ?\GuzzleHttp\Psr7\Response  $guzzle
     */
    public static function make(mixed $id, ?Response $guzzle): self
    {
        $metadata = HttpPoolResponseMetadata::make($guzzle);
        $body = HttpPoolResponseBody::make($guzzle);

        $self = new self(
            id: is_numeric($id) ? intval($id) : $id,
            guzzle: $guzzle,
            metadata: $metadata,
            success: $metadata->isSuccess(),
            body: $body,
            bodyAvailable: $body->isExists(),
        );

        return $self;
    }

    /**
     * Get unique identifier of HttpPoolResponse.
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * Get HttpPoolResponseMetadata.
     */
    public function getMetadata(): HttpPoolResponseMetadata
    {
        return $this->metadata;
    }

    /**
     * Get original GuzzleHttp\Psr7\Response.
     */
    public function getGuzzle(): ?Response
    {
        return $this->guzzle;
    }

    /**
     * Get HttpPoolResponseBody.
     */
    public function getBody(): HttpPoolResponseBody
    {
        return $this->body;
    }

    /**
     * Check if request is success.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Check if body exists.
     */
    public function isBodyAvailable(): bool
    {
        return $this->bodyAvailable;
    }
}
