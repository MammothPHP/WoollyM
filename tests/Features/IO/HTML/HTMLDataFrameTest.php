<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\HTML;

test('to html', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    expect(HTML::fromDataFrame($df)->toString(pretty: false))->toMatchSnapshot();
});

test('limit', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 10, 'b' => 11, 'c' => 12],
    ]);

    expect(HTML::fromDataFrame($df)->toString(pretty: false, limit: 2))->toMatchSnapshot();

    expect(HTML::fromDataFrame($df)->toString(pretty: false, limit: 2, offset: 2))->toMatchSnapshot();
});

test('pretty to html', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    expect(HTML::fromDataFrame($df)->toString())->toMatchSnapshot();
});

test('class id options', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    expect(HTML::fromDataFrame($df)->toString(
        pretty: false,
        class: 'classname'
    ))->toMatchSnapshot();

    expect(HTML::fromDataFrame($df)->toString(
        pretty: false,
        id: 'idname'
    ))->toMatchSnapshot();

    expect(HTML::fromDataFrame($df)->toString(
        pretty: false,
        class: 'classname',
        id: 'idname'
    ))->toMatchSnapshot();

    expect(HTML::fromDataFrame($df)->toString(
        pretty: false,
        class: 'classname',
        id: 'idname'
    ))->toMatchSnapshot();
});

test('data table', function (): void {
    $df = DataFrame::fromArray([['a' => 1]]);

    $actual = HTML::fromDataFrame($df)->toString(pretty: false);

    expect($actual)->toMatchSnapshot();
});

test('data table options', function (): void {
    $df = DataFrame::fromArray([['a' => 1]]);

    $actual = HTML::fromDataFrame($df)->toString(pretty: false, id: 'myid');

    expect($actual)->toMatchSnapshot();
});
