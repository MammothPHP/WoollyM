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

test('update record', function (): void {
    $i = 0;
    $df = DataFrame::fromArray([
        ['a' => ++$i, 'b' => ++$i],
        ['a' => ++$i, 'b' => ++$i],
        ['a' => ++$i, 'b' => ++$i],
        ['a' => ++$i, 'b' => ++$i],
    ]);

    $r = $df->getRecord(1);

    expect($r->toArray())->toBe(['a' => 3, 'b' => 4]);

    $r['b'] = 42;

    expect($df->getRecord(1)->toArray())
        ->toBe(['a' => 3, 'b' => 42])
        ->toBe($r->toArray());

    $r['c'] = 'insert';

    expect($df->getRecord(1)->toArray())
        ->toBe(['a' => 3, 'b' => 42, 'c' => 'insert'])
        ->toBe($r->toArray());


    unset($r['a']);
    expect($df->getRecord(1)->toArray())
        ->toBe(['b' => 42, 'c' => 'insert'])
        ->toBe($r->toArray());
});

test('bug/ bad recordKey', function (): void {
    $i = 0;
    $df = DataFrame::fromArray($input = [
        ['a' => $i++],
        ['a' => $i++],
        ['a' => $i++],
        ['a' => $i++],
        ['a' => $i++],
        ['a' => $i++],
        ['a' => $i++], # 6
    ]);

    $expected = array_map(function (array $e): array {
        $e['test'] = 42;

        return $e;
    }, $input);

    for ($i = 0; $i < \count($expected); $i++) {
        $df->getRecord($i)['test'] = 42;
    }

    expect($df->toArray())->toBe($expected);
})->done(issue: '34');
