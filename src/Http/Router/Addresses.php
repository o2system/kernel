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

namespace O2System\Kernel\Http\Router;

// ------------------------------------------------------------------------

use O2System\Kernel\Http\Message\Uri\Domain;

/**
 * Class Addresses
 *
 * @package O2System\Kernel\Http\Router
 */
class Addresses
{
    /**
     * Addresses::HTTP_GET
     *
     * The GET method is used to retrieve information from the given server using a given URI.
     * Requests using GET should only retrieve data and should have no other effect on the data.
     *
     * @var string
     */
    const HTTP_GET = 'GET';

    /**
     * Addresses::HTTP_HEAD
     *
     * The HEAD method is used to retrieve only the status line and header section information from the given server
     * using a given URI. Requests using HEAD should only retrieve data and should have no other effect on the data.
     *
     * @var string
     */
    const HTTP_HEAD = 'HEAD';

    /**
     * Addresses::HTTP_POST
     *
     * The POST method is used to send data to the server.
     *
     * @var string
     */
    const HTTP_POST = 'POST';

    /**
     * Addresses::HTTP_PUT
     *
     * The PUT method is used to replaces all current representations of the target resource with the
     * uploaded content.
     *
     * @var string
     */
    const HTTP_PUT = 'PUT';

    /**
     * Addresses::HTTP_DELETE
     *
     * Removes all current representations of the target resource given by a URI.
     *
     * @var string
     */
    const HTTP_DELETE = 'DELETE';

    /**
     * Addresses::HTTP_CONNECT
     *
     * Establishes a tunnel to the server identified by a given URI.
     *
     * @var string
     */
    const HTTP_CONNECT = 'CONNECT';

    /**
     * Addresses::HTTP_OPTIONS
     *
     * Describes the communication options for the target resource.
     *
     * @var string
     */
    const HTTP_OPTIONS = 'OPTIONS';

    /**
     * Addresses::HTTP_TRACE
     *
     * Performs a message loop-back test along the path to the target resource.
     *
     * @var string
     */
    const HTTP_TRACE = 'TRACE';

    /**
     * Addresses::HTTP_ANY
     *
     * Any http type.
     *
     * @var string
     */
    const HTTP_ANY = 'ANY';

    /**
     * Addresses::$subDomains
     *
     * @var array
     */
    protected $domains = [];

    /**
     * Addresses::$translations
     *
     * @var array
     */
    protected $translations = [];

    /**
     * Addresses::$attributes
     *
     * @var array
     */
    protected $attributes = [];

    // ------------------------------------------------------------------------

