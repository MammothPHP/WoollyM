<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\Exceptions\{InvalidSelectException};

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

    expect($originalDf->group('CompFirstName')->toArray())->toBe([
        ['CompFirstName' => 'Claude'],
        ['CompFirstName' => 'Johan'],
        ['CompFirstName' => 'Richard'],
        ['CompFirstName' => 'Frederick'],
    ]);

    expect($originalDf->group('CompBirth')->toArray())->toBe([
        ['CompBirth' => 1862],
        ['CompBirth' => 1804],
        ['CompBirth' => 1813],
        ['CompBirth' => 1864],
        ['CompBirth' => 1825],
    ]);

    expect($originalDf->group('CompName', 'CompFirstName')->toArray())->toBe([
        ['CompName' => 'Debussy', 'CompFirstName' => 'Claude'],
        ['CompName' => 'Strauss', 'CompFirstName' => 'Johan'],
        ['CompName' => 'Wagner', 'CompFirstName' => 'Richard'],
        ['CompName' => 'Delius', 'CompFirstName' => 'Frederick'],
        ['CompName' => 'Strauss', 'CompFirstName' => 'Richard'],
    ]);

    expect($originalDf->group('Nationality')->toArray())->toBe([
        ['Nationality' => null],
        ['Nationality' => 'Austrian'],
    ]);

    expect($originalDf->group('Nationality', 'CompFirstName')->toArray())->toBe([
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

    expect($originalDf->group('CompFirstName', 'Nationality')->toArray())->toBe([
        ['CompFirstName' => 'Johan', 'Nationality' => null],
        ['CompFirstName' => 'Johan', 'Nationality' => 'Austrian'],
    ]);

    $originalDf->group('CompFirstName', 'DiedIn');
})->throws(InvalidSelectException::class);
