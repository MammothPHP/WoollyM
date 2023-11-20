<?php

declare(strict_types=1);

namespace MammothPHP\WoollyM\Exceptions;

class MethodNotAvailableInColumnContextException extends DataFrameException
{
    protected $message = 'This method is not available ins a column context';
}
