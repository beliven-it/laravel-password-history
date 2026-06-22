# Changelog

All notable changes to `laravel-password-history` will be documented in this file.

## 1.3.0 - 2026-06-22

### What's Changed

- Add support for PHP 8.5
- Add support for Laravel 13 (`illuminate/contracts` `^13.0`)
- Bump dev dependencies: `orchestra/testbench` `^11.0`, `pestphp/pest` `^4.0` (+ `pest-plugin-arch` / `pest-plugin-laravel` `^4.0`)
- CI: add PHP 8.5 and Laravel 13 to the test matrix; run PHPStan on PHP 8.4

## 1.2.0 - 2025-03-07

### What's Changed

* chore(deps): bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/beliven-it/laravel-password-history/pull/2
* chore(deps): bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/beliven-it/laravel-password-history/pull/3

**Full Changelog**: https://github.com/beliven-it/laravel-password-history/compare/1.1.0...1.2.0

## 1.1.0 - 2025-01-15

### Refactor

- Remove exception when a already hash password is provided

## 1.0.6 - 2024-12-21

Fix getFacadeAccessor return type

## 1.0.5 - 2024-12-18

improve test coverage and fix issue related to usage of static class properties

## 1.0.4 - 2024-12-12

Improve test coverage and remove unused methods and assets

## 1.0.3 - 2024-12-12

Fix events during password assignations

## 1.0.2 - 2024-12-12

Handle updating from creating

## 1.0.1 - 2024-12-11

### Fix

Double hashing
