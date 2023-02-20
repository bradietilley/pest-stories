<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Enums;

enum StoryStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case SUCCESS = 'success';
    case FAILURE = 'failure';
    case SKIPPED = 'skipped';
    case INCOMPLETE = 'incomplete';
}
