<?php

declare(strict_types=1);
use Archon\DataFrame;

test('to h t m l', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = '<table>';
    $expected .= '<thead><tr><th>a</th><th>b</th><th>c</th></tr></thead>';
    $expected .= '<tfoot><tr><th>a</th><th>b</th><th>c</th></tr></tfoot>';
    $expected .= '<tbody>';
    $expected .= '<tr><td>1</td><td>2</td><td>3</td></tr>';
    $expected .= '<tr><td>4</td><td>5</td><td>6</td></tr>';
    $expected .= '<tr><td>7</td><td>8</td><td>9</td></tr>';
    $expected .= '</tbody>';
    $expected .= '</table>';

    expect($df->toHTML())->toEqual($expected);
});
test('limit', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
        ['a' => 10, 'b' => 11, 'c' => 12],
    ]);

    $expected = '<table>';
    $expected .= '<thead><tr><th>a</th><th>b</th><th>c</th></tr></thead>';
    $expected .= '<tfoot><tr><th>a</th><th>b</th><th>c</th></tr></tfoot>';
    $expected .= '<tbody>';
    $expected .= '<tr><td>1</td><td>2</td><td>3</td></tr>';
    $expected .= '<tr><td>4</td><td>5</td><td>6</td></tr>';
    $expected .= '</tbody>';
    $expected .= '</table>';
    expect($df->toHTML(['limit' => 2]))->toEqual($expected);

    $expected = '<table>';
    $expected .= '<thead><tr><th>a</th><th>b</th><th>c</th></tr></thead>';
    $expected .= '<tfoot><tr><th>a</th><th>b</th><th>c</th></tr></tfoot>';
    $expected .= '<tbody>';
    $expected .= '<tr><td>7</td><td>8</td><td>9</td></tr>';
    $expected .= '<tr><td>10</td><td>11</td><td>12</td></tr>';
    $expected .= '</tbody>';
    $expected .= '</table>';
    expect($df->toHTML(['offset' => 2, 'limit' => 2]))->toEqual($expected);
});
test('pretty to h t m l', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $expected = "<table>\n";
    $expected .= "    <thead>\n";
    $expected .= "        <tr>\n";
    $expected .= "            <th>a</th>\n";
    $expected .= "            <th>b</th>\n";
    $expected .= "            <th>c</th>\n";
    $expected .= "        </tr>\n";
    $expected .= "    </thead>\n";
    $expected .= "    <tfoot>\n";
    $expected .= "        <tr>\n";
    $expected .= "            <th>a</th>\n";
    $expected .= "            <th>b</th>\n";
    $expected .= "            <th>c</th>\n";
    $expected .= "        </tr>\n";
    $expected .= "    </tfoot>\n";
    $expected .= "    <tbody>\n";
    $expected .= "        <tr>\n";
    $expected .= "            <td>1</td>\n";
    $expected .= "            <td>2</td>\n";
    $expected .= "            <td>3</td>\n";
    $expected .= "        </tr>\n";
    $expected .= "        <tr>\n";
    $expected .= "            <td>4</td>\n";
    $expected .= "            <td>5</td>\n";
    $expected .= "            <td>6</td>\n";
    $expected .= "        </tr>\n";
    $expected .= "        <tr>\n";
    $expected .= "            <td>7</td>\n";
    $expected .= "            <td>8</td>\n";
    $expected .= "            <td>9</td>\n";
    $expected .= "        </tr>\n";
    $expected .= "    </tbody>\n";
    $expected .= '</table>';

    expect($df->toHTML(['pretty' => true]))->toEqual($expected);
});
test('class i d options', function (): void {
    $df = DataFrame::fromArray([
        ['a' => 1, 'b' => 2, 'c' => 3],
        ['a' => 4, 'b' => 5, 'c' => 6],
        ['a' => 7, 'b' => 8, 'c' => 9],
    ]);

    $fnExpected = static function ($tableString) {
        return $tableString.'<thead><tr><th>a</th><th>b</th><th>c</th></tr></thead>'
            .'<tfoot><tr><th>a</th><th>b</th><th>c</th></tr></tfoot>'
            .'<tbody>'
            .'<tr><td>1</td><td>2</td><td>3</td></tr>'
            .'<tr><td>4</td><td>5</td><td>6</td></tr>'
            .'<tr><td>7</td><td>8</td><td>9</td></tr>'
            .'</tbody>'
            .'</table>';
    };

    $expected = $fnExpected("<table class='classname'>");
    expect($df->toHTML([
        'class' => 'classname',
    ]))->toEqual($expected);

    $expected = $fnExpected("<table id='idname'>");
    expect($df->toHTML([
        'id' => 'idname',
    ]))->toEqual($expected);

    $expected = $fnExpected("<table class='classname' id='idname'>");
    expect($df->toHTML([
        'class' => 'classname',
        'id' => 'idname',
    ]))->toEqual($expected);

    $expected = $fnExpected('<table class="classname" id="idname">');
    expect($df->toHTML([
        'class' => 'classname',
        'id' => 'idname',
        'quote' => '"',
    ]))->toEqual($expected);
});
test('data table', function (): void {
    $df = DataFrame::fromArray([['a' => 1]]);

    $actual = $df->toHTML(['datatable' => true]);

    // Regex for the CSS ID because it's a UUID
    preg_match_all('/#\w*/', $actual, $matches);
    $matches = current($matches);
    expect(isset($matches[0]))->toBeTrue();

    $uuid = substr($matches[0], 1);
    $expected = "<table id='".$uuid."'>";
    $expected .= '<thead><tr><th>a</th></tr></thead>';
    $expected .= '<tfoot><tr><th>a</th></tr></tfoot>';
    $expected .= '<tbody>';
    $expected .= '<tr><td>1</td></tr>';
    $expected .= '</tbody>';
    $expected .= '</table>';

    // Defining this wrapper function because PHPStorm goes apeshit trying to interpret the generated JavaScript.
    $wrap = static function ($openTag, $closingTag) {
        return static function ($data) use ($openTag, $closingTag) {
            return $openTag . $data . $closingTag;
        };
    };

    $scriptTag = $wrap('<script>', '</script>');
    $expected .= $scriptTag('$(document).ready(function() {$(\''.$matches[0].'\').DataTable();});');

    expect($actual)->toEqual($expected);
});
test('data table options', function (): void {
    $df = DataFrame::fromArray([['a' => 1]]);

    $actual = $df->toHTML([
        'id' => 'myid',
        'datatable' => '{ "key": value }',
    ]);

    $expected = "<table id='myid'>";
    $expected .= '<thead><tr><th>a</th></tr></thead>';
    $expected .= '<tfoot><tr><th>a</th></tr></tfoot>';
    $expected .= '<tbody>';
    $expected .= '<tr><td>1</td></tr>';
    $expected .= '</tbody>';
    $expected .= '</table>';

    // Defining this wrapper function because PHPStorm goes apeshit trying to interpret the generated JavaScript.
    $wrap = static function ($openTag, $closingTag) {
        return static function ($data) use ($openTag, $closingTag) {
            return $openTag . $data . $closingTag;
        };
    };

    $scriptTag = $wrap('<script>', '</script>');
    $expected .= $scriptTag('$(document).ready(function() {$(\'#myid\').DataTable({ "key": value });});');

    expect($actual)->toEqual($expected);
});
