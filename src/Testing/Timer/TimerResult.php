<?php

namespace BradieTilley\StoryBoard\Testing\Timer;

enum TimerResult: int
{
    case FAILED = 0;
    case TIMED_OUT = 1;
    case PASSED = 2;
}