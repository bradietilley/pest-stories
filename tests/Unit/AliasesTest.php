<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Exceptions\ClassAliasNotFoundException;
use BradieTilley\Stories\Exceptions\ClassAliasNotSubClassException;
use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Story;
use Tests\Mocks\MockAction;
use Tests\Mocks\MockAssertion;
use Tests\Mocks\MockStory;

test('you can create a story using the story function while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(story())->not->toBeInstanceOf(MockStory::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Story::class, MockStory::class);
    expect(story())->toBeInstanceOf(MockStory::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Story::class, Story::class);
    expect(story())->not->toBeInstanceOf(MockStory::class);
});

test('you can create an action using the action function while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(action('do_something'))->not->toBeInstanceOf(MockAction::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Action::class, MockAction::class);
    expect(action('do_something'))->toBeInstanceOf(MockAction::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Action::class, Action::class);
    expect(action('do_something'))->not->toBeInstanceOf(MockAction::class);
});

test('you can create an assertion using the assertion function while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(assertion('expect_something'))->not->toBeInstanceOf(MockAssertion::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Assertion::class, MockAssertion::class);
    expect(assertion('expect_something'))->toBeInstanceOf(MockAssertion::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Assertion::class, Assertion::class);
    expect(assertion('expect_something'))->not->toBeInstanceOf(MockAssertion::class);
});

test('you cannot set an alias to a class that does not exist', function () {
    StoryAliases::setClassAlias(Story::class, 'BradieTilley\\Stories\\StoryDoesNotExist');
})->throws(ClassAliasNotFoundException::class, 'Cannot use class `BradieTilley\Stories\StoryDoesNotExist` as an alias for `BradieTilley\Stories\Story`: Class not found');

test('you cannot set an alias to a class that is not a correct subclass', function () {
    StoryAliases::setClassAlias(Story::class, 'BradieTilley\\Stories\\Assertion');
})->throws(ClassAliasNotSubClassException::class, 'Cannot use class `BradieTilley\Stories\Assertion` as an alias for `BradieTilley\Stories\Story`: Class is not a valid subclass');

test('you cannot set an alias to a function that does not exist ', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');
    expect(StoryAliases::getFunction('test'))->toBe('pest_stories_mock_test_function');

    StoryAliases::setFunction('test', 'pest_stories_mock_test_function_not_exists');
})->throws(FunctionAliasNotFoundException::class, 'Cannot use function `pest_stories_mock_test_function_not_exists` as an alias for `test`: Function not found');