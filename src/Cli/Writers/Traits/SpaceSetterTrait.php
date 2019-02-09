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
 * Class SpaceSetterTrait
 *
 * @package O2System\Kernel\Cli\Writers\Traits
 */
trait SpaceSetterTrait
{
    /**
     * SpaceSetterTrait::$indent
     *
     * Numbers of indentation.
     *
     * @var int
     */
    protected $space = 0;

    // ------------------------------------------------------------------------

    /**
     * SpaceSetterTrait::setSpace
     *
     * Sets indentation numbers.
     *
     * @param int $space
     *
     * @return static
     */
    public function setSpace($space)
    {
        $this->space = (int)$space;

        return $this;
    }
}