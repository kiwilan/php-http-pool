# Changelog

All notable changes to `php-http-pool` will be documented in this file.

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
