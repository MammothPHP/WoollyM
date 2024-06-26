<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

final readonly class AggProvider
{
    public readonly string $as;

    public function __construct(
        public string $column,
        public string $provideClass,
        ?string $as,
    ) {
        $this->as = $as ?? $this->column;
    }

    public function getAggObjectProvider(): AggInterface
    {
        return new $this->provideClass;
    }
}
