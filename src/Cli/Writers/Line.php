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

namespace O2System\Kernel\Cli\Writers;

// ------------------------------------------------------------------------

use O2System\Kernel\Cli\Writers\Interfaces\ContextualClassInterface;
use O2System\Kernel\Cli\Writers\Traits\ContextualColorClassSetterTrait;
use O2System\Kernel\Cli\Writers\Traits\IndentSetterTrait;
use O2System\Kernel\Cli\Writers\Traits\NewLinesSetterTrait;
use O2System\Kernel\Cli\Writers\Traits\StringSetterTrait;
use O2System\Spl\Traits\OptionsSetterTrait;

/**
 * Class Line
 *
 * Line generator for PHP command line interface (cli).
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Line implements ContextualClassInterface
{
    use OptionsSetterTrait;
    use ContextualColorClassSetterTrait;
    use NewLinesSetterTrait;
    use IndentSetterTrait;
    use StringSetterTrait;

    /**
     * Line::$contextualClassColorMap
     *
     * Contextual class color mapping.
     *
     * @var array
     */
    protected $contextualClassColorMap = [
        'default' => 'white',
        'primary' => 'blue',
        'success' => 'green',
        'info'    => 'cyan',
        'warning' => 'yellow',
        'danger'  => 'red',
    ];

    /**
     * Line::$width
     *
     * Numbers of each lines width.
     *
     * @var int
     */
    protected $width = 0;

    /**
     * Line::$numbers
     *
     * Numbers of lines.
     *
     * @var int
     */
    protected $numbers = 1;

    // ------------------------------------------------------------------------

    /**
     * Line::__construct
     *
     * @param int   $width
     * @param array $options
     */
    public function __construct($width, array $options = [], $contextualClass = 'default')
    {
        $this->width = (int)$width;
        $this->string = '-';

        $this->setOptions($options);
        $this->setContextualClass($contextualClass);
    }

    // ------------------------------------------------------------------------

    /**
     * Line::setNumbers
     *
     * Set numbers of lines.
     *
     * @param int $numbers Numbers of lines.
     *
     * @return static
     */
    public function setNumbers($numbers)
    {
        $this->numbers = (int)$numbers;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Line::setWidth
     *
     * Set numbers of lines width.
     *
     * @param int $width Numbers of lines width.
     *
     * @return static
     */
    public function setWidth($width)
    {
        $this->width = (int)$width;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Line::__toString
     *
     * Implementation __toString magic method so that when the class is converted to a string
     * automatically performs the rendering process.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    // ------------------------------------------------------------------------

    /**
     * Line::render
     *
     * Rendering lines string.
     *
     * @return string
     */
    public function render()
    {
        $lines = [];

        for ($i = 0; $i < $this->numbers; $i++) {
            $lines[ $i ] = $this->color->paint(str_repeat($this->string, $this->width));
        }

        return str_repeat(PHP_EOL, $this->newLinesBefore) . implode(PHP_EOL, $lines) . str_repeat(PHP_EOL,
                $this->newLinesAfter);
    }
}