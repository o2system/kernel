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

/**
 * Class Label
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Label implements ContextualClassInterface
{
    use ContextualColorClassSetterTrait;
    use IndentSetterTrait;
    use NewLinesSetterTrait;
    use StringSetterTrait;

    /**
     * Label::__construct
     *
     * @param string $string
     * @param string $contextualClass
     */
    public function __construct($string = null, $contextualClass = 'default')
    {
        $this->setIndent(1);
        $this->setString($string);
        $this->setContextualClass($contextualClass);
    }

    // ------------------------------------------------------------------------

    /**
     * Label::__toString
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
     * Label::render
     *
     * Rendering labeled string.
     *
     * @return string
     */
    public function render()
    {
        // if the string of label is empty then nothing to be processed.
        if (empty($this->string)) {
            return '';
        }

        $string = str_repeat(' ', 2) . $this->string . str_repeat(' ', 2);

        switch ($this->contextualClass) {
            default:
                $output = $string;
                break;
            case 'primary':
                $output = str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[44m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m" . "\r\n";
                $output .= str_repeat(
                        ' ',
                        $this->indent
                    ) . "\033[1;37m" . "\033[44m" . $string . "\033[0m" . "\r\n";
                $output .= str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[44m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m";
                break;
            case 'success':
                $output = str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[42m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m" . "\r\n";
                $output .= str_repeat(
                        ' ',
                        $this->indent
                    ) . "\033[1;37m" . "\033[42m" . $string . "\033[0m" . "\r\n";
                $output .= str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[42m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m";
                break;
            case 'info':
                $output = str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[46m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m" . "\r\n";
                $output .= str_repeat(
                        ' ',
                        $this->indent
                    ) . "\033[1;37m" . "\033[46m" . $string . "\033[0m" . "\r\n";
                $output .= str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[46m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m";
                break;
            case 'warning':
                $output = str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[43m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m" . "\r\n";
                $output .= str_repeat(
                        ' ',
                        $this->indent
                    ) . "\033[1;37m" . "\033[43m" . $string . "\033[0m" . "\r\n";
                $output .= str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[43m" . str_repeat(
                        ' ',
                        strlen($string)
                    ) . "\033[0m";
                break;
            case 'danger':
                $output = str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[41m" .
                    str_repeat(' ', strlen($string)) . "\033[0m" . "\r\n";
                $output .= str_repeat(
                        ' ',
                        $this->indent
                    ) . "\033[1;37m" . "\033[41m" . $string . "\033[0m" . "\r\n";
                $output .= str_repeat(' ', $this->indent) . "\033[1;37m" . "\033[41m" .
                    str_repeat(' ', strlen($string)) . "\033[0m";
                break;
        }

        return str_repeat(PHP_EOL, $this->newLinesBefore) . $output . str_repeat(PHP_EOL, $this->newLinesAfter);
    }
}