<?php

declare(strict_types=1);

/**
 * Contains the FileExistsException.
 * @package   DataFrame
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 */

namespace CondorcetPHP\Oliphant\Exceptions;

/**
 * Exception thrown when an file type implementation has been instructed to incorrectly write to a file which already
 * exists.
 * @package   CondorcetPHP\Oliphant\Exceptions
 * @author    Howard Gehring <hwgehring@gmail.com>
 * @copyright 2015 Howard Gehring <hwgehring@gmail.com>
 * @license   https://github.com/HWGehring/CondorcetPHP\Oliphant/blob/master/LICENSE BSD-3-Clause
 * @link      https://github.com/HWGehring/CondorcetPHP\Oliphant
 * @since     0.1.0
 *
 * @codeCoverageIgnore
 */
class FileExistsException extends DataFrameException {}
