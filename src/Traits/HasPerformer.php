<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Contracts\Auth\Authenticatable;

trait HasPerformer
{
    protected ?Authenticatable $user = null;

    /**
     * Set the user to perform this test
     * 
     * @return $this
     */
    public function setUser(Authenticatable|null $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user to perform this test
     */
    public function user(): Authenticatable|null
    {
        return $this->user;
    }
}