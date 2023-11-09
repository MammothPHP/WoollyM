<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

enum ModuleType: string
{
    case ColumnStatsProperty = ColumnStatsPropertyInterface::class;
    case ColumnStatsMethod = ColumnStatsMethodInterface::class;
}