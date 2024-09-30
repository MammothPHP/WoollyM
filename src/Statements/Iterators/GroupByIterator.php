<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Iterators;

use IteratorAggregate;
use MammothPHP\WoollyM\{ColumnIndex, DataFrame};
use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\AggProvider;
use MammothPHP\WoollyM\Stats\ModuleTypes\AggInterface;
use WeakMap;

class GroupByIterator implements IteratorAggregate
{
    public readonly Select $statement;
    protected readonly DataFrame $cache;

    public function __construct(protected readonly StatementUnfilteredColumnIterator $statementIterator, protected readonly WeakMap $groupBy)
    {
        $this->statement = $this->statementIterator->statement; // @phpstan-ignore assign.propertyType (only Select statement are pertinent. Else type error at runtime is justified)
        $this->cache = $this->executeGroupBy();
    }

    public function getIterator(): StatementRegularIterator
    {
        return $this->cache->selectAll()->getIterator(); // @phpstan-ignore return.type (It's a runtime check.)
    }

    protected function executeGroupBy(): DataFrame
    {
        $r = [];

        // Iterate over all filtered Dataframe
        foreach ($this->statementIterator as $record) {

            // Group by hash
            $hash = hash_init('sha224');

            foreach ($this->groupBy as $col => $v) {
                hash_update($hash, serialize($record[$col->getName()] ?? null));
            }

            $hash = hash_final($hash, false);

            // Hash combination not exist yet
            if (!isset($r[$hash])) {
                foreach ($this->statement->getSelect(provideColumnIndex: true) as $col) {
                    // col is grouped and selected
                    if ($col instanceof ColumnIndex && isset($this->groupBy[$col])) {
                        $r[$hash][$col->getName()] = $record[$col->getName()] ?? null;
                    }
                    // col is aggregated
                    elseif ($col instanceof AggProvider) {
                        $r[$hash][$col->as] = $col->getAggObjectProvider();
                    }
                }
            }

            foreach ($this->statement->getSelect() as $col) {
                if ($col instanceof AggProvider) {
                    $r[$hash][$col->as]->addValue($record[$col->col] ?? null);
                }
            }
        }

        foreach ($r as &$record) {
            foreach ($record as &$agg) {
                if ($agg instanceof AggInterface) {
                    $agg = $agg->getResult();
                }
            }
        }

        return DataFrame::fromArray($r);
    }
}
