<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Alarm;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Exceptions\ClassAliasNotFoundException;
use BradieTilley\Stories\Exceptions\ClassAliasNotSubClassException;
use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use BradieTilley\Stories\ExpectationChain;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\alarm;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\repeater;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Invocation;
use BradieTilley\Stories\InvocationQueue;
use BradieTilley\Stories\Repeater;
use BradieTilley\Stories\Story;
use Tests\Mocks\MockAction;
use Tests\Mocks\MockAlarm;
use Tests\Mocks\MockAssertion;
use Tests\Mocks\MockExpectationChain;
use Tests\Mocks\MockInvocation;
use Tests\Mocks\MockInvocationQueue;
use Tests\Mocks\MockRepeater;
use Tests\Mocks\MockStory;

test('you can create a story while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(story('something')))->toBe(Story::class);
    expect(get_class(Story::make('something')))->toBe(Story::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Story::class, MockStory::class);

    expect(get_class(story('something')))->toBe(MockStory::class);
    expect(get_class(Story::make('something')))->toBe(MockStory::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Story::class, Story::class);

    expect(get_class(story('something')))->toBe(Story::class);
    expect(get_class(Story::make('something')))->toBe(Story::class);
});

test('you can create an action while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(action('do_something')))->toBe(Action::class);
    expect(get_class(Action::make('do_something')))->toBe(Action::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Action::class, MockAction::class);

    expect(get_class(action('do_something')))->toBe(MockAction::class);
    expect(get_class(Action::make('do_something')))->toBe(MockAction::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Action::class, Action::class);

    expect(get_class(action('do_something')))->toBe(Action::class);
    expect(get_class(Action::make('do_something')))->toBe(Action::class);
});

test('you can create an assertion while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(assertion('expect_something')))->toBe(Assertion::class);
    expect(get_class(Assertion::make('expect_something')))->toBe(Assertion::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Assertion::class, MockAssertion::class);

    expect(get_class(assertion('expect_something')))->toBe(MockAssertion::class);
    expect(get_class(Assertion::make('expect_something')))->toBe(MockAssertion::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Assertion::class, Assertion::class);

    expect(get_class(assertion('expect_something')))->toBe(Assertion::class);
    expect(get_class(Assertion::make('expect_something')))->toBe(Assertion::class);
});

test('you can create a repeater while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(repeater()))->toBe(Repeater::class);
    expect(get_class(Repeater::make()))->toBe(Repeater::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Repeater::class, MockRepeater::class);

    expect(get_class(repeater()))->toBe(MockRepeater::class);
    expect(get_class(Repeater::make()))->toBe(MockRepeater::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Repeater::class, Repeater::class);

    expect(get_class(repeater()))->toBe(Repeater::class);
    expect(get_class(Repeater::make()))->toBe(Repeater::class);
});

test('you can create an alarm while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(alarm(100)))->toBe(Alarm::class);
    expect(get_class(Alarm::make(100)))->toBe(Alarm::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Alarm::class, MockAlarm::class);

    expect(get_class(alarm(100)))->toBe(MockAlarm::class);
    expect(get_class(Alarm::make(100)))->toBe(MockAlarm::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Alarm::class, Alarm::class);

    expect(get_class(alarm(100)))->toBe(Alarm::class);
    expect(get_class(Alarm::make(100)))->toBe(Alarm::class);
});

test('you can create an expectation chain while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(ExpectationChain::make()))->toBe(ExpectationChain::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(ExpectationChain::class, MockExpectationChain::class);

    expect(get_class(ExpectationChain::make()))->toBe(MockExpectationChain::class);

    // Set it back to the original
    StoryAliases::setClassAlias(ExpectationChain::class, ExpectationChain::class);

    expect(get_class(ExpectationChain::make()))->toBe(ExpectationChain::class);
});

test('you can create an invocation queue while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(InvocationQueue::make()))->toBe(InvocationQueue::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(InvocationQueue::class, MockInvocationQueue::class);

    expect(get_class(InvocationQueue::make()))->toBe(MockInvocationQueue::class);

    // Set it back to the original
    StoryAliases::setClassAlias(InvocationQueue::class, InvocationQueue::class);

    expect(get_class(InvocationQueue::make()))->toBe(InvocationQueue::class);
});

test('you can create an invocation while utilising a custom alias', function () {
    // Shouldn't be a mock instance
    expect(get_class(Invocation::makeFunction('test')))->toBe(Invocation::class);

    // Set it to be a mock instance
    StoryAliases::setClassAlias(Invocation::class, MockInvocation::class);

    expect(get_class(Invocation::makeFunction('test')))->toBe(MockInvocation::class);

    // Set it back to the original
    StoryAliases::setClassAlias(Invocation::class, Invocation::class);

    expect(get_class(Invocation::makeFunction('test')))->toBe(Invocation::class);
});

test('you cannot set an alias to a class that does not exist', function () {
    StoryAliases::setClassAlias(Story::class, 'BradieTilley\\Stories\\StoryDoesNotExist');
})->throws(ClassAliasNotFoundException::class, 'Cannot use class `BradieTilley\Stories\StoryDoesNotExist` as an alias for `BradieTilley\Stories\Story`: Class not found');

test('you cannot set an alias to a class that is not a correct subclass', function () {
    StoryAliases::setClassAlias(Story::class, 'BradieTilley\\Stories\\Assertion');
})->throws(ClassAliasNotSubClassException::class, 'Cannot use class `BradieTilley\Stories\Assertion` as an alias for `BradieTilley\Stories\Story`: Class is not a valid subclass');

test('you cannot set the test function alias to a function that does not exist ', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');
    expect(StoryAliases::getFunction('test'))->toBe('pest_stories_mock_test_function');

    StoryAliases::setFunction('test', 'pest_stories_mock_test_function_not_exists');
})->throws(FunctionAliasNotFoundException::class, 'Cannot use function `pest_stories_mock_test_function_not_exists` as an alias for `test`: Function not found');

test('you cannot set the expect function alias to a function that does not exist ', function () {
    StoryAliases::setFunction('expect', 'pest_stories_mock_expect_function');
    expect(StoryAliases::getFunction('expect'))->toBe('pest_stories_mock_expect_function');

    StoryAliases::setFunction('expect', 'pest_stories_mock_expect_function_not_exists');
})->throws(FunctionAliasNotFoundException::class, 'Cannot use function `pest_stories_mock_expect_function_not_exists` as an alias for `expect`: Function not found');
