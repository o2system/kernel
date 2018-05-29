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

namespace O2System\Kernel\Http;

// ------------------------------------------------------------------------

use O2System\Spl\Info\SplClassInfo;

/**
 * Class Controller
 *
 * @package O2System\Framework\Http
 */
class Controller
{
    public function getClassInfo()
    {
        $classInfo = new SplClassInfo($this);

        return $classInfo;
    }

    public function &__get($property)
    {
        $get[ $property ] = false;

        if (kernel()->hasService($property)) {
            return kernel()->getService($property);
        }

        return $get[ $property ];
    }

    // ------------------------------------------------------------------------

    public function __call($method, array $args = [])
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        }
    }
}