<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;

test('to html', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    expect($df->toHTML(pretty: false))->toMatchSnapshot();
});

test('limit', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 10, 'b' => 11, 'c' => 12],
    ]);

    expect($df->toHTML(pretty: false, limit: 2))->toMatchSnapshot();

    expect($df->toHTML(pretty: false, limit: 2, offset: 2))->toMatchSnapshot();
});

test('pretty to html', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    expect($df->toHTML())->toMatchSnapshot();
});

test('class id options', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $fnExpected = static function ($tableString) {
        return $tableString . '<thead><tr><th>a</th><th>b</th><th>c</th></tr></thead>'
            . '<tfoot><tr><th>a</th><th>b</th><th>c</th></tr></tfoot>'
            . '<tbody>'
            . '<tr><td>1</td><td>2</td><td>3</td></tr>'
            . '<tr><td>4</td><td>5</td><td>6</td></tr>'
            . '<tr><td>7</td><td>8</td><td>9</td></tr>'
            . '</tbody>'
            . '</table>';
    };

    $expected = $fnExpected("<table class='classname'>");
    expect($df->toHTML(
        pretty: false,
        class: 'classname'
    ))->toMatchSnapshot();

    $expected = $fnExpected("<table id='idname'>");
    expect($df->toHTML(
        pretty: false,
        id: 'idname'
    ))->toMatchSnapshot();

    $expected = $fnExpected("<table class='classname' id='idname'>");
    expect($df->toHTML(
        pretty: false,
        class: 'classname',
        id: 'idname'
    ))->toMatchSnapshot();

    $expected = $fnExpected('<table class="classname" id="idname">');
    expect($df->toHTML(
        pretty: false,
        class: 'classname',
        id: 'idname'
    ))->toMatchSnapshot();
});

test('data table', function (): void {
    $df = DataFrame::fromArray([['a' => 1]]);

    $actual = $df->toHTML(pretty: false);

    expect($actual)->toMatchSnapshot();
});

test('data table options', function (): void {
    $df = DataFrame::fromArray([['a' => 1]]);

    $actual = $df->toHTML(pretty: false, id: 'myid');

    expect($actual)->toMatchSnapshot();
});
