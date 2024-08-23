<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\Stats\Interfaces\{StatsMethodInterface, StatsPropertyInterface};

enum ModuleType: string
{
    case StatsProperty = StatsPropertyInterface::class;
    case StatsMethod = StatsMethodInterface::class;
}
