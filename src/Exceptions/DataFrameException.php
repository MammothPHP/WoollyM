<?php

declare(strict_types=1);

/**
 * Contains the DataFrameException.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */

namespace CondorcetPHP\Oliphant\Exceptions;

/**
 * Generic exception used as the root class for all other exceptions which occur within DataFrame operations.
 * @package   CondorcetPHP\Oliphant\Exceptions
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 *
 * @codeCoverageIgnore
 */
class DataFrameException extends \Exception {}
