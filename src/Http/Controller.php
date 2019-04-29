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
    /**
     * Controller::getClassInfo
     *
     * @return \O2System\Spl\Info\SplClassInfo
     */
    public function getClassInfo()
    {
        $classInfo = new SplClassInfo($this);

        return $classInfo;
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::__get
     *
     * @param string $property
     *
     * @return mixed
     */
    public function &__get($property)
    {
        $get[ $property ] = false;

        if (services()->has($property)) {
            $get[ $property ] = services()->get($property);
        }

        return $get[ $property ];
    }

    // ------------------------------------------------------------------------

    /**
     * Controller::__call
     *
     * @param string  $method
     * @param array   $args
     *
     * @return mixed
     */
    public function __call($method, array $args = [])
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        }
    }
}