<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hgehring
 * Date: 4/30/18
 * Time: 10:33 PM
 */

namespace CondorcetPHP\Oliphant;

use DateTimeImmutable;
use RuntimeException;

enum DataType
{
    case NUMERIC;
    case INTEGER;
    case DATETIME;
    case CURRENCY;
    case ACCOUNTING;


    public static function convertNumeric(mixed $value): int|float
    {
        if (is_numeric($value)) {
            return $value;
        }

        $value = str_replace(['$', ',', ' '], '', $value);

        if (substr($value, -1) == '-') {
            $value = '-' . substr($value, 0, -1);
        }

        $value = \floatval($value);

        return (\is_int($value / 1)) ? \intval($value) : $value;
    }

    public static function convertInt(mixed $value): int
    {
        if (empty($value)) {
            return 0;
        }

        $value = (string) $value;

        if (substr($value, -1) === '-') {
            $value = '-' . substr($value, 0, -1);
        }

        $value = str_replace(['$', ',', ' '], '', $value);

        return \intval(str_replace(',', '', $value));
    }

    public static function convertDatetime(mixed $value, array|string|null $fromFormat, string $toFormat): string
    {
        if (empty($value)) {
            return DateTimeImmutable::createFromFormat('Y-m-d', '0001-01-01')->format($toFormat);
        }

        if (!\is_array($fromFormat)) {
            $fromFormat = [$fromFormat];
        }

        $dateFormatSnapshot = null;

        foreach ($fromFormat as $dateFormat) {
            $dateFormatSnapshot = $dateFormat;

            $oldDateTime = DateTimeImmutable::createFromFormat($dateFormat, $value);
            if ($oldDateTime === false) {
                continue;
            } else {
                return $oldDateTime->format($toFormat);
            }
        }

        throw new RuntimeException("Error parsing date string '{$value}' with date format {$dateFormatSnapshot}");
    }

    public static function convertCurrency(string $value): string
    {
        $value = explode('.', $value);
        $value[1] = $value[1] ?? '00';
        $value[0] = ($value[0] == '' || $value[0] == '-') ? '0' : $value[0];
        $value[1] = ($value[1] == '' || $value[1] == '0') ? '00' : $value[1];

        $value[0] = \floatval($value[0]);
        $dollars = number_format($value[0]) . '.' . $value[1];

        if (substr($dollars, 0, 1) == '-') {
            $dollars = '-$' . ltrim($dollars, '-');
        } elseif (substr($dollars, -1) == '-') {
            $dollars = '-$' . rtrim($dollars, '-');
        } else {
            $dollars = '$' . $dollars;
        }

        return $dollars;
    }

    public static function convertAccounting(string $value): string
    {
        $value = explode('.', $value);
        $value[1] = $value[1] ?? '00';
        $value[0] = ($value[0] == '' || $value[0] == '-') ? '0' : $value[0];
        $value[1] = ($value[1] == '' || $value[1] == '0') ? '00' : $value[1];

        $value[0] = \floatval($value[0]);
        $dollars = number_format($value[0]) . '.' . $value[1];

        if (substr($dollars, 0, 1) == '-') {
            $dollars = '(' . ltrim($dollars, '-') . ')';
        } elseif (substr($dollars, -1) == '-') {
            $dollars = '(' . rtrim($dollars, '-') . ')';
        }

        return '$' . $dollars;
    }
}
