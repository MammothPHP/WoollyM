<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{PropertyNotExistException, UnavailableMethodInContext};
use MammothPHP\WoollyM\Statements\Select\Select;
use MammothPHP\WoollyM\Statements\StatementClause;

beforeEach(function (): void {
    $this->df = DataFrame::fromArray([
        ['colA' => 1, 'colB' => 2, 'colC' => 3],
        ['colA' => 4, 'colB' => 5, 'colC' => 6],
        ['colA' => 7, 'colB' => 8, 'colC' => 9],
    ]);
});

it('can can be count with Pest/PHPunit', function(): void {
    expect($this->df->select())->toHaveCount(1);
})->todo();
