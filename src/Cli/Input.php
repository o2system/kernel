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

namespace O2System\Kernel\Cli;

// ------------------------------------------------------------------------

use O2System\Spl\DataStructures\SplArrayObject;

/**
 * Class Input
 *
 * Http Kernel Input data with optional filter functionality, all data as it has arrived to the
 * Kernel Input from the CGI and/or PHP on command line interface environment, including:
 *
 * - The values represented in $_SERVER, $_ENV, and $_REQUEST.
 * - The values from argv.
 *
 * @package O2System\Kernel\Http
 */
class Input
{
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
     * Input::argv
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
    final public function argv($offset = null, $filter = null)
    {
        $arguments = $_SERVER[ 'argv' ];
        $numArguments = $_SERVER[ 'argc' ];

        $argv = [];
        for ($i = 1; $i < $numArguments; $i++) {
            $optionCommand = trim($arguments[ $i ]);
            $optionValue = true;

            if (empty($optionCommand)) {
                continue;
            }

            if (strpos($optionCommand, '=') !== false) {
                $xOptionCommand = explode('=', $optionCommand);
                $xOptionCommand = array_map('trim', $xOptionCommand);

                $optionCommand = str_replace(['-', '--'], '', $xOptionCommand[ 0 ]);
                $optionValue = $xOptionCommand[ 1 ];

                $argv[ $optionCommand ] = $optionValue;
                continue;
            }

            if (strpos($optionCommand, '--') !== false
                || strpos($optionCommand, '-') !== false
            ) {
                $optionCommand = str_replace(['-', '--'], '', $optionCommand);

                if (isset($arguments[ $i + 1 ])) {
                    $nextOptionCommand = $arguments[ $i + 1 ];

                    if (strpos($nextOptionCommand, '--') === false
                        || strpos($nextOptionCommand, '-') === false
                    ) {
                        $optionValue = $nextOptionCommand;
                        $arguments[ $i + 1 ] = null;
                    }
                }
            }

            if (isset($filter)) {
                $optionValue = filter_var($optionValue, $filter);
            } else {
                $optionValue = filter_var($optionValue, FILTER_DEFAULT);
            }

            $argv[ $optionCommand ] = $optionValue;
        }

        if (empty($offset)) {
            return $argv;
        } elseif (isset($argv[ $offset ])) {
            return $argv[ $offset ];
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Input::standard
     *
     * @return string
     */
    public function standard()
    {
        return trim(fgets(STDIN));
    }

    // ------------------------------------------------------------------------

    /**
     * Input::getApp
     *
     * @return bool
     */
    public function getApp()
    {
        return isset($_SERVER[ 'argv' ][ 0 ])
            ? $_SERVER[ 'argv' ][ 0 ]
            : false;
    }

    // ------------------------------------------------------------------------

    /**
     * Input::getCommand
     *
     * @return bool
     */
    public function getCommand()
    {
        return isset($_SERVER[ 'argv' ][ 1 ])
            ? $_SERVER[ 'argv' ][ 1 ]
            : false;
    }

    // ------------------------------------------------------------------------

    /**
     * Input::getOptions
     *
     * @param string|null $offset
     * @param mixed $filter
     *
     * @return array|mixed
     */
    public function getOptions($offset = null, $filter = null)
    {
        $arguments = $_SERVER[ 'argv' ];
        $numArguments = $_SERVER[ 'argc' ];

        $argv = [];

        for ($i = 2; $i < $numArguments; $i++) {
            $optionCommand = trim($arguments[ $i ]);
            $optionValue = true;

            if (empty($optionCommand)) {
                continue;
            }

            if (strpos($optionCommand, '=') !== false) {
                $xOptionCommand = explode('=', $optionCommand);
                $xOptionCommand = array_map('trim', $xOptionCommand);

                $optionCommand = str_replace(['-', '--'], '', $xOptionCommand[ 0 ]);
                $optionValue = $xOptionCommand[ 1 ];

                $argv[ $optionCommand ] = $optionValue;
                continue;
            }

            if (strpos($optionCommand, '--') !== false
                || strpos($optionCommand, '-') !== false
            ) {
                $optionCommand = str_replace(['-', '--'], '', $optionCommand);

                if (isset($arguments[ $i + 1 ])) {
                    $nextOptionCommand = $arguments[ $i + 1 ];

                    if (strpos($nextOptionCommand, '--') === false
                        || strpos($nextOptionCommand, '-') === false
                    ) {
                        $optionValue = $nextOptionCommand;
                        $arguments[ $i + 1 ] = null;
                    }
                }
            }

            if (isset($filter)) {
                $optionValue = filter_var($optionValue, $filter);
            } else {
                $optionValue = filter_var($optionValue, FILTER_DEFAULT);
            }

            $argv[ $optionCommand ] = $optionValue;
        }

        if (empty($offset)) {
            return $argv;
        } elseif (isset($argv[ $offset ])) {
            return $argv[ $offset ];
        }
    }

    // ------------------------------------------------------------------------

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
}