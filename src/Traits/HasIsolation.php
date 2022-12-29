<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Str;

trait HasIsolation
{
    protected static array $isolation = [];

    private ?string $isolationId = null;

    /**
     * Flush isolation flags
     */
    public static function flushIsolation(): void
    {
        static::$isolation = [];
    }

    /**
     * Add this instance to the list of isolated classes to run
     * 
     * @return $this 
     */
    public function isolate(): self
    {
        $isolationGroup = static::isolationGroup();
        $isolationId = $this->isolationId();

        static::$isolation[$isolationGroup] ??= [];
        static::$isolation[$isolationGroup][] = $isolationId;

        return $this;
    }

    /**
     * Does this group (class type) have isolation enabled?
     */
    public function isolationEnabled(): bool
    {
        $isolationGroup = static::isolationGroup();

        return !empty(static::$isolation[$isolationGroup]);
    }

    /**
     * Is this instance in the isolation group?
     * i.e. if the group has isolation enabled, should this instance run?
     * 
     * @requires HasInheritance
     */
    public function inIsolation(): bool
    {
        /** @var HasIsolation|HasInheritance $this */
        $isolationGroup = static::isolationGroup();

        $isolationParents = $this->combineFromParents('isolationId');

        foreach ($isolationParents as $isolationId) {
            if (in_array($isolationId, static::$isolation[$isolationGroup] ?? [])) {
                return true;
            }
        }

        return false;
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
    protected function isolationId(): string
    {
        return $this->isolationId ??= Str::random(64);
    }

    /**
     * Get the group key to use to isolate this class.
     */
    protected static function isolationGroup(): string
    {
        return static::class;
    }
}