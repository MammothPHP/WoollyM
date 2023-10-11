<?php

declare(strict_types=1);

namespace Tests;

use CondorcetPHP\Oliphant\DataFrame;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public DataFrame $df;
}
