<?php

namespace Kiwilan\HttpPool\Request;

class HttpPoolRequestItem
{
    public function __construct(
        public int|string|null $id = null,
        public ?string $url = null,
    ) {
    }
}
