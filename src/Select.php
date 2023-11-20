<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use Closure;
use Iterator;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, NotYetImplementedException};
use WeakReference;

class Select implements Iterator
{
    protected WeakReference $df;

    protected array $select = [];
    protected array $where = [];
    protected ?int $limit = null;
    protected int $offset = 0;

    public function __construct(DataFrame $df, string ...$selections)
    {
        $this->df = WeakReference::create($df);

        $this->select(...$selections);
    }

    public function getLinkedDataFrame(): DataFrameCore
    {
        $this->isAliveOrThrowInvalidSelectException();

        return $this->df->get();
    }

    public function isAlive(): bool
    {
        return $this->df->get() !== null;
    }

    protected function isAliveOrThrowInvalidSelectException(): void
    {
        $this->isAlive() || throw new InvalidSelectException;
    }

    public function config(SelectParam $param): array|int|null
    {
        return match ($param) {
            SelectParam::SELECT => $this->select,
            SelectParam::WHERE => $this->where,
            SelectParam::LIMIT => $this->limit,
            SelectParam::OFFSET => $this->offset,
        };
    }


    /// Public API, config
    public function reset(): self
    {
        return $this->resetSelect()->resetWhere()->resetLimit();
    }

    public function select(string ...$selections): self
    {
        $this->isAliveOrThrowInvalidSelectException();

        foreach ($selections as $oneSelection) {
            $this->select[] = $oneSelection;
        }

        return $this;
    }

    public function replaceSelect(string ...$selections): self
    {
        return $this->resetSelect()->select(...$selections);
    }

    public function resetSelect(): self
    {
        $this->select = [];

        return $this;
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

    public function whereColumn(string $column, mixed $condition): self
    {
        if ($condition instanceof Closure) {
            $condition = fn(mixed $v): bool => $condition($v[$column]);
        } else {
            $condition = fn(mixed $v): bool => $condition === $v[$column];
        }

        $this->and($condition);

        return $this;
    }

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

    public function resetOffset(): self
    {
        $this->offset(0);

        return $this;
    }


    public function get(): DataFrame
    {
        $this->isAliveOrThrowInvalidSelectException();

        $df = new DataFrame;

        foreach ($this as $record) {
            $df->addRecord($record);
        }

        return $df;
    }

    // Internal
    protected function filterColumn(array $record): array
    {
        return array_filter(
            array: $record,
            callback: fn(string $k): bool => \in_array($k, $this->select, true),
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


    // Iterator
    protected int $limitCount = 0;
    protected int $offsetCount = 0;

    public function moveToNextValidRecord(): void
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

    public function rewind(): void
    {
        $this->limitCount = 0;
        $this->offsetCount = 0;
        $this->getLinkedDataFrame()->rewind();
        $this->moveToNextValidRecord();
    }

    public function current(): mixed
    {
        $r = $this->getLinkedDataFrame()->current();

        return $this->filterColumn($r);
    }

    public function currentUnfiltered(): array
    {
        return $this->getLinkedDataFrame()->current();
    }

    public function key(): int
    {
        return $this->getLinkedDataFrame()->key();
    }

    public function next(): void
    {
        $this->getLinkedDataFrame()->next();
        $this->moveToNextValidRecord();
    }

    public function valid(): bool
    {
        return $this->getLinkedDataFrame()->valid();
    }

}
