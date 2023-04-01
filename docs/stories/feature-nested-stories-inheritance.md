# [Stories](/docs/stories/README.md) > Nested Stories / Inheritance

In Pest Stories you may define a story that has multiple child stories. Each child story is run in isolation with all the configuration (actions, assertions, data variables, callbacks, etc) applied to each child as well.

The level of inheritance is "infinite" so you can nest child stories inside child stories inside ... you get the idea.

## Usage

The below example doesn't do much, but it showcases how inheritance works. There are 3 stories that will get invoked here; all other `story()` objects are merely there for inheritance sake.

```php
story('can write a comment')
    ->before(fn () => 'Story Started');
    ->after(fn () => 'Story Finished');
    ->action(fn (Story $story) => dump('action 1: ' . $story->getTestName()))
    ->stories([
        story('as an admin'),
        story('as a customer')
            ->action(fn (Story $story) => dump('action 2: ' . $story->getTestName()))
            ->stories([
                story('if not blocked')
                    ->stories([
                        story('if the customer has purchased the product'),
                        story('if customer is VIP'),
                    ]);
            ]),
    ]);
```

Results in the following `dump`s:

```
Story Started
action 1: can write a comment as an admin
Story Finished

Story Started
action 1: can write a comment as a customer if not blocked if the customer has purchased the product
action 2: can write a comment as a customer if not blocked if the customer has purchased the product
Story Finished

Story Started
action 1: can write a comment as a customer if not blocked if customer is VIP
action 2: can write a comment as a customer if not blocked if customer is VIP
Story Finished
```

## Inheritable features + Order of inheritance 

- Actions
    - Example: `->action('do_something')...`
    - Handled as: `parent actions` + `child actions`, maintaining order
- Assertions
    - Example: `->assertion('assert_something')...`
    - Handled as: `parent assertions` + `child assertions`, maintaining order
- Name
    - Example: `story('a name of the story')...`
    - Handled as: `{parent} {child}`, as a string concatenation.
- Primary callback
    - Example: `->as(fn () => doSomething())...`
    - Handled as: `child primary callback` or if not set then `parent primary callback`
- Test Call Proxies
    - Example: `->skip('skipped because of a reason')...`
    - Handled as: `parent call proxies` + `child call proxies`, maintaining order
- Before callbacks
    - Example: `->before(fn () => doSomething())...`
    - Handled as: `parent before callbacks` + `child before callbacks`, maintaining order
- After callbacks
    - Example: `->after(fn () => doSomething())...`
    - Handled as: `parent after callbacks` + `child after callbacks`, maintaining order
- Set Up callbacks
    - Example: `->setUp(fn () => doSomething())...`
    - Handled as: `parent setUp callbacks` + `child setUp callbacks`, maintaining order
- Tear Down callbacks
    - Example: `->tearDown(fn () => doSomething())...`
    - Handled as: `parent tearDown callbacks` + `child tearDown callbacks`, maintaining order
- Conditionables
    - Example: `->when(fn () => $condition, fn () => doSomething())...`
    - Handled as: `parent conditionables` + `child conditionables`, maintaining order
- Data / Variables
    - Example: `->with([ 'variable_name_one' => 1 ])->set('variable_name_two', 2)`
    - Handled as: `parent data` + `child data`, replacing any shared variables by key (variable name)
- Appends
    - Example: `->appends('variable_name')...`
    - Handled as: `parent appends` + `child appends`, maintaining order
- Expectation Chain:
    - Example: `->expect('variable_name')->toBeString()...`
    - Handled as `parent chain` + `child chain`, maintaining order
