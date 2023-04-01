# [Stories](/docs/stories/README.md) > Lifecycle

This lifecycle is applicable for the `Story` class, and differs slightly for a [Generic lifecycle](/docs/about-lifecycle.md).

When a `Story` class is processed, it runs the internal Closure callbacks/processes in the following order:

### 1. Set Up Callbacks:

First up are the `setUp` callbacks.

These run immediately when the test is processed after the `TestCase` is made available to the `Story`.

You may queue any number of `Closure` callbacks by using the `->setUp()` method. The closure callbacks are called in the order that they are pushed.

### 2. Conditionables

Next up is the execution of the Laravel Conditionables that were registered.

Just like Laravel's Conditionable, you may when conditional based logic, using the  `->when()` and `->unless()` methods.

### 3. Actions

Next up are [Story Actions](/docs/stories/about-actions.md).


### 4. Before Callbacks:

Next up are the `before` callbacks.

You may queue any number of `Closure` callbacks by using the `->before()` method. The closure callbacks are called in the order that they are pushed.

### 5. Primary Callback:

Then comes the primary callback.

You may choose for the `Callback` class to have a primary `Closure` callback which can be specified by using the `->as()` method.

### 6. After Callbacks

Next up are the `after` callbacks.

You may queue any number of `Closure` callbacks by using the `->after()` method. The closure callbacks are called in the order that they are pushed.

### 7. Chained Pest Expectaitons

The Pest expectations are run next, but before the assertions.

You may specify pest expectations directly against the story by using the `->expect('...')` method, followed by a pest `->to___()` method.

### 8. Assertions

Next, your custom assertions are next run.

Like actions, you may add custom assertions by using the `->assertion()` method.

### 9. Tear Down Callbacks

Finally, the test suite / story is torn down.

Like the `setUp` callbacks, you may  queue callbacks via the `->tearDown()` method.

---

### Example:

```php
/** @var Story $story */
$story = ...;

$story->as(fn () => dump('Primary1'))
    ->before(fn () => dump('Before1'))
    ->before(fn () => dump('Before2'))
    ->setUp(fn () => dump('SetUp1'))
    ->setUp(fn () => dump('SetUp2'))
    ->tearDown(fn () => dump('TearDown1'))
    ->tearDown(fn () => dump('TearDown2'))
    ->after(fn () => dump('After1'))
    ->after(fn () => dump('After2'))
    ->action(fn () => dump('Action1'))
    ->action(fn () => dump('Action2'))
    ->assertion(fn () => dump('Assertion1'))
    ->assertion(fn () => dump('Assertion2'))
    ->expect('something1')
    ->toBe('abc')
    ->expect('something2')
    ->toBe('def')
    ->stories([
        story()->as(fn () => dump('Primary2'))
            ->before(fn () => dump('Before3'))
            ->before(fn () => dump('Before4'))
            ->setUp(fn () => dump('SetUp3'))
            ->setUp(fn () => dump('SetUp4'))
            ->tearDown(fn () => dump('TearDown3'))
            ->tearDown(fn () => dump('TearDown4'))
            ->after(fn () => dump('After3'))
            ->after(fn () => dump('After4'))
            ->action(fn () => dump('Action3'))
            ->action(fn () => dump('Action4'))
            ->assertion(fn () => dump('Assertion3'))
            ->assertion(fn () => dump('Assertion4'))
            ->expect('something3')
            ->toBe('ghi')
            ->expect('something4')
            ->toBe('jkl')
    ]);

$story->process();

/**
 * Output:
 *      SetUp1
 *      SetUp2
 *      SetUp3
 *      SetUp4
 *      Action1
 *      Action2
 *      Action3
 *      Action4
 *      Before1
 *      Before2
 *      Before3
 *      Before4
 *      Primary2 // Primary1 not inherit or run (overridden by child callback)
 *      After1
 *      After2
 *      After3
 *      After4
 *      {expect: something1 = abc}
 *      {expect: something2 = def}
 *      {expect: something3 = ghi}
 *      {expect: something4 = jkl}
 *      Assertion1
 *      Assertion2
 *      Assertion3
 *      Assertion4
 *      TearDown1
 *      TearDown2
 *      TearDown3
 *      TearDown4
 */
```
