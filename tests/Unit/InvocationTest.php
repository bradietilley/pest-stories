<?php

use BradieTilley\Stories\Exceptions\InvocationFunctionNotFoundException;
use BradieTilley\Stories\Exceptions\InvocationOrphanedMethodException;
use BradieTilley\Stories\Exceptions\InvocationOrphanedPropertyException;
use BradieTilley\Stories\Invocation;

if (! class_exists(PestStoriesInvocationTestClass::class)) {
    class PestStoriesInvocationTestClass
    {
        public static array $ran = [];

        public static array $called = [];

        public static array $gets = [];

        public function __call($name, $arguments)
        {
            static::$called[] = [
                $name,
                $arguments,
            ];

            return $name;
        }

        public function __get($name)
        {
            static::$gets[] = $name;

            return $name;
        }
    }
}

if (! function_exists('pest_stories_random_function_invocation_test')) {
    function pest_stories_random_function_invocation_test(): string
    {
        PestStoriesInvocationTestClass::$ran[] = 'ran';

        return 'it ran';
    }
}

test('an invocation can record a function call', function () {
    PestStoriesInvocationTestClass::$ran = [];

    // Setting up invocation does not run anything
    $invocation = Invocation::makeFunction(
        name: 'pest_stories_random_function_invocation_test',
    );
    expect(PestStoriesInvocationTestClass::$ran)->toBe([]);

    // Gets run when invoking
    expect($invocation->invoke())->toBe('it ran');
    expect(PestStoriesInvocationTestClass::$ran)->toBe([
        'ran',
    ]);
});

test('an invocation cannot run a function call if the function does not exist', function () {
    $invocation = Invocation::makeFunction(
        name: 'pest_stories_random_function_invocation_test_not_exists',
    );

    $invocation->invoke();
})->throws(InvocationFunctionNotFoundException::class, 'Invocation failed: function `pest_stories_random_function_invocation_test_not_exists` not found.');

test('an invocation can record a method call', function () {
    PestStoriesInvocationTestClass::$called = [];

    $object = new PestStoriesInvocationTestClass();

    // Setting up invocationg does not call anything
    $invocation = Invocation::makeMethod(
        name: 'someFunction',
        arguments: ['a', 'b', 'c'],
        object: $object,
    );
    expect(PestStoriesInvocationTestClass::$called)->toBe([]);

    // Returns expected value when invoked
    expect($invocation->invoke())->toBe('someFunction');
    expect(PestStoriesInvocationTestClass::$called)->toBe([
        [
            'someFunction',
            ['a', 'b', 'c'],
        ],
    ]);
});

test('an invocation cannot run a method call if the object is not specified', function () {
    $invocation = Invocation::makeMethod(
        name: 'someFunction',
        arguments: ['a', 'b', 'c'],
    );

    $invocation->invoke();
})->throws(InvocationOrphanedMethodException::class, 'Invocation failed: method `someFunction` is orphaned and does not have a parent object.');

test('an invocation can record a property get call', function () {
    PestStoriesInvocationTestClass::$gets = [];

    $object = new PestStoriesInvocationTestClass();

    // Setting up invocationg does not call anything
    $invocation = Invocation::makeProperty(
        name: 'someProperty',
        object: $object,
    );
    expect(PestStoriesInvocationTestClass::$gets)->toBe([]);

    // Returns expected value when invoked
    expect($invocation->invoke())->toBe('someProperty');
    expect(PestStoriesInvocationTestClass::$gets)->toBe([
        'someProperty',
    ]);
});

test('an invocation cannot run a property get call if the object is not specified', function () {
    $invocation = Invocation::makeProperty(
        name: 'someProperty',
    );

    $invocation->invoke();
})->throws(InvocationOrphanedPropertyException::class, 'Invocation failed: property `someProperty` is orphaned and does not have a parent object.');
