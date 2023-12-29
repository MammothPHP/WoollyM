<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\InvalidSelectException;
use WeakReference;

trait LinkedDataFrame
{
    protected readonly WeakReference $df;

    protected function setLinkedDataFrame(DataFrame $df): void
    {
        $this->df = WeakReference::create($df);
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
}