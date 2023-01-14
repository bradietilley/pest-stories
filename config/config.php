<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    /**
     * Datasets can be enabled whereby doing so will convert the parent-most
     * story into a test suite with its children (all nested children) as
     * corresponding datasets.
     *
     * Example:
     *
     * Create Something
     *                +--- 1
     *                +--- 2
     *                +--- 3
     *                     +--- i
     *                     +--- ii
     *
     * [Can] Create something with dataset "1"
     * [Can] Create something with dataset "2"
     * [Can] Create something with dataset "3i"
     * [Can] Create something with dataset "3ii"
     */
    'datasets' => false,

    'aliases' => [
        /**
         * The `test` function for StoryBoard is the function that is used to run
         * a given story as a test. The signature of this function must match that
         * of Pest's `test()` function. Specifically, this is:
         *
         *     function(string $nameOfTest, Closure $testRunner): mixed;
         */
        'test' => 'test',

        /**
         * The `auth` function for StoryBoard is used when a story performer (User)
         * is set, but only when an `actingAs` callback has not been specified. By
         * default this means without the `actingAs` callback, the `auth()` function
         * provided by Laravel is used. This function must return a class with the following signature:
         *
         *     public function login(Authenticatable $user): mixed;
         *
         *     public function logout(): mixed;
         */
        'auth' => 'auth',

        /**
         * The class to use when creating Stories via the `story()` method.
         *
         * The class returned must be an instance of `\BradieTilley\StoryBoard\Story`
         */
        'story' => \BradieTilley\StoryBoard\Story::class,

        /**
         * The class to use when creating Actions via the `action()` method.
         *
         * The class returned must be an instance of `\BradieTilley\StoryBoard\Story\Action`
         */
        'action' => \BradieTilley\StoryBoard\Story\Action::class,

        /**
         * The class to use when creating Tags via the `tag()` method.
         *
         * The class returned must be an instance of `\BradieTilley\StoryBoard\Story\Tag`
         */
        'tag' => \BradieTilley\StoryBoard\Story\Tag::class,
    ],

    /**
     * (Work In Progress:)
     *
     * Choose where various sources of story names can come from and where
     * in the story name they are arranged.
     *
     * - `inline`
     *     - Description: The relevant name fragment is embedded inline in accordance to hierarchy
     *     - Example Story: `Story::make('a')->stories([ Story::make('b')->can()->stories([ Story::make('c') ]) ])`
     *     - Example Name: `a b [Can] c`
     *
     * - `prefix`
     *     - Description: The relevant name fragment is embedded at the start of the name
     *     - Example Story: `Story::make('a')->stories([ Story::make('b')->can()->stories([ Story::make('c') ]) ])`
     *     - Example Name: `[Can] a b c`
     *
     * - `suffix`
     *     - Description: The relevant name fragment is embedded at the end of the name
     *     - Example Story: `Story::make('a')->stories([ Story::make('b')->can()->stories([ Story::make('c') ]) ])`
     *     - Example Name: `a b c [Can]`
     *
     * - `null`
     *     - Description: The relevant name fragment is no embedded anywhere
     *     - Example Story: `Story::make('a')->stories([ Story::make('b')->can()->stories([ Story::make('c') ]) ])`
     *     - Example Name: `a b c`
     */
    'naming' => [
        'expectations' => 'prefix', // inline|prefix|suffix|null
        'tags' => 'suffix', // inline|prefix|suffix|null
        'actions' => 'inline', // inline|prefix|suffix|null
    ],
];
