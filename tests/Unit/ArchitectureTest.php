<?php

declare(strict_types=1);

use MammothPHP\WoollyM\Stats\StatsInterface;

test('strict types')->expect('MammothPHP\WoollyM')->toUseStrictTypes();

test('stats modules implements interfaces')->expect('MammothPHP\WoollyM\Stats\Modules')->toImplement(StatsInterface::class);
