<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

trait HasPerformer
{
    protected ?Authenticatable $user = null;

    protected static ?Closure $actingAsCallback = null;

    /**
     * Specify what to do when the user is set
     */
    public static function actingAs(?Closure $actingAsCallback): void
    {
        static::$actingAsCallback = $actingAsCallback;
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

        if (static::$actingAsCallback !== null) {
            if ($this instanceof Story) {
                $this->call(static::$actingAsCallback, $this->getParameters());
            } else {
                call_user_func_array(static::$actingAsCallback, $this, $user);
            }
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
