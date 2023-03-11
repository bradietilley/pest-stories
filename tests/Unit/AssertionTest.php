<?php

use BradieTilley\StoryBoard\Enums\Expectation;
use BradieTilley\StoryBoard\Exceptions\ExpectationNotSpecifiedException;
use BradieTilley\StoryBoard\Exceptions\RunnableGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\RunnableNotFoundException;
use BradieTilley\StoryBoard\Exceptions\RunnableNotSpecifiedException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Assertion;
use BradieTilley\StoryBoard\Story\StoryAssertion;
use Illuminate\Support\Collection;

beforeEach(fn () => Assertion::flush());

test('a story must have at least one expectation', function () {
    $story = Story::make()
        ->action(fn () => null)
        ->assert(fn () => true)
        ->name('parent')
        ->stories([
            Story::make('child'),
        ]);

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throws(ExpectationNotSpecifiedException::class, 'No expectation was found for the story `parent child`');

test('a story works with one expectation', function () {
    $story = Story::make()
        ->can()
        ->action(fn () => null)
        ->assert(fn () => true)
        ->name('parent')
        ->stories([
            Story::make('child'),
        ]);

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throwsNoExceptions();

test('a story must have at least one assertion', function () {
    $story = Story::make()->action(fn () => null)->can()->name('parent')->stories(
        Story::make('child'),
    );

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throws(RunnableNotSpecifiedException::class, 'No "can" assertion was found for the story `parent child`');

test('you may create a story with an assertion and unset the assertion for a child story', function () {
    $story = Story::make()
        ->name('parent')
        ->can()
        ->stories([
            // can: inherits from 'parent'
            Story::make('child can implicit'),
            // can: overrwides from 'parent' (no affect really)
            Story::make()->can()->name('child can explicit'),
            // cannot: overrwides from 'parent'
            Story::make()->cannot()->name('child cannot explicit'),
            // null: overrwides from 'parent'
            Story::make()->resetExpectation()->name('child unset')->stories([
                // null: inherits from 'child unset'
                Story::make('grandchild null implicit'),
                // can: overrides resetExpectation from 'child unset'
                Story::make()->can()->name('grandchild can explicit'),
                // cannot: overrides resetExpectation from 'child unset'
                Story::make()->cannot()->name('grandchild cannot explicit'),
            ]),
        ]);

    $actual = $story->storiesAll->keys()->toArray();

    $expect = [
        '[Can] parent child can implicit',
        '[Can] parent child can explicit',
        '[Cannot] parent child cannot explicit',
        // This should have no [Can]/[Cannot] as it was reset to have no assertion
        'parent child unset grandchild null implicit',
        '[Can] parent child unset grandchild can explicit',
        '[Cannot] parent child unset grandchild cannot explicit',
    ];

    expect($actual)->toBe($expect);
});

test('assertions can be defined in various ways', function () {
    $ran = collect();

    $assertion1 = Assertion::make('assertion_1')->as(fn () => $ran[] = '1');

    Assertion::make('assertion_2')->as(fn () => $ran[] = '2');
    $assertion2 = 'assertion_2';

    $assertion3 = fn () => $ran[] = '3';

    Story::make()
        ->can()
        ->assert($assertion1)
        ->assert($assertion2)
        ->assert($assertion3)
        ->action(fn () => $ran[] = 'action')
        ->boot()
        ->perform();

    expect($ran->toArray())->toBe([
        'action',
        '1',
        '2',
        '3',
    ]);
});

test('assertions can be added to a story in various ways', function () {
    $ran = collect();

    Story::make()
        ->can()
        ->action(fn () => null)
        ->assert(fn () => $ran[] = 'assert')
        ->assertion(fn () => $ran[] = 'setAssertion')
        ->assertions([
            fn () => $ran[] = 'setAssertions',
        ])
        ->whenCan(fn () => $ran[] = 'whenCan')
        ->whenCannot(fn () => $ran[] = 'whenCannot')
        ->boot()
        ->perform();

    expect($ran->toArray())->toBe([
        'assert',
        'setAssertion',
        'setAssertions',
        'whenCan',
    ]);
});

test('the current expectation can be fetched', function () {
    $storyCan = Story::make()->can();
    $storyCannot = Story::make()->cannot();
    $storyDefault = Story::make();

    expect($storyCan->currentExpectation())->toBe(Expectation::CAN);
    expect($storyCannot->currentExpectation())->toBe(Expectation::CANNOT);
    expect($storyDefault->currentExpectation())->toBe(Expectation::ALWAYS);
});

test('assertions added to the always-expectation are run regardless on expectation', function () {
    $ran = collect();

    $story = Story::make()
        ->action(fn () => null)
        ->assert(
            can: fn (Story $story) => $ran[] = "can: {$story->getFullName()}",
            cannot: fn (Story $story) => $ran[] = "cannot: {$story->getFullName()}",
            always: fn (Story $story) => $ran[] = "always: {$story->getFullName()}",
        )
        ->stories([
            Story::make('story-can')->can(),
            Story::make('story-cannot')->cannot(),
            // Story::make('story-blank'),
        ])
        ->storiesAll
        ->each(function (Story $story) {
            $story->boot()->perform();
        });

    expect($ran->toArray())->toBe([
        'always: story-can',
        'can: story-can',
        'always: story-cannot',
        'cannot: story-cannot',
    ]);
});

test('an exception is thrown when an assertion is referenced but not found', function () {
    Assertion::make('found', fn () => null, 'var');

    Story::make()->assertion('found')->assertion('not_found')->perform();
})->throws(RunnableNotFoundException::class, 'The `not_found` assertion could not be found.');

test('assertions that are missing a generator throw an exception when performed', function () {
    $ran = Collection::make([]);

    Assertion::make('something_cooler')->as(fn () => $ran[] = 'yes');
    Assertion::make('something_cool');

    $story = Story::make()
        ->can()
        ->action(fn () => null)
        ->assertion('something_cooler')
        ->assertion('something_cool');

    // The assertion 'something_cooler' boots correctly
    // The assertion 'something_cool' does not (no generator)
    $story->perform();
})->throws(RunnableGeneratorNotFoundException::class, 'The `something_cool` assertion generator callback could not be found.');

test('assertion variables are passed through to subsequent assertions', function () {
    $ran = Collection::make([]);

    Assertion::make('json_response_valid')->variable('json')->as(function () use ($ran) {
        // Example assertion: Check JSON API response string looks good

        $ran[] = '1';

        return [
            'data' => [
                'attributes' => [
                    'test' => 'foo',
                ],
            ],
        ];
    });

    Assertion::make('test_is_foo')->variable('testValue')->as(function (array $json) use ($ran) {
        // Example assertion: Check a specific field matches what you'd expect, by leveragin
        // the previously returned JSON object

        $ran[] = '2';

        expect($json)->toBe([
            'data' => [
                'attributes' => [
                    'test' => 'foo',
                ],
            ],
        ]);

        return $json['data']['attributes']['test'];
    });

    Story::make('test_assertions')
        ->can()
        ->action(fn () => null)
        ->assert('json_response_valid')
        ->assert('test_is_foo')
        ->assert(function (string $testValue) use ($ran) {
            $ran[] = '3';

            expect($testValue)->toBe('foo');
        })
        ->runAssertions();

    expect($ran->toArray())->toBe([
        '1',
        '2',
        '3',
    ]);
});

test('assertions are added to their respective expectations', function () {
    $assertion1 = Assertion::make('assertion_1')->as(fn () => null);
    $assertion2 = Assertion::make('assertion_2')->as(fn () => null);
    $assertion3 = Assertion::make('assertion_3')->as(fn () => null);
    $assertion4 = Assertion::make('assertion_4')->as(fn () => null);
    $assertion5 = Assertion::make('assertion_5')->as(fn () => null);
    $assertion6 = Assertion::make('assertion_6')->as(fn () => null);

    $parent = Story::make('test')
        ->assert(
            $assertion1,
            $assertion2,
            $assertion3,
        )
        ->action(fn () => null)
        ->stories([
            $story1 = Story::make()->can()->assertion($assertion4),
            $story2 = Story::make()->cannot()->assertion($assertion5),
            $story3 = Story::make()->assertion($assertion6),
        ])
        ->register();

    $story1Assertions = collect($story1->getAssertions())
        ->map(
            fn (array $assertions) => collect($assertions)->map(
                fn (StoryAssertion $assertion) => $assertion->getAssertion()->getNameString()
            ),
        )
        ->toArray();

    expect($story1Assertions)->toBe([
        'can' => [
            'assertion_4',
            'assertion_1',
        ],
        'cannot' => [],
        'always' => [
            'assertion_3',
        ],
    ]);

    $story2Assertions = collect($story2->getAssertions())
        ->map(
            fn (array $assertions) => collect($assertions)->map(
                fn (StoryAssertion $assertion) => $assertion->getAssertion()->getNameString()
            ),
        )
        ->toArray();

    expect($story2Assertions)->toBe([
        'can' => [],
        'cannot' => [
            'assertion_5',
            'assertion_2',
        ],
        'always' => [
            'assertion_3',
        ],
    ]);

    $story3Assertions = collect($story3->getAssertions())
        ->map(
            fn (array $assertions) => collect($assertions)->map(
                fn (StoryAssertion $assertion) => $assertion->getAssertion()->getNameString()
            ),
        )
        ->toArray();

    expect($story3Assertions)->toBe([
        'can' => [],
        'cannot' => [],
        'always' => [
            'assertion_6',
            'assertion_3',
        ],
    ]);
});

test('an assertion can append a name to the story', function () {
    Assertion::make('201_response')->as(fn () => null)->appendName('with 201 response');
    Assertion::make('valid_json')->as(fn () => null)->appendName('with valid JSON');

    $story = Story::make('can create post')
        ->action(fn () => null)
        ->assertion('201_response')
        ->assertion('valid_json')
        ->register();

    expect($story->getLevelName())->toBe('can create post with 201 response with valid JSON');
});

test('assertions are booted in order of order property', function () {
    $ran = collect();

    Assertion::make('assertion_1')->as(fn () => $ran[] = '1');
    Assertion::make('assertion_2')->as(fn () => $ran[] = '2');
    Assertion::make('assertion_3')->as(fn () => $ran[] = '3');
    Assertion::make('assertion_5')->as(fn () => $ran[] = '5')->order(5);
    Assertion::make('assertion_4')->as(fn () => $ran[] = '4')->order(4);

    Story::make('test')
        ->action(fn () => null)
        ->can()
        ->assert('assertion_3')
        ->assert('assertion_5')
        ->assert('assertion_4')
        ->assert('assertion_2')
        ->assert('assertion_1')
        ->runAssertions();

    expect($ran->toArray())->toBe([
        '1',
        '2',
        '3',
        '4',
        '5',
    ]);
});
