<?php

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
