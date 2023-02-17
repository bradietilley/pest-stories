<?php

use Illuminate\Support\Facades\Config;

test('can fetch PSB config', function () {
    expect(Config::get('storyboard'))->toBeArray()->toHaveKeys(['datasets', 'naming']);
});
