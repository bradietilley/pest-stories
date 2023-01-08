<?php

namespace BradieTilley\StoryBoard\Traits;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

trait HasPerformer
{
    protected ?Authenticatable $user = null;

    /**
     * Specify what to do when the user is set
     */
    public static function actingAs(?Closure $actingAsCallback): void
    {
        /** @var HasPerformer|HasCallbacks $this */
        static::setStaticCallback('actingAs', $actingAsCallback);
    }

    /**
     * Alias of setUser()
     *
     * @return $this
     */
    public function user(Authenticatable|null $user): self
    {
        return $this->setUser($user);
    }

    /**
     * Set the user to perform this test
     *
     * @return $this
     */
    public function setUser(Authenticatable|null $user): self
    {
        $this->user = $user;

        /** @var HasPerformer|HasCallbacks $this */
        if (static::hasStaticCallback('actingAs')) {
            static::runStaticCallback('actingAs', $this->getParameters());
        } else {
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
