<?php

namespace BradieTilley\StoryBoard\Traits;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @property-read ?Authenticatable $user
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithCallbacks
 */
trait HasPerformer
{
    protected ?Authenticatable $user = null;

    /**
     * Property getter(s) for Performer trait
     */
    public function __getPerformer(string $name): mixed
    {
        if ($name === 'user') {
            return $this->user;
        }
    }

    /**
     * Specify what to do when the user is set
     */
    public static function actingAs(?Closure $actingAsCallback): void
    {
        static::setStaticCallback('actingAs', $actingAsCallback);
    }

    /**
     * Alias of setUser()
     */
    public function user(Authenticatable|null $user): static
    {
        return $this->setUser($user);
    }

    /**
     * Set the user to perform this test
     */
    public function setUser(Authenticatable|null $user): static
    {
        $this->user = $user; /** @phpstan-ignore-line */
        if (static::hasStaticCallback('actingAs')) {
            static::runStaticCallback('actingAs', $this->getParameters());
        } else {
            if (! function_exists('auth')) {
                throw new \Exception('no custom actingAs handler, and auth() function does not exist!');
            }

            if ($user !== null) {
                /** @phpstan-ignore-next-line */
                auth()->login($user);
            } else {
                /** @phpstan-ignore-next-line */
                auth()->logout();
            }
        }

        return $this;
    }

    /**
     * Get the user to perform this test
     */
    public function getUser(): Authenticatable|null
    {
        return $this->user;
    }
}
