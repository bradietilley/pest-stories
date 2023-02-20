<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use function BradieTilley\StoryBoard\debug;
use BradieTilley\StoryBoard\Exceptions\InvalidMagicMethodHandlerException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story\Config;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * This object has a performer (authenticated user) that
 * can be set and retrieved at any point. A custom override
 * for the underlying `actingAs` logic may also be provided.
 *
 * This interface has no inheritance as the authenticated models
 * are resolved by either the test suite migration or by a story
 * action (occurs after inheritance).
 *
 * Therefore, running `->user()` will immediately attempt a login
 * of the provided user.
 *
 * @property-read ?Authenticatable $user
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithCallbacks
 */
trait HasPerformer
{
    /**
     * The authorised user for this test
     */
    protected ?Authenticatable $user = null;

    /**
     * Property getter(s) for Performer trait
     */
    public function __getPerformer(string $name): mixed
    {
        if ($name === 'user') {
            return $this->user;
        }

        // @codeCoverageIgnoreStart
        throw StoryBoardException::invalidMagicMethodHandlerException($name, InvalidMagicMethodHandlerException::TYPE_PROPERTY);
        // @codeCoverageIgnoreEnd
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
    public function user(?Authenticatable $user): static
    {
        return $this->setUser($user);
    }

    /**
     * Set the user to perform this test as
     */
    public function setUser(?Authenticatable $user): static
    {
        if ($user === null) {
            debug('Setting the performer to null (logging out)');
        } else {
            /** @var string $authIdentifierName */
            $authIdentifierName = $user->getAuthIdentifierName();
            /** @var string $authIdentifier */
            $authIdentifier = $user->getAuthIdentifier();

            debug(
                sprintf(
                    'Setting the performer to user (%s: %s)',
                    $authIdentifierName,
                    $authIdentifier,
                ),
            );
        }

        $this->user = $user; /** @phpstan-ignore-line */
        if (static::hasStaticCallback('actingAs')) {
            static::runStaticCallback('actingAs', $this->getParameters());
        } else {
            $authFunction = Config::getAliasFunction('auth');

            debug(
                sprintf('Auth function resolved as `%s()`', $authFunction),
            );

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
     * Get the user to perform this test as
     */
    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }
}
