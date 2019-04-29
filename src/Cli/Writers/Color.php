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

use O2System\Kernel\Cli\Writers\Interfaces\ContextualColorsInterface;

/**
 * Class Color
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Color implements ContextualColorsInterface
{
    /**
     * Color::$foregroundColors
     *
     * List of available shell output foreground colors.
     *
     * @var array
     */
    protected $foregroundColors = [
        'black'        => '0;30',
        'dark-gray'    => '1;30',
        'blue'         => '0;34',
        'light-blue'   => '1;34',
        'green'        => '0;32',
        'light-green'  => '1;32',
        'cyan'         => '0;36',
        'light-cyan'   => '1;36',
        'red'          => '0;31',
        'light-red'    => '1;31',
        'purple'       => '0;35',
        'light-purple' => '1;35',
        'yellow'       => '0;33',
        'light-gray'   => '0;37',
        'white'        => '1;37',
    ];

    /**
     * Color::$backgroundColors
     *
     * List of available shell output background colors.
     *
     * @var array
     */
    protected $backgroundColors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light-gray' => '47',
    ];

    /**
     * Color::$foreground
     *
     * Shell output foreground color.
     *
     * @var string
     */
    protected $foreground = null;

    /**
     * Color::$background
     *
     * Shell output background color.
     *
     * @var string
     */
    protected $background = null;

    // ------------------------------------------------------------------------

    /**
     * Color::__construct
     *
     * @param string $foreground Foreground contextual color.
     * @param string $background Background contextual color.
     */
    public function __construct($foreground = null, $background = null)
    {
        $this->setForeground($foreground);
        $this->setBackground($background);
    }

    /**
     * Color::setForeground
     *
     * Sets shell output foreground color
     *
     * @param string $foreground
     *
     * @return static
     */
    public function setForeground($foreground)
    {
        if (isset($foreground)) {
            $foreground = strtolower($foreground);

            if (array_key_exists($foreground, $this->foregroundColors)) {
                $this->foreground = $foreground;
            }
        }


        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Color::setBackground
     *
     * Sets shell output background color
     *
     * @param string $background
     *
     * @return static
     */
    public function setBackground($background)
    {
        if (isset($background)) {
            $background = strtolower($background);

            if (array_key_exists($background, $this->backgroundColors)) {
                $this->background = $background;
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Color::paint
     *
     * @param string $string
     *
     * @return string
     */
    public function paint($string)
    {
        $coloredString = "";

        // set foreground color
        if (array_key_exists($this->foreground, $this->foregroundColors)) {
            $coloredString .= "\033[" . $this->foregroundColors[ $this->foreground ] . "m";
        }

        // set background color
        if (array_key_exists($this->background, $this->backgroundColors)) {
            $coloredString .= "\033[" . $this->backgroundColors[ $this->background ] . "m";
        }

        // combine with string and end the painting
        $coloredString .= $string . "\033[0m";

        return $coloredString;
    }
}