<?php

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\AlarmException;
use BradieTilley\Stories\Helpers\StoryAliases;

/**
 * @method static static make(int|float $amount, string $unit = self::UNIT_MICROSECONDS)
 */
class Alarm
{
    public const UNIT_MICROSECONDS = 'us';

    public const UNIT_MILLISECONDS = 'ms';

    public const UNIT_SECONDS = 's';

    protected int $microseconds = 0;

    protected ?int $start = null;

    protected ?int $end = null;

    public function __construct(int|float $amount, string $unit = self::UNIT_MICROSECONDS)
    {
        $this->setLimit($amount, $unit);
    }

    public static function make(): static
    {
        $class = StoryAliases::getClassAlias(Alarm::class);

        /** @var static $class */
        return new $class(...func_get_args());
    }

    /**
     * Set the time limit
     */
    public function setLimit(int|float $amount, string $unit = self::UNIT_MICROSECONDS): static
    {
        if ($unit === self::UNIT_SECONDS) {
            $this->microseconds = (int) ($amount * 1000000);

            return $this;
        }

        if ($unit === self::UNIT_MILLISECONDS) {
            $this->microseconds = (int) ($amount * 1000);

            return $this;
        }

        $this->microseconds = (int) $amount;

        return $this;
    }

    /**
     * Get time limit in microseconds
     */
    public function microseconds(): int
    {
        return $this->microseconds;
    }

    /**
     * Get time limit in seconds formatted to 3 decimals
     */
    public function seconds(): string
    {
        return number_format($this->microseconds() / 1000000, decimals: 6);
    }

    /**
     * Start the timer
     */
    public function start(): static
    {
        $this->start = (int) (microtime(true) * 1000000);

        return $this;
    }

    /**
     * Stop the timer and throw the AlarmException
     * if the limit was reached.
     *
     * @throws AlarmException
     */
    public function stop(): static
    {
        $this->end = (int) (microtime(true) * 1000000);
        $total = $this->end - $this->start;

        // Manual Alarm if number of seconds wasn't exceeded
        if ($total > $this->microseconds()) {
            throw AlarmException::make($this);
        }

        return $this;
    }
}
