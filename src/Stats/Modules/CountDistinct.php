<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};
use SplObjectStorage;

class CountDistinct implements StatsMethodInterface, StatsPropertyInterface
{
    public const string NAME = 'countDistinct';
    public const string HASH_ALGO = 'sha3-256';
    public const int HASH_START_AT = 256;

    public function executeProperty(Select $select): int|float
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int|float
    {
        return $this->execute($select);
    }

    protected function execute(Select $select): int|float
    {
        $distinctScalar = [];
        $distinctFloat = [];
        $distinctObject = new SplObjectStorage;
        $hasTrue = 0;
        $hasFalse = 0;
        $distinctHash = [];

        foreach ($select as $record) {
            foreach ($record as $value) {
                if ($value === true && $hasTrue === 0) {
                    $hasTrue = 1;
                } elseif ($value === false && $hasFalse === 0) {
                    $hasFalse = 1;
                } elseif (\is_object($value)) {
                    $distinctObject[$value] = null;
                } elseif (\is_float($value)) {
                    $distinctFloat[] = $value;
                } elseif (\is_scalar($value)) {
                    if (\is_string($value) && (\strlen($value) * 8) > static::HASH_START_AT) {
                        $distinctHash[hash(static::HASH_ALGO, $value, true)] = null;
                    } else {
                        $distinctScalar[$value] = null;
                    }
                }
            }
        }

        return $hasTrue + $hasFalse + \count($distinctObject) + \count(array_unique($distinctFloat)) + \count($distinctScalar) + \count($distinctHash);
    }
}
