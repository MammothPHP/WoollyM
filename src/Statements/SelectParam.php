<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

enum SelectParam
{
    case SELECT;
    case WHERE;
    case LIMIT;
    case OFFSET;
}