<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

if (! class_exists('PestStoryBoardPerformerAuthenticatable')) {
    class PestStoryBoardPerformerAuthenticatable implements Authenticatable
    {
        public static int $lastId = 0;

        public function __construct(public readonly int $id)
        {
        }

        /**
         * Get the name of the unique identifier for the user.
         */
        public function getAuthIdentifierName()
        {
            return 'id';
        }

        /**
         * Get the unique identifier for the user.
         */
        public function getAuthIdentifier(): int
        {
            return $this->id;
        }

        /**
         * Get the password for the user.
         */
        public function getAuthPassword(): string
        {
            return 'password';
        }

        /**
         * Get the token value for the "remember me" session.
         */
        public function getRememberToken(): string
        {
            return 'remember';
        }

        /**
         * Set the token value for the "remember me" session.
         *
         * @param  string  $value
         */
        public function setRememberToken($value): void
        {
            //s
        }

        /**
         * Get the column name for the "remember me" token.
         */
        public function getRememberTokenName(): string
        {
            return 'token_name';
        }
    }
}

if (! class_exists('PestStoryBoardPerformerAuthFaker')) {
    class PestStoryBoardPerformerAuthFaker
    {
        protected static ?Authenticatable $user = null;

        public function login(Authenticatable $user): self
        {
            static::$user = $user;

            return $this;
        }

        public function check(): bool
        {
            return static::$user !== null;
        }

        public function logout(): self
        {
            static::$user = null;

            return $this;
        }

        public function user(): ?Authenticatable
        {
            return static::$user;
        }
    }
}

/**
 * It shouldn't exist in pest-storyboard dev so what we'll do
 * is proxy calls to auth() to a fake auth class.
 */
if (! function_exists('auth')) {
    function auth(): PestStoryBoardPerformerAuthFaker
    {
        return new PestStoryBoardPerformerAuthFaker();
    }
}

test('when performer setUser method is run, the user is logged in', function () {
    Action::make('use_some_user')->as(function (Story $story) {
        $user = new PestStoryBoardPerformerAuthenticatable(
            ++PestStoryBoardPerformerAuthenticatable::$lastId,
        );

        $story->user($user);
    });

    $users = Collection::make([]);

    expect(auth()->check())->toBeFalse()
        ->and(auth()->user())->toBeNull();

    $story = Story::make()
        ->name('auth test')
        ->action('use_some_user')
        ->can()
        ->before(function (Story $story) use ($users) {
            $users[] = $story->getUser();
        })
        ->action(function (Story $story, $user) use ($users) {
            $users[] = $story->getUser();
            $users[] = $user;
        })
        ->after(function (Story $story, $user) use ($users) {
            $users[] = $story->getUser();
            $users[] = $user;
        })
        ->check(function (Story $story, $user) use ($users) {
            $users[] = $story->getUser();
            $users[] = $user;
        })
        ->boot()
        ->perform();
        
    // 2x each of the 4 callbacks, only one for before
    expect($users)->toHaveCount(7);

    // 2x each of the 3 callbacks (excl before)
    $users = $users->filter();
    expect($users)->toHaveCount(6);

    $users = $users->unique();
    expect($users)->toHaveCount(1);

    /** @var Authenticatable $user */
    $user = $users->first();

    expect(auth()->check())->toBeTrue()
        ->and(auth()->user())->toBe($user);

    auth()->logout();
});

test('a custom actingAs callback may be specified to replace the standard auth login method', function () {
    // Set to function we control
    $login = Collection::make([]);

    // Sanity check
    expect(auth()->check())->toBeFalse();
    expect($login)->toHaveCount(0);

    Story::actingAs(fn (Authenticatable $user) => $login[] = $user);

    // Setting the acting as method doesn't mean it gets run
    expect(auth()->check())->toBeFalse();
    expect($login)->toHaveCount(0);

    Action::make('use_some_user')->as(function (Story $story) {
        $user = new PestStoryBoardPerformerAuthenticatable(
            ++PestStoryBoardPerformerAuthenticatable::$lastId,
        );

        $story->user($user);
    });

    $story = Story::make()
        ->name('auth test')
        ->action('use_some_user')
        ->can()
        ->check(fn () => null);

    // Creating a story doesn't mean it gets run
    expect(auth()->check())->toBeFalse();
    expect($login)->toHaveCount(0);

    // Booting and asserting means the action (and thus actingAs callback) is run
    $story->boot()->perform();

    // Unlike previous test, auth()->check() should be false as there was no acting as logic
    expect(auth()->check())->toBeFalse();
    // But will be logged in
    expect($login)->toHaveCount(1);

    // Reset
    auth()->logout();
    Story::actingAs(null);
});

test('passing null to setUser method will log out the current user', function () {
    // Sanity check
    expect(auth()->check())->toBeFalse();

    $user = new PestStoryBoardPerformerAuthenticatable(
        ++PestStoryBoardPerformerAuthenticatable::$lastId,
    );

    $story = Story::make()
        ->can()
        ->name('auth test')
        ->user($user);

    // Should be logged in
    expect($story->user)->toBe($user);
    expect(auth()->check())->toBeTrue();

    $story->user(null);

    // Should be logged out now
    expect($story->user)->toBeNull();
    expect(auth()->check())->toBeFalse();
});
