<?php

use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Collection;

$value = Collection::make();

beforeAll(function () use ($value) {
    $value->push(
        story()->getTestSafe()
    );
});

test('a story will have a test case', function () use ($value) {
    $test = story()->fresh()->getTestSafe();
    expect($test)->toBe($this);

    expect($value)->toHaveCount(1)->first()->toBeNull();
});
