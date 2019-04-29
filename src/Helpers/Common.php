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

if ( ! function_exists('is_php')) {
    /**
     * is_php
     *
     * Determines if the current version of PHP is equal to or greater than the supplied value
     *
     * @param    string
     *
     * @return    bool    TRUE if the current version is $version or higher
     */
    function is_php($version)
    {
        static $_is_php;
        $version = (string)$version;

        if ( ! isset($_is_php[ $version ])) {
            $_is_php[ $version ] = version_compare(PHP_VERSION, $version, '>=');
        }

        return $_is_php[ $version ];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_true')) {
    /**
     * is_true
     *
     * Helper function to test boolean TRUE.
     *
     * @param    mixed $test
     *
     * @return    bool
     */
    function is_true($test)
    {
        return (bool)($test === true);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_false')) {
    /**
     * is_false
     *
     * Helper function to test boolean FALSE.
     *
     * @param    mixed $test
     *
     * @return    bool
     */
    function is_false($test)
    {
        return (bool)($test === false);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_really_writable')) {
    /**
     * is_really_writable
     *
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @link    https://bugs.php.net/bug.php?id=54709
     *
     * @param    string
     *
     * @return    bool
     */
    function is_really_writable($file)
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') || ! ini_get('safe_mode'))) {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);

            return true;
        } elseif ( ! is_file($file) || ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }

        fclose($fp);

        return true;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_https')) {
    /**
     * is_https
     *
     * Determines if the application is accessed via an encrypted
     * (HTTPS) connection.
     *
     * @return    bool
     */
    function is_https()
    {
        if ( ! empty($_SERVER[ 'HTTPS' ]) && strtolower($_SERVER[ 'HTTPS' ]) !== 'off') {
            return true;
        } elseif (isset($_SERVER[ 'HTTP_X_FORWARDED_PROTO' ]) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https') {
            return true;
        } elseif ( ! empty($_SERVER[ 'HTTP_FRONT_END_HTTPS' ]) && strtolower(
                $_SERVER[ 'HTTP_FRONT_END_HTTPS' ]
            ) !== 'off'
        ) {
            return true;
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_cli')) {
    /**
     * is_cli
     *
     * Test to see if a request was made from the command line.
     *
     * @return    bool
     */
    function is_cli()
    {
        return (php_sapi_name() === 'cli');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_ajax')) {
    /**
     * is_ajax
     *
     * Test to see if a request an ajax request.
     *
     * @return    bool
     */
    function is_ajax()
    {
        return ( ! empty($_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) && strtolower(
                $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]
            ) === 'xmlhttprequest');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('remove_invisible_characters')) {
    /**
     * remove_invisible_characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param    string
     * @param    bool
     *
     * @return    string
     */
    function remove_invisible_characters($str, $url_encoded = true)
    {
        $non_displayables = [];

        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/';    // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';    // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('function_usable')) {
    /**
     * function_usable
     *
     * Executes a function_exists() check, and if the Suhosin PHP
     * extension is loaded - checks whether the function that is
     * checked might be disabled in there as well.
     *
     * This is useful as function_exists() will return FALSE for
     * functions disabled via the *disable_functions* php.ini
     * setting, but not for *suhosin.executor.func.blacklist* and
     * *suhosin.executor.disable_eval*. These settings will just
     * terminate script execution if a disabled function is executed.
     *
     * The above described behavior turned out to be a bug in Suhosin,
     * but even though a fix was commited for 0.9.34 on 2012-02-12,
     * that version is yet to be released. This function will therefore
     * be just temporary, but would probably be kept for a few years.
     *
     * @link    http://www.hardened-php.net/suhosin/
     *
     * @param    string $function_name Function to check for
     *
     * @return    bool    TRUE if the function exists and is safe to call,
     *            FALSE otherwise.
     */
    function function_usable($function_name)
    {
        static $suhosinFuncBlacklist;

        if (function_exists($function_name)) {
            if ( ! isset($suhosinFuncBlacklist)) {
                if (extension_loaded('suhosin')) {
                    $suhosinFuncBlacklist = explode(',', trim(ini_get('suhosin.executor.func.blacklist')));

                    if ( ! in_array('eval', $suhosinFuncBlacklist, true) && ini_get(
                            'suhosin.executor.disable_eval'
                        )
                    ) {
                        $suhosinFuncBlacklist[] = 'eval';
                    }
                } else {
                    $suhosinFuncBlacklist = [];
                }
            }

            return ! in_array($function_name, $suhosinFuncBlacklist, true);
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        $headers = [];
        $http_regex = '/\AHTTP_/';
        foreach ($_SERVER as $server_key => $server_value) {
            if (preg_match($http_regex, $server_key)) {
                $header_key = preg_replace($http_regex, '', $server_key);
                $matches_regex = [];
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $matches_regex = explode('_', $header_key);
                if (count($matches_regex) > 0 and strlen($header_key) > 2) {
                    foreach ($matches_regex as $match_key => $match_value) {
                        $matches_regex[ $match_key ] = ucfirst($match_value);
                    }
                    $header_key = implode('-', $matches_regex);
                }
                $headers[ $header_key ] = $server_value;
            }
        }

        return ($headers);
    }
}


if ( ! function_exists('path_to_url')) {
    /**
     * path_to_url
     *
     * @param $path
     *
     * @return string
     */
    function path_to_url($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $root_dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_SERVER[ 'DOCUMENT_ROOT' ]);
        $base_dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname($_SERVER[ 'SCRIPT_FILENAME' ]));

        $root_dir = rtrim($root_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (is_dir(DIRECTORY_SEPARATOR . 'private' . $base_dir)) {
            $root_dir = DIRECTORY_SEPARATOR . 'private' . $root_dir;
            $base_dir = DIRECTORY_SEPARATOR . 'private' . $base_dir;
        }

        $root_dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root_dir);
        $base_dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $base_dir);

        $base_url = is_https() ? 'https' : 'http';

        if (isset($_SERVER[ 'HTTP_HOST' ])) {
            $base_url .= '://' . $_SERVER[ 'HTTP_HOST' ];
        } elseif (isset($_SERVER[ 'SERVER_NAME' ])) {

            // Add server name
            $base_url .= '://' . $_SERVER[ 'SERVER_NAME' ];

            // Add server port if needed
            $base_url .= $_SERVER[ 'SERVER_PORT' ] !== '80' ? ':' . $_SERVER[ 'SERVER_PORT' ] : '';
        }

        // Add base path
        $base_url .= '/' . str_replace($root_dir, '', $base_dir);
        $base_url = str_replace(DIRECTORY_SEPARATOR, '/', $base_url);
        $base_url = trim($base_url, '/') . '/';

        if(strpos($path, 'resources') !== false && defined('PATH_RESOURCES')) {
            $path_url = 'resources' . '/' . str_replace(PATH_RESOURCES, '', $path);
        } elseif(strpos($path, 'public') !== false && defined('PATH_PUBLIC')) {
            $path_url = str_replace(PATH_PUBLIC, '', $path);
        } elseif(strpos($path, 'storage') !== false && defined('PATH_RESOURCES')) {
            $path_url = 'storage' . '/' . str_replace(PATH_RESOURCES, '', $path);
        } else {
            $path_url = str_replace($base_dir, '', $path);
        }

        $path_url = str_replace(['/', '\\'], '/', $path_url);

        return $base_url . str_replace(DIRECTORY_SEPARATOR, '/', $path_url);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_namespace')) {
    /**
     * get_namespace
     *
     * @param $class
     *
     * @return string
     */
    function get_namespace($class)
    {
        $class = is_object($class) ? get_class($class) : prepare_class_name($class);

        $x_class = explode('\\', $class);
        array_pop($x_class);

        return trim(implode('\\', $x_class), '\\') . '\\';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_class_name')) {
    /**
     * get_class_name
     *
     * @param $class
     *
     * @return mixed|string
     */
    function get_class_name($class)
    {
        $class = is_object($class) ? get_class($class) : prepare_class_name($class);

        if (strpos($class, 'anonymous') !== false) {
            return $class;
        } else {
            $x_class = explode('\\', $class);

            return end($x_class);
        }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('prepare_class_name')) {
    /**
     * prepare_class_name
     *
     * @param $class
     *
     * @return string
     */
    function prepare_class_name($class)
    {
        $class = str_replace(['/', DIRECTORY_SEPARATOR, '.php'], ['\\', '\\', ''], $class);
        $class = trim($class);

        $segments = explode('\\', $class);

        if (count($segments) > 1) {
            if ($segments[ 0 ] === $segments[ 1 ]) {
                array_shift($segments);
            }
        }

        $segments = array_map('studlycase', $segments);

        return implode('\\', $segments);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('prepare_filename')) {
    /**
     * prepare_filename
     *
     * @param      $filename
     * @param null $ext
     *
     * @return string
     */
    function prepare_filename($filename, $ext = null)
    {
        $filename = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filename);
        $filename = trim($filename, DIRECTORY_SEPARATOR);

        $segments = explode(DIRECTORY_SEPARATOR, $filename);
        $segments = array_map('studlycase', $segments);

        return implode(DIRECTORY_SEPARATOR, $segments) . $ext;
    }
}

// ------------------------------------------------------------------------


if ( ! function_exists('prepare_namespace')) {

    /**
     * prepare_namespace
     *
     * Return a valid namespace class
     *
     * @param    string $class class name with namespace
     *
     * @return   string     valid string namespace
     */
    function prepare_namespace($class, $get_namespace = true)
    {
        return ($get_namespace === true ? get_namespace($class) : prepare_class_name($class));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('http_parse_headers')) {

    /**
     * http_parse_headers
     *
     * @param $raw_headers
     *
     * @return array
     */
    function http_parse_headers($raw_headers)
    {
        $headers = [];
        $key = ''; // [+]

        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[ 1 ])) {
                if ( ! isset($headers[ $h[ 0 ] ])) {
                    $headers[ $h[ 0 ] ] = trim($h[ 1 ]);
                } elseif (is_array($headers[ $h[ 0 ] ])) {
                    $headers[ $h[ 0 ] ] = array_merge($headers[ $h[ 0 ] ], [trim($h[ 1 ])]); // [+]
                } else {
                    $headers[ $h[ 0 ] ] = array_merge([$headers[ $h[ 0 ] ]], [trim($h[ 1 ])]); // [+]
                }

                $key = $h[ 0 ]; // [+]
            } else // [+]
            { // [+]
                if (substr($h[ 0 ], 0, 1) == "\t") // [+]
                {
                    $headers[ $key ] .= "\r\n\t" . trim($h[ 0 ]);
                } // [+]
                elseif ( ! $key) // [+]
                {
                    $headers[ 0 ] = trim($h[ 0 ]);
                }
                trim($h[ 0 ]); // [+]
            } // [+]
        }

        return $headers;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_html')) {
    /**
     * is_html
     *
     * Determine if string is HTML
     *
     * @param $string
     *
     * @return bool
     */
    function is_html($string)
    {
        return (bool)($string !== strip_tags($string) ? true : false);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_serialized')) {
    /**
     * is_serialized
     *
     * Check is the string is serialized array
     *
     * @param   string $string Source string
     *
     * @return  bool
     */
    function is_serialized($string)
    {
        if ( ! is_string($string)) {
            return false;
        }
        if (trim($string) == '') {
            return false;
        }
        if (preg_match("/^(i|s|a|o|d)(.*);/si", $string)) {
            $is_valid = @unserialize($string);

            if (empty($is_valid)) {
                return false;
            }

            return true;
        }

        return false;
    }
}
// ------------------------------------------------------------------------

if ( ! function_exists('is_json')) {
    /**
     * is_json
     *
     * Check is the string is json array or object
     *
     * @item    string or array
     * @return  boolean (true or false)
     */
    function is_json($string)
    {
        // make sure provided input is of type string
        if ( ! is_string($string)) {
            return false;
        }
        // trim white spaces
        $string = trim($string);
        // get first character
        $first_char = substr($string, 0, 1);
        // get last character
        $last_char = substr($string, -1);
        // check if there is a first and last character
        if ( ! $first_char || ! $last_char) {
            return false;
        }
        // make sure first character is either { or [
        if ($first_char !== '{' && $first_char !== '[') {
            return false;
        }
        // make sure last character is either } or ]
        if ($last_char !== '}' && $last_char !== ']') {
            return false;
        }
        // let's leave the rest to PHP.
        // try to decode string
        json_decode($string);
        // check if error occurred
        $is_valid = json_last_error() === JSON_ERROR_NONE;

        return $is_valid;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_parse_string')) {
    /**
     * is_parse_string
     *
     * @return string
     *
     * @params string $string
     */
    function is_parse_string($string)
    {
        if (preg_match('[=]', $string)) {
            return true;
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_multidimensional_array')) {
    /**
     * is_multidimensional_array
     *
     * Checks if the given array is multidimensional.
     *
     * @param array $array
     *
     * @return bool
     */
    function is_multidimensional_array(array $array)
    {
        if (count($array) != count($array, COUNT_RECURSIVE)) {
            return true;
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_associative_array')) {
    /**
     * is_associative_array
     *
     * Check if the given array is associative.
     *
     * @param array $array
     *
     * @return bool
     */
    function is_associative_array(array $array)
    {
        if ($array == []) {
            return true;
        }
        $keys = array_keys($array);
        if (array_keys($keys) !== $keys) {
            foreach ($keys as $key) {
                if ( ! is_numeric($key)) {
                    return true;
                }
            }
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('is_indexed_array')) {
    /**
     * is_indexed_array
     *
     * Check if an array has a numeric index.
     *
     * @param array $array
     *
     * @return bool
     */
    function is_indexed_array(array $array)
    {
        if ($array == []) {
            return true;
        }

        return ! is_associative_array($array);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('error_code_string')) {
    /**
     * error_code_string
     *
     * @param int $code
     *
     * @return string
     */
    function error_code_string($code)
    {
        static $errors = [];

        if (empty($errors)) {
            $errors = require(str_replace('Helpers', 'Config', __DIR__) . DIRECTORY_SEPARATOR . 'Errors.php');
        }

        $string = null;

        if (isset($errors[ $code ])) {
            $string = $errors[ $code ];
        }

        return strtoupper($string);
    }
}