<?php

namespace BradieTilley\StoryBoard\Traits;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @mixin \BradieTilley\StoryBoard\Contracts\WithCallbacks
 */
trait HasPerformer
{
    protected ?Authenticatable $user = null;

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
                auth()->login($user);
            } else {
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
