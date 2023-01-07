<?php

namespace BradieTilley\StoryBoard\Testing\Timer;

enum TimerUnit: string
{
    case SECOND = 's';
    case MILLISECOND = 'ms';
    case MICROSECOND = 'us';

    public function factor(): int
    {
        return match ($this) {
            self::SECOND => 1000000,
            self::MILLISECOND => 1000,
            self::MICROSECOND => 1,
        };
    }

    public function toUnit(int|float $value, TimerUnit $unit): int|float
    {
        $fromFactor = $this->factor(); // e.g. 1000000
        $toFactor = $unit->factor(); // e.g. 1

        // second (1mil) to ms (1000) = delta of 1000
        $deltaFactor = ($fromFactor / $toFactor);

        // value is seconds (e.g. 2) * delta (e.g. 1000) = 2000 (ms)
        $value = $value * $deltaFactor;

        if ($fromFactor >= $toFactor) {
            return (int) $value;
        }

        return (float) $value;
    }

    /**
     * Standardise the given value to microseconds
     */
    public function toMicroseconds(int|float $value): int
    {
        return (int) $this->toUnit($value, self::MICROSECOND);
    }

    /**
     * Convert the given time and unit to seconds
     */
    public function toSeconds(int $value): float
    {
        return (float) $this->toUnit($value, self::SECOND);
    }

    public function format(int|float $value): string
    {
        $whole = floor($value) === ceil($value);
        $one = ($value === 1) || ($value === 1.0);
        $value = ($whole) ? (int) $value : $value;

        if (! $whole) {
            $value = number_format($value, 6, '.', '');
            $value = rtrim($value, '.0');
        }

        return sprintf(
            '%s %s%s',
            $value,
            $this->label(),
            ($one) ? '' : 's',
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::SECOND => 'second',
            self::MILLISECOND => 'millisecond',
            self::MICROSECOND => 'microsecond',
        };
    }
}