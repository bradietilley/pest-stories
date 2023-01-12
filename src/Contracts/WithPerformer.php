<?php

namespace BradieTilley\StoryBoard\Contracts;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

interface WithPerformer
{
    /**
     * Specify what to do when the user is set
     */
    public static function actingAs(?Closure $actingAsCallback): void;

    /**
     * Alias of setUser()
     */
    public function user(Authenticatable|null $user): static;

    /**
     * Set the user to perform this test
     */
    public function setUser(Authenticatable|null $user): static;

    /**
     * Get the user to perform this test
     */
    public function getUser(): Authenticatable|null;
}
