<?php

namespace BradieTilley\StoryBoard\Enums;

enum Expectation: string
{
    /** Expectation: Always (Occurs in any scenario) */
    case ALWAYS = 'always';
    /** Expectation: Only occurs when a story is flagged as `can()` */
    case CAN = 'can';
    /** Expectation: Only occurs when a story is flagged as `cannot()` */
    case CANNOT = 'cannot';
}
