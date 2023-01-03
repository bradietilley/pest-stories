[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Name

### Story Names

Each story *should* have a name. This is what Pest uses, so keep them descriptive but short.

**Setting the name**

You may define the name in two ways:

```php
// as part of the make() static method
$story = Story::make('can create something very cool');

// using the name() method
$story = Story::make()->name('can create something cool');
```

**Getting the name**

You may get the story's name via:

```php
$story->getName(); // can create something cool

// Get the full name, including inheritance (see below)
$story->getFullName(); // can create something cool
```

Note: The full name is what is passed to pest as the test name.

**Inheritance**

See [Inheritance](/docs/stories/inheritance.md) for base information on what inheritance is.

The way naming is inherited from parent stories differs slightly from how other story attributes inherit from their parents. Parent stories offer their name as a prefix to the child story name. The full name can be retrieved with `getFullName()` method.

Example:

```php
Story::make('parent')->stories([
    $a = Story::make('child1'),
    Story::make('child2')->stories([
        $b = Story::make('grandchild1'),
    ]),
    Story::make()->stories([
        $c = Story::make('grandchild2'),
    ]),
]);

$a->getFullName(); // parent child1
$b->getFullName(); // parent child2 grandchild1
$c->getFullName(); // parent grandchild2

// Note: this code won't execute due to the stories missing required elements (such as expectations and tasks). Purely informational.
```

<a id="expectation-names">

**Expectation names**

Stories must have a can/cannot [Expectation](/docs/stories/expectations.md), and as such to retrieve the full name of a test you must supply `->can()` or `->cannot()` somewhere in the inheritance chain. The full name of a story is then prefixed with `[Can]` or `[Cannot]`.

Example:

```php
Story::make('parent')->stories([
    $a = Story::make('child 1')->can(),
    $a = Story::make('child 2')->cannot(),
]);

$a->getFullName(); // [Can] parent child 1
$b->getFullName(); // [Cannot] parent child 2
```

**Names from actions**

When you apply an [Action](/docs/actions.md) such as a scenario or task [to a story](/docs/stories/actions.md), it can offer a suffix to the story's full name.

Example:

```php
Scenario::make('as_admin')->appendName();
Story::make('do something')->can()->scenario('as_admin');

// Full Name: '[Can] do something as admin'
```