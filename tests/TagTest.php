<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Tag;
use Illuminate\Support\Collection;

test('a story can have tags applied', function () {
    $story = Story::make()->assert(fn () => null)->can()->tag('issue', '123');
    $story->register();

    expect($story->getTagsData())->toBe([
        'issue' => '123',
    ]);
});

test('a story can have multiple tags applied', function () {
    $story = Story::make()->assert(fn () => null)->can()->tag([
        'issue' => '123',
        'milestone' => '1.2',
        'somerandomidentifier',
    ]);
    $story->register();

    expect($story->getTagsData())->toBe([
        'issue' => '123',
        'milestone' => '1.2',
        'somerandomidentifier' => 'somerandomidentifier',
    ]);
});

test('a story can inherit tags from parents', function () {
    Story::make('parent')
        ->tag('issue', '123')
        ->stories([
            $child1 = Story::make('child 1'),
            $child2 = Story::make('child 2')->tag('issue', '456'),
            $child3 = Story::make('child 2')->tag([
                'issue' => '456',
                'something' => 'nice',
            ]),
        ])
        ->storiesAll;
    
    expect($child1->register()->getTagsData())->toBe([
        'issue' => '123',
    ]);

    expect($child2->register()->getTagsData())->toBe([
        'issue' => '456',
    ]);

    expect($child3->register()->getTagsData())->toBe([
        'issue' => '456',
        'something' => 'nice',
    ]);
});

test('a story can have tags resolved by callback', function () {
    Story::make('parent')
        ->tag('issue', fn () => '123')
        ->stories([
            $child1 = Story::make('child 1'),
            $child2 = Story::make('child 2')->tag('issue', fn () => '456'),
            $child3 = Story::make('child 2')->tag([
                'issue' => fn () => '456',
                'something' => fn () => 'nice',
            ]),
        ])
        ->storiesAll;
    
    expect($child1->register()->getTagsData())->toBe([
        'issue' => '123',
    ]);

    expect($child2->register()->getTagsData())->toBe([
        'issue' => '456',
    ]);

    expect($child3->register()->getTagsData())->toBe([
        'issue' => '456',
        'something' => 'nice',
    ]);
});

test('a story can have shared instance-based tags', function () {
    $tags = [
        '123' => new Tag('issue', '123'),
        '456' => new Tag('issue', '456'),
        'something' => new Tag('something', 'nice'),
    ];

    Story::make('parent')
        ->tag($tags['123'])
        ->stories([
            $child1 = Story::make('child 1'),
            $child2 = Story::make('child 2')->tag($tags['456']),
            $child3 = Story::make('child 2')->tag([
                'issue' => $tags['456'],
                'something' => $tags['something'],
            ]),
        ])
        ->storiesAll;
    
    expect($child1->register()->getTagsData())->toBe([
        'issue' => '123',
    ]);

    expect($child2->register()->getTagsData())->toBe([
        'issue' => '456',
    ]);

    expect($child3->register()->getTagsData())->toBe([
        'issue' => '456',
        'something' => 'nice',
    ]);
});

test('tags can be compiled to string form', function () {
    $tags = [];

    $tags[] = new Tag('issue', 123);
    $tags[] = new Tag('issue', '456');
    $tags[] = new Tag('client_approved', false);
    $tags[] = new Tag('client_approved', true);

    $tags = Collection::make($tags)->map(fn (Tag $tag) => (string) $tag)->toArray();

    expect($tags)->toBe([
        'issue: 123',
        'issue: 456',
        'client_approved: false',
        'client_approved: true',
    ]);
});

test('stories may be suffixed with tags', function () {
    Story::make('a parent')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null)
        ->tag([
            'issue' => '123',
            'client_approved' => false,
            'something else',
        ])
        ->appendTags()
        ->stories([
            $story = Story::make('a child'),
        ]);

    $story->run();

    expect($story->getTestName())->toBe('[Can] a parent a child | issue: 123 | client_approved: false | something else');
});
