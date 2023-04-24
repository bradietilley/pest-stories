<?php

namespace Tests\Fixtures\Traits;

trait TestBootableTrait
{
    public function bootTestBootableTrait()
    {
        static::$ran[] = 'bootTestBootableTrait';
    }
}