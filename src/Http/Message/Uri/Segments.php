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

use O2System\Spl\DataStructures\SplArrayObject;
use O2System\Spl\Exceptions\RuntimeException;
use O2System\Spl\Iterators\ArrayIterator;

/**
 * Class Segments
 *
 * @package O2System\Kernel\Http\Message\Uri
 */
class Segments extends ArrayIterator
{
    /**
     * Segments::__construct
     *
     * @param string|null $points
     *
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    public function __construct($points = null)
    {
        parent::__construct([]);

        if (is_null($points)) {
            if (kernel()->services->has('config')) {
                if (config()->offsetExists('uri')) {
                    $protocol = strtoupper(config('uri')->offsetGet('protocol'));
                }
            }

            empty($protocol) && $protocol = 'REQUEST_URI';

            switch ($protocol) {
                case 'AUTO':
                case 'REQUEST_URI':
                    $points = $this->parseRequestUri();
                    break;
                case 'QUERY_STRING':
                    $points = $this->parseQueryString();
                    break;
                case 'PATH_INFO':
                default:
                    $points = isset($_SERVER[ $protocol ])
                        ? $_SERVER[ $protocol ]
                        : $this->parseRequestUri();
                    break;
            }

        } elseif (is_array($points)) {
            $points = implode('/', $points);
        }
        
        $points = str_replace(['\\', '_'], ['/', '-'], $points);
        $points = trim(remove_invisible_characters($points, false), '/');

        $this->setPoints(explode('/', $points));
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::parseRequestUri
     *
     * Parse REQUEST_URI
     *
     * Will parse REQUEST_URI and automatically detect the URI from it,
     * while fixing the query string if necessary.
     *
     * @access  protected
     * @return  string
     */
    protected function parseRequestUri()
    {
        if ( ! isset($_SERVER[ 'REQUEST_URI' ], $_SERVER[ 'SCRIPT_NAME' ])) {
            return '';
        }

        $uri = parse_url($_SERVER[ 'REQUEST_URI' ]);
        $query = isset($uri[ 'query' ])
            ? $uri[ 'query' ]
            : '';
        $uri = isset($uri[ 'path' ])
            ? $uri[ 'path' ]
            : '';
        
        if(pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
            if (isset($_SERVER[ 'SCRIPT_NAME' ][ 0 ])) {
                if (strpos($uri, $_SERVER[ 'SCRIPT_NAME' ]) === 0) {
                    $uri = (string)substr($uri, strlen($_SERVER[ 'SCRIPT_NAME' ]));
                } elseif (strpos($uri, dirname($_SERVER[ 'SCRIPT_NAME' ])) === 0) {
                    $uri = (string)substr($uri, strlen(dirname($_SERVER[ 'SCRIPT_NAME' ])));
                }
            }
        }

        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (trim($uri, '/') === '' AND strncmp($query, '/', 1) === 0) {
            $query = explode('?', $query, 2);
            $uri = $query[ 0 ];

            $_SERVER[ 'QUERY_STRING' ] = isset($query[ 1 ])
                ? $query[ 1 ]
                : '';
        } else {
            $_SERVER[ 'QUERY_STRING' ] = $query;
        }

        if (isset($_GET[ 'SEGMENTS_STRING' ])) {
            $uri = $_GET[ 'SEGMENTS_STRING' ];
            unset($_GET[ 'SEGMENTS_STRING' ]);

            $_SERVER[ 'QUERY_STRING' ] = str_replace([
                'SEGMENTS_STRING=' . $uri . '&',
                'SEGMENTS_STRING=' . $uri,
            ], '', $_SERVER[ 'QUERY_STRING' ]);
        }

        parse_str($_SERVER[ 'QUERY_STRING' ], $_GET);

        if ($uri === '/' || $uri === '') {
            return '/';
        }

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::parseQueryString
     *
     * Parse QUERY_STRING
     *
     * Will parse QUERY_STRING and automatically detect the URI from it.
     *
     * @access  protected
     * @return  string
     */
    protected function parseQueryString()
    {
        $uri = isset($_SERVER[ 'QUERY_STRING' ])
            ? $_SERVER[ 'QUERY_STRING' ]
            : @getenv('QUERY_STRING');

        if (trim($uri, '/') === '') {
            return '';
        } elseif (strncmp($uri, '/', 1) === 0) {
            $uri = explode('?', $uri, 2);
            $_SERVER[ 'QUERY_STRING' ] = isset($uri[ 1 ])
                ? $uri[ 1 ]
                : '';
            $uri = rawurldecode($uri[ 0 ]);
        }

        parse_str($_SERVER[ 'QUERY_STRING' ], $_GET);

        return $uri;
    }

    // --------------------------------------------------------------------

    /**
     * Segments::addString
     *
     * @param string $string
     *
     * @return \O2System\Kernel\Http\Message\Uri\Segments
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    public function addString($string)
    {
        $string = $this->__toString() . '/' . trim($string, '/');

        return $this->withString($string);
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::withString
     *
     * @param string $string
     *
     * @return \O2System\Kernel\Http\Message\Uri\Segments
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    public function withString($string)
    {
        $string = trim(remove_invisible_characters($string, false), '/');

        return $this->withPoints(explode('/', $string));
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::withPoints
     *
     * @param array $points
     *
     * @return \O2System\Kernel\Http\Message\Uri\Segments
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    public function withPoints(array $points)
    {
        $uri = clone $this;
        $uri->setPoints($points);

        return $uri;
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::addPoints
     *
     * @param array $points
     *
     * @return \O2System\Kernel\Http\Message\Uri\Segments
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    public function addPoints(array $points)
    {
        return $this->withPoints($points);
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::getPoint
     *
     * Get Segment
     *
     * @param int $index (n) of Uri Segments
     *
     * @return mixed
     */
    public function getPoint($index)
    {
        if($this->offsetExists($index)) {
            return $this->offsetGet($index);
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::setPoints
     *
     * @param array $points
     *
     * @throws \O2System\Spl\Exceptions\RuntimeException
     */
    protected function setPoints(array $points)
    {
        if (count($points)) {
            $validPoints = [];

            if (count($points)) {
                foreach ($points as $part) {
                    // Filter segments for security
                    if ($part = trim($this->filterPoint($part))) {
                        if (class_exists('O2System\Framework', false)) {
                            if (false !== ($language = language()->hasOption($part))) {
                                language()->setDefault($part);

                                continue;
                            }
                        }

                        if ( ! in_array($part, $validPoints)) {
                            $validPoints[] = $part;
                        }
                    }
                }
            }

            $validPoints = array_filter($validPoints);
            array_unshift($validPoints, null);

            unset($validPoints[ 0 ]);

            $this->merge($validPoints);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::__toString
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->count()) {
            return implode('/', $this->getArrayCopy());
        }

        return '';
    }

    // ------------------------------------------------------------------------

    /**
     * Segments::filterPoint
     *
     * Filters segments for malicious characters.
     *
     * @param string $string URI String
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected function filterPoint($string)
    {
        if (function_exists('config')) {
            $config = config('uri');
        }

        if (empty($config)) {
            $config = new SplArrayObject([
                'permittedChars' => 'a-z 0-9~%.:_\-@#',
                'suffix'         => null,
            ]);
        }

        if ( ! empty($string) AND
            ! empty($config->offsetGet('permittedChars')) AND
            ! preg_match('/^[' . $config->offsetGet('permittedChars') . ']+$/i', $string) AND
            ! is_cli()
        ) {
            throw new RuntimeException('E_URI_HAS_DISALLOWED_CHARACTERS', 105);
        }

        $regex = ['$', '(', ')', '%28', '%29', 'index', '.php', '.phtml']; // Bad
        $replace = ['&#36;', '&#40;', '&#41;', '&#40;', '&#41;']; // Good

        if ( ! empty($config)) {
            array_push($regex, $config->offsetGet('suffix'));
            array_push($replace, '');
        }

        // Convert programatic characters to entities and return
        return str_replace($regex, $replace, $string);
    }
}