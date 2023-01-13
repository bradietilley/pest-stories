<?php

use Illuminate\Contracts\Console\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;

test('can fetch PSB config', function () {
    expect(Config::get('storyboard'))->toBeArray()->toHaveKeys(['datasets', 'naming']);
});