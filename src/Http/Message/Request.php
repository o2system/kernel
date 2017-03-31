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

namespace O2System\Kernel\Http\Message;

// ------------------------------------------------------------------------

use O2System\Kernel\Http\Abstracts\AbstractMessage;
use O2System\Psr\Http\Header\RequestFieldInterface;
use O2System\Psr\Http\Message\RequestInterface;
use O2System\Psr\Http\Message\UriInterface;

/**
 * Class Request
 *
 * @package O2System\Curl
 */
class Request extends AbstractMessage implements
    RequestInterface,
    RequestFieldInterface
{
    /**
     * Request Method
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * Request Uri
     *
     * @var Uri|\O2System\Framework\Http\Message\Uri
     */
    protected $uri;

    // ------------------------------------------------------------------------

    /**
     * RequestInterface::getRequestTarget
     *
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        $requestTarget = '/';

        if ( empty( $this->target ) ) {
            if ( $this->uri instanceof Uri ) {
                $requestTarget = $this->uri->getPath();

                if ( null !== ( $query = $this->uri->getQuery() ) ) {
                    $requestTarget .= '?' . $query;
                }
            }
        }

        return $requestTarget;
    }

    // ------------------------------------------------------------------------

    /**
     * RequestInterface::withRequestTarget
     *
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     *
     * @param mixed $requestTarget
     *
     * @return static
     */
    public function withRequestTarget( $requestTarget )
    {
        $requestTarget = trim( $requestTarget );
        $parseTarget = parse_url( $requestTarget );

        $uri = $this->uri;

        if ( isset( $parseTarget[ 'path' ] ) ) {
            $uri = $this->uri->withPath( $parseTarget[ 'path' ] );
        }

        if ( isset( $parseTarget[ 'query' ] ) ) {
            $uri = $this->uri->withPath( $parseTarget[ 'query' ] );
        }

        $this->uri = $uri;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * RequestInterface::getMethod
     *
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    // ------------------------------------------------------------------------

    /**
     * RequestInterface::withMethod
     *
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     *
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod( $method )
    {
        $method = strtoupper( $method );

        if ( in_array(
            $method,
            [
                'OPTIONS',
                'GET',
                'HEAD',
                'PATCH',
                'POST',
                'PUT',
                'DELETE',
                'TRACE',
                'CONNECT',
            ]
        ) ) {
            $this->method = $method;

            return $this;
        }

        throw new \InvalidArgumentException( 'Invalid HTTP Method' );
    }

    // ------------------------------------------------------------------------

    /**
     * RequestInterface::getUri
     *
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     * @return Uri|\O2System\Framework\Http\Message\Uri Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function &getUri()
    {
        if ( empty( $this->uri ) ) {
            $this->uri = new Uri();
        }

        return $this->uri;
    }

    // ------------------------------------------------------------------------

    /**
     * RequestInterface::withUri
     *
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     *
     * @return static
     */
    public function withUri( UriInterface $uri, $preserveHost = false )
    {
        $message = clone $this;
        $message->uri = $uri;

        if ( $preserveHost ) {
            if ( null !== ( $host = $uri->getHost() ) ) {
                if ( null !== ( $port = $uri->getPort() ) ) {
                    $host .= ':' . $port;
                }

                $message->withHeader( 'Host', $host );
            }
        }

        return $message;
    }
}