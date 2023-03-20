<?php

use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use Tests\Mocks\PestStoriesMockTestCall;

test('a story can be registered and will register the story test via the Pest test function', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');
    expect(StoryAliases::getFunction('test'))->toBe('pest_stories_mock_test_function');

    $testCall = story('can do something')->test();
    expect($testCall)->toBeInstanceOf(PestStoriesMockTestCall::class);
});
