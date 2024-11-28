<?php

declare(strict_types=1);
use MammothPHP\WoollyM\DataFrame;
use MammothPHP\WoollyM\IO\{JSON, XLSX as IOXLSX};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

afterEach(function (): void {
    $testFile = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'test_to.xlsx';

    if (file_exists($testFile)) {
        unlink($testFile);
    }
});

test('load xlsx', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'test.xlsx';

    // Suppress warning coming from PHPExcel date/time nonsense
    $xlsx = IOXLSX::fromFilePath($fileName);
    $xlsx = $xlsx->toArray();

    $assertion_array = [
        [
            'col_a' => 'physician',
            'col_b' => 'heal',
            'col_c' => 'thyself',
            'col_d' => '',
            'col_e' => 'lorem',
            'col_f' => 'ipsum',
        ],
        [
            'col_a' => '',
            'col_b' => 'physician',
            'col_c' => 'heal',
            'col_d' => 'thyself',
            'col_e' => '',
            'col_f' => 'lorem',
        ],
        [
            'col_a' => 'ipsum',
            'col_b' => '',
            'col_c' => 'physician',
            'col_d' => 'heal',
            'col_e' => 'thyself',
            'col_f' => '',
        ],
        [
            'col_a' => 'lorem',
            'col_b' => 'ipsum',
            'col_c' => '',
            'col_d' => 'physician',
            'col_e' => 'heal',
            'col_f' => 'thyself',
        ],
    ];

    expect($xlsx)->toEqual($assertion_array);
});

test('to xlsx', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'test_to.xlsx';

    $sheetA = [
        [
            'a' => 'one',
            'b' => 'two',
            'c' => 'three',
        ],
        [
            'a' => 'four',
            'b' => 'five',
            'c' => 'six',
        ],
    ];

    $sheetB = [
        [
            'd' => 'seven',
            'e' => 'eight',
            'f' => 'nine',
        ],
        [
            'd' => 'ten',
            'e' => 'eleven',
            'f' => 'twelve',
        ],
    ];

    $sheetC = [
        [
            'g' => 'thirteen',
            'h' => 'fourteen',
            'i' => 'fifteen',
        ],
        [
            'g' => 'sixteen',
            'h' => 'seventeen',
            'i' => 'eighteen',
        ],
    ];

    $a = DataFrame::fromArray($sheetA);
    $b = DataFrame::fromArray($sheetB);
    $c = DataFrame::fromArray($sheetC);

    $xlsx = new Spreadsheet;

    IOXLSX::fromDataFrame($a)->toExcelSpreadsheet(spreadsheet: $xlsx, worksheetTitle: 'A');
    IOXLSX::fromDataFrame($b)->toExcelSpreadsheet(spreadsheet: $xlsx, worksheetTitle: 'B');
    IOXLSX::fromDataFrame($c)->toExcelSpreadsheet(spreadsheet: $xlsx, worksheetTitle: 'C');

    expect($sheetA)->toEqual($a->toArray());
    expect($sheetB)->toEqual($b->toArray());
    expect($sheetC)->toEqual($c->toArray());

    $writer = new Xlsx($xlsx);
    $writer->save($fileName);

    // Suppress warning coming from PhpSpreadsheet  date/time nonsense
    // Suppress warning coming from PhpSpreadsheet  date/time nonsense
    $a = IOXLSX::fromFilePath($fileName)->format(sheetName: 'A')->import();
    $b = IOXLSX::fromFilePath($fileName)->format(sheetName: 'B')->import();
    $c = IOXLSX::fromFilePath($fileName)->format(sheetName: 'C')->import();

    expect($sheetA)->toEqual($a->toArray());
    expect($sheetB)->toEqual($b->toArray());
    expect($sheetC)->toEqual($c->toArray());
});

test('to xlsx file', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'test_to.xlsx';

    $sheetA = [
        [
            'a' => 'one',
            'b' => 'two',
            'c' => 'three',
        ],
        [
            'a' => 'four',
            'b' => 'five',
            'c' => 'six',
        ],
    ];

    $df1 = new DataFrame($sheetA);
    IOXLSX::fromDataFrame($df1)->toFile(filePath: $fileName, overwriteFile: true);

    $df2 = IOXLSX::fromFilePath($fileName)->import();

    expect($df2->toArray())->toBe($df1->toArray())->toBe($sheetA);
});

test('to xlsx with array data from a Json', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'test_to.xlsx';

    $df1 = JSON::fromFilePath(__DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'testJsonArray.json')->import();

    IOXLSX::fromDataFrame($df1)->toFile($fileName);
    $df2 = IOXLSX::fromFilePath($fileName)->import();

    expect($df2[0]['claim_categories'])->toBeString()->toContain('Recrutement');
});

test('to xlsx with array data from direct input', function (): void {
    $fileName = __DIR__ . \DIRECTORY_SEPARATOR . 'TestFiles' . \DIRECTORY_SEPARATOR . 'test_to.xlsx';

    $df1 = DataFrame::fromArray($sheetA = [
        [
            'colA' => '42',
            'colB' => [42, 43],
        ],
    ]);

    IOXLSX::fromDataFrame($df1)->toFile($fileName);
    $df2 = IOXLSX::fromFilePath($fileName)->import();
    $sheetA[0]['colB'] = json_encode($sheetA[0]['colB'], \JSON_PRETTY_PRINT);

    expect($df2->toArray())->toBe($sheetA);
});

test('bad colRow', function (): void {
    IOXLSX::fromDataFrame(new DataFrame)->colRow = 0;
})->throws(UnexpectedValueException::class);
