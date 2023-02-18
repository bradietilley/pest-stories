<?php

namespace BradieTilley\StoryBoard\Exceptions;

use Exception;

class InvalidConfigurationException extends Exception
{
    public static function mustBeString(string $key, mixed $value): self
    {
        return self::mustBe('string', $key, $value);
    }

    public static function mustBeInteger(string $key, mixed $value): self
    {
        return self::mustBe('string', $key, $value);
    }

    public static function mustBeBoolean(string $key, mixed $value): self
    {
        return self::mustBe('string', $key, $value);
    }

    public static function mustBe(string $type, string $key, mixed $value): self
    {
        return new self(
            sprintf(
                'Invalid config: The `%s` key must be a %s; %s found.',
                $key,
                $type,
                self::identifyType($value),
            ),
        );
    }

    private static function identifyType(mixed $value): string
    {
        return match (true) {
            is_string($value) => 'string',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_bool($value) => 'boolean',
            is_array($value) => 'array',
            is_object($value) => 'object',
            is_resource($value) => 'resource',
            default => 'unknown',
        };
    }
}
