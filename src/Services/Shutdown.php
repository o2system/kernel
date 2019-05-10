<?php
/**
 * This file is part of the O2System Framework package.
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
    /**
     * Shutdown::execute
     */
    public function execute()
    {
        if ($this->count()) {
            foreach ($this->closures as $offset => $closure) {
                call_user_func($closure);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Shutdown::register
     *
     * @return mixed
     */
    public function register()
    {
        return call_user_func_array('register_shutdown_function', func_get_args());
    }
}