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
 * Class IndentSetterTrait
 *
 * @package O2System\Kernel\Cli\Writers\Traits
 */
trait IndentSetterTrait
{
    /**
     * IndentSetterTrait::$indent
     *
     * Numbers of indentation.
     *
     * @var int
     */
    protected $indent = 0;

    // ------------------------------------------------------------------------

    /**
     * IndentSetterTrait::setIndent
     *
     * Sets indentation numbers.
     *
     * @param int $indent
     *
     * @return static
     */
    public function setIndent($indent)
    {
        $this->indent = (int)$indent;

        return $this;
    }
}