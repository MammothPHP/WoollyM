<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Statements\Insert;

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Statements\{SelectAllMode, Statement};
use Traversable;

class Insert extends Statement
{
    use SelectAllMode;

    /**
     * Allows user to "array_merge" two DataFrames so that the rows of one are appended to the rows of current DataFrame object
     * @param $iterable - The one to add to the current.
     */
    public function append(array|Traversable $iterable): DataFrame
    {
        $df = $this->getLinkedDataFrame();

        foreach ($iterable as $dfRow) {
            $df->addRecord($dfRow);
        }

        return $df;
    }


    public function setColumn(string $targetColumn, mixed $rightHandSide): self
    {
        $this->getLinkedDataFrame()->addColumn($targetColumn);
        $this->getLinkedDataFrame()->mustHaveColumn($targetColumn);

        $this->getLinkedDataFrame()->col($targetColumn)->set($rightHandSide);

        return $this;
    }

}
