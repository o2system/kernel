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

use O2System\Psr\Http\ClientInterface;
use O2System\Psr\Http\Message\RequestInterface;
use O2System\Psr\Http\TransportInterface;

class Client implements ClientInterface
{
    /**
     * Client Transport
     *
     * @var Transport
     */
    protected $transport;

    public function __construct ( TransportInterface $transport = null )
    {
        if ( isset( $transport ) ) {
            $this->transport =& $transport;
        } else {
            $this->transport = new Transport\Curl();
        }
    }

    public function getParams ()
    {
        // TODO: Implement getParams() method.
    }

    public function withParam ( $name, $value )
    {
        // TODO: Implement withParam() method.
    }

    public function options ( RequestInterface $request )
    {
        // TODO: Implement options() method.
    }

    public function get ( $request, array $params = [ ], array $headers = [ ] )
    {
        if ( is_string( $request ) ) {
            $request = ( new Message\Request() )
                ->withMethod( Message\Request::METHOD_GET )
                ->withUri( new Uri( $request ) );
        }

        if ( $request instanceof RequestInterface ) {
            if ( ! empty( $params ) ) {
                $request = $request->withUri( $request->getUri()->withQuery( http_build_query( $params ) ) );
            }

            if ( ! empty( $headers ) ) {
                foreach ( $headers as $name => $value ) {
                    $request = $request->withHeader( $name, $value );
                }
            }

            if ( $this->transport instanceof TransportInterface ) {
                return $this->transport->getResponse( $request );
            }
        }

        return false;
    }

    public function head ( RequestInterface $request )
    {
        // TODO: Implement head() method.
    }

    public function patch ( RequestInterface $request )
    {
        // TODO: Implement patch() method.
    }

    public function post ( RequestInterface $request )
    {
        // TODO: Implement post() method.
    }

    public function put ( RequestInterface $request )
    {
        // TODO: Implement put() method.
    }

    public function delete ( RequestInterface $request )
    {
        // TODO: Implement delete() method.
    }

    public function trace ( RequestInterface $request )
    {
        // TODO: Implement trace() method.
    }

    public function connect ( RequestInterface $request )
    {
        // TODO: Implement connect() method.
    }

    public function &getTransport ()
    {
        return $this->transport;
    }

    public function withTransport ( TransportInterface $transport )
    {
        $this->transport =& $transport;

        return $this;
    }
}