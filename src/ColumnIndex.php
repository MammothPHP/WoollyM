<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

use WeakReference;

class ColumnIndex
{
    public readonly WeakReference $df;
    protected ?DataType $forcedType = null;

    public function __construct(protected string $name, DataFrame $df)
    {
        $this->df = WeakReference::create($df);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->df->get()->clearColumnsCache();
    }

    public function getForcedType(): ?DataType
    {
        return $this->forcedType;
    }

    public function setForcedType(?DataType $forcedType): void
    {
        $this->forcedType = $forcedType;
        $this->df->get()->clearColumnsCache();
    }
}
