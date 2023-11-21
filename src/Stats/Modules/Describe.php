<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use MammothPHP\WoollyM\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Describe implements StatsMethodInterface
{
    public const NAME = 'describe';

    public function executeMethod(Select $select, array $arguments): array
    {
        return $this->execute($select);
    }

    protected function execute(Select $select): array
    {
        return [
            'count' => $select->count(),
            'countNumerics' => $select->count(true),
            'size' => $select->size(),
            'sum' => $select->average(),
            'mean' => $select->mean(),
        ];
    }
}
