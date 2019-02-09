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

namespace O2System\Kernel\Cli\Writers\Traits;

// ------------------------------------------------------------------------

/**
 * Class StringSetterTrait
 *
 * @package O2System\Kernel\Cli\Writers\Traits
 */
trait StringSetterTrait
{
    /**
     * StringSetterTrait::$string
     *
     * String content.
     *
     * @var string
     */
    protected $string;

    // ------------------------------------------------------------------------

    /**
     * StringSetterTrait::setString
     *
     * Sets string content.
     *
     * @param string $string
     *
     * @return static
     */
    public function setString($string)
    {
        if (isset($string)) {
            $this->string = $string;
        }

        return $this;
    }
}