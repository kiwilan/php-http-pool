# Changelog

All notable changes to `php-http-pool` will be documented in this file.

## v0.3.4 - 2024-03-16

`symfony/console` allow `v7`

## v0.3.3 - 2024-03-16

Add support for Laravel 11

## v0.3.2 - 2023-12-23

- add `syslog` for logging
- update for PHP 8.3

## v0.3.1 - 2023-10-25

- Add `setHeaders()` method to `HttpPool` class to allow setting headers for all requests.

## 0.3.0 - 2023-08-19

- `HttpPoolFullfilled`: `getFullfilled` and `getRejected` now return a filter of `getResponses` to reduce size of the response, same for `getFullfilledCount` and `getRejectedCount`, add `toArray` method
- `HttpPoolResponseMetadata` extra headers: `Origin` is now `origin`, `ID` is now `id` and new header `status` is added to get status of Guzzle pool response, add `getStatus` method
- `HttpPoolResponseBody` remove `isString` method
- refactoring: reduce complexity of `HttpPool`, `HttpPoolRequest` and `HttpPoolFullfilled`

## 0.2.32 - 2023-08-18

- `HttpPoolResponseMetadata` now have headers as `array<string, string>` instead of `array<string, array<string>>`
- Fix a problem with origin request for `HttpPoolResponseMetadata`

## 0.2.31 - 2023-08-11

- Allow all URL to be null, to prevent throw errors if a full chunk is not available

## 0.2.30 - 2023-08-11

- Allow some URL to be null in pool, a warning message will appear if console can print. An error will be added to `errors` list.
- If all URL are null, an Exception will be raised.

## 0.2.22 - 2023-08-10

- fix `HttpPool` `isAllowMemoryPeak` property

## 0.2.21 - 2023-08-10

- `HttpPool` remove `handleMemoryPeak` to replace with `allowMemoryPeak`, more flexible
- `HttpPoolExecuted` is now `HttpPoolFullfilled`
- add `isBinary` to `HttpPoolResponseBody` to check if the response body is binary, other checks will be triggered if it is not binary

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
