<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use MammothPHP\WoollyM\Stats\Modules\{Average, Count, CountDistinct, Describe, Max, Mean, Min, Size, Sum};

abstract class Modules
{
    protected static ?array $modules = null;

    protected static function init(): void
    {
        if (self::$modules === null) {
            self::$modules = [];

            // Summary
            self::registerModule(new Describe);

            // Calculation
            self::registerModule(new Average);
            self::registerModule(new CountDistinct);
            self::registerModule(new Count);
            self::registerModule(new Max);
            self::registerModule(new Mean);
            self::registerModule(new Min);
            self::registerModule(new Size);
            self::registerModule(new Sum);
        }
    }

    protected static function getModule(string $name, ModuleType $type): ?StatsInterface
    {
        self::init();

        $r = self::$modules[$name] ?? null;

        return $r instanceof $type->value ? $r : null;
    }

    public static function getStatsPropertyModule(string $property): ?StatsPropertyInterface
    {
        return self::getModule($property, ModuleType::StatsProperty);
    }

    public static function getStatsMethodModule(string $method): ?StatsMethodInterface
    {
        return self::getModule($method, ModuleType::StatsMethod);
    }

    public static function registerModule(StatsInterface $module): void
    {
        self::init();

        if (empty($module::NAME)) {
            throw new NotYetImplementedException;
        }

        self::$modules[$module::NAME] = $module;
    }

    public static function removeModuleByMethod(string $method): void
    {
        self::init();

        if (\array_key_exists($method, self::$modules)) {
            unset(self::$modules[$method]);
        }
    }
}
