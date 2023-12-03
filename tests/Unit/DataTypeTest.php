<?php

declare(strict_types=1);

use MammothPHP\WoollyM\DataType;

test('convertInt', function (): void {
    expect(DataType::convertInt(' 42 '))
        ->toBe(DataType::convertInt(' 42$ '))
        ->toBe(42);

    expect(DataType::convertInt(null))
        ->toBe(DataType::convertInt([]))
        ->toBe(DataType::convertInt(''))
        ->toBe(DataType::convertInt(false))
        ->toBe(0);
});

test('convertFloat', function (): void {
    expect(DataType::convertFloat(' 42 '))
        ->toBe(DataType::convertFloat(' 42$ '))
        ->toBe(42.0);

    expect(DataType::convertFloat(null))
        ->toBe(DataType::convertFloat([]))
        ->toBe(DataType::convertFloat(''))
        ->toBe(DataType::convertFloat(false))
        ->toBe(0.0);
});
