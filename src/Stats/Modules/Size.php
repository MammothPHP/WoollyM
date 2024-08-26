<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\Bases\AbstractAgg;

class Size extends AbstractAgg
{
    public const string NAME = 'size';

    protected bool $isInitializated = false;
    public readonly bool $ignoreNonNumericValue;
    public readonly bool $ignoreNullValue;

    public function init(bool $ignoreNonNumericValue = false, bool $ignoreNullValue = false): void
    {
        if (!$this->isInitializated) {
            $this->ignoreNonNumericValue = $ignoreNonNumericValue;
            $this->ignoreNullValue = $ignoreNullValue;
            $this->isInitializated = true;
        }
    }

    public function executeMethod(Select $select, array $arguments): int
    {
        $this->execute($select, ...$arguments);

        return $this->getResult();
    }

    public function addValue(mixed $value): void
    {
        if ($this->ignoreNonNumericValue && (is_numeric($value) || $value === true)) {
            $this->agg++;
        } elseif ($this->ignoreNullValue && $value !== null) {
            $this->agg++;
        }
    }

    protected function execute(Select $select, bool $ignoreNonNumericValue = false, bool $ignoreNullValue = false): void
    {
        $this->init(ignoreNonNumericValue: $ignoreNonNumericValue, ignoreNullValue: $ignoreNullValue);

        if (!$this->ignoreNonNumericValue && !$this->ignoreNullValue) {
            foreach ($select as $record) {
                $this->agg += \count($record);
            }
        } else {
            parent::execute($select);
        }
    }
}
