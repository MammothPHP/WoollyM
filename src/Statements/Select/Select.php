<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use BadMethodCallException;
use Countable;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, PropertyNotExistException};
use MammothPHP\WoollyM\{DataFrame, Record};
use MammothPHP\WoollyM\Statements\{Statement, StatementClause};
use MammothPHP\WoollyM\Stats\{AggProvider, StmtModules};
use MammothPHP\WoollyM\Stats\Bases\Group;
use MammothPHP\WoollyM\Stats\ModuleTypes\AggInterface;
use WeakMap;

class Select extends Statement implements Countable
{
    protected WeakMap $select;
    protected bool $byPassColumnFilter = false;

    public function __construct(DataFrame $df, string|Group|AggProvider ...$selections)
    {
        parent::__construct($df);

        $this->resetSelect()->select(...$selections);
    }

    public function __clone(): void
    {
        $this->select = clone $this->select;
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
    public function select(string|Group|AggProvider ...$selections): static
    {
        $this->isAliveOrThrowInvalidSelectException();

        $df = $this->getLinkedDataFrame();

        foreach ($selections as $oneSelection) {
            if (\is_string($oneSelection)) {
                $this->select[$df->getColumnIndexObject($oneSelection)] = null;
            } else {
                $this->select[$oneSelection->col] = $oneSelection;
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
    public function getSelect(): array
    {
        $r = [];

        foreach ($this->select as $col => $v) {
            $r[] = $col->getName();
        }

        return $r;
    }

    // Record array

    protected function getRecordArray(Record $record): array
    {
        if ($this->byPassColumnFilter) {
            return parent::getRecordArray($record);
        } else {
            $recordArray = $record->toArray();
            $r = [];

            foreach ($this->getSelect() as $columnName) {
                $r[$columnName] = $recordArray[$columnName] ?? null;
            }

            return $r;
        }
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

    public function groupBy(string|AggProvider ...$args): DataFrame
    {
        // Check invalid columns
        array_walk($args, fn(string|AggProvider $col) => $this->df->mustHaveColumn(\is_string($col) ? $col : $col->col));

        $this->byPassColumnFilter = true;
        $r = [];

        foreach ($this as $record) {
            $hash = hash_init('sha224');

            foreach ($args as $col) {
                if (\is_string($col)) {
                    hash_update($hash, serialize($record[$col] ?? null));
                }
            }

            $hash = hash_final($hash, false);

            if (!isset($r[$hash])) {
                foreach ($args as $col) {
                    if (\is_string($col)) {
                        $r[$hash][$col] = $record[$col] ?? null;
                    } else {
                        $r[$hash][$col->as] = $col->getAggObjectProvider();
                    }
                }
            }

            foreach ($args as $col) {
                if (!\is_string($col)) {
                    $r[$hash][$col->as]->addValue($record[$col->col]);
                }
            }
        }

        foreach ($r as &$record) {
            foreach ($record as &$value) {
                if ($value instanceof AggInterface) {
                    $value = $value->getResult();
                }
            }
        }

        $this->byPassColumnFilter = false;

        return DataFrame::fromArray($r);
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
        $c = 0;

        foreach ($this as $record) {
            $c++;
        }

        return $c;
    }

}
