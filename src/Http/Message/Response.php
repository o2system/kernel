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

namespace O2System\Kernel\Http\Message;

use O2System\Kernel\Http\Abstracts\AbstractMessage;
use O2System\Psr\Http\Header\ResponseFieldInterface;
use O2System\Psr\Http\Message\ResponseInterface;

/**
 * Class Response
 *
 * @package O2System\Kernel\Http\Message
 */
class Response extends AbstractMessage implements
    ResponseInterface,
    ResponseFieldInterface
{
    /**
     * Response::$statusCode
     *
     * Response Status Code
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Response::$reasonPhrase
     *
     * Response Reason Phrase
     *
     * @var string
     */
    protected $reasonPhrase = 'OK';

    // ------------------------------------------------------------------------

    /**
     * Response::__construct
     */
    public function __construct()
    {
        $this->body = new Stream();
    }

    // ------------------------------------------------------------------------

    /**
     * ResponseInterface::getStatusCode
     *
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    // ------------------------------------------------------------------------

    /**
     * ResponseInterface::withStatus
     *
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     *
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $message = clone $this;
        $message->statusCode = $code;
        $message->reasonPhrase = $reasonPhrase;

        return $message;
    }

    // ------------------------------------------------------------------------

    /**
     * ResponseInterface::getReasonPhrase
     *
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be empty. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code DataStructures) for the response's
     * status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
}