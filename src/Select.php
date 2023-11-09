<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use MammothPHP\WoollyM\Exceptions\{DataFrameException, InvalidColumnException, MethodNotExistException, PropertyNotExistException};
use Stringable;
use WeakReference;

class Select
{
    protected WeakReference $df;
    protected array $selections = [];

    public function __construct(DataFrame $df, string ...$selection)
    {
        $this->df = WeakReference::create($df);

        // Nettoie les selections valides
        $this->selections = $selection;
    }

    public function getLinkedDataFrame(): DataFrameCore
    {
        $this->isAliveOrThrowInvalidColumnException();

        return $this->df->get();
    }

    public function isAlive(): bool
    {
        if ($this->df->get() === null) {
            return false;
        }

        return true;
    }

    protected function isAliveOrThrowInvalidColumnException(): void
    {
        $this->isAlive() || throw new InvalidColumnException;
    }


    public function get(): DataFrame
    {
        return new DataFrame();
    }
}