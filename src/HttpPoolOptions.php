<?php

namespace Kiwilan\HttpPool;

/**
 * HttpPoolOptions to set options for HttpPool.
 */
class HttpPoolOptions
{
    public function __construct(
        public int $poolLimit = 250,
        public int $maxCurlHandles = 100,
        public int $maxRedirects = 10,
        public int $timeout = 30,
        public int $concurrencyMaximum = 5,
        public bool $allowPrintConsole = false,
    ) {
    }
}
