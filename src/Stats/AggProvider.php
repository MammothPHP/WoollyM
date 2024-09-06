<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\Stats\ModuleTypes\AggInterface;

final readonly class AggProvider
{
    public readonly string $as;

    public function __construct(
        public string $col,
        public string $provideClass,
        ?string $as,
    ) {
        $this->as = $as ?? $this->provideClass::NAME . '(' . $this->col . ')';
    }

    public function getAggObjectProvider(): AggInterface
    {
        return new $this->provideClass;
    }
}
