<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\ModuleTypes;

enum ModuleStmtAccessType: string
{
    case StatsProperty = StatsPropertyInterface::class;
    case StatsMethod = StatsMethodInterface::class;
}
