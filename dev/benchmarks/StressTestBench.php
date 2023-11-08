<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use PhpBench\Attributes as Bench;

class IntensiveUsageBench
{
    #[Bench\Warmup(1)]
    #[Bench\Iterations(5)]
    #[Bench\Revs(10)]
    #[Bench\OutputTimeUnit('milliseconds')]
    public function benchSimpleStressTest1(): void
    {
        $df = DataFrame::fromArray($model = [[
            'columnName1' => 0,
            'columnName2' => 1,
            'columnName3' => 2,
            'columnName4' => 3,
        ]]);

        for ($i = 0; $i < 5_000_000; $i++) {
            $df[] = $model;
        }

        \count($df);
    }
}
