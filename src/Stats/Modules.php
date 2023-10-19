<?php

declare(strict_types=1);

namespace CondorcetPHP\Oliphant\Stats;

use CondorcetPHP\Oliphant\Exceptions\NotYetImplementedException;
use CondorcetPHP\Oliphant\Stats\Modules\Average;
use CondorcetPHP\Oliphant\Stats\Modules\Count;
use CondorcetPHP\Oliphant\Stats\Modules\Sum;

abstract class Modules
{
    protected static ?array $modules = null;

    protected static function init (): void
    {
        if (self::$modules === null) {
            self::$modules = [];

            self::registerModule(new Average);
            self::registerModule(new Count);
            self::registerModule(new Sum);
        }
    }

    protected static function getModule(string $name, ModuleType $type): ?StatsInterface {
        self::init();

        $r = self::$modules[$name] ?? null;

        return $r instanceof $type->value ? $r : null;
    }

    public static function getColumnStatsPropertyModule (string $property): ?ColumnStatsPropertyInterface {
        return self::getModule($property, ModuleType::ColumnStatsProperty);
    }

    public static function getColumnStatsMethodModule (string $method): ?ColumnStatsMethodInterface {
        return self::getModule($method, ModuleType::ColumnStatsMethod);
    }

    public static function registerModule(StatsInterface $module): void
    {
        self::init();

        if (empty($module::NAME)) {
            throw new NotYetImplementedException;
        }

        self::$modules[$module::NAME] = $module;
    }

    public static function removeModuleByMethod (string $method) {
        self::init();

        if (array_key_exists($method, self::$modules)) {
            unset(self::$modules[$method]);
        }
    }
}