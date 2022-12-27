<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Contracts\Auth\Authenticatable;

trait HasPerformer
{
    protected ?Authenticatable $user = null;

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