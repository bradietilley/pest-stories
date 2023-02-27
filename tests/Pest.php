<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Assertion;
use BradieTilley\StoryBoard\Story\Config;
use Tests\TestCase;

uses(TestCase::class)->in('Unit', 'Feature');

beforeEach(function () {
    Action::flush();
    Assertion::flush();
    Story::flushIsolation();
    Config::disableDatasets();
});
