<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;

test('you can create a story using the story function', function () {
    expect(story())->toBeInstanceOf(Story::class)->getName()->toBe('');
    expect(story('with name'))->toBeInstanceOf(Story::class)->getName()->toBe('with name');
});

test('you can create an action using the action function', function () {
    expect(action('do_something'))->toBeInstanceOf(Action::class)->getName()->toBe('do_something');
});

test('you can create a assertion using the assertion function', function () {
    expect(assertion('expect_something'))->toBeInstanceOf(Assertion::class)->getName()->toBe('expect_something');
});
