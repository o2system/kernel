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

// ------------------------------------------------------------------------

use O2System\Psr\Http\Message\ServerRequestInterface;
use O2System\Psr\Http\Message\StreamInterface;
use O2System\Psr\Http\Message\UploadedFileInterface;

/**
 * Class ServerRequest
 *
 * @package O2System\Kernel\Http\Message
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * Server Params
     *
     * @var array
     */
    protected $serverParams = [];

    /**
     * Server Cookie
     *
     * @var array
     */
    protected $cookieParams = [];

    /**
     * Server Query
     *
     * @var array
     */
    protected $queryParams = [];

    /**
     * Server Uploaded Files
     *
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * ServerRequest::__construct
     */
    public function __construct()
    {
        parent::__construct();

        // Set Cookie Params
        $this->cookieParams = $_COOKIE;

        // Set Header Params
        // In Apache, you can simply call apache_request_headers()
        if (function_exists('apache_request_headers')) {
            $this->headers = apache_request_headers();
        }

        $this->headers[ 'Content-Type' ] = isset($_SERVER[ 'CONTENT_TYPE' ])
            ? $_SERVER[ 'CONTENT_TYPE' ]
            : @getenv(
                'CONTENT_TYPE'
            );

        foreach ($_SERVER as $key => $val) {
            if (strpos($key, 'SERVER') !== false) {
                $key = str_replace('SERVER_', '', $key);
                $this->serverParams[ $key ] = $val;
            }

            if (sscanf($key, 'HTTP_%s', $header) === 1) {
                // take SOME_HEADER and turn it into Some-Header
                $header = str_replace('_', ' ', strtolower($header));
                $header = str_replace(' ', '-', ucwords($header));

                $this->headers[ $header ] = $_SERVER[ $key ];
            }
        }

        // Set Query Params
        if (null !== ($queryString = $this->uri->getQuery())) {
            parse_str($queryString, $this->queryParams);
        }

        // Populate file array
        $uploadedFiles = [];

        if (count($_FILES)) {
            foreach ($_FILES as $key => $value) {
                if (is_array($value[ 'name' ])) {
                    for ($i = 0; $i < count($value[ 'name' ]); $i++) {
                        if ( ! is_array($value[ 'name' ])) {
                            $uploadedFiles[ $key ][ $i ] = $value;
                            break;
                        }

                        $uploadedFiles[ $key ][ $i ][ 'name' ] = $value[ 'name' ][ $i ];
                        $uploadedFiles[ $key ][ $i ][ 'type' ] = $value[ 'type' ][ $i ];
                        $uploadedFiles[ $key ][ $i ][ 'tmp_name' ] = $value[ 'tmp_name' ][ $i ];
                        $uploadedFiles[ $key ][ $i ][ 'error' ] = $value[ 'error' ][ $i ];
                        $uploadedFiles[ $key ][ $i ][ 'size' ] = $value[ 'size' ][ $i ];
                    }
                } else {
                    $uploadedFiles[ $key ][ 'name' ] = $value[ 'name' ];
                    $uploadedFiles[ $key ][ 'type' ] = $value[ 'type' ];
                    $uploadedFiles[ $key ][ 'tmp_name' ] = $value[ 'tmp_name' ];
                    $uploadedFiles[ $key ][ 'error' ] = $value[ 'error' ];
                    $uploadedFiles[ $key ][ 'size' ] = $value[ 'size' ];
                }
            }
        }

        $this->uploadedFiles = $uploadedFiles;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getServerParams
     *
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getCookieParams
     *
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::withCookieParams
     *
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     *
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $message = clone $this;

        foreach ($cookies as $key => $value) {
            $message->cookieParams[ $key ] = $value;
        }

        return $message;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getQueryParams
     *
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::withQueryParams
     *
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     *
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $message = clone $this;
        $message->queryParams = $query;
        $message->uri = $this->uri->withQuery(http_build_query($query));

        return $message;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getUploadedFiles
     *
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     * @throws \O2System\Spl\Exceptions\Logic\BadFunctionCall\BadPhpExtensionCallException
     */
    public function getUploadedFiles()
    {
        $response = [];
        foreach ($this->uploadedFiles as $key => $uploadedFile) {
            if (empty($uploadedFile[ 'name' ])) {
                continue;
            } elseif (is_numeric(key($uploadedFile))) {
                foreach ($uploadedFile as $index => $file) {
                    $response[ $key ][ $index ] = new UploadFile($file);
                }
            } else {
                $response[ $key ] = new UploadFile($uploadedFile);
            }
        }

        return $response;
    }

    // --------------------------------------------------------------------------------------

    /**
     * ServerRequest::withUploadedFiles
     *
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     *
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $message = clone $this;

        foreach ($uploadedFiles as $uploadedFile) {
            if ($uploadedFile instanceof UploadedFileInterface) {
                $message->uploadedFiles[] = $uploadedFile;
            } else {
                throw new \InvalidArgumentException('Not Instance Of UploadedFileInterface');
                break;
            }
        }

        return $message;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getParsedBody
     *
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        if (isset($this->headers[ 'Content-Type' ])) {
            if (in_array(
                strtolower($this->headers[ 'Content-Type' ]),
                [
                    'application/x-www-form-urlencoded',
                    'multipart/form-data',
                ]
            )) {
                return $_POST;
            }
        }

        return $_REQUEST;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::withParsedBody
     *
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     *
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        $message = clone $this;

        if ($data instanceof StreamInterface) {
            $message->body = $data;
        } elseif (is_string($data)) {
            $message->body->write($data);
        }

        return $message;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getAttributes
     *
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return mixed[] Attributes derived from the request.
     */
    public function getAttributes()
    {
        return array_keys($this->serverParams);
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::getAttribute
     *
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $name = str_replace('SERVER_', '', $name);
        $name = strtoupper($name);

        if (isset($this->serverParams[ $name ])) {
            return $this->serverParams[ $name ];
        } elseif (isset($_SERVER[ $name ])) {
            return $_SERVER[ $name ];
        }

        return $default;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::withAttribute
     *
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     *
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $name = str_replace('SERVER_', '', $name);
        $name = strtoupper($name);

        $message = clone $this;
        $message->serverParams[ $name ] = $value;

        if (empty($_SERVER[ 'SERVER_' . $name ])) {
            $_SERVER[ 'SERVER_' . $name ] = $value;
        }

        return $message;
    }

    //--------------------------------------------------------------------

    /**
     * ServerRequest::withoutAttribute
     *
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     *
     * @param string $name The attribute name.
     *
     * @return static
     */
    public function withoutAttribute($name)
    {
        $name = str_replace('SERVER_', '', $name);
        $name = strtoupper($name);

        $message = clone $this;

        if (isset($this->serverParams[ $name ])) {
            unset($this->serverParams[ $name ]);
        }

        return $message;
    }
}