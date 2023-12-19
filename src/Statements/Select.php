<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use BadMethodCallException;
use Closure;
use Iterator;
use MammothPHP\WoollyM\{DataFrame, DataFrameModifiers};
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, NotYetImplementedException, PropertyNotExistException};
use MammothPHP\WoollyM\Stats\Modules;
use WeakMap;
use WeakReference;

class Select implements Iterator
{
    protected readonly WeakReference $df;

    protected WeakMap $select;
    protected array $where = [];
    protected ?int $limit = null;
    protected int $offset = 0;

    public function __construct(DataFrame $df, string ...$selections)
    {
        $this->setLinkedDataFrame($df);
        $this->resetSelect();

        $this->select(...$selections);
    }

    protected function setLinkedDataFrame(DataFrame $df): void
    {
        $this->df = WeakReference::create($df);
    }

    public function __clone(): void
    {
        $this->select = clone $this->select;
    }

    /**
     * Get the Linked DataFrame object
     * @throws InvalidSelectException
     */
    public function getLinkedDataFrame(): DataFrame
    {
        $this->isAliveOrThrowInvalidSelectException();

        return $this->df->get();
    }

    /**
     * @return - false if linked dataFrame no longer exist.
     */
    public function isAlive(): bool
    {
        return $this->df->get() !== null;
    }

    protected function isAliveOrThrowInvalidSelectException(): void
    {
        $this->isAlive() || throw new InvalidSelectException;
    }

    /**
     * Get the current filters configuration for this Select object
     */
    public function config(SelectParam $param): array|int|null
    {
        return match ($param) {
            SelectParam::SELECT => $this->getSelect(),
            SelectParam::WHERE => $this->where,
            SelectParam::LIMIT => $this->limit,
            SelectParam::OFFSET => $this->offset,
        };
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

        if ($module = Modules::getStatsPropertyModule($name)) {
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

        return Modules::getStatsPropertyModule($name) ? true : false;
    }

    /**
     * @internal
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($module = Modules::getStatsMethodModule($name)) {
            return $module->executeMethod($this, $arguments);
        }

        throw new BadMethodCallException('Call to undefined method ' . $name . '()');
    }


    /// Public API, config

    /**
     * Reset all filters from this Select object
     * @throws InvalidSelectException
     * @throws NotYetImplementedException
     */
    public function reset(): self
    {
        return $this->resetSelect()->resetWhere()->resetLimit(); // resetLimit also reset offSet
    }

    /**
     * Add columns to the Select object
     * @param $selections - Valid columns names to select
     * @throws InvalidSelectException
     */
    public function select(string ...$selections): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        $df = $this->getLinkedDataFrame();

        foreach ($selections as $oneSelection) {
            $this->select[$df->getColumnIndexObject($oneSelection)] = null;
        }

        return $this;
    }

    /**
     * Reset and set columns to the select object
     * @param $selections - Valid columns names to select
     * @throws InvalidSelectException
     */
    public function replaceSelect(string ...$selections): self
    {
        return $this->resetSelect()->select(...$selections);
    }

    /**
     * Unselect all columns from the Select object
     */
    public function resetSelect(): self
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

    public function where(Closure|string ...$conditions): self
    {
        foreach ($conditions as $oneCondition) {
            $this->and($oneCondition);
        }

        return $this;
    }


    public function and(Closure|string ...$conditions): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        foreach ($conditions as $oneCondition) {
            $this->where[] = [$oneCondition];
        }

        return $this;
    }

    public function or(Closure|string ...$conditions): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        foreach ($conditions as $oneCondition) {
            if (\count($this->where) === 0) {
                $this->where($oneCondition);

                continue;
            }

            end($this->where);
            $k = key($this->where);

            $this->where[$k][] = $oneCondition;
        }

        return $this;
    }

    public function whereColumnEqual(string $column, mixed $condition): self
    {
        if ($condition instanceof Closure) {
            $condition = static fn(mixed $v): bool => $condition($v[$column]);
        } else {
            $condition = static fn(mixed $v): bool => $condition === $v[$column];
        }

        $this->and($condition);

        return $this;
    }

    public function whereKeyBetween(int $start = 0, ?int $end = null): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        $this->where['keyBetween'] = [static fn(mixed $v, int $k): bool => $k >= $start && ($k <= $end || $end === null)];

        return $this;
    }

    /**
     * Remove all where condition from the select object
     */
    public function resetWhere(): self
    {
        $this->where = [];

        return $this;
    }

    public function limit(?int $limit = null, int $offset = 0): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($limit !== null && $limit < 0) {
            throw new NotYetImplementedException('$limit argument must be >= 0');
        }

        $this->limit = $limit;
        $this->offset($offset);

        return $this;
    }

    /**
     * Remove all limit and offset conditions from the select object
     */
    public function resetLimit(): self
    {
        $this->limit(limit: null, offset: 0);

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        if ($offset < 0) {
            throw new NotYetImplementedException('$offset argument must be >= 0');
        }

        $this->offset = $offset;

        return $this;
    }

    /**
     * Remove all offset condition from the select object
     */
    public function resetOffset(): self
    {
        $this->offset(0);

        return $this;
    }


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
     * Convert selection to array
     * @return array<int,array>
     */
    public function toArray(): array
    {
        $this->isAliveOrThrowInvalidSelectException();

        return iterator_to_array($this);
    }

    protected function filterColumn(array $record): array
    {
        return array_filter(
            array: $record,
            callback: fn(string $k): bool => \in_array($k, $this->getSelect(), true),
            mode: \ARRAY_FILTER_USE_KEY
        );
    }

    protected function passWhereStatement(int $key, array $record): bool
    {
        foreach ($this->where as $conditionsGroup) {
            $r = false;

            foreach ($conditionsGroup as $condition) {
                if ($condition($record, $key)) {
                    $r = true;

                    break;
                }
            }

            if ($r === false) {
                return false;
            }
        }

        return true;
    }

    public function countRecords(): int
    {
        $c = 0;

        foreach ($this as $record) {
            $c++;
        }

        return $c;
    }


    // Iterator
    protected int $limitCount = 0;
    protected int $offsetCount = 0;

    protected function moveToNextValidRecord(): void
    {
        if ($this->valid()) {
            if ($this->limit !== null && $this->limitCount >= $this->limit) {
                $this->next();
            } elseif ($this->passWhereStatement($this->key(), $this->currentUnfiltered())) {
                if ($this->offsetCount++ < $this->offset) {
                    $this->next();
                } else {
                    $this->limitCount++;
                }
            } else {
                $this->next();
            }
        }
    }

    /**
     * @internal
     */
    public function rewind(): void
    {
        $this->limitCount = 0;
        $this->offsetCount = 0;
        $this->getLinkedDataFrame()->rewind();
        $this->moveToNextValidRecord();
    }

    /**
     * @internal
     */
    public function current(): mixed
    {
        $r = $this->getLinkedDataFrame()->current();

        return $this->filterColumn($r);
    }

    /**
     * @internal
     */
    protected function currentUnfiltered(): array
    {
        return $this->getLinkedDataFrame()->current();
    }

    /**
     * @internal
     */
    public function key(): int
    {
        return $this->getLinkedDataFrame()->key();
    }

    /**
     * @internal
     */
    public function next(): void
    {
        $this->getLinkedDataFrame()->next();
        $this->moveToNextValidRecord();
    }

    /**
     * @internal
     */
    public function valid(): bool
    {
        return $this->getLinkedDataFrame()->valid();
    }

}
