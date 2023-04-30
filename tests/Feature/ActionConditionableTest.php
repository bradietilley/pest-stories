<?php

use BradieTilley\Stories\Concerns\Stories;
use Tests\Fixtures\AnExampleActionWithMethods;

uses(Stories::class);

test('actions can conditionally run things')
    ->action(
        AnExampleActionWithMethods::make()
            ->when(
                true,
                fn (AnExampleActionWithMethods $action) => $action->abc(111),
                fn (AnExampleActionWithMethods $action) => $action->abc(222),
            )
            ->when(
                false,
                fn (AnExampleActionWithMethods $action) => $action->def(333),
                fn (AnExampleActionWithMethods $action) => $action->def(444),
            ),
    )
    ->action(function (int $abc, int $def) {
        expect($abc)->toBe(111);
        expect($def)->toBe(444);
    });
