<?php

declare(strict_types=1);

use MammothPHP\WoollyM\Stats\StatsInterface;

arch('strict types')->skipOnWindows()->expect('MammothPHP\WoollyM')->toUseStrictTypes();

arch('stats modules implements interfaces')->expect('MammothPHP\WoollyM\Stats\Modules')->toImplement(StatsInterface::class);
