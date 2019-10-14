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

use O2System\Kernel\Http\Message\Uri\Domain;
use O2System\Kernel\Http\Message\Uri\Segments;
use O2System\Psr\Http\Message\UriInterface;

/**
 * Class Uri
 *
 * @package O2System\Kernel\Http
 */
class Uri implements UriInterface
{
    /**
     * Uri::$domain
     *
     * @var Domain
     */
    public $domain;

    /**
     * Uri::$segments
     *
     * @var \O2System\Kernel\Http\Message\Uri\Segments
     */
    public $segments;

    /**
     * Uri::$suffix
     *
     * @var string
     */
    protected $suffix;

    /**
     * Uri::$host
     *
     * The host subcomponent of authority is identified by an IP literal
     * encapsulated within square brackets, an IPv4 address in dotted-decimal form,
     * or a registered name.
     *
     * @see https://tools.ietf.org/html/rfc3986
     *
     * @var string  IP-literal / IPv4address / registered-name
     */
    protected $host;

    /**
     * Uri::$port
     *
     * @var int
     */
    protected $port = 80;

    /**
     * Uri::$username
     *
     * @var string
     */
    protected $username;

    /**
     * Uri::$password
     *
     * @var string
     */
    protected $password;

    /**
     * Uri::$path
     *
     * @var string
     */
    protected $path;

    /**
     * Uri::$query
     *
     * @var string
     */
    protected $query;

    /**
     * Uri::$fragment
     *
     * @var string
     */
    protected $fragment;

    /**
     * Uri::$attribute
     *
     * @var string
     */
    protected $attribute;

    // ------------------------------------------------------------------------

