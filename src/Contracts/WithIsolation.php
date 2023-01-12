<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithIsolation
{
    /**
     * Flush isolation flags
     */
    public static function flushIsolation(): void;

    /**
     * Add this instance to the list of isolated classes to run
     */
    public function isolate(): static;

    /**
     * Does this group (class type) have isolation enabled?
     */
    public function isolationEnabled(): bool;

    /**
     * Inherit isolation flags (i.e. run in isolation) from
     * this item's parents
     */
    public function inheritIsolation(): void;

    /**
     * Is this instance in the isolation group?
     * i.e. should this instance run?
     *
     * @requires HasInheritance
     */
    public function inIsolation(): bool;

    /**
     * Should the booting and asserting be skipped due to
     * isolation mode being enabled?
     */
    public function skipDueToIsolation(): bool;

    /**
     * Get a unique ID for this instance
     */
    public function isolationId(): string;
}
