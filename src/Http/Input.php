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

namespace O2System\Kernel\Http;

// ------------------------------------------------------------------------

use O2System\Psr\Http\Message\UploadedFileInterface;
use O2System\Spl\DataStructures\SplArrayObject;

/**
 * Class Input
 *
 * Http Kernel Input data with optional filter functionality, all data as it has arrived to the
 * Kernel Input from the CGI and/or PHP environment, including:
 *
 * - The values represented in $_SERVER, $_ENV, $_REQUEST and $_SESSION.
 * - Any cookies provided (generally via $_COOKIE)
 * - Query string arguments (generally via $_GET, or as parsed via parse_str())
 * - Uploader files, if any (as represented by $_FILES)
 * - Deserialized body binds (generally from $_POST)
 *
 * @package O2System\Kernel\Http
 */
class Input
{
    /**
     * Input::__construct
     */
    public function __construct()
    {
        // Turn register_globals off.
        if ( ! ini_get('register_globals')) {
            return;
        }

        if (isset($_REQUEST[ 'GLOBALS' ])) {
            die('GLOBALS overwrite attempt detected');
        }

        // Variables that shouldn't be unset
        $no_unset = ['GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix'];

        $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES,
            isset($_SESSION) && is_array($_SESSION) ? $_SESSION : []);
        foreach ($input as $k => $v) {
            if ( ! in_array($k, $no_unset) && isset($GLOBALS[ $k ])) {
                unset($GLOBALS[ $k ]);
            }
        }

        // Standardize $_SERVER variables across setups.
        $default_server_values = [
            'SERVER_SOFTWARE' => '',
            'REQUEST_URI'     => '',
        ];

        $_SERVER = array_merge($default_server_values, $_SERVER);

        // Fix for IIS when running with PHP ISAPI
        if (empty($_SERVER[ 'REQUEST_URI' ]) || (PHP_SAPI != 'cgi-fcgi' && preg_match('/^Microsoft-IIS\//',
                    $_SERVER[ 'SERVER_SOFTWARE' ]))) {

            if (isset($_SERVER[ 'HTTP_X_ORIGINAL_URL' ])) {
                // IIS Mod-Rewrite
                $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_ORIGINAL_URL' ];
            } elseif (isset($_SERVER[ 'HTTP_X_REWRITE_URL' ])) {
                // IIS Isapi_Rewrite
                $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_REWRITE_URL' ];
            } else {
                // Use ORIG_PATH_INFO if there is no PATH_INFO
                if ( ! isset($_SERVER[ 'PATH_INFO' ]) && isset($_SERVER[ 'ORIG_PATH_INFO' ])) {
                    $_SERVER[ 'PATH_INFO' ] = $_SERVER[ 'ORIG_PATH_INFO' ];
                }

                // Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
                if (isset($_SERVER[ 'PATH_INFO' ])) {
                    if ($_SERVER[ 'PATH_INFO' ] == $_SERVER[ 'SCRIPT_NAME' ]) {
                        $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'PATH_INFO' ];
                    } else {
                        $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'SCRIPT_NAME' ] . $_SERVER[ 'PATH_INFO' ];
                    }
                }

