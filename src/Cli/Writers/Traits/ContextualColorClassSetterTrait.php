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

use O2System\Kernel\Cli\Writers\Color;

/**
 * Class ContextualClassSetterTrait
 *
 * @package O2System\Kernel\Cli\Writers\Traits
 */
trait ContextualColorClassSetterTrait
{
    /**
     * ContextualColorClassSetterTrait::$contextualClass
     *
     * Text contextual class.
     *
     * @var string
     */
    protected $contextualClass;

    /**
     * ContextualColorClassSetterTrait::$color
     *
     * Text color instance.
     *
     * @var Color
     */
    protected $color;

    // ------------------------------------------------------------------------

    /**
     * ContextualColorClassSetterTrait::setContextualClass
     *
     * Sets contextual class.
     *
     * @param string $class
     *
     * @return static
     */
    public function setContextualClass($class)
    {
        $class = strtolower($class);
        $this->contextualClass = $class;

        if (property_exists($this, 'contextualClassColorMap')) {
            if (array_key_exists($class, $this->contextualClassColorMap)) {
                $this->setColor(new Color($this->contextualClassColorMap[ $class ]));
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * ContextualColorClassSetterTrait::setColor
     *
     * Set contextual color instance.
     *
     * @param Color $color
     *
     * @return $this
     */
    public function setColor(Color $color)
    {
        $this->color = $color;

        return $this;
    }
}