    /**
     * @param      $path
     * @param null $domain
     *
     * @return bool|\O2System\Kernel\Http\Router\DataStructures\Action
     */
    public function getTranslation($path, $domain = null)
    {
        $path = '/' . ltrim($path, '/');
        $translations = $this->getTranslations($domain);

        if (isset($translations[ $path ])) {
            return $translations[ $path ];
        } elseif (count($translations)) {
            foreach ($translations as $translation => $action) {
                if ($action->isValidUriString($path)) {
                    return $action;
                    break;
                }
            }
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::getTranslations
     *
     * Gets array of domain routes translations.
     *
     * @param string $domain The domain url.
     *
     * @return array Returns array of domain routes maps.
     */
    public function getTranslations($domain = null)
    {
        $hostDomain = new Domain();

        if (is_null($domain)) {
            $domain = $hostDomain->getHost();
        }

        $translations = [];

        if (isset($this->translations[ $domain ])) {
            $translations = $this->translations[ $domain ];
        } else {
            $domain = new Domain($domain);
            if (array_key_exists($domain->getHost(), $this->translations)) {
                $translations = $this->translations[ $domain->getHost() ];
            } else {
                foreach ($this->translations as $domainRoute => $domainMap) {
                    if (preg_match('/[{][a-zA-Z0-9$_]+[}]/', $domainRoute)) {
                        $domainRoute = new Domain($domainRoute);

                        if ($domain->getMainDomain() === $domainRoute->getMainDomain() AND
                            $domain->getNumOfSubDomains() == $domainRoute->getNumOfSubDomains()
                        ) {
                            if (isset($domainMap[ $domainRoute->getSubDomain() ])) {
                                $translations = $domainMap;
                                $address = $translations[ $domainRoute->getSubDomain() ]->setClosureParameters(
                                    $domain->getSubDomains()
                                );

                                unset($translations[ $domainRoute->getSubDomain() ]);

                                if (false !== ($closureParameters = $address->getClosure())) {

                                    $closureParameters = ! is_array($closureParameters)
                                        ? [$closureParameters]
                                        : $closureParameters;

                                    foreach ($translations as $address) {
                                        $address->setClosureParameters((array)$closureParameters);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $translations;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::any
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function any($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_ANY);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::addTranslation
     *
     * @param string  $path
     * @param string  $address
     * @param string  $method
     *
     * @return static
     */
    public function addTranslation($path, $address, $method = self::HTTP_GET)
    {
        $path = '/' . ltrim($path, '/');

        if ($address instanceof \Closure) {
            $closure = $address;
        } else {

            if (is_string($address)) {
                $namespace = isset($this->attributes[ 'namespace' ])
                    ? $this->attributes[ 'namespace' ]
                    : null;
                $controllerClassName = trim($namespace, '\\') . '\\' . $address;

                if (class_exists($controllerClassName)) {
                    $address = $controllerClassName;
                }
            }

            $closure = function () use ($address) {
                return $address;
            };
        }

        $domain = isset($this->attributes[ 'domain' ])
            ? $this->attributes[ 'domain' ]
            : null;

        $prefix = isset($this->attributes[ 'prefix' ])
            ? $this->attributes[ 'prefix' ]
            : null;

        if ( ! preg_match('/[{][a-zA-Z0-9$_]+[}]/', $path)) {
            $path = '/' . trim(trim($prefix, '/') . '/' . trim($path, '/'), '/');
        }

        $action = new DataStructures\Action($method, $path, $closure, $domain);

        $this->translations[ $action->getDomain() ][ $action->getPath() ] = $action;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::middleware
     *
     * @param array $middleware
     * @param bool  $register
     *
     * @return static
     */
    public function middleware(array $middleware, $register = true)
    {
        foreach ($middleware as $offset => $object) {
            $offset = is_numeric($offset) ? $object : $offset;

            if ($register) {
                middleware()->register($object, $offset);
            } else {
                middleware()->unregister($offset);
            }
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::pool
     *
     * @param          $attributes
     * @param \Closure $closure
     */
    public function pool($attributes, \Closure $closure)
    {
        $parentAttributes = $this->attributes;
        $this->attributes = $attributes;

        call_user_func($closure, $this);

        $this->attributes = $parentAttributes;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::domains
     *
     * @param array $domains
     */
    public function domains(array $domains)
    {
        foreach ($domains as $domain => $address) {
            $this->domain($domain, $address);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::domain
     *
     * @param string $domain
     * @param string $address
     */
    public function domain($domain, $address)
    {
        if ($domain !== '*') {
            $hostDomain = new Domain();
            $domainParts = explode('.', $domain);

            if (count($domainParts) == 1) {
                $domain = $domain . '.' . $hostDomain->getMainDomain();
            } else {
                $domain = str_replace('.' . $hostDomain->getMainDomain(), '',
                        $domain) . '.' . $hostDomain->getMainDomain();
            }
        }

        $this->domains[ $domain ] = $address;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::getDomain
     *
     * @param string|null $domain
     *
     * @return int|mixed|string|null
     */
    public function getDomain($domain = null)
    {
        $domain = is_null($domain)
            ? isset($_SERVER[ 'HTTP_HOST' ])
                ? $_SERVER[ 'HTTP_HOST' ]
                : $_SERVER[ 'SERVER_NAME' ]
            : $domain;

        if (array_key_exists($domain, $this->domains)) {
            if (is_callable($this->domains[ $domain ])) {
                return call_user_func($this->domains[ $domain ]);
            }

            return $this->domains[ $domain ];
        } elseif (isset($this->domains[ '*' ]) and is_callable($this->domains[ '*' ])) {
            // check wildcard domain closure
            if (false !== ($address = call_user_func($this->domains[ '*' ], $domain))) {
                return $address;
            }
        } elseif (count($this->domains)) {
            // check pregmatch domain closure
            foreach ($this->domains as $address => $closure) {
                if (preg_match('/[{][a-zA-Z0-9$_]+[}]/', $address) and $closure instanceof \Closure) {
                    $addressDomain = new Domain($address);
                    $checkDomain = new Domain($domain);
                    $parameters = [];

                    if ($addressDomain->getNumOfSubDomains() === $checkDomain->getNumOfSubDomains()) {
                        foreach ($addressDomain->getSubDomains() as $level => $name) {
                            if (false !== ($checkDomainName = $checkDomain->getSubDomain($level))) {
                                $parameters[] = $checkDomainName;
                            }
                        }

                        if (false !== ($address = call_user_func_array($closure, $parameters))) {
                            return $address;
                            break;
                        }
                    }
                }
            }
        }

        return null;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::get
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function get($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_GET);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::post
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function post($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_POST);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::put
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function put($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_PUT);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::connect
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function connect($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_CONNECT);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::delete
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function delete($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_DELETE);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::delete
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function head($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_HEAD);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::options
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function options($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_OPTIONS);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Addresses::trace
     *
     * @param string $path    The URI string path.
     * @param mixed  $address The routing map of the URI:
     *                        [string]: string of controller name.
     *                        [array]: array of URI segment.
     *                        [\Closure]: the closure map of URI.
     *
     * @return static
     */
    public function trace($path, $address)
    {
        $this->addTranslation($path, $address, self::HTTP_TRACE);

        return $this;
    }
}
