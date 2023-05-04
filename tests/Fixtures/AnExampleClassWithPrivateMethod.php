<?php

namespace Tests\Fixtures;

class AnExampleClassWithPrivateMethod
{
    private function privateInvokeMe(): int
    {
        return 1;
    }

    protected function protectedInvokeMe(): int
    {
        return 2;
    }

    public function publicInvokeMe(): int
    {
        return 3;
    }

    private static function privateInvokeMeStatic(): int
    {
        return 4;
    }

    protected static function protectedInvokeMeStatic(): int
    {
        return 5;
    }

    public static function publicInvokeMeStatic(): int
    {
        return 6;
    }
}
