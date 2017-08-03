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

if ( ! function_exists( 'set_cookie' ) ) {
    /**
     * set_cookie
     *
     * Accepts seven parameters, or you can submit an associative
     * array in the first parameter containing all the values.
     *
     * @param   mixed  $name
     * @param   string $value     The value of the cookie
     * @param   string $expire    The number of seconds until expiration
     * @param   string $domain    For site-wide cookie.
     *                            Usually: .yourdomain.com
     * @param   string $path      The cookie path
     * @param   string $prefix    The cookie prefix
     * @param   bool   $secure    true makes the cookie secure
     * @param   bool   $httpOnly  true makes the cookie accessible via
     *                            http(s) only (no javascript)
     *
     * @return  void
     */
    function set_cookie(
        $name,
        $value = '',
        $expire = 0,
        $domain = '',
        $path = '/',
        $prefix = '',
        $secure = null,
        $httponly = null
    ) {
        if ( is_array( $name ) ) {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach ( [ 'value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name' ] as $item ) {
                if ( isset( $name[ $item ] ) ) {
                    $$item = $name[ $item ];
                }
            }
        }

        if ( $prefix === '' && function_exists( 'config' ) ) {
            $prefix = config()->offsetGet( 'cookie' )[ 'prefix' ];
        }

        if ( $domain === '' && function_exists( 'config' ) ) {
            $domain = config()->offsetGet( 'cookie' )[ 'domain' ];
        }

        if ( $path === '' && function_exists( 'config' ) ) {
            $path = config()->offsetGet( 'cookie' )[ 'path' ];
        }

        if ( $secure === null && function_exists( 'config' ) ) {
            $secure = config()->offsetGet( 'cookie' )[ 'secure' ];
        }

        if ( $httponly === null && function_exists( 'config' ) ) {
            $httponly = config()->offsetGet( 'cookie' )[ 'httpOnly' ];
        }

        if ( ! is_numeric( $expire ) OR $expire < 0 ) {
            $expire = 1;
        } else {
            $expire = ( $expire > 0 ) ? time() + $expire : 0;
        }

        setcookie( $prefix . $name, $value, $expire, $path, $domain, $secure, $httponly );
    }
}
//--------------------------------------------------------------------
if ( ! function_exists( 'get_cookie' ) ) {
    /**
     * get_cookie
     *
     * Fetch an item from the COOKIE array
     *
     * @param   string $index The cookie index name.
     *
     * @return  mixed Returns FALSE if the cookie is not exists.
     */
    function get_cookie( $index )
    {
        if ( isset( $_COOKIE[ $index ] ) ) {
            return $_COOKIE[ $index ];
        } elseif ( function_exists( 'config' ) ) {
            $prefix = config()->offsetGet( 'cookie' )[ 'prefix' ];
            if ( isset( $_COOKIE[ $prefix . $index ] ) ) {
                return $_COOKIE[ $prefix . $index ];
            }
        }

        return false;
    }
}
//--------------------------------------------------------------------
if ( ! function_exists( 'delete_cookie' ) ) {
    /**
     * delete_cookie
     *
     * Delete a COOKIE
     *
     * @param   mixed  $name   The cookie name.
     * @param   string $domain The cookie domain. Usually: .yourdomain.com
     * @param   string $path   The cookie path
     * @param   string $prefix The cookie prefix
     *
     * @return  void
     */
    function delete_cookie( $name, $domain = '', $path = '/', $prefix = '' )
    {
        set_cookie( $name, '', '', $domain, $path, $prefix );
    }
}