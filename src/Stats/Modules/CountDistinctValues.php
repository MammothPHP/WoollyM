<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use ReflectionClass;
use SplObjectStorage;

class CountDistinctValues extends AbstractAgg
{
    public const string NAME = 'countDistinctValues';
    public const string HASH_ALGO = 'sha3-256';
    public const int HASH_START_AT = 256;

    protected array $distinctScalar = [];
    protected array $distinctFloat = [];
    protected SplObjectStorage $distinctObject;
    protected int $hasTrue = 0;
    protected int $hasFalse = 0;
    protected array $distinctHash = [];

    public function getResult(): int|float
    {
        return $this->agg = $this->hasTrue +
                            $this->hasFalse +
                            \count($this->distinctObject) +
                            \count(array_unique($this->distinctFloat)) +
                            \count($this->distinctScalar) +
                            \count($this->distinctHash);
    }

    protected function reset(): void
    {
        $rfl = new ReflectionClass(self::class);

        $this->distinctScalar = $rfl->getProperty('distinctScalar')->getDefaultValue();
        $this->distinctFloat = $rfl->getProperty('distinctFloat')->getDefaultValue();
        $this->distinctObject = new SplObjectStorage;
        $this->hasTrue = $rfl->getProperty('hasTrue')->getDefaultValue();
        $this->hasFalse = $rfl->getProperty('hasFalse')->getDefaultValue();
        $this->distinctHash = $rfl->getProperty('distinctHash')->getDefaultValue();

        parent::reset();
    }

    public function addValue(mixed $value): void
    {
        $this->distinctObject ??= new SplObjectStorage;

        if ($value === true && $this->hasTrue === 0) {
            $this->hasTrue = 1;
        } elseif ($value === false && $this->hasFalse === 0) {
            $this->hasFalse = 1;
        } elseif (\is_object($value)) {
            $this->distinctObject[$value] = null;
        } elseif (\is_float($value)) {
            $this->distinctFloat[] = $value;
        } elseif (\is_scalar($value)) {
            if (\is_string($value) && (\strlen($value) * 8) > static::HASH_START_AT) {
                $this->distinctHash[hash(static::HASH_ALGO, $value, true)] = null;
            } else {
                $this->distinctScalar[$value] = null;
            }
        }

    }
}
