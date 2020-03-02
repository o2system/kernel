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

use O2System\Kernel\DataStructures;
use O2System\Psr\Http\Message\UploadedFileInterface;

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
        $shouldNotUnset = ['GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES'];

        $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES,
            isset($_SESSION) && is_array($_SESSION) ? $_SESSION : []);

        foreach ($input as $key => $value) {
            if ( ! in_array($key, $shouldNotUnset) && isset($GLOBALS[ $key ])) {
                unset($GLOBALS[ $key ]);
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
     * @param string|null $offset  The offset of $_GET or $_POST variable to fetch.
     *                             When set null will returns filtered $_GET or $_POST variable.
     * @param mixed       $default Default value
     * @param int         $filter  The ID of the filter to apply.
     *                             The Types of filters manual page lists the available filters.
     *                             If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                             This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Get|DataStructures\Input\Post
     */
    final public function getPost($offset, $default = null, $filter = FILTER_DEFAULT)
    {
        // Use $_GET directly here, since filter_has_var only
        // checks the initial GET data, not anything that might
        // have been added since.
        return isset($_GET[ $offset ])
            ? $this->get($offset, $default, $filter)
            : $this->post($offset, $default, $filter);
    }

    // ------------------------------------------------------------------------

    /**
     * Input::get
     *
     * Fetch input from GET data.
     *
     * @param string|null $offset The offset of $_GET variable to fetch.
     *                            When set null will returns filtered $_GET variable.
     * @param mixed       $defult Default value.
     * @param int         $filter The ID of the filter to apply.
     *                            The Types of filters manual page lists the available filters.
     *                            If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                            This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Get
     */
    final public function get($offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_GET, $offset, $default, $filter);
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
     * @return mixed|DataStructures\Input\Post
     */
    final public function post($offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_POST, $offset, $default, $filter);
    }

    // ------------------------------------------------------------------------

    /**
     * Input::getPost
     *
     * Fetch input from POST data with fallback to GET.
     *
     * @param string|null $offset  The offset of $_POST or $_GET variable to fetch.
     *                             When set null will returns filtered $_POST or $_GET variable.
     * @param mixed       $default Default value.
     * @param int         $filter  The ID of the filter to apply.
     *                             The Types of filters manual page lists the available filters.
     *                             If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                             This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Get|DataStructures\Input\Post
     */
    final public function postGet($offset, $default = null, $filter = FILTER_DEFAULT)
    {
        // Use $_POST directly here, since filter_has_var only
        // checks the initial POST data, not anything that might
        // have been added since.
        return isset($_POST[ $offset ])
            ? $this->post($offset, $default, $filter)
            : $this->get($offset, $default, $filter);
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
     * @return array|UploadedFileInterface|\O2System\Kernel\DataStructures\Input\Files
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
     * @param string|null $offset  The offset of $_ENV variable to fetch.
     *                             When set null will returns filtered $_ENV variable.
     * @param mixed       $default Default value.
     * @param int         $filter  The ID of the filter to apply.
     *                             The Types of filters manual page lists the available filters.
     *                             If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                             This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Env
     */
    final public function env($offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_ENV, $offset, $default, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::cookie
     *
     * Fetch input from COOKIE data.
     *
     * @param string|null $offset  The offset of $_COOKIE variable to fetch.
     *                             When set null will returns filtered $_COOKIE variable.
     * @param mixed       $default Default value.
     * @param int         $filter  The ID of the filter to apply.
     *                             The Types of filters manual page lists the available filters.
     *                             If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                             This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Cookie
     */
    final public function cookie($offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_COOKIE, $offset, $default, $filter);
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
     * @return mixed|DataStructures\Input\Request
     */
    final public function request($offset = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_REQUEST, $offset, $filter);
    }

    //--------------------------------------------------------------------

    /**
     * Input::session
     *
     * Fetch input from SESSION data.
     *
     * @param string|null $offset  The offset of $_SESSION variable to fetch.
     *                             When set null will returns filtered $_SESSION variable.
     * @param mixed       $default Default value.
     * @param int         $filter  The ID of the filter to apply.
     *                             The Types of filters manual page lists the available filters.
     *                             If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                             This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Session
     */
    final public function session($offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_SESSION, $offset, $default, $filter);
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

        $ipAddress = $ipAddress === '::1' ? '127.0.0.1' : $ipAddress;

        return (empty($ipAddress) ? '0.0.0.0' : $ipAddress);
    }

    //--------------------------------------------------------------------

    /**
     * Input::server
     *
     * Fetch input from SERVER data.
     *
     * @param string|null $offset  The offset of $_SERVER variable to fetch.
     *                             When set null will returns filtered $_SERVER variable.
     * @param mixed       $default Default value
     * @param int         $filter  The ID of the filter to apply.
     *                             The Types of filters manual page lists the available filters.
     *                             If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                             This will result in no filtering taking place by default.
     *
     * @return mixed|DataStructures\Input\Server
     */
    final public function server($offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        return $this->fetchData(INPUT_SERVER, $offset, $default, $filter);
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

    // ------------------------------------------------------------------------

    /**
     * Input::fetchData
     *
     * Gets a specific external variable by name and optionally filters it.
     *
     * @see http://php.net/manual/en/function.filter-input.php
     *
     * @param int   $type    One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
     * @param mixed $offset  The offset key of input variable.
     * @param mixed $default Default value.
     * @param int   $filter  The ID of the filter to apply.
     *                       The Types of filters manual page lists the available filters.
     *                       If omitted, FILTER_DEFAULT will be used, which is equivalent to FILTER_UNSAFE_RAW.
     *                       This will result in no filtering taking place by default.
     *
     * @return mixed|\O2System\Spl\DataStructures\SplArrayObject
     */
    protected function fetchData($type, $offset = null, $default = null, $filter = FILTER_DEFAULT)
    {
        switch ($type) {
            case INPUT_GET    :
                $data = new DataStructures\Input\Get();

                break;
            case INPUT_POST   :
                $data = new DataStructures\Input\Post();

                break;
            case INPUT_COOKIE :
                $data = new DataStructures\Input\Cookie();

                break;
            case INPUT_SERVER :
                $data = new DataStructures\Input\Server();

                break;
            case INPUT_ENV    :
                $data = new DataStructures\Input\Env();

                break;
            case INPUT_REQUEST    :
                $data = new DataStructures\Input\Request();

                break;
            case INPUT_SESSION    :
                $data = new DataStructures\Input\Session();

                break;
        }

        // Set filter mode
        if( ! empty($filter) ) {
            $data->setFilter($filter);
        }

        if (isset($offset)) {
            if ($data->offsetExists($offset)) {
                $value = $data->offsetGet($offset);
            }

            if (empty($value)) {
                return $default;
            }

            return $value;
        } elseif($data->count()) {
            return $data;
        }

        return false;
    }
}
