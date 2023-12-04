<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataDrivers\PhpArray\PhpArrayDriver;

beforeEach(function (): void {
    $this->PhpArray = new PhpArrayDriver;
});

it('throw KeyNotExist exception', function(): void {
    $this->PhpArray->getRecordKey(42);
})->throws(KeyNotExistException::class);

it('can setRecordColumn on new key', function(): void {
    $this->PhpArray->setRecordColumn(42, 7, 'value1');
    expect($this->PhpArray->getRecordKey(42))->toBe([7 => 'value1']);

    $this->PhpArray->setRecordColumn(42, 8, 'value2');
    expect($this->PhpArray->getRecordKey(42))->toBe([7 => 'value1', 8 => 'value2']);
});