                // Append the query string if it exists and isn't null
                if ( ! empty($_SERVER[ 'QUERY_STRING' ])) {
                    $_SERVER[ 'REQUEST_URI' ] .= '?' . $_SERVER[ 'QUERY_STRING' ];
                }
            }
        }

        // Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
        if (isset($_SERVER[ 'SCRIPT_FILENAME' ]) && (strpos($_SERVER[ 'SCRIPT_FILENAME' ],
                    'php.cgi') == strlen($_SERVER[ 'SCRIPT_FILENAME' ]) - 7)) {
            $_SERVER[ 'SCRIPT_FILENAME' ] = $_SERVER[ 'PATH_TRANSLATED' ];
        }

        // Fix for Dreamhost and other PHP as CGI hosts
        if (strpos($_SERVER[ 'SCRIPT_NAME' ], 'php.cgi') !== false) {
            unset($_SERVER[ 'PATH_INFO' ]);
        }

        // Fix empty PHP_SELF
        if (empty($PHP_SELF)) {
            $_SERVER[ 'PHP_SELF' ] = $PHP_SELF = preg_replace('/(\?.*)?$/', '', $_SERVER[ 'REQUEST_URI' ]);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Input::getPost
     *
     * Fetch input from GET data with fallback to POST.
     *
     * @param string|null $offset The offset of $_GET or $_POST variable to fetch.
     *                            When set null will returns filtered $_GET or $_POST variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function getPost($offset, $filter = null)
    {
        // Use $_GET directly here, since filter_has_var only
        // checks the initial GET data, not anything that might
        // have been added since.
        return isset($_GET[ $offset ])
            ? $this->get($offset, $filter)
            : $this->post($offset, $filter);
    }

    // ------------------------------------------------------------------------

    /**
     * Input::get
     *
     * Fetch input from GET data.
     *
     * @param string|null $offset The offset of $_GET variable to fetch.
     *                            When set null will returns filtered $_GET variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function get($offset = null, $filter = null)
    {
        return $this->filter(INPUT_GET, $offset, $filter);
    }

    // ------------------------------------------------------------------------

    /**
     * Input::filter
     *
     * Gets a specific external variable by name and optionally filters it.
     *
     * @see http://php.net/manual/en/function.filter-input.php
     *
     * @param int   $type   One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
     * @param mixed $offset The offset key of input variable.
     * @param int   $filter The ID of the filter to apply.
     *                      The Types of filters manual page lists the available filters.
     *                      If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                      This will result in no filtering taking place by default.
     *
     * @return mixed|\O2System\Spl\DataStructures\SplArrayObject
     */
    protected function filter($type, $offset = null, $filter = FILTER_DEFAULT)
    {
        // If $offset is null, it means that the whole input type array is requested
        if (is_null($offset)) {
            $loopThrough = [];

            switch ($type) {
                case INPUT_GET    :
                    $loopThrough = $_GET;
                    break;
                case INPUT_POST   :
                    $loopThrough = $_POST;
                    break;
                case INPUT_COOKIE :
                    $loopThrough = $_COOKIE;
                    break;
                case INPUT_SERVER :
                    $loopThrough = $_SERVER;
                    break;
                case INPUT_ENV    :
                    $loopThrough = $_ENV;
                    break;
                case INPUT_REQUEST    :
                    $loopThrough = $_REQUEST;
                    break;
                case INPUT_SESSION    :
                    $loopThrough = $_ENV;
                    break;
            }

            $loopThrough = $this->filterRecursive($loopThrough, $filter);

            if (empty($loopThrough)) {
                return false;
            }

            return new SplArrayObject($loopThrough);
        } // allow fetching multiple keys at once
        elseif (is_array($offset)) {
            $loopThrough = [];

            foreach ($offset as $key) {
                $loopThrough[ $key ] = $this->filter($type, $key, $filter);
            }

            if (empty($loopThrough)) {
                return false;
            }

            return new SplArrayObject($loopThrough);
        } elseif (isset($offset)) {
            // Due to issues with FastCGI and testing,
            // we need to do these all manually instead
            // of the simpler filter_input();
            switch ($type) {
                case INPUT_GET:
                    $value = isset($_GET[ $offset ])
                        ? $_GET[ $offset ]
                        : null;
                    break;
                case INPUT_POST:
                    $value = isset($_POST[ $offset ])
                        ? $_POST[ $offset ]
                        : null;
                    break;
                case INPUT_SERVER:
                    $value = isset($_SERVER[ $offset ])
                        ? $_SERVER[ $offset ]
                        : null;
                    break;
                case INPUT_ENV:
                    $value = isset($_ENV[ $offset ])
                        ? $_ENV[ $offset ]
                        : null;
                    break;
                case INPUT_COOKIE:
                    $value = isset($_COOKIE[ $offset ])
                        ? $_COOKIE[ $offset ]
                        : null;
                    break;
                case INPUT_REQUEST:
                    $value = isset($_REQUEST[ $offset ])
                        ? $_REQUEST[ $offset ]
                        : null;
                    break;
                case INPUT_SESSION:
                    $value = isset($_SESSION[ $offset ])
                        ? $_SESSION[ $offset ]
                        : null;
                    break;
                default:
                    $value = '';
            }

            if (is_array($value)) {
                $value = $this->filterRecursive($value, $filter);

                if (is_string(key($value))) {
                    return new SplArrayObject($value);
                } else {
                    return $value;
                }
            } elseif (is_object($value)) {
                return $value;
            }

            if (isset($filter)) {
                return filter_var($value, $filter);
            }

            return $value;
        }

        return null;
    }

    // ------------------------------------------------------------------------

    /**
     * Input::filterRecursive
     *
     * Gets multiple variables and optionally filters them.
     *
     * @see http://php.net/manual/en/function.filter-var.php
     * @see http://php.net/manual/en/function.filter-var-array.php
     *
     *
     * @param array     $data   An array with string keys containing the data to filter.
     * @param int|mixed $filter The ID of the filter to apply.
     *                          The Types of filters manual page lists the available filters.
     *                          If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                          This will result in no filtering taking place by default.
     *                          Its also can be An array defining the arguments.
     *                          A valid key is a string containing a variable name and a valid value is either
     *                          a filter type, or an array optionally specifying the filter, flags and options.
     *                          If the value is an array, valid keys are filter which specifies the filter type,
     *                          flags which specifies any flags that apply to the filter, and options which
     *                          specifies any options that apply to the filter. See the example below for
     *                          a better understanding.
     *
     * @return mixed
     */
    protected function filterRecursive(array $data, $filter = FILTER_DEFAULT)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) AND is_array($filter)) {
                $data[ $key ] = filter_var_array($value, $filter);
            } elseif (is_array($value)) {
                $data[ $key ] = $this->filterRecursive($value, $filter);
            } elseif (isset($filter)) {
                $data[ $key ] = filter_var($value, $filter);
            } else {
                $data[ $key ] = $value;
            }
        }

        return $data;
    }

    // ------------------------------------------------------------------------

    /**
     * Input::post
     *
     * Fetch input from POST data.
     *
     * @param string|null $offset The offset of $_POST variable to fetch.
     *                            When set null will returns filtered $_POST variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function post($offset = null, $filter = null)
    {
        return $this->filter(INPUT_POST, $offset, $filter);
    }

    // ------------------------------------------------------------------------

    /**
     * Input::getPost
     *
     * Fetch input from POST data with fallback to GET.
     *
     * @param string|null $offset The offset of $_POST or $_GET variable to fetch.
     *                            When set null will returns filtered $_POST or $_GET variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function postGet($offset, $filter = null)
    {
        // Use $_POST directly here, since filter_has_var only
        // checks the initial POST data, not anything that might
        // have been added since.
        return isset($_POST[ $offset ])
            ? $this->post($offset, $filter)
            : $this->get($offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::files
     *
     * Fetch input from FILES data. Returns an array of all files that have been uploaded with this
     * request. Each file is represented by an UploadedFileInterface instance.
     *
     * @param string|null $offset The offset of $_FILES variable to fetch.
     *                            When set null will returns filtered $_FILES variable.
     *
     * @return array|UploadedFileInterface
     */
    final public function files($offset = null)
    {
        $uploadFiles = server_request()->getUploadedFiles();

        if (isset($offset)) {
            if (isset($uploadFiles[ $offset ])) {
                return $uploadFiles[ $offset ];
            }
        }

        return $uploadFiles;
    }

    //--------------------------------------------------------------------

    /**
     * Input::env
     *
     * Fetch input from ENV data.
     *
     * @param string|null $offset The offset of $_ENV variable to fetch.
     *                            When set null will returns filtered $_ENV variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function env($offset = null, $filter = null)
    {
        return $this->filter(INPUT_ENV, $offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::cookie
     *
     * Fetch input from COOKIE data.
     *
     * @param string|null $offset The offset of $_COOKIE variable to fetch.
     *                            When set null will returns filtered $_COOKIE variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function cookie($offset = null, $filter = null)
    {
        return $this->filter(INPUT_COOKIE, $offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::request
     *
     * Fetch input from REQUEST data.
     *
     * @param string|null $offset The offset of $_REQUEST variable to fetch.
     *                            When set null will returns filtered $_REQUEST variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function request($offset = null, $filter = null)
    {
        return $this->filter(INPUT_REQUEST, $offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::session
     *
     * Fetch input from SESSION data.
     *
     * @param string|null $offset The offset of $_SESSION variable to fetch.
     *                            When set null will returns filtered $_SESSION variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function session($offset = null, $filter = null)
    {
        return $this->filter(INPUT_SESSION, $offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::ipAddress
     *
     * Fetch input ip address.
     * Determines and validates the visitor's IP address.
     *
     * @param string|array $proxyIps List of proxy ip addresses.
     *
     * @return string
     */
    public function ipAddress($proxyIps = [])
    {
        if ( ! empty($proxyIps) && ! is_array($proxyIps)) {
            $proxyIps = explode(',', str_replace(' ', '', $proxyIps));
        }

        foreach ([
                     'HTTP_CLIENT_IP',
                     'HTTP_FORWARDED',
                     'HTTP_X_FORWARDED_FOR',
                     'HTTP_X_CLIENT_IP',
                     'HTTP_X_CLUSTER_CLIENT_IP',
                     'REMOTE_ADDR',
                 ] as $header
        ) {
            if (null !== ($ipAddress = $this->server($header))) {
                if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                    if ( ! in_array($ipAddress, $proxyIps)) {
                        break;
                    }
                }
            }
        }

        return (empty($ipAddress) ? '0.0.0.0' : $ipAddress);
    }

    //--------------------------------------------------------------------

    /**
     * Input::server
     *
     * Fetch input from SERVER data.
     *
     * @param string|null $offset The offset of $_SERVER variable to fetch.
     *                            When set null will returns filtered $_SERVER variable.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed
     */
    final public function server($offset = null, $filter = null)
    {
        return $this->filter(INPUT_SERVER, $offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::userAgent
     *
     * @return string
     */
    public function userAgent()
    {
        return $this->server('HTTP_USER_AGENT');
    }

    //--------------------------------------------------------------------

    /**
     * Input::bearerToken
     *
     * @return string
     */
    public function bearerToken()
    {
        $authorization = $this->server('HTTP_AUTHORIZATION');

        if (preg_match('/(Bearer)/', $authorization)) {
            return str_replace('Bearer ', '', $authorization);
        }

        return false;
    }

    //--------------------------------------------------------------------

    /**
     * Input::webToken
     *
     * @return string
     */
    public function webToken()
    {
        if ($webToken = $this->server('HTTP_X_WEB_TOKEN')) {
            return $webToken;
        }

        return false;
    }

    //--------------------------------------------------------------------

    /**
     * Input::basicAuth
     *
     * @return string
     */
    public function basicAuth()
    {
        $authorization = $this->server('HTTP_AUTHORIZATION');

        if (preg_match('/(Basic)/', $authorization)) {
            return str_replace('Basic ', '', $authorization);
        }

        return false;
    }

    //--------------------------------------------------------------------

    /**
     * Input::submit
     *
     * Determines if the POST input is submit
     *
     * @return bool
     */
    final public function submit()
    {
        return (bool)isset($_POST[ 'submit' ]);
    }
}