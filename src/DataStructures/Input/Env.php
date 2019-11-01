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

namespace O2System\Kernel\DataStructures\Input;

// ------------------------------------------------------------------------

use O2System\Kernel\DataStructures\Input\Abstracts\AbstractInput;

/**
 * Class Env
 * @package O2System\Kernel\DataStructures\Input
 */
class Env extends AbstractInput
{
    /**
     * Env::__construct
     */
    public function __construct()
    {
        $this->storage =& $_ENV;
    }
}