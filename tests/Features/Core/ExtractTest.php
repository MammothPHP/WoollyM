<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException};
use MammothPHP\WoollyM\Stats\Modules\{CountDistinctValues, Sum};

beforeEach(function (): void {
    $this->input = [
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ];

    $this->df = DataFrame::fromArray($this->input);
});

test('clone', function (): void {
    $clone = $this->df->extract()->clone();

    expect($clone)->not->toBe($this->df);
    expect($clone->toArray())->toBe($this->df->toArray());
});

test('filter', function (): void {
    $df = $this->df;

    $df = $df->extract()->withFilter(static function ($row) {
        return $row['a'] > 4 || $row['a'] < 4;
    });

    expect($df)->not->toBe($this->df);

    expect($df->toArray())->toBe([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);
});

test('unique', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);

    expect($df->extract()->unique('a')->toArray())->toBe([
        ['a' => 1],
        ['a' => 2],
        ['a' => 3],
    ]);

    expect($df->extract()->unique(['a', 'b'])->toArray())->toBe([
        ['a' => 1, 'b' => 2],
        ['a' => 1, 'b' => 3],
        ['a' => 2, 'b' => 4],
        ['a' => 3, 'b' => 5],
    ]);

    $df = $df->extract()->unique(['a', 'b', 'c']);

    expect($df)->not->toBe($this->df);

    expect($df->toArray())->toBe([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
    ]);
});

test('simple group', function (): void {
    $originalDf = DataFrame::fromArray([
        ['CompFirstName' => 'Claude', 'CompName' => 'Debussy', 'CompBirth' => 1862],
        ['CompFirstName' => 'Johan', 'CompName' => 'Strauss', 'Nationality' => 'Austrian', 'CompBirth' => 1804],
        ['CompFirstName' => 'Richard', 'CompName' => 'Wagner', 'CompBirth' => 1813],
        ['CompFirstName' => 'Frederick', 'CompName' => 'Delius', 'CompBirth' => 1862],
        ['CompFirstName' => 'Richard', 'CompName' => 'Strauss', 'CompBirth' => 1864],
        ['CompFirstName' => 'Johan', 'CompName' => 'Strauss', 'CompBirth' => 1825],
    ]);

    expect($originalDf->groupBy('CompFirstName')->toArray())->toBe([
        ['CompFirstName' => 'Claude'],
        ['CompFirstName' => 'Johan'],
        ['CompFirstName' => 'Richard'],
        ['CompFirstName' => 'Frederick'],
    ]);

    expect($originalDf->groupBy('CompBirth')->toArray())->toBe([
        ['CompBirth' => 1862],
        ['CompBirth' => 1804],
        ['CompBirth' => 1813],
        ['CompBirth' => 1864],
        ['CompBirth' => 1825],
    ]);

    expect($originalDf->groupBy('CompName', 'CompFirstName')->toArray())->toBe([
        ['CompName' => 'Debussy', 'CompFirstName' => 'Claude'],
        ['CompName' => 'Strauss', 'CompFirstName' => 'Johan'],
        ['CompName' => 'Wagner', 'CompFirstName' => 'Richard'],
        ['CompName' => 'Delius', 'CompFirstName' => 'Frederick'],
        ['CompName' => 'Strauss', 'CompFirstName' => 'Richard'],
    ]);

    expect($originalDf->groupBy('Nationality')->toArray())->toBe([
        ['Nationality' => null],
        ['Nationality' => 'Austrian'],
    ]);

    expect($originalDf->groupBy('Nationality', 'CompFirstName')->toArray())->toBe([
        ['Nationality' => null, 'CompFirstName' => 'Claude'],
        ['Nationality' => 'Austrian', 'CompFirstName' => 'Johan'],
        ['Nationality' => null, 'CompFirstName' => 'Richard'],
        ['Nationality' => null, 'CompFirstName' => 'Frederick'],
        ['Nationality' => null, 'CompFirstName' => 'Johan'],
    ]);
});


test('group must has a valid col', function (): void {
    $originalDf = DataFrame::fromArray([
        ['CompFirstName' => 'Johan', 'CompName' => 'Strauss', 'CompBirth' => 1825],
        ['CompFirstName' => 'Johan', 'CompName' => 'Strauss', 'Nationality' => 'Austrian', 'CompBirth' => 1804],
    ]);

    expect($originalDf->groupBy('CompFirstName', 'Nationality')->toArray())->toBe([
        ['CompFirstName' => 'Johan', 'Nationality' => null],
        ['CompFirstName' => 'Johan', 'Nationality' => 'Austrian'],
    ]);

    $originalDf->groupBy('CompFirstName', 'DiedIn');
})->throws(InvalidSelectException::class);


test('group simple group aggregation', function (): void {
    $df = new DataFrame([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 1, 'b' => 3, 'c' => 4],
        ['a' => 2, 'b' => 4, 'c' => 5],
        ['a' => 2, 'b' => 4, 'c' => 6],
        ['a' => 3, 'b' => 5, 'c' => 7],
        ['a' => 3, 'b' => 5, 'c' => 8],
        ['a' => 4, 'b' => 5, 'c' => 9],
    ]);

    $grouped = $df->groupBy('a', Sum::col('b'));

    expect($grouped->toArray())->tobe([
        ['a' => 1, 'b' => 5],
        ['a' => 2, 'b' => 8],
        ['a' => 3, 'b' => 10],
        ['a' => 4, 'b' => 5],
    ]);

    $grouped = $df->groupBy('b', CountDistinctValues::col('a'));

    expect($grouped->toArray())->tobe([
        ['b' => 2, 'a' => 1],
        ['b' => 3, 'a' => 1],
        ['b' => 4, 'a' => 1],
        ['b' => 5, 'a' => 2],
    ]);
});

test('group alias "as"', function (): void {
    $df = new DataFrame([
        ['a' => 'foo', 'b' => 7],
        ['a' => 'foo', 'b' => 7],
        ['a' => 'bar', 'b' => 42],
    ]);

    $grouped = $df->groupBy('a', Sum::col('b', as: 'total'));

    expect($grouped->toArray())->toBe([
        ['a' => 'foo', 'total' => 14],
        ['a' => 'bar', 'total' => 42],
    ]);
});

test('group column not exist', function (): void {
    $this->df->groupBy('non-existent column');
})->throws(InvalidSelectException::class);

test('aggregation function on non-existent column', function (): void {
    $this->df->groupBy('a', Sum::col('non-existent column'));
})->throws(InvalidSelectException::class);