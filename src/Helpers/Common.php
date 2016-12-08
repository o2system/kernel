<?php
/**
 * This file is part of the O2System PHP Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_php' ) ) {
    /**
     * Determines if the current version of PHP is equal to or greater than the supplied value
     *
     * @param    string
     *
     * @return    bool    TRUE if the current version is $version or higher
     */
    function is_php ( $version )
    {
        static $_is_php;
        $version = (string) $version;

        if ( ! isset( $_is_php[ $version ] ) ) {
            $_is_php[ $version ] = version_compare( PHP_VERSION, $version, '>=' );
        }

        return $_is_php[ $version ];
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_really_writable' ) ) {
    /**
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
    function is_really_writable ( $file )
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if ( DIRECTORY_SEPARATOR === '/' && ( is_php( '5.4' ) || ! ini_get( 'safe_mode' ) ) ) {
            return is_writable( $file );
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if ( is_dir( $file ) ) {
            $file = rtrim( $file, '/' ) . '/' . md5( mt_rand() );
            if ( ( $fp = @fopen( $file, 'ab' ) ) === false ) {
                return false;
            }

            fclose( $fp );
            @chmod( $file, 0777 );
            @unlink( $file );

            return true;
        } elseif ( ! is_file( $file ) || ( $fp = @fopen( $file, 'ab' ) ) === false ) {
            return false;
        }

        fclose( $fp );

        return true;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_https' ) ) {
    /**
     * Is HTTPS?
     *
     * Determines if the application is accessed via an encrypted
     * (HTTPS) connection.
     *
     * @return    bool
     */
    function is_https ()
    {
        if ( ! empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] ) !== 'off' ) {
            return true;
        } elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https' ) {
            return true;
        } elseif ( ! empty( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) && strtolower(
                                                                        $_SERVER[ 'HTTP_FRONT_END_HTTPS' ]
                                                                    ) !== 'off'
        ) {
            return true;
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_cli' ) ) {
    /**
     * Is CLI?
     *
     * Test to see if a request was made from the command line.
     *
     * @return    bool
     */
    function is_cli ()
    {
        return ( PHP_SAPI === 'cli' || defined( 'STDIN' ) );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'is_ajax' ) ) {
    /**
     * Is AJAX?
     *
     * Test to see if a request an ajax request.
     *
     * @return    bool
     */
    function is_ajax ()
    {
        return ( ! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower(
                                                                       $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]
                                                                   ) === 'xmlhttprequest' );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'remove_invisible_characters' ) ) {
    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param    string
     * @param    bool
     *
     * @return    string
     */
    function remove_invisible_characters ( $str, $url_encoded = true )
    {
        $non_displayables = [ ];

        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ( $url_encoded ) {
            $non_displayables[] = '/%0[0-8bcef]/';    // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';    // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace( $non_displayables, '', $str, -1, $count );
        }
        while ( $count );

        return $str;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'html_escape' ) ) {
    /**
     * Returns HTML escaped variable.
     *
     * @param    mixed $var           The input string or array of strings to be escaped.
     * @param    bool  $double_encode $double_encode set to FALSE prevents escaping twice.
     *
     * @return    mixed            The escaped string or array of strings as a result.
     */
    function html_escape ( $var, $encoding = 'UTF-8', $double_encode = true )
    {
        if ( is_array( $var ) ) {
            return array_map( 'html_escape', $var, array_fill( 0, count( $var ), $double_encode ) );
        }

        return htmlspecialchars( $var, ENT_QUOTES, $encoding, $double_encode );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'stringify_attributes' ) ) {
    /**
     * Stringify attributes for use in HTML tags.
     *
     * Helper function used to convert a string, array, or object
     * of attributes to a string.
     *
     * @param    mixed    string, array, object
     * @param    bool
     *
     * @return    string
     */
    function stringify_attributes ( $attributes, $js = false )
    {
        $atts = null;

        if ( empty( $attributes ) ) {
            return $atts;
        }

        if ( is_string( $attributes ) ) {
            return ' ' . $attributes;
        }

        $attributes = (array) $attributes;

        foreach ( $attributes as $key => $val ) {
            $atts .= ( $js ) ? $key . '=' . $val . ',' : ' ' . $key . '="' . $val . '"';

        }


        return rtrim( $atts, ',' );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'function_usable' ) ) {
    /**
     * Function usable
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
    function function_usable ( $function_name )
    {
        static $suhosinFuncBlacklist;

        if ( function_exists( $function_name ) ) {
            if ( ! isset( $suhosinFuncBlacklist ) ) {
                if ( extension_loaded( 'suhosin' ) ) {
                    $suhosinFuncBlacklist = explode( ',', trim( ini_get( 'suhosin.executor.func.blacklist' ) ) );

                    if ( ! in_array( 'eval', $suhosinFuncBlacklist, true ) && ini_get(
                            'suhosin.executor.disable_eval'
                        )
                    ) {
                        $suhosinFuncBlacklist[] = 'eval';
                    }
                } else {
                    $suhosinFuncBlacklist = [ ];
                }
            }

            return ! in_array( $function_name, $suhosinFuncBlacklist, true );
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'path_to_url' ) ) {
    /**
     * path_to_url
     *
     * @param $path
     *
     * @return string
     */
    function path_to_url ( $path )
    {
        $path = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );

        $base_url = is_https() ? 'https' : 'http';
        $base_url .= '://' . ( isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ] );

        // Add server port if needed
        $base_url .= $_SERVER[ 'SERVER_PORT' ] !== '80' ? ':' . $_SERVER[ 'SERVER_PORT' ] : '';

        // Add base path
        $base_url .= dirname( $_SERVER[ 'SCRIPT_NAME' ] );
        $base_url = str_replace( DIRECTORY_SEPARATOR, '/', $base_url );
        $base_url = trim( $base_url, '/' ) . '/';

        // Vendor directory
        $base_dir = explode( 'vendor' . DIRECTORY_SEPARATOR . 'o2system', __DIR__ );
        $base_dir = str_replace( [ 'o2system', '/' ], [ '', DIRECTORY_SEPARATOR ], $base_dir[ 0 ] );
        $base_dir = trim( $base_dir, DIRECTORY_SEPARATOR );

        $path = str_replace( [ $base_dir, DIRECTORY_SEPARATOR ], [ '', '/' ], $path );
        $path = trim( $path, '/' );

        $path = str_replace( DIRECTORY_SEPARATOR, '/', $path );
        $path = str_replace( '//', '/', $path );

        return trim( $base_url . $path, '/' );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'get_namespace' ) ) {
    /**
     * get_namespace
     *
     * @param $class
     *
     * @return string
     */
    function get_namespace ( $class )
    {
        $class = is_object( $class ) ? get_class( $class ) : prepare_class_name( $class );

        $x_class = explode( '\\', $class );
        array_pop( $x_class );

        return trim( implode( '\\', $x_class ), '\\' ) . '\\';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'get_class_name' ) ) {
    /**
     * get_class_name
     *
     * @param $class
     *
     * @return mixed|string
     */
    function get_class_name ( $class )
    {
        $class = is_object( $class ) ? get_class( $class ) : prepare_class_name( $class );

        if ( strpos( $class, 'anonymous' ) !== false ) {
            return $class;
        } else {
            $x_class = explode( '\\', $class );

            return end( $x_class );
        }
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'prepare_class_name' ) ) {
    /**
     * prepare_class_name
     *
     * @param $class
     *
     * @return string
     */
    function prepare_class_name ( $class )
    {
        $class = str_replace( [ '/', DIRECTORY_SEPARATOR, '.php' ], [ '\\', '\\', '' ], $class );
        $class = trim( $class );

        $segments = explode( '\\', $class );
        $segments = array_map( 'studlycapcase', $segments );

        return implode( '\\', $segments );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'prepare_filename' ) ) {
    /**
     * prepare_filename
     *
     * @param      $filename
     * @param null $ext
     *
     * @return string
     */
    function prepare_filename ( $filename, $ext = null )
    {
        $filename = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $filename );
        $filename = trim( $filename, DIRECTORY_SEPARATOR );

        $segments = explode( DIRECTORY_SEPARATOR, $filename );
        $segments = array_map( 'studlycapcase', $segments );

        return implode( DIRECTORY_SEPARATOR, $segments ) . $ext;
    }
}

