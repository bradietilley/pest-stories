<?php

namespace BradieTilley\StoryBoard\Exceptions;

use Exception;

/**
 * Exception for when a Trait handles a magic method (e.g. __get, __call, etc)
 * but does not "know" what to do with the given magic method call.
 */
class InvalidMagicMethodHandlerException extends Exception
{
    public const TYPE_PROPERTY = 'property';

    public const TYPE_METHOD = 'method';

    public const TYPE_STATIC_METHOD = 'static method';

    public function __construct(
        public string $name,
        public string $type,
    ) {
        parent::__construct(
            message: sprintf(
                'Failed to locate the `%s%s%s` magic %s shandler',
                match ($type) {
                    self::TYPE_PROPERTY => '$',
                    self::TYPE_STATIC_METHOD => '::',
                    default => '',
                },
                $name,
                match ($type) {
                    self::TYPE_METHOD => '()',
                    self::TYPE_STATIC_METHOD => '()',
                    default => '',
                },
                $type,
            ),
        );
    }
}
