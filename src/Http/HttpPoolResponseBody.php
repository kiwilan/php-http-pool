<?php

namespace Kiwilan\HttpPool\Http;

use GuzzleHttp\Psr7\Response;
use SimpleXMLElement;

/**
 * HttpPoolResponseBody contains body of request.
 */
class HttpPoolResponseBody
{
    public function __construct(
        protected bool $isExists = false,
        protected bool $isJson = false,
        public bool $isArray = false,
        protected bool $isXml = false,
        protected bool $isString = false,
        protected mixed $json = null,
        protected ?array $array = null,
        protected ?SimpleXMLElement $xml = null,
        protected ?string $string = null,
    ) {
    }

    /**
     * Create HttpPoolResponseBody from Guzzle Response.
     */
    public static function make(?Response $guzzle): self
    {
        if (! $guzzle) {
            return new self();
        }

        $raw = $guzzle->getBody()->getContents();
        $self = new self(
            isExists: ! empty($raw),
        );

        $self->isJson = $self->isValidJson($raw);
        $self->isArray = $self->isValidArray($raw);
        $self->isXml = $self->isValidXml($raw);
        $self->string = ! empty($raw) ? $raw : null;

        if ($self->isJson) {
            $contents = json_decode($raw);
            $self->json = $contents;
        }

        if ($self->isArray) {
            $contents = json_decode($raw, true);
            $self->array = $contents;
        }

        if ($self->isXml) {
            $self->xml = simplexml_load_string($raw);
        }

        return $self;
    }

    /**
     * Check if body exists.
     */
    public function isExists(): bool
    {
        return $this->isExists;
    }

    /**
     * Body as `string` form Guzzle.
     */
    public function getString(): ?string
    {
        return $this->string;
    }

    /**
     * Body as `object`.
     */
    public function getJson(): ?object
    {
        return $this->json;
    }

    /**
     * Body as `SimpleXMLElement`.
     */
    public function getXml(): ?SimpleXMLElement
    {
        return $this->xml;
    }

    /**
     * Get body.
     *
     * @return string|array|object|SimpleXMLElement|null
     */
    public function getContents(): mixed
    {
        return $this->json ?? $this->xml ?? $this->string;
    }

    /**
     * Check if body is `json`.
     */
    public function isJson(): bool
    {
        return $this->isJson;
    }

    /**
     * Check if body is `xml`.
     */
    public function isXml(): bool
    {
        return $this->isXml;
    }

    /**
     * Check if body is `string`.
     */
    public function isString(): bool
    {
        return $this->isString;
    }

    /**
     * Body as `array`.
     */
    public function toArray(): ?array
    {
        if (! $this->string || ! is_string($this->string)) {
            return null;
        }

        return json_decode($this->string, true);
    }

    private function isValidJson(mixed $raw): bool
    {
        if (! is_string($raw)) {
            return false;
        }

        json_decode($raw);

        return JSON_ERROR_NONE === json_last_error();
    }

    private function isValidArray(mixed $raw): bool
    {
        if (! is_string($raw)) {
            return false;
        }

        $contents = json_decode($raw, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return false;
        }

        if (! is_array($contents)) {
            return false;
        }

        return true;
    }

    private function isValidXml(mixed $raw): bool
    {
        if (! is_string($raw)) {
            return false;
        }

        $content = trim($raw);

        if (empty($content)) {
            return false;
        }

        if (false !== stripos($content, '<!DOCTYPE html>')) {
            return false;
        }

        libxml_use_internal_errors(true);
        simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }

    /**
     * Find `$key` into `body`.
     */
    public function find(string $key): mixed
    {
        if (! $this->isExists() || $this->toArray() === null) {
            return null;
        }

        return $this->findKeyRecursive($this->toArray(), $key);
    }

    private function findKeyRecursive(array $array, string $searchKey): mixed
    {
        foreach ($array as $key => $value) {
            if ($key === $searchKey) {
                return $array[$key];
            }

            if (is_array($value)) {
                $result = $this->findKeyRecursive($value, $searchKey);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }
}
