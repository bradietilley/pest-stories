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

    /**
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
        'tags'         => 'suffix', // inline|prefix|suffix|null
        'actions'      => 'inline', // inline|prefix|suffix|null
    ],
];