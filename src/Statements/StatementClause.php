<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

/**
 * @internal
 */
enum StatementClause
{
    case SELECT;
    case WHERE;
    case LIMIT;
    case OFFSET;
}
