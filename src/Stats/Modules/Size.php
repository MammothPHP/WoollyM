<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats\Modules;

use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Stats\{StatsMethodInterface, StatsPropertyInterface};

class Size implements StatsMethodInterface, StatsPropertyInterface
{
    public const string NAME = 'size';

    public function executeProperty(Select $select): int
    {
        return $this->execute($select);
    }

    public function executeMethod(Select $select, array $arguments): int
    {
        return $this->execute($select, ...$arguments);
    }

    protected function execute(Select $select, bool $ignoreNonNumericValue = false, bool $ignoreNullValue = false): int
    {
        $r = 0;

        if (!$ignoreNonNumericValue && !$ignoreNullValue) {
            foreach ($select as $record) {
                $r += \count($record);
            }
        } else {
            foreach ($select as $record) {
                foreach ($select as $record) {
                    foreach ($record as $value) {
                        if ($ignoreNonNumericValue && (is_numeric($value) || $value === true)) {
                            $r++;
                        } elseif ($ignoreNullValue && $value !== null) {
                            $r++;
                        }
                    }
                }
            }
        }

        return $r;
    }
}
