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
 * Class Post
 * @package O2System\Kernel\DataStructures\Input
 */
class Post extends AbstractInput
{
    /**
     * Post::__construct
     */
    public function __construct()
    {
        $this->storage =& $_POST;
    }
}