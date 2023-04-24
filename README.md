# Pest Stories

A clean approach for writing large test suites.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)


## Introduction

User Stories are short, simple descriptions of a feature or functionality of a software application, typically written from the perspective of an end user.

Pest Stories is a PHP package that extends the PestPHP testing framework, allowing developers to write user stories in a clear and reusable way, making it easier to maintain and test their software applications. The idea is your tests should be written in a human readable way that reflects a user story.


## Installation

```
composer require bradietilley/pest-stories --dev
```

To add Stories to your test suites, you must add the following trait via Pest's `uses()` helper:

```php
uses(BradieTilley\Stories\Concerns\Stories::class);
```

*Refer to Pest's documentation on how to use the `uses()` helper.*


## Documentation

Read the [docs](/docs/README.md).