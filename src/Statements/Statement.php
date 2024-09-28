<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements;

use Closure;
use Iterator;
use IteratorAggregate;
use LimitIterator;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, NotYetImplementedException, UnknownOptionException};
use MammothPHP\WoollyM\{DataFrame, LinkedDataFrame, Record};
use MammothPHP\WoollyM\Statements\Iterators\StatementRegularIterator;
use Spatie\Regex\Regex;
use Stringable;

/**
 * @internal
 */
abstract class Statement implements IteratorAggregate
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
        foreach ($equal as $oneCondition) {
            $this->and($oneCondition);
        }

        return $this;
    }


    public function and(Closure|string ...$conditions): static
    {
        $this->invalidateCache();

        $this->isAliveOrThrowInvalidSelectException();

        foreach ($conditions as $oneCondition) {
            $this->where[] = [$oneCondition];
        }

        return $this;
    }

    public function or(Closure|string ...$conditions): static
    {
        $this->invalidateCache();

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
        $this->invalidateCache();

        $this->isAliveOrThrowInvalidSelectException();

        $this->where['keyBetween'] = [static fn(mixed $v, int $k): bool => $k >= $start && ($k <= $end || $end === null)];

        return $this;
    }

    /**
     * Remove all where condition from the select object
     */
    public function resetWhere(): static
    {
        $this->invalidateCache();

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

    /**
     * @internal
     */
    public function passWhereStatement(int $key, Record $record): bool
    {
        foreach ($this->where as $conditionsGroup) {
            $r = false;

            foreach ($conditionsGroup as $condition) {
                if ($condition($record->toArray(), $key)) {
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
    public function getIterator(): Iterator
    {
        $statementIterator = new StatementRegularIterator($this);

        if ($this->limit !== null || $this->offset > 0) {
            $statementIterator = new LimitIterator(iterator: $statementIterator, limit: $this->limit ?? -1, offset: $this->offset);
        }

        return $statementIterator;
    }
}
