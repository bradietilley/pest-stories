<?php

use Illuminate\Contracts\Container\BindingResolutionException;

test('my test')
    ->action('my test')
    ->throws(BindingResolutionException::class, 'Target class [url] does not exist.');
