<?php

namespace Kiwilan\HttpPool\Response;

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
        protected bool $isArray = false,
        protected bool $isXml = false,
        protected bool $isHtml = false,
        protected bool $isBinary = false,
        protected ?string $contents = null,
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

        if (! $self->isExists) {
            return $self;
        }
        $self->isBinary = $self->isValidBinary($raw);

        if (! $self->isBinary) {
            $self->isJson = $self->isValidJson($raw);
            $self->isArray = $self->isValidArray($raw);

            if (! $self->isJson) {
                $self->isXml = $self->isValidXml($raw);
                $self->isHtml = $self->isValidHtml($raw);
            }
        }
        $self->contents = ! empty($raw) ? $raw : null;

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
     * Body as `string` from Guzzle.
     */
    public function getContents(): ?string
    {
        return $this->contents;
    }

    /**
     * Body as JSON object.
     */
    public function getJSON(): ?object
    {
        if ($this->isJson) {
            return json_decode($this->contents);
        }

        return null;
    }

    /**
     * Body as `SimpleXMLElement`.
     */
    public function getXML(): ?SimpleXMLElement
    {
        if ($this->isXml) {
            return simplexml_load_string($this->contents);
        }

        return null;
    }

    public function getHTML(): ?\DOMDocument
    {
        if ($this->isHtml) {
            $dom = new \DOMDocument();
            $dom->loadHTML($this->contents);

            return $dom;
        }

        return null;
    }

    /**
     * Check if body is `json`.
     */
    public function isJSON(): bool
    {
        return $this->isJson;
    }

    /**
     * Check if body is `array`.
     */
    public function isArray(): bool
    {
        return $this->isArray;
    }

    /**
     * Check if body is `xml`.
     */
    public function isXML(): bool
    {
        return $this->isXml;
    }

    /**
     * Check if body is `html`.
     */
    public function isHTML(): bool
    {
        return $this->isHtml;
    }

    /**
     * Check if body is `binary`.
     */
    public function isBinary(): bool
    {
        return $this->isBinary;
    }

    /**
     * Body as `array`.
     */
    public function toArray(): ?array
    {
        if (! $this->contents || ! is_string($this->contents)) {
            return null;
        }

        return json_decode($this->contents, true);
    }

    private function isValidBinary(mixed $raw): bool
    {
        return mb_detect_encoding((string) $raw, null, true) === false;
    }

    private function isValidJson(mixed $raw): bool
    {
        if (! is_string($raw)) {
            return false;
        }

        json_decode($raw);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private function isValidArray(mixed $raw): bool
    {
        if (! is_string($raw)) {
            return false;
        }

        $contents = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
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

        if (stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }

        libxml_use_internal_errors(true);
        simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }

    private function isValidHtml(mixed $raw): bool
    {
        return $raw !== strip_tags($raw);
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
