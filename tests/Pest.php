<?php

declare(strict_types=1);

use MammothPHP\WoollyM\Record;

pest()->project()->github('MammothPHP/WoollyM');

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->in('Features');
pest()->extend(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeSameRecord', function (Record $record) {
    expect($this->value)->toBeInstanceOf(Record::class);
    expect($this->value->toArray())->toBe($record->toArray())->and($this->value->recordKey)->tobe($record->recordKey);

    return $this;
});

expect()->extend('dump', function () {
    fwrite(\STDERR, var_export($this->value, true));

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// function something(): void
// {
//     // ..
// }
