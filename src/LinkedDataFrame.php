<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\InvalidSelectException;
use ReflectionProperty;

trait LinkedDataFrame
{
    public readonly DataFrame $df;

    protected function setLinkedDataFrame(DataFrame $df): void
    {
        $this->df = $df;
    }

    /**
     * Get the Linked DataFrame object
     * @throws InvalidSelectException
     */
    public function getLinkedDataFrame(): DataFrame
    {
        $this->isAliveOrThrowInvalidSelectException();

        return $this->df;
    }

    /**
     * @return bool false if linked dataFrame no longer exist.
     * @internal
     */
    public function isAlive(): bool
    {
        return new ReflectionProperty($this, 'df')->isInitialized($this);
    }

    /**
     * @internal
     */
    public function isAliveOrThrowInvalidSelectException(): void
    {
        $this->isAlive() || throw new InvalidSelectException;
    }
}
