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
     * Domain::$host
     *
     * @var string
     */
    protected $host;

    /**
     * Domain::$scheme
     *
     * @var string
     */
    protected $scheme;

    /**
     * Domain::$www
     *
     * @var bool
     */
    protected $www = false;

    /**
     * Domain::$ipv4
     *
     * @var string
     */
    protected $ipv4 = null;

    /**
     * Domain::$port
     *
     * @var int
     */
    protected $port = 80;

    /**
     * Domain::$mainDomain
     *
     * @var string|null
     */
    protected $mainDomain = null;

    /**
     * Domain::$subDomains
     *
     * @var array
     */
    protected $subDomains = [];

    /**
     * Domain::$subDomain
     *
     * @var string
     */
    protected $subDomain = null;

    /**
     * Domain::$tlds
     *
     * @var array
     */
    protected $tlds = [];

    /**
     * Domain::$tld
     *
     * @var string
     */
    protected $tld = null;

    // ------------------------------------------------------------------------

    /**
     * Domain::__construct
     *
     * @param string|null $string
     */
    public function __construct($host = null)
    {
        if (isset($host)) {
            $this->host = parse_url($host, PHP_URL_HOST);
            $this->scheme = parse_url($host, PHP_URL_SCHEME);
            $this->ipv4 = gethostbyname($this->host);
            $this->port = parse_url($host, PHP_URL_PORT);
        } else {
            $this->host = isset($_SERVER[ 'HTTP_HOST' ])
                ? $_SERVER[ 'HTTP_HOST' ]
                : $_SERVER[ 'SERVER_NAME' ];

            $this->scheme = is_https() ? 'https' : 'http';
            if (preg_match('/(:)([0-9]+)/', $this->host, $matches)) {
                $this->port = $matches[ 2 ];
            } else {
                $this->port = is_https() ? 443 : 80;
            }
        }

        $this->ipv4 = gethostbyname($this->host);

        if (strpos($this->host, 'www') !== false) {
            $this->www = true;
            $this->host = ltrim($this->host, 'www.');
        }

        if (filter_var($this->host, FILTER_VALIDATE_IP) !== false) {
            $tlds = [$this->host];
        } else {
            $tlds = explode('.', $this->host);
        }

        $tldsDatabase = require(PATH_KERNEL . 'Config' . DIRECTORY_SEPARATOR . 'Tlds.php');

        if (($numTlds = count($tlds)) > 1) {
            $possibleTlds[] = implode('.', array_slice($tlds, $numTlds - 2, 2));
            $possibleTlds[] = end($tlds);

            foreach($possibleTlds as $possibleTld) {
                if(in_array($possibleTld, $tldsDatabase) or in_array($possibleTld, ['local', 'test', end($tlds)])) {
                    $this->setSubDomain(array_diff($tlds, $this->tlds = explode('.', $possibleTld)));
                    $this->setTld($possibleTld);
                    break;
                }
            }

            $this->mainDomain = end($this->subDomains);
            array_pop($this->subDomains);

            $this->mainDomain = implode('.', array_slice($this->subDomains, 1))
                . '.'
                . $this->mainDomain
                . $this->tld;
            $this->mainDomain = ltrim($this->mainDomain, '.');

            if (count($this->subDomains) > 0) {
                $this->subDomain = reset($this->subDomains);
            }
        } else {
            $this->mainDomain = $this->host;
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getHost
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::setHost
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = parse_url($host, PHP_URL_HOST);
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
     * Domain::setScheme
     *
     * @param string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
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
        return gethostbyname($this->host);
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
     * Domain::setPort
     *
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = intval($port);
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getMainDomain
     *
     * @return string|null
     */
    public function getMainDomain()
    {
        return $this->mainDomain;
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::setMainDomain
     *
     * @param $mainDomain
     */
    public function setMainDomain($mainDomain)
    {
        $this->mainDomain = $mainDomain;
        if( ! empty($this->subDomain)) {
            $domain = new Domain($this->mainDomain);
        } else {
            $domain = new Domain($this->subDomain . '.' . $this->mainDomain);
        }

        $this->host = $domain->getHost();
        $this->ipv4 = $domain->getIpAddress();
        $this->subDomain = $domain->getSubDomain();
        $this->subDomains= $domain->getSubDomains();
        $this->tld = $domain->getTld();
        $this->tlds = $domain->getTlds();
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getSubDomain
     *
     * @param string $level
     *
     * @return bool|mixed
     */
    public function getSubDomain($level = null)
    {
        if (isset($level)) {
            if(isset($this->subDomains[$level])) {
                return $this->subDomains[ $level ];
            }
        } elseif(count($this->subDomains)) {
            return reset($this->subDomains);
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
     * Domain::setSubDomain
     *
     * @param string|array $subDomains
     */
    public function setSubDomain($subDomains)
    {
        if(is_string($subDomains)) {
            $subDomains = explode('.', $subDomain);
        }

        $this->subDomains = [];
        foreach($subDomains as $subDomain) {
            $this->addSubDomain($subDomain);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::addSubDomain
     *
     * @param string $subDomain
     */
    public function addSubDomain($subDomain)
    {
        $subDomains = array_values($this->subDomains);
        array_push($subDomains, $subDomain);

        $ordinalEnds = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        $this->subDomains = [];
        foreach ($subDomains as $key => $subdomain) {
            $ordinalNumber = count($subDomains) - $key;

            if ((($ordinalNumber % 100) >= 11) && (($ordinalNumber % 100) <= 13)) {
                $ordinalKey = $ordinalNumber . 'th';
            } else {
                $ordinalKey = $ordinalNumber . $ordinalEnds[ $ordinalNumber % 10 ];
            }

            $this->subDomains[ $ordinalKey ] = $subdomain;
        }

        $this->subDomain = reset($this->subDomains);
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getNumOfSubDomains
     *
     * @return int
     */
    public function getNumOfSubDomains()
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
     * Domain::setTld
     *
     * @param string|array $tlds
     */
    public function setTld($tlds)
    {
        if(is_string($tlds)) {
            $tlds = explode('.', $tlds);
        }

        $this->tlds = [];
        foreach($tlds as $tld) {
            $this->addTld($tld);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::addTld
     *
     * @param string $tld
     */
    public function addTld($tld)
    {
        $tlds = array_values($this->tlds);
        array_push($tlds, $tld);

        $ordinalEnds = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        $this->tlds = [];
        foreach ($tlds as $key => $tld) {
            $ordinalNumber = count($tlds) - $key;

            if ((($ordinalNumber % 100) >= 11) && (($ordinalNumber % 100) <= 13)) {
                $ordinalKey = $ordinalNumber . 'th';
            } else {
                $ordinalKey = $ordinalNumber . $ordinalEnds[ $ordinalNumber % 10 ];
            }

            $this->tlds[ $ordinalKey ] = $tld;
        }

        $this->tld = '.' . implode('.', $this->tlds);
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::getNumOfTlds
     *
     * @return int
     */
    public function getNumOfTlds()
    {
        return count($this->tlds);
    }

    // ------------------------------------------------------------------------

    /**
     * Domain::__toString
     */
    public function __toString()
    {
        return $this->host;
    }
}
