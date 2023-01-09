<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use Illuminate\Support\Collection;

test('macros can be added to stories', function () {
    $data = Collection::make([]);

    Action::make('as_role', function (string $role) use ($data) {
        $data[] = "role:{$role}";
    });

    Story::macro('asAdmin', function () {
        return $this->action('as_role', ['role' => 'admin']);
    });

    expect($data->all())->toBe([]);

    Story::make()
        ->can()
        ->check(fn () => null)
        ->asAdmin()
        ->boot();

    expect($data->all())->toBe([
        'role:admin',
    ]);
});
