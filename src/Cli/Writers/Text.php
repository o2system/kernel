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
use O2System\Kernel\Cli\Writers\Traits\SpaceSetterTrait;
use O2System\Kernel\Cli\Writers\Traits\StringSetterTrait;

/**
 * Class Text
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Text implements ContextualClassInterface
{
    use ContextualColorClassSetterTrait;
    use IndentSetterTrait;
    use SpaceSetterTrait;
    use StringSetterTrait;

    /**
     * Text::$contextualClassColorMap
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
     * Text::__construct
     *
     * @param string $string
     * @param string $contextualClass
     */
    public function __construct($string = null, $contextualClass = 'default')
    {
        $this->setString($string);
        $this->setContextualClass($contextualClass);
    }

    // ------------------------------------------------------------------------

    /**
     * Text::getLength
     *
     * Gets lengths of text string.
     *
     * @return int
     */
    public function getLength()
    {
        return (int)strlen($this->string);
    }

    // ------------------------------------------------------------------------

    /**
     * Text::__toString
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
     * Text::render
     *
     * Rendering painted string.
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->string)) {
            return '';
        }

        $string = str_repeat(' ', $this->indent) . $this->string . str_repeat(' ', $this->space);

        return $this->color->paint($string);
    }
}