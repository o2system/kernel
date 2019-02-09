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
 * Class QuoteSetterTrait
 *
 * @package O2System\Kernel\Cli\Writers\Traits
 */
trait QuoteSetterTrait
{
    /**
     * QuoteSetterTrait::$quote
     *
     * Quote string content.
     *
     * @var string
     */
    protected $quote;

    // ------------------------------------------------------------------------

    /**
     * QuoteSetterTrait::setQuote
     *
     * Sets quote string content.
     *
     * @param string $quote
     *
     * @return static
     */
    public function setQuote($quote)
    {
        if (isset($quote)) {
            $this->quote = trim($quote);
        }

        return $this;
    }
}