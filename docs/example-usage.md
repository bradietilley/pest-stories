# Real Life Example

In this real life example, we'll do a unit test for a custom `Username` Rule. We'll test a few valid usernames and then iterate various reasons for failure and for each reason we'll provide a few different sample usernames.

`app/Rules/Username.php`

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Username implements ValidationRule
{
    public const ERROR_MUST_BE_STRING = 'The :attribute must be a string';

    public const ERROR_MUST_BE_LOWERCASE = 'The :attribute must be lowercase';

    public const ERROR_MUST_START_WITH_LETTER = 'The :attribute must start with a letter';

    public const ERROR_MUST_END_WITH_ALPHANUMERIC = 'The :attribute must end with a letter or number';

    public const ERROR_MUST_BE_ALPHANUMERIC_OR_UNDERSCORE = 'The :attribute must only consist of letters, numbers and underscores';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(self::ERROR_MUST_BE_STRING);

            return;
        }

        if (strtolower($value) !== $value) {
            $fail(self::ERROR_MUST_BE_LOWERCASE);

            return;
        }

        if (! preg_match('/^[a-z]/i', $value)) {
            $fail(self::ERROR_MUST_START_WITH_LETTER);

            return;
        }

        if (! preg_match('/[a-z0-9]$/i', $value)) {
            $fail(self::ERROR_MUST_END_WITH_ALPHANUMERIC);

            return;
        }

        if (! preg_match('/^[a-z0-9_]+$/', $value)) {
            $fail(self::ERROR_MUST_BE_ALPHANUMERIC_OR_UNDERSCORE);
        }
    }
}
```

`tests/Unit/Rules/UsernameTest.php`

```php
<?php

use App\Rules\Username;
use Illuminate\Support\Collection;
use function BradieTilley\Stories\Helpers\story;

story('Username rule')
    ->action(function (mixed $username) {
        $fail = Collection::make();

        $rule = new Username();
        $rule->validate('username', $username, fn ($message) => $fail[] = $message);

        return $fail->first();
    }, for: 'error')
    ->appends('username')
    ->stories([
        story('accepts valid username')
            ->expect('error')
            ->toBeNull()
            ->stories([
                story()->set('username', 'test'),
                story()->set('username', 'test_test'),
                story()->set('username', 'an0th3r'),
                story()->set('username', 'aw350m3'),
                story()->set('username', 'y34h'),
            ]),
        story('rejects non strings')
            ->expect('error')
            ->toBe(Username::ERROR_MUST_BE_STRING)
            ->stories([
                story()->set('username', []),
                story()->set('username', false),
                story()->set('username', true),
                story()->set('username', null),
                story()->set('username', 3.14),
            ]),
        story('rejects capital letters')
            ->expect('error')
            ->toBe(Username::ERROR_MUST_BE_LOWERCASE)
            ->stories([
                story()->set('username', 'Test'),
                story()->set('username', 'anotherTest'),
                story()->set('username', 'capitaliseD'),
            ]),
        story('rejects forbidden prefixes')
            ->expect('error')
            ->toBe(Username::ERROR_MUST_START_WITH_LETTER)
            ->stories([
                story()->set('username', '1test'),
                story()->set('username', '_test'),
                story()->set('username', '1_test'),
            ]),
        story('rejects forbidden suffixes')
            ->expect('error')
            ->toBe(Username::ERROR_MUST_END_WITH_ALPHANUMERIC)
            ->stories([
                story()->set('username', 'test_'),
            ]),
        story('rejects non-alphanumeric')
            ->expect('error')
            ->toBe(Username::ERROR_MUST_BE_ALPHANUMERIC_OR_UNDERSCORE)
            ->stories([
                story()->set('username', 'test!test'),
                story()->set('username', 'test-test'),
                story()->set('username', 'test$test'),
            ]),
    ])
    ->test();
```

The result is:

![artisan test](/docs/artisan-test-example.png)