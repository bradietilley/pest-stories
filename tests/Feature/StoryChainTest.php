<?php

use BradieTilley\Stories\Concerns\Stories;

uses(Stories::class);

if (! function_exists('assertDatabaseHasPestStoriesSampleTestFunction')) {
    function assertDatabaseHasPestStoriesSampleTestFunction(string $abc)
    {
        expect($abc)->toBe('abc');
    }
}

test('you can run anonymous functions in the chain')
    ->action(fn () => 'the chaining of anonymous functions is a native Pest feature... just making sure it still works')
    ->assertDatabaseHasPestStoriesSampleTestFunction('abc')
    ->action(fn () => 'this test will be incomplete if assertDatabaseHasPestStoriesSampleTestFunction is not run');
