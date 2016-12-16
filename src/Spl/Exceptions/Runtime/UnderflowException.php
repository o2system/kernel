<?php
/**
 * This file is part of the O2System PHP Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

namespace O2System\Kernel\Spl\Exceptions\Runtime;

// ------------------------------------------------------------------------

use O2System\Kernel\Spl\Exceptions\RuntimeException;

/**
 * Class UnderflowException
 *
 * Exception thrown when performing an invalid operation on an empty container, such as removing an element.
 *
 * @see     http://php.net/manual/en/class.underflowexception.php
 *
 * @package O2System\Kernel\Spl\Exceptions\Runtime
 */
class UnderflowException extends RuntimeException
{

}