<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

enum ModuleType: string
{
    case StatsProperty = StatsPropertyInterface::class;
    case StatsMethod = StatsMethodInterface::class;
}
