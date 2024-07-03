<?php

declare(strict_types=1);

use MammothPHP\WoollyM\{DataFrame, Record};
use MammothPHP\WoollyM\Exceptions\{SourceDataFrameNoLongerExist};

test('toContextualArray', function (): void {
    $df = new DataFrame;
    $df->addColumns(['A', 'B', 'C']);

    $record = new Record($df, 42, $ori = ['A' => 42, 'C' => 126]);

    expect($record->toContextualArray())->toBe(['A' => 42, 'B' => null, 'C' => 126]);

    expect($record->toArray())->toBe($ori);
});

test('records keys', function (): void {
    $df = new DataFrame;

    $record = new Record($df, 42, ['A' => 42, 'C' => 126]);
    expect($record->recordKey)->toBe(42);

    $record = new Record($df, '42', ['A' => 42, 'C' => 126]);
    expect($record->recordKey)->toBe('42');
});

test('dataFrame link', function (): void {
    $df = new DataFrame;

    $record = new Record($df, 42, ['A' => 42, 'C' => 126]);

    expect($record->getDataFrame())->toBe($df);

    $df = null;
    expect($record->getDataFrame())->toBeNull();

    expect(fn() => $record->toContextualArray())->toThrow(exception: SourceDataFrameNoLongerExist::class);

    expect($record->toArray())->toBeArray()->toHaveCount(2);
});
