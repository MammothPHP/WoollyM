<?php

declare(strict_types=1);

namespace Tests;

use MammothPHP\WoollyM\DataFrame;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public DataFrame $df;
    public DataFrame $df1;
    public DataFrame $df2;
}
