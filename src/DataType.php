<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hgehring
 * Date: 4/30/18
 * Time: 10:33 PM
 */

namespace Archon;

enum DataType
{
    case NUMERIC;
    case INTEGER;
    case DATETIME;
    case CURRENCY;
    case ACCOUNTING;
}
