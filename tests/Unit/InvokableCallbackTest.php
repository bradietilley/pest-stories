<?php

use function BradieTilley\Stories\Helpers\story;
use Tests\Mocks\MockInvokableAction;
use Tests\Mocks\MockInvokableAssertion;

test('an invokable callback class can be stored multiple times', function () {
    $foo = MockInvokableAction::make()->withString('Foo')->withInteger(12);
    $bar = MockInvokableAction::make()->withString('Bar')->withInteger(34);
    $baz = MockInvokableAssertion::make()->withString('Baz')->withInteger(56);
    $qux = MockInvokableAssertion::make()->withString('Qux')->withInteger(78);

    ($story = story('a story with the sample action with different instances'))
        ->with([
            'a' => 'Testing',
            'b' => 111,
        ])
        ->action($foo)
        ->action($bar)
        ->assertion($baz)
        ->assertion($qux)
        ->process();

    expect(MockInvokableAction::$invoked)->ToBe([
        [
            'story' => $story,
            'a' => 'Testing',
            'b' => 111,
            'string' => 'Foo',
            'integer' => 12,
        ],
        [
            'story' => $story,
            'a' => 'Testing',
            'b' => 111,
            'string' => 'Bar',
            'integer' => 34,
        ],
    ]);

    expect(MockInvokableAssertion::$invoked)->ToBe([
        [
            'story' => $story,
            'a' => 'Testing',
            'b' => 111,
            'string' => 'Baz',
            'integer' => 56,
        ],
        [
            'story' => $story,
            'a' => 'Testing',
            'b' => 111,
            'string' => 'Qux',
            'integer' => 78,
        ],
    ]);
});
