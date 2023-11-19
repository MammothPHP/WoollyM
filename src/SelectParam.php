<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

enum SelectParam
{
    case SELECT;
    case WHERE;
    case LIMIT;
    case OFFSET;
}
