<?php

use BradieTilley\Stories\Exceptions\TestCaseUnavailableException;
use function BradieTilley\Stories\Helpers\story;

test('TestCaseUnavailableException can be created with a story', function () {
    $story = story('can do attitude');

    throw TestCaseUnavailableException::make($story);
})->throws(TestCaseUnavailableException::class, 'The `PHPUnit\Framework\TestCase` instance was not available when the `can do attitude` story was booted.');
