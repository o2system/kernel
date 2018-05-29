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

namespace O2System\Kernel\Services;

// ------------------------------------------------------------------------

use O2System\Spl\Containers\SplClosureContainer;

/**
 * Class Shutdown
 * @package O2System\Kernel\Services
 */
class Shutdown extends SplClosureContainer
{
    public function execute()
    {
        foreach ($this as $offset => $closure) {
            call_user_func($closure);
        }
    }
}