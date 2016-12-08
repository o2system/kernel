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

namespace O2System\Kernel\Http;


use O2System\Psr\Http\TransportInterface;

class Transport implements TransportInterface
{
    public function __construct ()
    {
        if ( ! function_exists( 'curl_init' ) ) {

        }
    }

    public function getHandle ()
    {
        // TODO: Implement getHandle() method.
    }

    public function getOptions ()
    {
        return $this->options;
    }

    public function withOption ( $name, $value )
    {
        $this->options[ $name ] = $value;
    }
}