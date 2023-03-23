<?php

namespace BradieTilley\Stories\Helpers;

use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Stringable;
use Throwable;

class VariableNaming
{
    public static function stringify(mixed $variable): string
    {
        try {
            if (is_string($variable)) {
                return $variable;
            }

            if ($variable instanceof Stringable) {
                $variable = (string) $variable;
            }

            if ($variable instanceof Arrayable) {
                $variable = $variable->toArray();
            }

            if ($variable instanceof Jsonable) {
                return $variable->toJson();
            }

            if (is_int($variable) || is_float($variable) || is_bool($variable) || is_null($variable) || is_array($variable)) {
                return (string) json_encode($variable);
            }

            if (is_object($variable)) {
                if ($variable instanceof BackedEnum) {
                    return (string) $variable->value;
                }
            }

            /** @phpstan-ignore-next-line */
            return (string) $variable;
        } catch (Throwable $e) {
            return '???';
        }
    }
}
