<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataDrivers\DriversExceptions\KeyNotExistException;
use MammothPHP\WoollyM\DataDrivers\PdoSql\PdoSql;
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\SQL;

beforeEach(function (): void {
    $tableName = 'testTable';

    $this->df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['c' => 6, 'a' => 4],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE ' . $tableName . ' (id INTEGER PRIMARY KEY, a TEXT, b TEXT, c TEXT);');
    SQL::fromDataFrame($this->df)->toPDO($pdo, 'testTable');

    $PdoDriver = new PdoSql(db: $pdo, table: $tableName);

    $this->df = new DataFrame(dataDriver: $PdoDriver);
    $this->df->addColumns(['a', 'b', 'c']);
});


test('get a key, delete it, try again', function (): void {

    expect($this->df->getRecordAsArray(2))->toBe(['a' => '4', 'b' => null, 'c' => '6']);

    $this->df->removeRecord(2);

    expect($this->df->getRecordAsArray(2))->toBe(['a' => '4', 'b' => null, 'c' => '6']);
})->throws(KeyNotExistException::class);

test('count', function (): void {
    expect($this->df)->toHaveCount(3);
});

test('add record', function (): void {
    $this->df[] = ['c' => 'foo', 'a' => 42];
    expect($this->df)->toHaveCount(4);
    expect($this->df->getRecordAsArray(4))->toBe(['a' => '42', 'b' => null, 'c' => 'foo']);
});

test('update record', function (): void {
    $this->df[1] = ['c' => 'foo', 'a' => 42];
    expect($this->df)->toHaveCount(3);
    expect($this->df->getRecordAsArray(1))->toBe(['a' => '42', 'b' => null, 'c' => 'foo']);
});

test('iterator', function (): void {
    $r = [];

    foreach ($this->df as $key => $record) {
        $r[$key] = $record->toArray();
    }

    expect($r)->toBe([
        1 => ['a' => '1', 'b' => '2', 'c' => '3'],
        2 => ['a' => '4', 'b' => null, 'c' => '6'],
        3 => ['a' => '7', 'b' => '8', 'c' => '9'],
    ]);
});
