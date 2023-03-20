<?php

use Tests\Mocks\PestStoriesMockTestCall;
use Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');

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
