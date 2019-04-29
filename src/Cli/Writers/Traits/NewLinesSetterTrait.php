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
 * Class NewLinesSetterTrait
 *
 * @package O2System\Kernel\Cli\Writers\Traits
 */
trait NewLinesSetterTrait
{
    /**
     * NewLinesSetterTrait::$newLinesBefore
     *
     * Numbers of new lines before.
     *
     * @var int
     */
    protected $newLinesBefore = 0;

    /**
     * NewLinesSetterTrait::$newLinesAfter
     *
     * Numbers of new lines after.
     *
     * @var int
     */
    protected $newLinesAfter = 0;

    // ------------------------------------------------------------------------

    /**
     * NewLinesSetterTrait::setNewLinesBefore
     *
     * Sets numbers of new lines before.
     *
     * @param $lines
     *
     * @return static
     */
    public function setNewLinesBefore($lines)
    {
        $this->newLinesBefore = (int)$lines;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * NewLinesSetterTrait::setNewLinesAfter
     *
     * Sets numbers of new lines after.
     *
     * @param $lines
     *
     * @return static
     */
    public function setNewLinesAfter($lines)
    {
        $this->newLinesAfter = (int)$lines;

        return $this;
    }
}