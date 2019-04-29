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

use O2System\Kernel\Http\Message\Stream;
use O2System\Psr\Http\Message\MessageInterface;
use O2System\Psr\Http\Message\StreamInterface;

/**
 * Class Message
 *
 * @package O2System\Kernel\Http
 */
abstract class AbstractMessage implements MessageInterface
{
    /**
     * Message Protocol
     *
     * @var  string
     */
    protected $protocol = '1.1';

    /**
     * Message Headers
     *
     * @var  array
     */
    protected $headers = [];

    /**
     * Message Body
     *
     * @var Stream
     */
    protected $body;

    // ------------------------------------------------------------------------

    /**
     * Message::getProtocolVersion
     *
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::withProtocolVersion
     *
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     *
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if (in_array($version, ['1.0', '1.1', '2'])) {
            $message = clone $this;
            $message->protocol = $version;

            return $message;
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::getHeaders
     *
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ': ' . implode(', ', $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers.
     *     Each key MUST be a header name, and each value MUST be an array of
     *     strings for that header.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::hasHeader
     *
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return (bool)isset($this->headers[ $name ]);
    }

    // ------------------------------------------------------------------------

    /**
     * Message::getHeaderLine
     *
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        if (isset($this->headers[ $name ])) {
            $this->headers[ $name ];
        }

        return '';
    }

    // ------------------------------------------------------------------------

    /**
     * Message::withAddedHeader
     *
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws \InvalidArgumentException for invalid header names.
     * @throws \InvalidArgumentException for invalid header values.
     */
    public function withAddedHeader($name, $value)
    {
        $lines = $this->getHeader($name);
        $value = array_map('trim', explode(',', $value));

        $lines = array_merge($lines, $value);

        return $this->withHeader($name, implode(', ', $lines));
    }

    // ------------------------------------------------------------------------

    /**
     * Message::getHeader
     *
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        $lines = [];

        if (isset($this->headers[ $name ])) {
            $lines = array_map('trim', explode(',', $this->headers[ $name ]));
        }

        return $lines;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::withHeader
     *
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $message = clone $this;
        $message->headers[ $name ] = $value;

        return $message;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::withoutHeader
     *
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return static
     */
    public function withoutHeader($name)
    {
        $message = clone $this;

        if (isset($message->headers[ $name ])) {
            unset($message->headers[ $name ]);
        }

        return $message;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::getBody
     *
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function &getBody()
    {
        if (empty($this->body)) {
            $this->body = new Stream();
        }

        return $this->body;
    }

    // ------------------------------------------------------------------------

    /**
     * Message::withBody
     *
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     *
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $message = clone $this;
        $message->body = $body;

        return $message;
    }
}