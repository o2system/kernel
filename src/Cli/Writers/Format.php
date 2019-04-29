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
use O2System\Kernel\Cli\Writers\Traits\QuoteSetterTrait;
use O2System\Kernel\Cli\Writers\Traits\SpaceSetterTrait;
use O2System\Kernel\Cli\Writers\Traits\StringSetterTrait;
use O2System\Spl\Traits\OptionsSetterTrait;

/**
 * Class Format
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Format implements ContextualClassInterface
{
    use OptionsSetterTrait;
    use ContextualColorClassSetterTrait;
    use IndentSetterTrait;
    use SpaceSetterTrait;
    use NewLinesSetterTrait;
    use QuoteSetterTrait;
    use StringSetterTrait;

    /**
     * Format::$contextualClassColorMap
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

    // ------------------------------------------------------------------------

    /**
     * Format::__construct
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    // ------------------------------------------------------------------------

    /**
     * Format::__toString
     *
     * Implementation __toString magic method so that when the class is converted to a string
     * automatically performs the rendering process.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->apply();
    }

    // ------------------------------------------------------------------------

    /**
     * Format::apply
     *
     * @return string
     */
    public function apply()
    {
        if (empty($this->string)) {
            return '';
        }

        $lines = explode("\n", $this->string);

        foreach ($lines as &$line) {
            $line = ((string)$this->quote)
                . $line;
        }

        if ($this->color instanceof Color) {
            $output = str_repeat(' ', $this->indent) . $this->color->paint(implode("\n",
                    $lines)) . str_repeat(' ', $this->space);
        } else {
            $output = str_repeat(' ', $this->indent) . implode("\n", $lines) . str_repeat(' ', $this->space);
        }

        return str_repeat(PHP_EOL, $this->newLinesBefore) . $output . str_repeat(PHP_EOL, $this->newLinesAfter);
    }
}