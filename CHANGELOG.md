# Changelog
All notable changes to this project will be documented in this file.

## [Unreleased]
- Laravel 11, 12 and 13 support added.
- PHPUnit 10, 11 and 12 support for tests; test methods now use `#[Test]` attributes alongside the `@test` annotations.
- Fixed `HasMeta` boot method instantiating the model while it is being booted, which throws a `LogicException` on newer Laravel versions.
- Fixed undefined `$table` variable in the meta fields migration stub's "table already exists" exception message.
- Code style formatted to PSR-12 with Laravel Pint (`composer format` / `composer lint`).
- Replaced Travis CI with GitHub Actions (PHP 8.2–8.4 × Laravel 11/12/13, plus a code style check).
- Require `touhidurabir/laravel-model-uuid` `^1.2.0`, which contains the Laravel 12/13 boot fix.


## [1.0.0] - 2021-09-27
- Initial release

## [1.0.1] - 2021-10-18
- README Update
- Bug Fix
