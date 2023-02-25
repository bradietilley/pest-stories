<?php

namespace BradieTilley\StoryBoard\Enums;

enum Expectation: string
{
    /** Expectation: Always (Occurs in any scenario) */
    case EXPECT_ALWAYS = 'always';
    /** Expectation: Only occurs when a story is flagged as `can()` */
    case EXPECT_CAN = 'can';
    /** Expectation: Only occurs when a story is flagged as `cannot()` */
    case EXPECT_CANNOT = 'cannot';
}
