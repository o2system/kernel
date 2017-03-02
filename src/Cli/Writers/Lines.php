<?php
/**
 * This file is part of the O2System PHP Framework package.
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

/**
 * Class Lines
 *
 * Lines generator for PHP command line interface (cli).
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Lines
{
    /**
     * Lines::$string
     *
     * Line string character.
     *
     * @var string
     */
    protected $string = '-';

    /**
     * Lines::$width
     *
     * Numbers of each lines width.
     *
     * @var int
     */
    protected $width = 0;

    /**
     * Lines::$numbers
     *
     * Numbers of lines.
     *
     * @var int
     */
    protected $numbers = 1;

    // ------------------------------------------------------------------------

    /**
     * Lines::__construct
     *
     * @param string $string  Lines character.
     * @param int    $numbers Numbers of lines.
     * @param int    $width   Numbers of lines width.
     */
    public function __construct ( $string = '-', $numbers = 0, $width = 0 )
    {
        $this->string = (string) $string;
        $this->numbers = (int) $numbers;
        $this->width = (int) $width;
    }

    // ------------------------------------------------------------------------

    /**
     * Lines::setCharacter
     *
     * Set string of lines character.
     *
     * @param $character Lines character.
     *
     * @return static
     */
    public function setCharacter ( $character )
    {
        $this->string = $character;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Lines::setNumbers
     *
     * Set numbers of lines.
     *
     * @param int $numbers Numbers of lines.
     *
     * @return static
     */
    public function setNumbers ( $numbers )
    {
        $this->numbers = (int) $numbers;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Lines::setWidth
     *
     * Set numbers of lines width.
     *
     * @param int $width Numbers of lines width.
     *
     * @return static
     */
    public function setWidth ( $width )
    {
        $this->width = (int) $width;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Lines::__toString
     *
     * Render lines into string.
     *
     * @return string
     */
    public function __toString ()
    {
        $lines = [ ];

        for ( $i = 0; $i < $this->numbers; $i++ ) {
            $lines[ $i ] = str_repeat( $this->string, $this->width );
        }

        return implode( PHP_EOL, $lines );
    }
}