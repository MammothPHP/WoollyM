<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Select;

use BadMethodCallException;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException, PropertyNotExistException};
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\{Statement, StatementClause};
use MammothPHP\WoollyM\Stats\Modules;

class Select extends Statement
{
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


    // Select Methods

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
        $c = 0;

        foreach ($this as $record) {
            $c++;
        }

        return $c;
    }

}
