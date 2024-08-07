<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\SQL;

test('tosql', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $pdo = new PDO('sqlite::memory:');

    $pdo->exec('CREATE TABLE testTable (a TEXT, b TEXT, c TEXT);');
    SQL::fromDataFrame($df)->toPDO($pdo, 'testTable');
    $result = $pdo->query('SELECT * FROM testTable;')->fetchAll(PDO::FETCH_ASSOC);

    expect($df->toArray(true))->toEqual($result);

    $pdo->exec('DROP TABLE testTable;');
});

test('from sql', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE testFromSQL (x TEXT, y TEXT, z TEXT);');
    $pdo->exec('INSERT INTO testFromSQL (x, y, z) VALUES (1, 2, 3), (4, 5, 6), (7, 8, 9);');

    $df = SQL::fromSql($pdo, 'SELECT * FROM testFromSQL;')->import();

    $pdo->exec('DROP TABLE testFromSQL;');

    $expected = [
        ['x' => 1, 'y' => 2, 'z' => 3],
        ['x' => 4, 'y' => 5, 'z' => 6],
        ['x' => 7, 'y' => 8, 'z' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('group by sqlite', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 'foo', 'b' => 2],
        ['a' => 'foo', 'b' => 2],
        ['a' => 'bar', 'b' => 2],
        ['a' => 'bar', 'b' => 2],
        ['a' => 'baz', 'b' => 2],
        ['a' => 'baz', 'b' => 2],
    ]);

    $expected = [
        ['a' => 'bar', 'b' => 4],
        ['a' => 'baz', 'b' => 4],
        ['a' => 'foo', 'b' => 4],
    ];

    $actual = $df->extract()->fromSqlQuery('SELECT a, sum(b) AS b FROM dataframe GROUP BY 1 ORDER BY 1 ASC')->toArray();

    expect($actual)->not->toBe($df);
    expect($actual)->toBe($expected);
});

test('data frame select', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $df = $df->extract()->fromSqlQuery("SELECT a, c
        FROM dataframe
        WHERE a = '4'
          OR b = '2';");

    $expected = [
        ['a' => 1, 'c' => 3],
        ['a' => 4, 'c' => 6],
    ];

    expect($df->toArray())->toEqual($expected);
});

test('data frame select update', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $df = $df->extract()->fromSqlQuery('UPDATE dataframe SET a = c * 2;');

    $expected = [
        ['a' => 6, 'b' => 2, 'c' => 3],
        ['a' => 12, 'b' => 5, 'c' => 6],
        ['a' => 18, 'b' => 8, 'c' => 9],
    ];

    expect($df->toArray())->toEqual($expected);
});
