<?php

use Tests\Mocks\PestStoriesMockExpectation;
use Tests\Mocks\PestStoriesMockTestCall;
use Tests\TestCase;

uses(TestCase::class)->group('unit')->in('Unit');
uses(TestCase::class)->group('extended')->in('Extended');

if (! function_exists('pest_stories_mock_test_function')) {
    /**
     * A mock replacement to Pest's `test()` function to be used
     * in internal test cases
     */
    function pest_stories_mock_test_function(string $description, Closure $callback): PestStoriesMockTestCall
    {
        return new PestStoriesMockTestCall($description, $callback);
    }
}

if (! function_exists('pest_stories_mock_expect_function')) {
    function pest_stories_mock_expect_function(mixed $value): PestStoriesMockExpectation
    {
        return new PestStoriesMockExpectation($value);
    }
}
