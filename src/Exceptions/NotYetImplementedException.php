<?php

declare(strict_types=1);

/**
 * Contains the NotYetImplementedException.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/MammothPHP\WoollyM/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/MammothPHP\WoollyM
 * @since     0.1.0
 */

namespace MammothPHP\WoollyM\Exceptions;

/**
 * Exception thrown when a feature is called which has no tested implementation.
 * @package   MammothPHP\WoollyM\Exceptions
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/MammothPHP\WoollyM/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/MammothPHP\WoollyM
 * @since     0.1.0
 *
 * @codeCoverageIgnore
 */
class NotYetImplementedException extends DataFrameException {}
