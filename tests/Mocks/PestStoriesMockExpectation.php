<?php

namespace Tests\Mocks;

class PestStoriesMockExpectation
{
    public static array $calls = [];

    public static array $gets = [];

    public function __call($name, $arguments)
    {
        static::$calls[] = [
            $name,
            $arguments,
        ];

        return $this;
    }

    public function __get($name)
    {
        static::$gets[] = $name;

        return $this;
    }
}