// ------------------------------------------------------------------------


if ( ! function_exists( 'prepare_namespace' ) ) {

    /**
     * prepare_namespace
     *
     * Return a valid namespace class
     *
     * @param    string $class class name with namespace
     *
     * @return   string     valid string namespace
     */
    function prepare_namespace ( $class, $get_namespace = true )
    {
        return ( $get_namespace === true ? get_namespace( $class ) : prepare_filename( $class ) );
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'http_parse_headers' ) ) {

    /**
     * http_parse_headers
     *
     * @param $raw_headers
     *
     * @return array
     */
    function http_parse_headers ( $raw_headers )
    {
        $headers = [ ];
        $key = ''; // [+]

        foreach ( explode( "\n", $raw_headers ) as $i => $h ) {
            $h = explode( ':', $h, 2 );

            if ( isset( $h[ 1 ] ) ) {
                if ( ! isset( $headers[ $h[ 0 ] ] ) ) {
                    $headers[ $h[ 0 ] ] = trim( $h[ 1 ] );
                } elseif ( is_array( $headers[ $h[ 0 ] ] ) ) {
                    $headers[ $h[ 0 ] ] = array_merge( $headers[ $h[ 0 ] ], [ trim( $h[ 1 ] ) ] ); // [+]
                } else {
                    $headers[ $h[ 0 ] ] = array_merge( [ $headers[ $h[ 0 ] ] ], [ trim( $h[ 1 ] ) ] ); // [+]
                }

                $key = $h[ 0 ]; // [+]
            } else // [+]
            { // [+]
                if ( substr( $h[ 0 ], 0, 1 ) == "\t" ) // [+]
                {
                    $headers[ $key ] .= "\r\n\t" . trim( $h[ 0 ] );
                } // [+]
                elseif ( ! $key ) // [+]
                {
                    $headers[ 0 ] = trim( $h[ 0 ] );
                }
                trim( $h[ 0 ] ); // [+]
            } // [+]
        }

        return $headers;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists( 'stripslashes_recursive' ) ) {
    /**
     * Recursive Strip Slashes
     *
     * Un-quotes a quoted string
     *
     * @link  http://php.net/manual/en/function.stripslashes.php
     *
     * @param string $string <p>
     *                       The input string.
     *                       </p>
     *
     * @return string a string with backslashes stripped off.
     * (\' becomes ' and so on.)
     * Double backslashes (\\) are made into a single
     * backslash (\).
     * @since 4.0
     * @since 5.0
     */
    function stripslashes_recursive ( $string )
    {
        $string = is_array( $string ) ? array_map( 'stripslashes_recursive', $string ) : stripslashes( $string );

        return $string;
    }
}