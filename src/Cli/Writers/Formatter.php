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
 * Class Formatter
 *
 * @package O2System\Kernel\Cli\Writers
 */
class Formatter
{
    /** @var int */
    public static $defaultIndentWidth = 1;
    /** @var int */
    protected $indentWidth = 1;
    /** @var int */
    protected $indent = 0;
    /** @var string */
    protected $quote = '';
    /** @var string */
    protected $fgColor;
    /** @var string */
    protected $bgColor;

    /**
     * @param array $options
     */
    public function __construct ( array $options = [ ] )
    {
        $this->indentWidth = self::$defaultIndentWidth;
        $this->setOptions( $options );
    }

    /**
     * Returns the text formated with the specified options
     *
     * @param string $text
     * @param array  $options
     *
     * @return string
     */
    public static function apply ( $text, array $options = [ ] )
    {
        $formater = new self( $options );

        return $formater->format( $text );
    }

    /**
     * Available options:
     *  - indentWidth
     *  - indent
     *  - quote
     *  - fgcolor
     *  - bgcolor
     *
     * @param array $options
     *
     * @return Formatter
     */
    public function setOptions ( array $options )
    {
        if ( isset( $options['indentWidth'] ) ) {
            $this->setIndentWidth( $options['indentWidth'] );
        }
        if ( isset( $options['indent'] ) ) {
            $this->setIndent( $options['indent'] );
        }
        if ( isset( $options['quote'] ) ) {
            $this->setQuote( $options['quote'] );
        }
        if ( isset( $options['fgcolor'] ) ) {
            $this->setFgColor( $options['fgcolor'] );
        }
        if ( isset( $options['bgcolor'] ) ) {
            $this->setBgColor( $options['bgcolor'] );
        }

        return $this;
    }

    /**
     * @param int $indent
     *
     * @return Formatter
     */
    public function setIndentWidth ( $width )
    {
        $this->indentWidth = (int) $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getIndentWidth ()
    {
        return $this->indentWidth;
    }

    /**
     * @param int $indent
     *
     * @return Formatter
     */
    public function setIndent ( $indent )
    {
        $this->indent = (int) $indent;

        return $this;
    }

    /**
     * @return int
     */
    public function getIndent ()
    {
        return $this->indent;
    }

    /**
     * @param string $quote
     *
     * @return Formatter
     */
    public function setQuote ( $quote )
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuote ()
    {
        return $this->quote;
    }

    /**
     * @param string $color
     *
     * @return Formatter
     */
    public function setFgColor ( $color )
    {
        $this->fgColor = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getFgColor ()
    {
        return $this->fgColor;
    }

    /**
     * @param string $color
     *
     * @return Formatter
     */
    public function setBgColor ( $color )
    {
        $this->bgColor = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getBgColor ()
    {
        return $this->bgColor;
    }

    /**
     * Formatters $text according to the formater's options
     *
     * @param string $text
     */
    public function format ( $text )
    {
        $lines = explode( "\n", $text );
        foreach ( $lines as &$line ) {
            $line = ( (string) $this->quote )
                    . str_repeat( ' ', $this->indent * $this->indentWidth )
                    . $line;
        }

        return Colors::colorize( implode( "\n", $lines ), $this->fgColor, $this->bgColor );
    }
}