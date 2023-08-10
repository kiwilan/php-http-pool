# Changelog

All notable changes to `php-http-pool` will be documented in this file.

## 0.2.2 - 2023-08-10

- `HttpPool` remove `handleMemoryPeak` to replace with `allowMemoryPeak`, more flexible
- `HttpPoolExecuted` is now `HttpPoolFullfilled`
- add `isBinary` to `HttpPoolResponseBody` to check if the response body is binary, other checks will be triggered if it is not binary

## 0.2.1 - 2023-08-07

- remove `allowMemoryPeak` replaced with `HttpPool::handleMemoryPeak`

## 0.2.0 - 2023-08-07

### BREAKING CHANGES

- `execute()` method will return `HttpPoolExecuted::class` instead of `HttpPool::class`, pool can be accessed with `getPool()` method

### Improvements

- `error` is now `errors` and is an array of errors (getter is now `getErrors`)
- `HttpPool::make()` has now `throwErrors` param to prevent errors throwing
- add `HttpPoolRequestItem` class for request item
- refactor `HttpPool::class`
- memory peak option do not reset memory peak anymore, you have to call `HttpPool::resetMemory()`

## 0.1.01 - 2023-08-07

- `disallowPrintConsole` become `allowPrintConsole` and default value is `false`
- `setMaximumMemory` removed, merged into `allowMemoryPeak`

## 0.1.0 - 2023-08-06

init
