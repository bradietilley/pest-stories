<?php

namespace BradieTilley\StoryBoard\Enums;

enum StoryStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case SUCCESS = 'success';
    case FAILURE = 'failure';
    case SKIPPED = 'skipped';
    case RISKY = 'risky';
    case INCOMPLETE = 'incomplete';
}
