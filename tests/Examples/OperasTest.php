<?php

declare(strict_types=1);

use MammothPHP\WoollyM\IO\CSV;
use MammothPHP\WoollyM\Sort\Desc;
use MammothPHP\WoollyM\Stats\Modules\{CountDistinctValues, Sum};

# from https://dataverse.harvard.edu/dataset.xhtml?persistentId=doi:10.7910/DVN/8LUFN8
# public domain

beforeEach(function (): void {
    $this->df = CSV::fromFilePath(__DIR__ . '/opera_performances.csv')->import();
});

it('has correct columns', function (): void {
    expect($this->df->columnsNames())->toBe([
        'season',
        'iso',
        'city',
        'composer',
        'db',
        'dd',
        'nat',
        'mf',
        'work',
        'worknat',
        'type',
        'start date',
        'performances',
        'production',
    ]);

});


test('rank composers runs', function (): void {
    $lines = 33_127;
    expect($this->df)->toHaveCount($lines);
    expect($this->df->selectAll()->size())->toBe($lines * \count($this->df->columnsNames()));

    expect(
        $this->df->select(
            'composer',
            Sum::col('performances', as: 'total_performances'),
            CountDistinctValues::col('work', as: 'distincts_work'),
            CountDistinctValues::col('iso', as: 'distincts_countries'),
            CountDistinctValues::col('city', as: 'distincts_cities'),
        )
            ->groupBy('composer')
            ->export()
            ->orderBy(Desc::col('total_performances'))
            ->head(10)
    )->toMatchSnapshot();
});
