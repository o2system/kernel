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

namespace O2System\Kernel\Http\Message\Uri;

// ------------------------------------------------------------------------

/**
 * Class Domain
 *
 * @package O2System\Kernel\Http\Message
 */
class Domain
{
    /**
     * Domain::$string
     *
     * @var string
     */
    protected $string;

    /**
     * Domain::$origin
     *
     * @var string
     */
    protected $origin;

    /**
     * Domain::$scheme
     *
     * @var string
     */
    protected $scheme = 'http';

    /**
     * Domain::$www
     *
     * @var bool
     */
    protected $www = false;

    /**
     * Domain::$port
     *
     * @var int
     */
    protected $port = 80;

    /**
     * Domain::$parentDomain
     *
     * @var string|null
     */
    protected $parentDomain = null;

    /**
     * Domain::$subDomains
     *
     * @var array
     */
    protected $subDomains = [];

    /**
     * Domain::$tlds
     *
     * @var array
     */
    protected $tlds = [];

    /**
     * Domain::$path
     *
     * @var string
     */
    protected $path;

    // ------------------------------------------------------------------------

    /**
     * Domain::__construct
     *
     * @param string|null $string
     */
    public function __construct($string = null)
    {
        $this->origin = isset($_SERVER[ 'HTTP_HOST' ])
            ? $_SERVER[ 'HTTP_HOST' ]
            : $_SERVER[ 'SERVER_NAME' ];
        $this->scheme = is_https()
            ? 'https'
            : 'http';

        $paths = explode('.php', $_SERVER[ 'PHP_SELF' ]);
        $paths = explode('/', trim($paths[ 0 ], '/'));
        array_pop($paths);

        $this->path = empty($paths)
            ? null
            : implode('/', $paths);

        if (isset($string)) {
            $this->string = trim($string, '/');
            $metadata = parse_url($string);
            $metadata[ 'path' ] = empty($metadata[ 'path' ])
                ? null
                : $metadata[ 'path' ];

            $this->scheme = empty($metadata[ 'scheme' ])
                ? $this->scheme
                : $metadata[ 'scheme' ];

            if ($metadata[ 'path' ] === $this->string) {
                $paths = explode('/', $this->string);
                $this->origin = $paths[ 0 ];

                $this->path = implode('/', array_slice($paths, 1));
            } elseif (isset($metadata[ 'host' ])) {
                $this->path = trim($metadata[ 'path' ]);
                $this->origin = $metadata[ 'host' ];
            }
        }

        $directories = explode('/', str_replace('\\', '/', dirname($_SERVER[ 'SCRIPT_FILENAME' ])));
        $paths = explode('/', $this->path);
        $paths = array_intersect($paths, $directories);

        $this->path = '/' . trim(implode('/', $paths), '/');

        if (strpos($this->origin, 'www') !== false) {
            $this->www = true;
            $this->origin = ltrim($this->origin, 'www.');
        }

        if (preg_match('/(:)([0-9]+)/', $this->string, $matches)) {
            $this->port = $matches[ 2 ];
        }

        if (filter_var($this->origin, FILTER_VALIDATE_IP) !== false) {
            $tlds = [$this->origin];
        } else {
            $tlds = explode('.', $this->origin);
        }

        if (count($tlds) > 1) {
            foreach ($tlds as $key => $tld) {
                if (strlen($tld) <= 3 AND $key >= 1) {
                    $this->tlds[] = $tld;
                }
            }

            if (empty($this->tlds)) {
                $this->tlds[] = end($tlds);
            }

            $this->tld = '.' . implode('.', $this->tlds);

            $this->subDomains = array_diff($tlds, $this->tlds);
            $this->subDomains = count($this->subDomains) == 0
                ? $this->tlds
                : $this->subDomains;

            $this->parentDomain = end($this->subDomains);
            array_pop($this->subDomains);

            $this->parentDomain = implode('.', array_slice($this->subDomains, 1))
                . '.'
                . $this->parentDomain
                . $this->tld;
            $this->parentDomain = ltrim($this->parentDomain, '.');

            if (count($this->subDomains) > 0) {
                $this->subDomain = reset($this->subDomains);
            }
        } else {
            $this->parentDomain = $this->origin;
        }

        $ordinalEnds = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        foreach ($this->subDomains as $key => $subdomain) {
            $ordinalNumber = count($tlds) - $key;

            if ((($ordinalNumber % 100) >= 11) && (($ordinalNumber % 100) <= 13)) {
                $ordinalKey = $ordinalNumber . 'th';
            } else {
                $ordinalKey = $ordinalNumber . $ordinalEnds[ $ordinalNumber % 10 ];
            }

            $this->subDomains[ $ordinalKey ] = $subdomain;

            unset($this->subDomains[ $key ]);
        }

        foreach ($this->tlds as $key => $tld) {
            $ordinalNumber = count($this->tlds) - $key;

            if ((($ordinalNumber % 100) >= 11) && (($ordinalNumber % 100) <= 13)) {
                $ordinalKey = $ordinalNumber . 'th';
            } else {
                $ordinalKey = $ordinalNumber . $ordinalEnds[ $ordinalNumber % 10 ];
            }

            $this->tlds[ $ordinalKey ] = $tld;

            unset($this->tlds[ $key ]);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getString
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getOrigin
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getScheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::isWWWW
     *
     * @return bool
     */
    public function isWWW()
    {
        return $this->www;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getIpAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return gethostbyname($this->origin);
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getPort
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getParentDomain
     *
     * @return string|null
     */
    public function getParentDomain()
    {
        return $this->parentDomain;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getSubDomain
     *
     * @param string $level
     *
     * @return bool|mixed
     */
    public function getSubDomain($level = '3rd')
    {
        if (isset($this->subDomains[ $level ])) {
            return $this->subDomains[ $level ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getSubDomains
     *
     * @return array
     */
    public function getSubDomains()
    {
        return $this->subDomains;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getTotalSubDOmains
     *
     * @return int
     */
    public function getTotalSubDomains()
    {
        return count($this->subDomains);
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getTld
     *
     * @param string|null $level
     *
     * @return bool|mixed|string
     */
    public function getTld($level = null)
    {
        if (is_null($level)) {
            return implode('.', $this->tlds);
        } elseif (isset($this->tlds[ $level ])) {
            return $this->tlds[ $level ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getTlds
     *
     * @return array
     */
    public function getTlds()
    {
        return $this->tlds;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getTotalTlds
     *
     * @return int
     */
    public function getTotalTlds()
    {
        return count($this->tlds);
    }
}