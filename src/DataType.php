<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hgehring
 * Date: 4/30/18
 * Time: 10:33 PM
 */

namespace Archon;

class DataType
{
    public const NUMERIC    = 0x001;
    public const INTEGER    = 0x010;
    public const DATETIME   = 0x011;
    public const CURRENCY   = 0x100;
    public const ACCOUNTING = 0x101;
}
