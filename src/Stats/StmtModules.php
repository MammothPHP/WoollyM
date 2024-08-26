<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Stats;

use MammothPHP\WoollyM\Exceptions\NotYetImplementedException;
use MammothPHP\WoollyM\Stats\ModuleTypes\{ModuleStmtAccessType, StatsInterface, StatsMethodInterface, StatsPropertyInterface};
use MammothPHP\WoollyM\Stats\Modules\{Average, CountDistinctValues, Max, Mean, Min, Size, Sum};

abstract class StmtModules
{
    protected static ?array $modules = null;

    protected static function init(): void
    {
        if (self::$modules === null) {
            self::$modules = [];

            // Calculation
            self::registerModule(Average::class);
            self::registerModule(CountDistinctValues::class);
            self::registerModule(Max::class);
            self::registerModule(Mean::class);
            self::registerModule(Min::class);
            self::registerModule(Size::class);
            self::registerModule(Sum::class);
        }
    }

    protected static function getModule(string $name, ModuleStmtAccessType $type): ?StatsInterface
    {
        self::init();

        if (!isset(self::$modules[$name])) {
            return null;
        }

        $r = new self::$modules[$name];

        return $r instanceof $type->value ? $r : null;
    }

    /**
     * @internal
     */
    public static function getStatsPropertyModule(string $property): ?StatsPropertyInterface
    {
        return self::getModule($property, ModuleStmtAccessType::StatsProperty);
    }

    /**
     * @internal
     */
    public static function getStatsMethodModule(string $method): ?StatsMethodInterface
    {
        return self::getModule($method, ModuleStmtAccessType::StatsMethod);
    }

    public static function registerModule(string $moduleClass): void
    {
        self::init();

        if (!class_exists($moduleClass) || !class_implements($moduleClass)) {
            throw new NotYetImplementedException;
        }

        if (empty($moduleClass::NAME)) {
            throw new NotYetImplementedException;
        }

        self::$modules[$moduleClass::NAME] = $moduleClass;
    }

    public static function removeModuleByMethod(string $method): void
    {
        self::init();

        if (\array_key_exists($method, self::$modules)) {
            unset(self::$modules[$method]);
        }
    }
}
