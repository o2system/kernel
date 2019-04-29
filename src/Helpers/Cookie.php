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

if ( ! function_exists('set_cookie')) {
    /**
     * set_cookie
     *
     * Accepts seven parameters, or you can submit an associative
     * array in the first parameter containing all the values.
     *
     * @param   mixed  $name
     * @param   string $value     The value of the cookie
     * @param   int    $expire    The number of seconds until expiration
     * @param   string $domain    For site-wide cookie.
     *                            Usually: .yourdomain.com
     * @param   string $path      The cookie path
     * @param   string $prefix    The cookie prefix
     * @param   bool   $secure    true makes the cookie secure
     * @param   bool   $httpOnly  true makes the cookie accessible via
     *                            http(s) only (no javascript)
     *
     * @return  bool
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
        if (is_array($name)) {
            // always leave 'name' in last place, as the loop will break otherwise, due to $$item
            foreach (['value', 'expire', 'domain', 'path', 'prefix', 'secure', 'httponly', 'name'] as $item) {
                if (isset($name[ $item ])) {
                    $$item = $name[ $item ];
                }
            }
        }

        if ($prefix === '' && function_exists('config')) {
            $prefix = config()->offsetGet('cookie')[ 'prefix' ];
        }

        if ($domain === '' && function_exists('config')) {
            $domain = config()->offsetGet('cookie')[ 'domain' ];
        }

        if ($path === '' && function_exists('config')) {
            $path = config()->offsetGet('cookie')[ 'path' ];
        }

        if ($secure === null && function_exists('config')) {
            $secure = config()->offsetGet('cookie')[ 'secure' ];
        }

        if ($httponly === null && function_exists('config')) {
            $httponly = config()->offsetGet('cookie')[ 'httpOnly' ];
        }

        if ( ! is_numeric($expire) OR $expire < 0) {
            $expire = -1;
        } else {
            $expire = ($expire > 0) ? time() + $expire : -1;
        }

        return setcookie(
            $prefix . $name,
            $value,
            $expire,
            $path,
            '.' . ltrim($domain, '.'),
            $secure,
            $httponly
        );
    }
}
//--------------------------------------------------------------------
if ( ! function_exists('get_cookie')) {
    /**
     * get_cookie
     *
     * Fetch an item from the COOKIE array
     *
     * @param   string $name The cookie index name.
     *
     * @return  mixed Returns FALSE if the cookie is not exists.
     */
    function get_cookie($name)
    {
        if (isset($_COOKIE[ $name ])) {
            return $_COOKIE[ $name ];
        } elseif (function_exists('config')) {
            $prefix = config()->offsetGet('cookie')[ 'prefix' ];
            if (isset($_COOKIE[ $prefix . $name ])) {
                return $_COOKIE[ $prefix . $name ];
            }
        }

        return false;
    }
}

//--------------------------------------------------------------------

if ( ! function_exists('delete_cookie')) {
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
     * @return  bool
     */
    function delete_cookie($name, $domain = '', $path = '/', $prefix = '')
    {
        if ($prefix === '' && function_exists('config')) {
            $prefix = config()->offsetGet('cookie')[ 'prefix' ];
        }

        if (isset($_COOKIE[ $prefix . $name ])) {
            unset($_COOKIE[ $prefix . $name ]);
        }

        return set_cookie($name, '', -1, $domain, $path, $prefix);
    }
}