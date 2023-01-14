<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\InvalidMagicAliasException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story\Config;
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

        throw StoryBoardException::invalidMagicAliasException($name, InvalidMagicAliasException::TYPE_PROPERTY);
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
            $authFunction = Config::getAliasFunction('auth');

            if ($user !== null) {
                /** @phpstan-ignore-next-line */
                $authFunction()->login($user);
            } else {
                /** @phpstan-ignore-next-line */
                $authFunction()->logout();
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
