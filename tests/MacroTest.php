<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Scenario;
use Illuminate\Support\Collection;

test('macros can be added to stories', function () {
    $data = Collection::make([]);

    Scenario::make('as_role', function (string $role) use ($data) {
        $data[] = "role:{$role}";
    });

    Story::macro('asAdmin', function () {
        return $this->scenario('as_role', [ 'role' => 'admin', ]);
    });

    expect($data->all())->toBe([]);

    Story::make()
        ->asAdmin()
        ->bootScenarios();

    expect($data->all())->toBe([
        'role:admin',
    ]);
});
