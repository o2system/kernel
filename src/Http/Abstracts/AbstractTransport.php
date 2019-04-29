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

namespace O2System\Kernel\Http\Abstracts;

// ------------------------------------------------------------------------

use O2System\Psr\Http\TransportInterface;

abstract class AbstractTransport implements TransportInterface
{
    /**
     * Property options.
     *
     * @var  array
     */
    protected $options = [];

    /**
     * AbstractTransport::__construct
     *
     * @param   array|\ArrayAccess $options Client options object.
     *
     * @since   2.1
     */
    public function __construct($options = [])
    {

    }

    // ------------------------------------------------------------------------

    /**
     * AbstractTransport::getOption
     *
     * Get option value.
     *
     * @param   string $name    Option name.
     * @param   mixed  $default The default value if not exists.
     *
     * @return  mixed  The found value or default value.
     */
    public function getOption($name, $default = null)
    {
        if ( ! isset($this->options[ $name ])) {
            return $default;
        }

        return $this->options[ $name ];
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractTransport::setOption
     *
     * Set option value.
     *
     * @param   string $name  Option name.
     * @param   mixed  $value The value you want to set in.
     *
     * @return  static  Return self to support chaining.
     */
    public function setOption($name, $value)
    {
        $this->options[ $name ] = $value;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * AbstractTransport::getOptions
     *
     * Method to get property Options
     *
     * @return  array
     */
    public function getOptions()
    {
        return $this->options;
    }
}