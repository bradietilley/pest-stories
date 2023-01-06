<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Str;

trait HasIsolation
{
    protected static array $isolationStories = [];

    private ?string $isolationId = null;

    /**
     * Flush isolation flags
     */
    public static function flushIsolation(): void
    {
        static::$isolationStories = [];
    }

    /**
     * Add this instance to the list of isolated classes to run
     * 
     * @return $this 
     */
    public function isolate(): self
    {
        static::$isolationStories[] = $this->isolationId();

        return $this;
    }

    /**
     * Does this group (class type) have isolation enabled?
     */
    public function isolationEnabled(): bool
    {
        return !empty(static::$isolationStories);
    }

    public function inheritIsolation(): void
    {
        /** @var self|HasInheritance $this */

        foreach ($this->getAncestors() as $ancestor) {
            if ($ancestor === $this) {
                continue;
            }

            if ($ancestor->inIsolation()) {
                $this->isolate();
            }
        }
    }

    /**
     * Is this instance in the isolation group?
     * i.e. should this instance run?
     * 
     * @requires HasInheritance
     */
    public function inIsolation(): bool
    {
        /** @var HasIsolation|HasInheritance $this */
        return in_array($this->isolationId(), static::$isolationStories);
    }

    /**
     * Should the booting and asserting be skipped due to
     * isolation mode being enabled?
     */
    public function skipDueToIsolation(): bool
    {
        return $this->isolationEnabled() && ! $this->inIsolation();
    }

    /**
     * Get a unique ID for this instance
     */
    public function isolationId(): string
    {
        return $this->isolationId ??= Str::random(64);
    }
}