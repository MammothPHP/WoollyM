<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM;

final class NullValue {
    protected static self $instance;

    public static function create(): self {
        self::$instance ??= new self;

        return self::$instance;
    }
}