    /**
     * Uri::__construct
     *
     * @param string|null $httpStringRequest
     */
    public function __construct($httpStringRequest = null)
    {
        if (isset($httpStringRequest)) {
            $this->segments = new Segments('');
            $httpStringRequest = ltrim($httpStringRequest, '//');

            if (strpos($httpStringRequest, 'http://') === false) {
                if (strpos($httpStringRequest, 'https://') === false) {
                    $httpStringRequest = 'http://' . $httpStringRequest;
                }
            }

            $httpStringRequest = trim($httpStringRequest, '/');
            $parseUrl = parse_url($httpStringRequest);

            $this->host = isset($parseUrl[ 'host' ]) ? $parseUrl[ 'host' ] : null;

            if (isset($parseUrl[ 'path' ])) {
                $xRequest = explode('/', $parseUrl[ 'path' ]);
                $this->path = implode('/', array_slice($xRequest, 1));
            }

            if (strpos($this->path, '.php') !== false) {
                $xPath = explode('.php', $this->path);
                $xPath = explode('/', trim($xPath[ 0 ], '/'));
                array_pop($xPath);

                $this->path = empty($xPath) ? null : implode('/', $xPath);
            }

            $this->query = isset($parseUrl[ 'query' ]) ? $parseUrl[ 'query' ] : null;
            $this->username = isset($parseUrl[ 'user' ]) ? $parseUrl[ 'user' ] : null;
            $this->password = isset($parseUrl[ 'pass' ]) ? $parseUrl[ 'pass' ] : null;
            $this->port = isset($parseUrl[ 'port' ]) ? $parseUrl[ 'port' ] : 80;
            $this->fragment = isset($parseUrl[ 'fragment' ]) ? $parseUrl[ 'fragment' ] : null;
        } else {
            $this->domain = new Domain();
            $this->segments = new Segments();

            /**
             * Define Uri Attribute
             */
            if (strpos($_SERVER[ 'PHP_SELF' ], '/@') !== false) {
                $xPhpSelf = explode('/@', $_SERVER[ 'PHP_SELF' ]);

                $this->attribute = '@' . $xPhpSelf[ 1 ];

                if (strpos($this->attribute, '/') !== false) {
                    $xAttribute = explode('/', $this->attribute);

                    $this->attribute = $xAttribute[ 0 ];
                }
            }

            /**
             * Define Uri User and Password
             */
            if (preg_match("/[a-zA-Z0-9]+[@][a-zA-Z0-9]+/", $_SERVER[ 'PHP_SELF' ], $usernamePassword)) {
                $xUsernamePassword = explode('@', $usernamePassword[ 0 ]);
                $this->username = $xUsernamePassword[ 0 ];
                $this->password = $xUsernamePassword[ 1 ];
            }

            /**
             * Define Uri Path
             */
            $xPath = explode('.php', $_SERVER[ 'PHP_SELF' ]);
            $xPath = explode('/', trim($xPath[ 0 ], '/'));
            array_pop($xPath);

            $this->path = empty($xPath) ? null : implode('/', $xPath) . '/';

            $this->query = isset($_SERVER[ 'QUERY_STRING' ]) ? $_SERVER[ 'QUERY_STRING' ] : null;

        }
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getSegments
     *
     * @return Segments
     */
    public function &getSegments()
    {
        return $this->segments;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withSegments
     *
     * @param Segments $segments
     *
     * @return Uri
     */
    public function withSegments(Segments $segments)
    {
        $uri = clone $this;
        $uri->segments = $segments;

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::addSegments
     *
     * @param Segments|string|array $segments
     *
     * @return \O2System\Kernel\Http\Message\Uri
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    public function addSegments($segments)
    {
        if ( ! $segments instanceof Segments) {
            $segments = new Segments($segments);
        }

        $currentSegments = $this->segments->getArrayCopy();
        $addSegments = $segments->getArrayCopy();

        $uri = clone $this;
        $uri->segments = new Segments(array_merge($currentSegments, $addSegments));

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getScheme
     *
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->domain->getScheme();
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getAuthority
     *
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        if (empty($this->getHost())) {
            return null;
        }

        $authority = $this->getHost();

        if ( ! empty($this->getUserInfo())) {
            $authority = $this->getUserInfo() . '@' . $authority;
        }

        if ( ! empty($this->getPort())) {
            if ($this->getPort() != 80) {
                $authority .= ':' . $this->getPort();
            }
        }

        return $authority;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getUserInfo
     *
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        $userInfo = $this->username;

        if ( ! empty($this->password)) {
            $userInfo .= ':' . $this->password;
        }

        return $userInfo;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getHost
     *
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getPort
     *
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->domain->getPort();
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getPath
     *
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function &getPath()
    {
        return $this->path;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getQuery
     *
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getFragment
     *
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getSubDomain
     *
     * @param string $level
     *
     * @return bool|mixed
     */
    public function getSubDomain($level = 'AUTO')
    {
        if ($level === 'AUTO') {
            return $this->domain->getSubDomain();
        }

        return $this->domain->getSubDomain($level);
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::addSubDomain
     *
     * @param string|array|null $subDomain
     *
     * @return \O2System\Kernel\Http\Message\Uri
     */
    public function addSubDomain($subDomain)
    {
        $uri = clone $this;
        $uri->domain->addSubDomain($subDomain);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withSubDomain
     *
     * @param string|array|null $subDomain
     *
     * @return \O2System\Kernel\Http\Message\Uri
     */
    public function withSubDomain($subDomain)
    {
        $uri = clone $this;
        $uri->domain->setSubDomain($subDomain);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getSubDomains
     *
     * @return array
     */
    public function getSubDomains()
    {
        return $this->domain->getSubDomains();
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withSubDomains
     *
     * @param array $subDomains
     *
     * @return \O2System\Kernel\Http\Message\Uri
     */
    public function withSubDomains(array $subDomains)
    {
        $uri = clone $this;
        $uri->domain->setSubDomain($subDomains);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withScheme
     *
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid schemes.
     * @throws \InvalidArgumentException for unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $uri = clone $this;
        $uri->domain->setScheme($scheme);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withUserInfo
     *
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string      $user     The user name to use for authority.
     * @param null|string $password The password associated with $user.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = clone $this;
        $userInfo->username = $user;

        return $userInfo;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withHost
     *
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $uri = clone $this;
        $uri->domain->setHost($host);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withPort
     *
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        $uri = clone $this;
        $uri->port = $port;

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withPath
     *
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If an HTTP path is intended to be host-relative rather than path-relative
     * then it must begin with a slash ("/"). HTTP paths not starting with a slash
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $uri = clone $this;
        $uri->path = ltrim($path, '/');

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::addPath
     *
     * @param string $path
     *
     * @return \O2System\Kernel\Http\Message\Uri
     */
    public function addPath($path)
    {
        $uri = clone $this;
        $uri->path .= '/' . ltrim($path, '/');

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withQuery
     *
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string|array $query The query string to use with the new instance.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $uri = clone $this;
        $uri->query = is_array($query) ? http_build_query($query, PHP_QUERY_RFC3986, '&', PHP_QUERY_RFC3986) : $query;

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::addQuery
     *
     * @param array|string $query
     *
     * @return \O2System\Kernel\Http\Message\Uri
     */
    public function addQuery($query)
    {
        $uri = clone $this;
        $query = is_array($query) ? http_build_query($query, PHP_QUERY_RFC3986, '&', PHP_QUERY_RFC3986) : $query;

        parse_str($query, $newQuery);

        if ( ! empty($uri->query)) {
            parse_str($uri->query, $oldQuery);
            $query = array_merge($oldQuery, $newQuery);
        } else {
            $query = $newQuery;
        }

        if (is_array($query)) {
            $uri->query = is_array($query) ? http_build_query($query, PHP_QUERY_RFC3986, '&', PHP_QUERY_RFC3986) : $query;
        }

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withFragment
     *
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     *
     * @return static|\O2System\Kernel\Http\Message\Uri A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        $uri = clone $this;
        $uri->fragment = $fragment;

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::getSuffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::setSuffix
     *
     * @param string $suffix
     */
    protected function setSuffix($suffix)
    {
        if (is_null($suffix) or is_bool($suffix)) {
            $this->suffix = null;
        } elseif ($suffix === '/') {
            $this->suffix = $suffix;
        } else {
            $this->suffix = '.' . trim($suffix, '.');
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::withSuffix
     *
     * @param string $suffix
     *
     * @return \O2System\Kernel\Http\Message\Uri
     */
    public function withSuffix($suffix)
    {
        $uri = clone $this;
        $uri->setSuffix($suffix);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Uri::__toString
     *
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        $uriString = $this->getScheme() . '://' . $this->domain->getHost();        

        $uriPath = empty($this->path)
            ? '/'
            : '/' . trim($this->path, '/') . '/';

        $uriPath .= empty($this->string)
            ? ''
            : '/' . trim($this->string, '/') . '/';

        $uriPath .= $this->segments->count() == 0
            ? ''
            : '/' . trim($this->segments->__toString(), '/');

        if ($uriPath !== '/' &&
            substr($uriPath, strlen($uriPath) - 1) !== '/' &&
            $this->suffix !== '' && $this->suffix !== '.' &&
            ($uriPath . '/' !== $_SERVER[ 'REQUEST_URI' ]) &&
            pathinfo($uriPath, PATHINFO_EXTENSION) === '' &&
            strpos($uriPath, '#') === false &&
            empty($this->query)
        ) {
            $uriPath .= $this->suffix;
        } elseif (pathinfo($uriPath, PATHINFO_EXTENSION) === '') {
            $uriPath .= '/';
        }

        $uriPath = rtrim($uriPath, '/');

        $uriString .= str_replace('//', '/', $uriPath);
        $uriString .= empty($this->query)
            ? ''
            : '?' . $this->query;
        $uriString .= empty($this->fragment)
            ? ''
            : $this->fragment;

        return $uriString;
    }
}
