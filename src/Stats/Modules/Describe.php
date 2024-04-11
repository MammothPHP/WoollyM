<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\StatsMethodInterface;

class Describe implements StatsMethodInterface
{
    public const string NAME = 'describe';

    public function executeMethod(Select $select, array $arguments): array
    {
        return $this->execute($select);
    }

    protected function execute(Select $select): array
    {
        return [
            'count records' => $select->countRecords(),
            'size' => $select->size(),
            'sum' => $select->average(),
            'mean' => $select->mean(),
            'max' => $select->max(),
            'min' => $select->min(),
        ];
    }
}
