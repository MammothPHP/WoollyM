<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use Closure;
use Iterator;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, NotYetImplementedException, UnknownOptionException};
use MammothPHP\WoollyM\{DataFrame, LinkedDataFrame, Record};
use Spatie\Regex\Regex;
use Stringable;

/**
 * @internal
 */
abstract class Statement implements Iterator
{
    use LinkedDataFrame;
    protected DataFrame|CacheStatus $cache = CacheStatus::UNUSED;
    protected array $where = [];
    protected ?int $limit = null;
    protected int $offset = 0;

    public function __construct(DataFrame $df)
    {
        $this->setLinkedDataFrame($df);
    }

    public function getCacheStatus(): CacheStatus
    {
        if ($this->cache instanceof CacheStatus) {
            return $this->cache;
        }

        return CacheStatus::SET;
    }

    protected function invalidateCache(): void
    {
        $this->cache = CacheStatus::UNUSED;
    }

    /**
     * Get the current filters configuration for this Select object
     */
    public function config(StatementClause $param): array|int|null
    {
        return match ($param) {
            StatementClause::WHERE => $this->where,
            StatementClause::LIMIT => $this->limit,
            StatementClause::OFFSET => $this->offset,
        };
    }

    /// Public API, config

    /**
     * Reset all filters from this statement
     * @throws InvalidSelectException
     * @throws NotYetImplementedException
     */
    public function reset(): static
    {
        return $this->resetWhere()->resetLimit(); // resetLimit also reset offSet
    }

    public function where(Closure|string ...$equal): static
    {
        $this->invalidateCache();

        foreach ($equal as $oneCondition) {
            $this->and($oneCondition);
        }

        return $this;
    }


    public function and(Closure|string ...$conditions): static
    {
        $this->isAliveOrThrowInvalidSelectException();

        foreach ($conditions as $oneCondition) {
            $this->where[] = [$oneCondition];
        }

        return $this;
    }

    public function or(Closure|string ...$conditions): static
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

    public function whereColumn(string $column, mixed $equal = null, ?string $contain = null, ?string $match = null): static
    {
        if ($equal !== null) {
            if ($equal instanceof Closure) {
                $equal = static fn(mixed $v): bool => $equal($v[$column]);
            } else {
                $equal = static fn(mixed $v): bool => $equal === $v[$column];
            }
        } elseif ($contain !== null) {
            $equal = static function (mixed $v) use ($column, $contain): bool {
                if (\is_string($v[$column]) ||
                    \is_int($v[$column]) ||
                    \is_float($v[$column]) ||
                    $v[$column] instanceof Stringable
                ) {
                    return str_contains((string) $v[$column], $contain);
                }

                return false;
            };
        } elseif ($match !== null) {
            $equal = static function (mixed $v) use ($column, $match): bool {
                if (\is_string($v[$column]) ||
                \is_int($v[$column]) ||
                \is_float($v[$column]) ||
                $v[$column] instanceof Stringable
                ) {
                    return Regex::match($match, (string) $v[$column])->hasMatch();
                }

                return false;
            };
        } else {
            throw new UnknownOptionException('Condition parameter is not set');
        }

        $this->and($equal);

        return $this;
    }

    public function whereKeyBetween(int $start = 0, ?int $end = null): static
    {
        $this->isAliveOrThrowInvalidSelectException();

        $this->where['keyBetween'] = [static fn(mixed $v, int $k): bool => $k >= $start && ($k <= $end || $end === null)];

        return $this;
    }

    /**
     * Remove all where condition from the select object
     */
    public function resetWhere(): static
    {
        $this->where = [];

        return $this;
    }

    public function limit(?int $limit = null, int $offset = 0): static
    {
        $this->invalidateCache();

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
    public function resetLimit(): static
    {
        $this->limit(limit: null, offset: 0);

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->invalidateCache();

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
    public function resetOffset(): static
    {
        $this->offset(0);

        return $this;
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

    protected function getStmtSourceIterator(): DataFrame
    {
        return $this->getCacheStatus() === CacheStatus::SET ? $this->cache : $this->getLinkedDataFrame();
    }

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
        $this->getStmtSourceIterator()->rewind();

        if ($this->getCacheStatus() !== CacheStatus::SET) {
            $this->moveToNextValidRecord();
        }
    }

    protected function getRecordArray(Record $record): array
    {
        return $record->toArray();
    }

    /**
     * @internal
     */
    public function current(): mixed
    {
        $r = $this->getStmtSourceIterator()->current();

        return $this->getRecordArray($r);
    }

    /**
     * @internal
     */
    protected function currentUnfiltered(): array
    {
        return $this->getStmtSourceIterator()->current()->toArray();
    }

    /**
     * @internal
     */
    public function key(): int
    {
        $k = $this->getStmtSourceIterator()->key();

        if ($this->getCacheStatus() == CacheStatus::SET) {
            $b = $k + 1;
        }

        return $k;
    }

    /**
     * @internal
     */
    public function next(): void
    {
        $this->getStmtSourceIterator()->next();

        if ($this->getCacheStatus() !== CacheStatus::SET) {
            $this->moveToNextValidRecord();
        }
    }

    /**
     * @internal
     */
    public function valid(): bool
    {
        return $this->getStmtSourceIterator()->valid();
    }

}
