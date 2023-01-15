<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Str;

/**
 * This object (Story) can be target to run in isolatation.
 * This means only it and its children will run/boot.
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithInheritance
 */
trait HasIsolation
{
    /**
     * Container of recorded story IDs (Story isolation IDs)
     * that are to be run as part of the current isolation mode.
     *
     * When empty, all stories are run.
     */
    protected static array $isolationStories = [];

    /**
     * The Story's isolation ID
     */
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
     */
    public function isolate(): static
    {
        static::$isolationStories[] = $this->isolationId();

        return $this;
    }

    /**
     * Does this objec type (e.g. Stories) have isolation enabled?
     */
    public function isolationEnabled(): bool
    {
        return ! empty(static::$isolationStories);
    }

    /**
     * Is this instance in the isolation group?
     * i.e. should this instance run?
     */
    public function inIsolation(): bool
    {
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

    /**
     * Inherit isolation flags (i.e. run in isolation) from
     * this item's parents
     */
    public function inheritIsolation(): void
    {
        foreach ($this->getAncestors() as $ancestor) {
            if ($ancestor === $this) {
                continue;
            }

            if ($ancestor->inIsolation()) {
                $this->isolate();
            }
        }
    }
}
