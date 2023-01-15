<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This object (Story) can be target to run in isolatation.
 * This means only it and its children will run/boot.
 *
 * @mixin WithInheritance
 */
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
     * Does this objec type (e.g. Stories) have isolation enabled?
     */
    public function isolationEnabled(): bool;

    /**
     * Is this instance in the isolation group?
     * i.e. should this instance run?
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

    /**
     * Inherit isolation flags (i.e. run in isolation) from
     * this item's parents
     */
    public function inheritIsolation(): void;
}
