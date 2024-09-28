<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use BadMethodCallException;
use Countable;
use Iterator;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, PropertyNotExistException};
use MammothPHP\WoollyM\{DataFrame, Record};
use MammothPHP\WoollyM\Statements\{Statement, StatementClause};
use MammothPHP\WoollyM\Statements\Iterators\{GroupByIterator, StatementRegularIterator, StatementUnfilteredColumnIterator};
use MammothPHP\WoollyM\Stats\{AggProvider, StmtModules};
use MammothPHP\WoollyM\Stats\Modules\First;
use WeakMap;

class Select extends Statement implements Countable
{
    protected WeakMap $select;
    public WeakMap $groupBy;
    protected bool $byPassColumnFilter = false;

    public function __construct(DataFrame $df, string|AggProvider ...$selections)
    {
        $this->groupBy = new WeakMap;

        parent::__construct($df);

        $this->resetSelect()->select(...$selections);
    }

    public function __clone(): void
    {
        $this->select = clone $this->select;
    }
    protected function getBaseIterator(): StatementRegularIterator|Iterator
    {
        if ($this->countGroupBy() !== 0) {
            return (new GroupByIterator(new StatementUnfilteredColumnIterator($this)))->getIterator();
        }

        return parent::getBaseIterator();
    }

    public function config(StatementClause $param): array|int|null
    {
        if ($param === StatementClause::SELECT) {
            return $this->getSelect();
        }

        return parent::config($param);
    }

    public function reset(): static
    {
        return parent::reset()->resetSelect();
    }
    public function select(string|AggProvider ...$selections): static
    {
        $this->invalidateCache();

        $this->isAliveOrThrowInvalidSelectException();

        $df = $this->getLinkedDataFrame();

        foreach ($selections as $oneSelection) {
            if (\is_string($oneSelection)) {
                $this->select[$df->getColumnIndexObject($oneSelection)] = null;
            } else {
                $this->select[$df->getColumnIndexObject($oneSelection->col)] = $oneSelection;
            }
        }

        return $this;
    }

    /**
     * Reset and set columns to the select object
     * @param $selections - Valid columns names to select
     * @throws InvalidSelectException
     */
    public function replaceSelect(string ...$selections): static
    {
        return $this->resetSelect()->select(...$selections);
    }

    /**
     * Unselect all columns from the Select object
     */
    public function resetSelect(): static
    {
        $this->select = new WeakMap;

        return $this;
    }

    /**
     * Get the selected columns
     * @return string[]
     */
    public function getSelect(bool $forceString = false, bool $provideColumnIndex = false): array
    {
        $r = [];

        foreach ($this->select as $col => $v) {
            // forceString
            if ($forceString) {
                $r[] = $col->getName();
            }
            // regular select but in a groupBy context
            elseif ($v === null && $this->countGroupBy() !== 0 && !isset($this->groupBy[$col])) {
                $r[] = First::col(column: $col->getName(), as: $col->getName());
            }
            // is an agg provider
            elseif ($v instanceof AggProvider) {
                $r[] = $v;
            } else {
                $r[] = $provideColumnIndex ? $col : $col->getName();
            }


        }

        return $r;
    }

    public function groupBy(string ...$group): static
    {
        $this->invalidateCache();

        // Check invalid columns
        $group = array_unique($group, \SORT_REGULAR);
        array_walk($group, fn(string $col) => $this->df->mustHaveColumn($col));

        foreach ($group as $col) {
            $this->groupBy[$this->df->getColumnIndexObject($col)] = null;
        }

        return $this;
    }

    /**
     * @internal
     */
    public function countGroupBy(): int
    {
        return \count($this->groupBy ?? []);
    }

    // MODULES Implementation

    // Implement property & methods overloading

    /**
     * @internal
     */
    public function __set(string $name, mixed $value): void
    {
        throw new PropertyNotExistException;
    }

    /**
     * @internal
     */
    public function __get(string $name): mixed
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($module = StmtModules::getStatsPropertyModule($name)) {
            return $module->executeProperty($this);
        }

        throw new PropertyNotExistException('Call to undefined property ' . $name);
    }

    /**
     * @internal
     */
    public function __isset(string $name): bool
    {
        $this->isAliveOrThrowInvalidSelectException();

        return StmtModules::getStatsPropertyModule($name) ? true : false;
    }

    /**
     * @internal
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($module = StmtModules::getStatsMethodModule($name)) {
            return $module->executeMethod($this, $arguments);
        }

        throw new BadMethodCallException('Call to undefined method ' . $name . '()');
    }

    /**
     *
     */
    public function describe(): array
    {
        return [
            'count records' => $this->countRecords(),
            'size' => $this->size(),
            'sum' => $this->average(),
            'mean' => $this->mean(),
            'max' => $this->max(),
            'min' => $this->min(),
        ];
    }

    /**
     * Alias of countRecords. Implement Countable.
     * @internal
     */
    public function count(): int
    {
        return $this->countRecords();
    }


    // Select Methods

    /**
     * Get a record by key
     */
    public function record(int $key): Record
    {
        return $this->getLinkedDataFrame()->getRecord($key);
    }

    /**
     * Export Selection as a new Dataframe object
     * @throws InvalidSelectException
     */
    public function export(): DataFrame
    {
        $this->isAliveOrThrowInvalidSelectException();

        $df = new DataFrame;

        foreach ($this as $record) {
            $df->addRecord($record);
        }

        return $df;
    }

    /**
     * Get selection as PHP array
     * @return array<int,array>
     */
    public function toArray(): array
    {
        $this->isAliveOrThrowInvalidSelectException();

        return iterator_to_array($this);
    }

    /**
     * Count Records in statement
     */
    public function countRecords(): int
    {
        return iterator_count($this);
    }

}
